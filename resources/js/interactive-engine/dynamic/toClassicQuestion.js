import { blocksToPlainText } from './BlockRenderer.js';
import { isMathyLabel } from './mathText.js';

/**
 * Reshape a dynamic question into the classic module shape.
 * @param {object} question
 */
export function toClassicQuestion(question) {
    if (!question) return question;
    if (question.interaction && typeof question.interaction === 'object') {
        const type = question.interaction.type;
        const payload = question.interaction.payload || {};
        const stem =
            question.stem ||
            blocksToPlainText(question.stemBlocks) ||
            '';
        return {
            ...question,
            type,
            payload,
            stem,
        };
    }
    return question;
}

export function isDynamicSchema(schema) {
    return schema?.mode === 'dynamic' || schema?.version === '2.0';
}

export function collectLibraries(schema, question) {
    const libs = new Set();
    (schema?.assets?.libraries || []).forEach((k) => libs.add(k));
    (question?.assets?.libraries || []).forEach((k) => libs.add(k));
    const blocks = question?.stemBlocks || [];
    if (blocks.some((b) => b?.type === 'math')) libs.add('katex');
    if (blocks.some((b) => b?.type === 'scene' || b?.type === 'sticker' || b?.type === 'icon')) {
        libs.add('stickers');
        libs.add('icons');
    }
    const classic = toClassicQuestion(question);
    const options = classic?.payload?.options || [];
    if (options.some((o) => isMathyLabel(o?.latex || o?.math || o?.label))) {
        libs.add('katex');
    }
    return [...libs];
}
