/**
 * مفاتيح مطابقة عبارات التغذية الراجعة — نسخة JS مطابقة لمنطق
 * App\InteractiveLearning\Support\FeedbackPhrases::normalizeKey/compactKey.
 *
 * الهدف: أن تُطابَق رسالة السؤال المحفوظة مع عبارة لها تسجيل صوتي حتى لو اختلف
 * التشكيل أو صيغة الألف أو المسافات (مثل «لا تتوقف» مقابل «لاتتوقف»).
 * أي تعديل هنا يجب أن يُطبَّق في نسخة PHP أيضاً.
 */

// تشكيل + تطويل + علامات قرآنية
const DIACRITICS = /[ً-ْـٰۖ-ۭ]/g;
// ! ؟ ? . ، , … : - — –
const PUNCTUATION = /[!؟?.،,…:\-—–]/g;
const ALEF_FORMS = /[أإآٱ]/g; // أ إ آ ٱ

export function normalizeKey(text) {
    return String(text ?? '')
        .replace(DIACRITICS, '')
        .replace(ALEF_FORMS, 'ا') // ا
        .replace(/ة/g, 'ه') // ة -> ه
        .replace(/ى/g, 'ي') // ى -> ي
        .replace(PUNCTUATION, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

export function compactKey(text) {
    return normalizeKey(text).replace(/ /g, '');
}

/**
 * ابنِ فهرساً للبحث عن عبارة بنصها: مفتاح عادي + مفتاح بلا مسافات.
 * @param {Array<{text: string, url: string}>} rows
 */
export function buildPhraseIndex(rows) {
    const index = new Map();
    (rows || []).forEach((row) => {
        if (!row || !row.text) return;
        const normal = normalizeKey(row.text);
        const compact = compactKey(row.text);
        if (!index.has(normal)) index.set(normal, row);
        if (!index.has(compact)) index.set(compact, row);
    });
    return index;
}

/** ابحث عن العبارة الموافقة للنص، أو أعد null. */
export function lookupPhrase(index, text) {
    const raw = String(text ?? '').trim();
    if (!raw || !index) return null;
    return index.get(normalizeKey(raw)) || index.get(compactKey(raw)) || null;
}
