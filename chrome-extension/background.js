importScripts('content/quiz-mapper.js');

const STORAGE_KEYS = {
  token: 'qlms_token',
  environmentId: 'qlms_environment_id',
  customEnvironments: 'qlms_custom_environments',
};

chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
  if (message?.type === 'QLMS_API') {
    handleApiRequest(message.payload)
      .then((data) => sendResponse({ ok: true, data }))
      .catch((error) => sendResponse({ ok: false, error: error.message || String(error) }));

    return true;
  }

  if (message?.type === 'QLMS_EXTRACT_PAGE') {
    chrome.tabs.query({ active: true, currentWindow: true }, async (tabs) => {
      const tab = tabs[0];
      if (!tab?.id) {
        sendResponse({ ok: false, error: 'لا يوجد تبويب نشط.' });
        return;
      }

      if (!tab.url || !tab.url.includes('notebooklm.google.com')) {
        sendResponse({
          ok: false,
          error: 'افتح NotebookLM (تبويب Quiz) ثم أعد المحاولة.',
        });
        return;
      }

      try {
        const data = await extractQuizFromTab(tab.id);
        sendResponse({ ok: true, data });
      } catch (error) {
        sendResponse({
          ok: false,
          error: error.message || 'فشل الاستخراج.',
        });
      }
    });

    return true;
  }
});

async function extractQuizFromTab(tabId) {
  const fromAppData = await extractViaScriptInjection(tabId);
  if (fromAppData?.questions?.length) {
    return fromAppData;
  }

  const fromDom = await extractViaContentScriptMessages(tabId);
  if (fromDom?.questions?.length) {
    return fromDom;
  }

  return {
    source: 'notebooklm_dom',
    questions: [],
    error:
      'لم يُعثر على أسئلة. افتح بطاقة Quiz في Studio (انقر عليها حتى تظهر الأسئلة)، ثم أعد الاستخراج أو استخدم «لصق JSON».',
  };
}

async function extractViaScriptInjection(tabId) {
  let results = [];

  try {
    results = await chrome.scripting.executeScript({
      target: { tabId, allFrames: true },
      files: ['content/inject-extract.js'],
    });
  } catch (error) {
    console.warn('QLMS inject-extract failed', error);
    return null;
  }

  for (const entry of results) {
    const payload = entry?.result;
    if (!payload?.quiz?.length) {
      continue;
    }

    const questions = mapNotebookLmQuizItems(payload.quiz);
    if (questions.length > 0) {
      return {
        source: 'notebooklm_app_data',
        questions,
        frameUrl: payload.frameUrl,
      };
    }
  }

  return null;
}

async function extractViaContentScriptMessages(tabId) {
  const frames = await getAllFrames(tabId);
  const merged = {
    source: 'notebooklm_dom',
    questions: [],
    paginated: false,
  };

  for (const frame of frames) {
    try {
      const result = await sendMessageToFrame(tabId, frame.frameId, {
        type: 'QLMS_EXTRACT',
        paginate: true,
      });

      if (result?.questions?.length) {
        merged.questions = dedupeQuestions(merged.questions.concat(result.questions));
        if (result.paginated) {
          merged.paginated = true;
        }
      }
    } catch {
      /* ignore */
    }
  }

  return merged.questions.length > 0 ? merged : null;
}

function getAllFrames(tabId) {
  return new Promise((resolve) => {
    if (!chrome.webNavigation?.getAllFrames) {
      resolve([{ frameId: 0 }]);
      return;
    }

    chrome.webNavigation.getAllFrames({ tabId }, (frames) => {
      resolve(frames && frames.length > 0 ? frames : [{ frameId: 0 }]);
    });
  });
}

function sendMessageToFrame(tabId, frameId, message) {
  return new Promise((resolve, reject) => {
    chrome.tabs.sendMessage(tabId, message, { frameId }, (result) => {
      if (chrome.runtime.lastError) {
        reject(new Error(chrome.runtime.lastError.message));
        return;
      }
      resolve(result);
    });
  });
}

function dedupeQuestions(questions) {
  const byTitle = new Map();

  for (const q of questions) {
    const key = (q.title || q.question || '').trim();
    if (!key) {
      continue;
    }

    const optCount = (q.options || []).length;
    const existing = byTitle.get(key);

    if (!existing || optCount > (existing.options || []).length) {
      byTitle.set(key, q);
    }
  }

  return Array.from(byTitle.values());
}

async function getStorage(keys) {
  return new Promise((resolve) => {
    chrome.storage.local.get(keys, resolve);
  });
}

async function resolveApiBase() {
  const stored = await getStorage([
    STORAGE_KEYS.environmentId,
    STORAGE_KEYS.customEnvironments,
  ]);

  const envId = stored[STORAGE_KEYS.environmentId] || 'local';
  const custom = stored[STORAGE_KEYS.customEnvironments] || [];

  let defaults = [];
  try {
    const res = await fetch(chrome.runtime.getURL('config/environments.json'));
    const json = await res.json();
    defaults = json.environments || [];
  } catch {
    defaults = [
      {
        id: 'local',
        apiBase: 'http://127.0.0.1:8000/api/v1/extension',
      },
    ];
  }

  const all = [...defaults, ...custom];
  const match = all.find((e) => e.id === envId) || all[0];

  return (match?.apiBase || '').replace(/\/$/, '');
}

async function handleApiRequest({ method, path, body, auth = true }) {
  const apiBase = await resolveApiBase();
  if (!apiBase) {
    throw new Error('لم يتم ضبط عنوان API.');
  }

  const headers = {
    Accept: 'application/json',
    'Content-Type': 'application/json',
  };

  if (auth) {
    const stored = await getStorage([STORAGE_KEYS.token]);
    const token = stored[STORAGE_KEYS.token];
    if (!token) {
      throw new Error('سجّل الدخول أولاً.');
    }
    headers.Authorization = `Bearer ${token}`;
  }

  const response = await fetch(`${apiBase}${path}`, {
    method: method || 'GET',
    headers,
    body: body ? JSON.stringify(body) : undefined,
  });

  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const message = data.message || data.error || `خطأ ${response.status}`;
    throw new Error(message);
  }

  if (path === '/auth/login' && data.token) {
    await new Promise((resolve) => {
      chrome.storage.local.set({ [STORAGE_KEYS.token]: data.token }, resolve);
    });
  }

  return data;
}
