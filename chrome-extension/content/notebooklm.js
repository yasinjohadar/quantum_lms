(function () {
  const SOURCE = 'notebooklm_dom';
  const MAX_PAGINATION_STEPS = 30;
  const PAGINATION_DELAY_MS = 450;

  chrome.runtime.onMessage.addListener((message, _sender, sendResponse) => {
    if (message?.type === 'QLMS_EXTRACT') {
      extractQuizPayloadAsync(Boolean(message.paginate))
        .then(sendResponse)
        .catch((error) =>
          sendResponse({
            source: SOURCE,
            questions: [],
            error: error.message || String(error),
          })
        );
      return true;
    }
  });

  async function extractQuizPayloadAsync(paginate = true) {
    let questions = [];

    questions = mergeUnique(questions, extractFromAppData(document));
    questions = mergeUnique(questions, extractFromEmbeddedJson());

    const panelQuestions = paginate
      ? await extractQuizPanelWithPagination(document)
      : extractFromQuizPanel(document);

    questions = mergeUnique(questions, panelQuestions);

    if (questions.length === 0) {
      questions = mergeUnique(questions, extractFromCardHeuristic());
    }

    if (questions.length === 0) {
      return {
        source: SOURCE,
        questions: [],
        error:
          'لم يُعثر على أسئلة. تأكد أن لوحة Quiz (Studio) ظاهرة، أو استخدم «لصق JSON».',
      };
    }

    return {
      source: SOURCE,
      questions,
      paginated: paginate && panelQuestions.length > 1,
    };
  }

  async function extractQuizPanelWithPagination(doc) {
    const collected = [];
    const seen = new Set();

    for (let step = 0; step < MAX_PAGINATION_STEPS; step++) {
      const batch = extractFromQuizPanel(doc);
      for (const q of batch) {
        const key = questionKey(q);
        if (!seen.has(key)) {
          seen.add(key);
          collected.push(q);
        }
      }

      const nextBtn = findNextQuestionButton(doc);
      if (!nextBtn || nextBtn.disabled || nextBtn.getAttribute('aria-disabled') === 'true') {
        break;
      }

      const before = doc.body?.innerText?.slice(0, 500) || '';
      nextBtn.click();
      await sleep(PAGINATION_DELAY_MS);
      const after = doc.body?.innerText?.slice(0, 500) || '';
      if (before === after) {
        break;
      }
    }

    return collected;
  }

  function extractFromQuizPanel(doc) {
    const results = [];

    const headerNodes = findQuestionHeaderElements(doc);
    if (headerNodes.length > 0) {
      for (const header of headerNodes) {
        const root = findQuizContainer(header);
        const parsed = parseQuizContainer(root, header);
        if (parsed) {
          results.push(parsed);
        }
      }
      return results;
    }

    const single = parseQuizContainer(doc.body, null);
    return single ? [single] : [];
  }

  function findQuestionHeaderElements(doc) {
    const headers = [];
    const walker = doc.createTreeWalker(doc.body, NodeFilter.SHOW_TEXT);

    while (walker.nextNode()) {
      const text = cleanText(walker.currentNode.textContent);
      if (/^question\s+\d+\s+of\s+\d+$/i.test(text) || /^سؤال\s+\d+\s+من\s+\d+$/i.test(text)) {
        const el = walker.currentNode.parentElement;
        if (el && !headers.includes(el)) {
          headers.push(el);
        }
      }
    }

    return headers;
  }

  function findQuizContainer(startEl) {
    let node = startEl;
    for (let depth = 0; depth < 18 && node; depth++) {
      if (countMcqOptions(node) >= 2) {
        return node;
      }
      node = node.parentElement;
    }
    return startEl?.parentElement || startEl || document.body;
  }

  function countMcqOptions(root) {
    if (!root) {
      return 0;
    }
    let count = 0;
    root.querySelectorAll('*').forEach((el) => {
      if (el.children.length > 4) {
        return;
      }
      const line = firstLine(el.innerText);
      if (/^[A-D][\.\):]\s+\S/i.test(line)) {
        count++;
      }
    });
    return count;
  }

  function parseQuizContainer(root, headerEl) {
    if (!root) {
      return null;
    }

    const title = extractQuestionTitle(root, headerEl);
    const options = extractMcqOptions(root);

    if (!title || options.length < 2) {
      return null;
    }

    return {
      title,
      type: inferType(options),
      explanation: extractQuizExplanation(root),
      difficulty: 'medium',
      default_points: 1,
      options,
    };
  }

  function extractQuestionTitle(root, headerEl) {
    const lines = (root.innerText || '').split('\n').map(cleanText).filter(Boolean);
    const candidates = [];

    let afterHeader = !headerEl;
    for (const line of lines) {
      if (headerEl && line.match(/^question\s+\d+\s+of\s+\d+$/i)) {
        afterHeader = true;
        continue;
      }
      if (!afterHeader) {
        continue;
      }
      if (/^[A-D][\.\):]\s/i.test(line)) {
        break;
      }
      if (/^(right answer|correct|hint|explanation|الإجابة)/i.test(line)) {
        continue;
      }
      if (/^question\s+\d+\s+of\s+\d+$/i.test(line)) {
        continue;
      }
      if (line.length >= 15) {
        candidates.push(line);
      }
    }

    if (candidates.length > 0) {
      return candidates.sort((a, b) => b.length - a.length)[0];
    }

    const blocks = root.querySelectorAll('p, div, span, h2, h3, h4');
    for (const block of blocks) {
      if (headerEl && (block === headerEl || block.contains(headerEl))) {
        continue;
      }
      const text = cleanText(block.innerText);
      if (text.length < 15) {
        continue;
      }
      if (/^[A-D][\.\):]/i.test(text)) {
        continue;
      }
      if (/right\s*answer|hyperlink\s*quiz/i.test(text)) {
        continue;
      }
      return text.split('\n')[0];
    }

    return '';
  }

  function extractMcqOptions(root) {
    const byLetter = new Map();

    root.querySelectorAll('*').forEach((el) => {
      if (el.children.length > 6) {
        return;
      }

      const line = firstLine(el.innerText);
      const match = line.match(/^([A-D])[\.\):]\s*(.+)$/i);
      if (!match) {
        return;
      }

      const letter = match[1].toUpperCase();
      let text = cleanText(match[2]);
      if (!text || text.length > 200) {
        return;
      }

      if (/^(right answer|correct answer)$/i.test(text)) {
        return;
      }

      const isCorrect = isCorrectOptionElement(el);
      const existing = byLetter.get(letter);

      if (!existing || (isCorrect && !existing.is_correct)) {
        byLetter.set(letter, { text, is_correct: isCorrect });
      }
    });

    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];
    const options = letters
      .filter((l) => byLetter.has(l))
      .map((l) => byLetter.get(l));

    if (options.length >= 2 && !options.some((o) => o.is_correct)) {
      const rightEl = findRightAnswerElement(root);
      if (rightEl) {
        const rightLine = firstLine(rightEl.innerText);
        const m = rightLine.match(/^([A-D])[\.\):]/i);
        if (m) {
          const idx = options.findIndex((o) =>
            rightLine.toLowerCase().includes(o.text.toLowerCase().slice(0, 12))
          );
          if (idx >= 0) {
            options[idx].is_correct = true;
          } else {
            const letter = m[1].toUpperCase();
            const opt = byLetter.get(letter);
            if (opt) {
              opt.is_correct = true;
            }
          }
        }
      }
    }

    if (options.length >= 2 && !options.some((o) => o.is_correct)) {
      options[0].is_correct = true;
    }

    return options;
  }

  function isCorrectOptionElement(el) {
    let node = el;
    for (let i = 0; i < 8 && node; i++) {
      const text = (node.innerText || '').slice(0, 400);
      if (/right\s*answer|correct\s*answer|الإجابة\s*الصحيحة/i.test(text)) {
        return true;
      }

      const cls = (node.className || '').toString();
      if (/correct|right|success|selected/i.test(cls)) {
        return true;
      }

      try {
        const style = getComputedStyle(node);
        const border = style.borderColor || '';
        const bg = style.backgroundColor || '';
        if (/rgb\(\s*\d+,\s*1\d{2,},\s*\d+/.test(border + bg)) {
          const nums = (border + bg).match(/\d+/g) || [];
          if (nums.length >= 3) {
            const g = parseInt(nums[1], 10);
            if (g > 100) {
              return true;
            }
          }
        }
      } catch {
        /* ignore */
      }

      node = node.parentElement;
    }

    return false;
  }

  function findRightAnswerElement(root) {
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) {
      if (/right\s*answer|correct\s*answer|الإجابة\s*الصحيحة/i.test(walker.currentNode.textContent)) {
        return walker.currentNode.parentElement;
      }
    }
    return null;
  }

  function findNextQuestionButton(doc) {
    const selectors = [
      'button[aria-label*="Next" i]',
      'button[aria-label*="التالي" i]',
      'button[aria-label*="next question" i]',
      '[role="button"][aria-label*="Next" i]',
    ];

    for (const sel of selectors) {
      const btn = doc.querySelector(sel);
      if (btn) {
        return btn;
      }
    }

    const buttons = doc.querySelectorAll('button, [role="button"]');
    for (const btn of buttons) {
      const label = (
        btn.getAttribute('aria-label') ||
        btn.getAttribute('title') ||
        btn.innerText ||
        ''
      ).trim();
      if (/^(next|التالي|→|›|»)$/i.test(label) || /^next\s*question$/i.test(label)) {
        return btn;
      }
    }

    return null;
  }

  function extractQuizExplanation(root) {
    const right = findRightAnswerElement(root);
    if (!right) {
      return '';
    }
    const block = right.closest('div, section, article, li') || right.parentElement;
    const text = cleanText(block?.innerText || '');
    const parts = text.split(/right\s*answer/i);
    return parts.length > 1 ? cleanText(parts[1]).slice(0, 500) : '';
  }

  function extractFromAppData(doc) {
    const results = [];
    doc.querySelectorAll('[data-app-data], app-root[data-app-data]').forEach((node) => {
      try {
        const raw = node.getAttribute('data-app-data');
        if (!raw) {
          return;
        }
        const data = JSON.parse(raw);
        collectQuestionsFromUnknownJson(data, results);
      } catch {
        /* ignore */
      }
    });
    return results.map(normalizeQuestionObject).filter(Boolean);
  }

  function collectQuestionsFromUnknownJson(data, out) {
    if (!data) {
      return;
    }
    if (Array.isArray(data)) {
      data.forEach((item) => collectQuestionsFromUnknownJson(item, out));
      return;
    }
    if (typeof data !== 'object') {
      return;
    }

    if (Array.isArray(data.questions)) {
      out.push(...data.questions);
    }
    if (Array.isArray(data.quizQuestions)) {
      out.push(...data.quizQuestions);
    }
    if (data.question || data.title || data.prompt) {
      out.push(data);
    }

    Object.values(data).forEach((val) => {
      if (val && typeof val === 'object') {
        collectQuestionsFromUnknownJson(val, out);
      }
    });
  }

  function extractFromEmbeddedJson() {
    const scripts = document.querySelectorAll('script');
    for (const script of scripts) {
      const text = script.textContent || '';
      if (!/quiz|question|flashcard/i.test(text)) {
        continue;
      }

      const arrays = findJsonArrays(text);
      for (const arr of arrays) {
        const mapped = arr.map(mapRawQuestion).filter(Boolean);
        if (mapped.length > 0) {
          return mapped;
        }
      }
    }

    return [];
  }

  function findJsonArrays(text) {
    const results = [];
    const patterns = [
      /"questions"\s*:\s*(\[[\s\S]*?\])/i,
      /"quizQuestions"\s*:\s*(\[[\s\S]*?\])/i,
    ];

    for (const pattern of patterns) {
      const match = text.match(pattern);
      if (!match) {
        continue;
      }
      try {
        const parsed = JSON.parse(match[1]);
        if (Array.isArray(parsed) && parsed.length > 0) {
          results.push(parsed);
        }
      } catch {
        /* ignore */
      }
    }

    return results;
  }

  function extractFromCardHeuristic() {
    const selectors = ['[role="listitem"]', 'article', '[class*="quiz" i]'];
    const seen = new Set();
    const cards = [];

    for (const selector of selectors) {
      document.querySelectorAll(selector).forEach((el) => {
        if (seen.has(el)) {
          return;
        }
        const options = extractMcqOptions(el);
        const title = extractQuestionTitle(el, null);
        if (title && options.length >= 2) {
          seen.add(el);
          cards.push({ title, options, el });
        }
      });
    }

    return cards.map(({ title, options }) => ({
      title,
      type: inferType(options),
      explanation: '',
      difficulty: 'medium',
      default_points: 1,
      options,
    }));
  }

  function normalizeQuestionObject(raw) {
    return mapRawQuestion(raw);
  }

  function mapRawQuestion(raw) {
    if (!raw || typeof raw !== 'object') {
      return null;
    }

    const title = cleanText(
      raw.title || raw.question || raw.text || raw.prompt || raw.stem || ''
    );
    if (!title) {
      return null;
    }

    let options = [];
    const rawOptions = raw.options || raw.choices || raw.answers || [];

    if (Array.isArray(rawOptions)) {
      options = rawOptions
        .map((opt) => {
          if (typeof opt === 'string') {
            const m = opt.match(/^([A-D])[\.\):]\s*(.+)$/i);
            return {
              text: cleanText(m ? m[2] : opt),
              is_correct: false,
            };
          }
          return {
            text: cleanText(opt.text || opt.content || opt.label || opt.answer || ''),
            is_correct: Boolean(opt.is_correct ?? opt.correct ?? opt.isCorrect),
          };
        })
        .filter((o) => o.text);
    }

    return {
      title,
      type: raw.type || inferType(options),
      explanation: cleanText(raw.explanation || raw.rationale || ''),
      difficulty: raw.difficulty || 'medium',
      default_points: raw.points || raw.default_points || 1,
      options,
    };
  }

  function inferType(options) {
    if (options.length === 2 && options.every((o) => /^(صح|خطأ|true|false)$/i.test(o.text))) {
      return 'true_false';
    }
    const correctCount = options.filter((o) => o.is_correct).length;
    if (correctCount > 1) {
      return 'multiple_choice';
    }
    return 'single_choice';
  }

  function mergeUnique(existing, incoming) {
    const seen = new Set(existing.map(questionKey));
    const merged = [...existing];
    for (const q of incoming) {
      const key = questionKey(q);
      if (!seen.has(key)) {
        seen.add(key);
        merged.push(q);
      }
    }
    return merged;
  }

  function questionKey(q) {
    return `${q.title}::${(q.options || []).map((o) => o.text).join('|')}`;
  }

  function firstLine(text) {
    return cleanText(String(text || '').split('\n')[0]);
  }

  function cleanText(value) {
    return String(value || '')
      .replace(/\s+/g, ' ')
      .trim();
  }

  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }
})();
