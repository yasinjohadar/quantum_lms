const state = {
  token: null,
  user: null,
  questions: [],
  environments: [],
};

const els = {
  loginSection: document.getElementById('login-section'),
  appSection: document.getElementById('app-section'),
  envSelect: document.getElementById('env-select'),
  email: document.getElementById('email'),
  password: document.getElementById('password'),
  btnLogin: document.getElementById('btn-login'),
  loginStatus: document.getElementById('login-status'),
  userLabel: document.getElementById('user-label'),
  btnLogout: document.getElementById('btn-logout'),
  btnExtract: document.getElementById('btn-extract'),
  btnJson: document.getElementById('btn-json'),
  jsonPanel: document.getElementById('json-panel'),
  jsonInput: document.getElementById('json-input'),
  btnParseJson: document.getElementById('btn-parse-json'),
  classSelect: document.getElementById('class-select'),
  subjectSelect: document.getElementById('subject-select'),
  unitSelect: document.getElementById('unit-select'),
  preview: document.getElementById('preview'),
  btnImport: document.getElementById('btn-import'),
  importStatus: document.getElementById('import-status'),
};

document.addEventListener('DOMContentLoaded', init);

async function init() {
  await loadEnvironments();
  const stored = await storageGet(['qlms_token', 'qlms_environment_id', 'qlms_email']);
  if (stored.qlms_environment_id) {
    els.envSelect.value = stored.qlms_environment_id;
  }
  if (stored.qlms_email) {
    els.email.value = stored.qlms_email;
  }

  if (stored.qlms_token) {
    state.token = stored.qlms_token;
    await showApp();
  }

  els.btnLogin.addEventListener('click', onLogin);
  els.btnLogout.addEventListener('click', onLogout);
  els.btnExtract.addEventListener('click', onExtract);
  els.btnJson.addEventListener('click', () => els.jsonPanel.classList.toggle('hidden'));
  els.btnParseJson.addEventListener('click', onParseJson);
  els.classSelect.addEventListener('change', loadSubjects);
  els.subjectSelect.addEventListener('change', loadUnits);
  els.btnImport.addEventListener('click', onImport);
  els.envSelect.addEventListener('change', () => {
    storageSet({ qlms_environment_id: els.envSelect.value });
  });
}

async function loadEnvironments() {
  const res = await fetch(chrome.runtime.getURL('config/environments.json'));
  const json = await res.json();
  const custom = (await storageGet(['qlms_custom_environments'])).qlms_custom_environments || [];
  state.environments = [...(json.environments || []), ...custom];

  els.envSelect.innerHTML = state.environments
    .map((e) => `<option value="${e.id}">${e.label || e.id}</option>`)
    .join('');
}

function api(payload) {
  return new Promise((resolve, reject) => {
    chrome.runtime.sendMessage({ type: 'QLMS_API', payload }, (response) => {
      if (chrome.runtime.lastError) {
        reject(new Error(chrome.runtime.lastError.message));
        return;
      }
      if (!response?.ok) {
        reject(new Error(response?.error || 'فشل الطلب'));
        return;
      }
      resolve(response.data);
    });
  });
}

async function onLogin() {
  setStatus(els.loginStatus, 'جاري تسجيل الدخول...');
  els.btnLogin.disabled = true;

  try {
    await storageSet({
      qlms_environment_id: els.envSelect.value,
      qlms_email: els.email.value.trim(),
    });

    const data = await api({
      method: 'POST',
      path: '/auth/login',
      auth: false,
      body: {
        email: els.email.value.trim(),
        password: els.password.value,
        device_name: 'chrome-extension',
      },
    });

    state.token = data.token;
    state.user = data.user;
    els.password.value = '';
    await showApp();
    setStatus(els.loginStatus, '', false);
  } catch (error) {
    setStatus(els.loginStatus, error.message, true);
  } finally {
    els.btnLogin.disabled = false;
  }
}

async function onLogout() {
  try {
    await api({ method: 'POST', path: '/auth/logout' });
  } catch {
    /* ignore */
  }
  await storageSet({ qlms_token: null });
  state.token = null;
  state.user = null;
  els.loginSection.classList.remove('hidden');
  els.appSection.classList.add('hidden');
}

async function showApp() {
  els.loginSection.classList.add('hidden');
  els.appSection.classList.remove('hidden');

  try {
    const me = await api({ method: 'GET', path: '/auth/me' });
    state.user = me.user;
    els.userLabel.textContent = state.user?.name || state.user?.email || '';
  } catch (error) {
    setStatus(els.importStatus, error.message, true);
    return;
  }

  await loadClasses();
  await loadSubjects();
}

async function loadClasses() {
  const data = await api({ method: 'GET', path: '/curriculum/classes' });
  els.classSelect.innerHTML =
    '<option value="">— الكل —</option>' +
    (data.data || [])
      .map((c) => `<option value="${c.id}">${escapeHtml(c.name)}</option>`)
      .join('');
}

async function loadSubjects() {
  const classId = els.classSelect.value;
  const query = classId ? `?class_id=${encodeURIComponent(classId)}` : '';
  const data = await api({ method: 'GET', path: `/curriculum/subjects${query}` });
  els.subjectSelect.innerHTML = (data.data || [])
    .map((s) => `<option value="${s.id}">${escapeHtml(s.name)}</option>`)
    .join('');

  if (els.subjectSelect.value) {
    await loadUnits();
  }
}

async function loadUnits() {
  const subjectId = els.subjectSelect.value;
  if (!subjectId) {
    els.unitSelect.innerHTML = '<option value="">— عام —</option>';
    return;
  }

  const data = await api({
    method: 'GET',
    path: `/curriculum/units?subject_id=${encodeURIComponent(subjectId)}`,
  });

  els.unitSelect.innerHTML =
    '<option value="">— عام —</option>' +
    (data.data || [])
      .map((u) => `<option value="${u.id}">${escapeHtml(u.title)}</option>`)
      .join('');
}

function onExtract() {
  setStatus(els.importStatus, 'جاري الاستخراج… (قد يستغرق بضع ثوانٍ)');
  els.btnExtract.disabled = true;
  chrome.runtime.sendMessage({ type: 'QLMS_EXTRACT_PAGE' }, (response) => {
    els.btnExtract.disabled = false;
    if (!response?.ok) {
      setStatus(els.importStatus, response?.error || 'فشل الاستخراج', true);
      return;
    }

    const payload = response.data || {};
    if (payload.error && (!payload.questions || payload.questions.length === 0)) {
      setStatus(els.importStatus, payload.error, true);
      return;
    }

    setQuestions(payload.questions || []);
    let msg = `تم استخراج ${state.questions.length} سؤال`;
    if (payload.source === 'notebooklm_app_data') {
      msg += ' (من بيانات NotebookLM)';
    } else if (payload.paginated) {
      msg += ' (تنقل تلقائي بين الأسئلة)';
    }
    setStatus(els.importStatus, msg, false, true);
  });
}

function onParseJson() {
  try {
    const parsed = JSON.parse(els.jsonInput.value.trim());
    let questions = parsed.questions;

    if (!questions && Array.isArray(parsed.quiz)) {
      questions = parsed.quiz.map((item) => ({
        title: item.question || item.title,
        type: 'single_choice',
        options: (item.answerOptions || item.options || []).map((o) => ({
          text: o.text || o,
          is_correct: Boolean(o.isCorrect ?? o.is_correct),
        })),
        explanation: item.explanation || '',
      }));
    }

    if (!questions) {
      questions = Array.isArray(parsed) ? parsed : null;
    }

    if (!Array.isArray(questions)) {
      throw new Error('صيغة JSON غير صالحة: استخدم questions أو quiz');
    }
    setQuestions(questions);
    setStatus(els.importStatus, `تم تحميل ${state.questions.length} سؤال من JSON`, false, true);
    els.jsonPanel.classList.add('hidden');
  } catch (error) {
    setStatus(els.importStatus, error.message, true);
  }
}

function cleanImportText(value) {
  return String(value || '')
    .replace(/<[^>]+>/g, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function normalizeOptionForImport(opt) {
  if (typeof opt === 'string') {
    const text = cleanImportText(opt);
    return text ? { text, is_correct: false } : null;
  }
  if (!opt || typeof opt !== 'object') {
    return null;
  }
  const raw = opt.text ?? opt.content ?? opt.label ?? opt.answer ?? opt.value ?? '';
  const text = cleanImportText(raw);
  if (!text) {
    return null;
  }
  return {
    text,
    is_correct: Boolean(opt.is_correct ?? opt.isCorrect ?? opt.correct),
  };
}

function sanitizeQuestions(questions) {
  return (questions || [])
    .map((q) => {
      const title = cleanImportText(q.title || q.question || q.text || '');
      const rawOpts = q.options || q.answerOptions || q.choices || [];
      const options = rawOpts.map(normalizeOptionForImport).filter(Boolean);

      if (title.length < 3 || options.length < 2) {
        return null;
      }

      const correctCount = options.filter((o) => o.is_correct).length;
      let type = q.type || 'single_choice';
      if (options.length === 2 && options.every((o) => /^(صح|خطأ|true|false)$/i.test(o.text))) {
        type = 'true_false';
      } else if (correctCount > 1) {
        type = 'multiple_choice';
      }

      if ((type === 'single_choice' || type === 'true_false') && correctCount === 0) {
        options[0].is_correct = true;
      }

      return {
        title,
        type,
        content: cleanImportText(q.content) || title,
        explanation: cleanImportText(q.explanation || q.rationale || ''),
        difficulty: q.difficulty || 'medium',
        default_points: q.default_points || 1,
        options,
      };
    })
    .filter(Boolean);
}

function setQuestions(questions) {
  const valid = sanitizeQuestions(questions);
  const dropped = (questions || []).length - valid.length;

  state.questions = valid;
  els.btnImport.disabled = valid.length === 0;
  renderPreview();

  if (dropped > 0) {
    setStatus(
      els.importStatus,
      `تم تجاهل ${dropped} سؤال بدون خيارات كافية`,
      false,
      valid.length > 0
    );
  }
}

function renderPreview() {
  if (state.questions.length === 0) {
    els.preview.innerHTML = '<p class="preview-meta">لا توجد أسئلة للمعاينة.</p>';
    return;
  }

  els.preview.innerHTML = state.questions
    .slice(0, 20)
    .map((q, i) => {
      const opts = (q.options || []).length;
      return `<div class="preview-item">
        <strong>${i + 1}. ${escapeHtml(q.title || q.question || '')}</strong>
        <span class="preview-meta">${escapeHtml(q.type || 'single_choice')} — ${opts} خيار</span>
      </div>`;
    })
    .join('');

  if (state.questions.length > 20) {
    els.preview.innerHTML += `<p class="preview-meta">+ ${state.questions.length - 20} أسئلة أخرى</p>`;
  }
}

async function onImport() {
  const subjectId = els.subjectSelect.value;
  if (!subjectId) {
    setStatus(els.importStatus, 'اختر المادة أولاً', true);
    return;
  }

  els.btnImport.disabled = true;
  setStatus(els.importStatus, 'جاري الحفظ...');

  const questionsToSend = sanitizeQuestions(state.questions);
  if (questionsToSend.length === 0) {
    setStatus(els.importStatus, 'لا توجد أسئلة صالحة للحفظ (كل سؤال يحتاج عنواناً وخيارين على الأقل).', true);
    els.btnImport.disabled = false;
    return;
  }

  const dropped = state.questions.length - questionsToSend.length;

  try {
    const body = {
      subject_id: Number(subjectId),
      class_id: els.classSelect.value ? Number(els.classSelect.value) : null,
      unit_id: els.unitSelect.value ? Number(els.unitSelect.value) : null,
      questions: questionsToSend,
    };

    const result = await api({
      method: 'POST',
      path: '/questions/import',
      body,
    });

    let msg = result.message || `تم حفظ ${result.imported} سؤال`;
    if (dropped > 0) {
      msg += ` (تجاهل ${dropped} قبل الإرسال)`;
    }
    if (result.skipped > 0) {
      msg += ` (تخطي ${result.skipped} على السيرفر)`;
    }
    const isError = result.imported === 0;
    setStatus(els.importStatus, msg, isError, result.imported > 0);

    if (result.errors?.length) {
      console.warn('Import errors', result.errors);
      const first = result.errors[0]?.message;
      if (first && result.imported > 0) {
        setStatus(els.importStatus, `${msg} — مثال: ${first}`, false, true);
      }
    }
  } catch (error) {
    setStatus(els.importStatus, error.message, true);
  } finally {
    els.btnImport.disabled = state.questions.length === 0;
  }
}

function setStatus(el, text, isError = false, isSuccess = false) {
  el.textContent = text;
  el.classList.toggle('error', Boolean(isError));
  el.classList.toggle('success', Boolean(isSuccess));
}

function storageGet(keys) {
  return new Promise((resolve) => chrome.storage.local.get(keys, resolve));
}

function storageSet(obj) {
  return new Promise((resolve) => chrome.storage.local.set(obj, resolve));
}

function escapeHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
