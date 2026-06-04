/**
 * Maps NotebookLM app-data quiz items to Quantum LMS import format.
 * Used by background service worker (importScripts).
 */
function mapNotebookLmQuizItems(quizItems) {
  if (!Array.isArray(quizItems)) {
    return [];
  }

  return quizItems
    .map((item) => mapOneQuizItem(item))
    .filter((q) => q && Array.isArray(q.options) && q.options.length >= 2);
}

function mapOneQuizItem(item) {
  if (!item || typeof item !== 'object') {
    return null;
  }

  const title = cleanText(
    item.question || item.title || item.text || item.prompt || ''
  );
  if (!title) {
    return null;
  }

  const answerOptions = item.answerOptions || item.options || item.choices || [];
  const options = answerOptions
    .map((opt) => mapOneOption(opt))
    .filter((o) => o && o.text);

  if (options.length < 2) {
    return null;
  }

  const correctCount = options.filter((o) => o.is_correct).length;
  const type =
    options.length === 2 &&
    options.every((o) => /^(true|false|صح|خطأ)$/i.test(o.text))
      ? 'true_false'
      : correctCount > 1
        ? 'multiple_choice'
        : 'single_choice';

  if (type === 'single_choice' && correctCount === 0) {
    options[0].is_correct = true;
  }

  const correctOpt = options.find((o) => o.is_correct);

  return {
    title,
    type,
    content: title,
    explanation:
      cleanText(item.explanation || item.rationale || '') ||
      (correctOpt?.rationale || ''),
    difficulty: 'medium',
    default_points: 1,
    options: options.map(({ text, is_correct }) => ({ text, is_correct })),
  };
}

function mapOneOption(opt) {
  if (typeof opt === 'string') {
    return { text: cleanText(stripHtml(opt)), is_correct: false };
  }
  if (!opt || typeof opt !== 'object') {
    return null;
  }

  let text = opt.text ?? opt.content ?? opt.label ?? opt.answer ?? opt.value ?? '';
  if (text && typeof text === 'object') {
    text = text.raw ?? text.text ?? text.plain ?? '';
  }

  text = cleanText(stripHtml(String(text)));
  if (!text) {
    return null;
  }

  return {
    text,
    is_correct: Boolean(opt.isCorrect ?? opt.is_correct ?? opt.correct),
    rationale: cleanText(opt.rationale || opt.explanation || ''),
  };
}

function stripHtml(value) {
  const tmp = value.replace(/<[^>]+>/g, ' ');
  return tmp.replace(/&nbsp;/gi, ' ').replace(/&amp;/gi, '&');
}

function cleanText(value) {
  return String(value || '')
    .replace(/\s+/g, ' ')
    .trim();
}

if (typeof globalThis !== 'undefined') {
  globalThis.mapNotebookLmQuizItems = mapNotebookLmQuizItems;
}
