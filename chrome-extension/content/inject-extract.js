/**
 * Injected into ALL frames via chrome.scripting.executeScript.
 * NotebookLM stores quiz JSON on app-root (data-app-data / dataset.appData)
 * inside blob: + usercontent.goog iframes.
 */
(function extractNotebookLmQuizFromFrame() {
  function decodeAppData(raw) {
    if (!raw || typeof raw !== 'string') {
      return null;
    }

    const decoded = raw
      .replace(/&quot;/g, '"')
      .replace(/&#34;/g, '"')
      .replace(/&amp;/g, '&')
      .replace(/&lt;/g, '<')
      .replace(/&gt;/g, '>');

    try {
      return JSON.parse(decoded);
    } catch {
      try {
        return JSON.parse(raw);
      } catch {
        return null;
      }
    }
  }

  function readAppRootData(root) {
    const raw =
      root.getAttribute('data-app-data') ||
      root.dataset?.appData ||
      root.getAttribute('data-app-data-json');

    return raw ? decodeAppData(raw) : null;
  }

  function findQuizPayload(data) {
    if (!data || typeof data !== 'object') {
      return null;
    }

    if (Array.isArray(data.quiz) && data.quiz.length > 0) {
      return data.quiz;
    }

    if (Array.isArray(data.questions) && data.questions.length > 0) {
      return data.questions;
    }

    for (const value of Object.values(data)) {
      if (value && typeof value === 'object') {
        const nested = findQuizPayload(value);
        if (nested) {
          return nested;
        }
      }
    }

    return null;
  }

  const href = window.location.href;
  const isLikelyQuizFrame =
    (href.includes('blob:') && href.includes('usercontent.goog')) ||
    href.includes('notebooklm.google.com') ||
    document.querySelector('app-root[data-app-data], app-root');

  if (!isLikelyQuizFrame) {
    return null;
  }

  const roots = document.querySelectorAll('app-root');
  for (const root of roots) {
    const data = readAppRootData(root);
    const quiz = findQuizPayload(data);
    if (quiz) {
      return { quiz, frameUrl: href };
    }
  }

  return null;
})();
