/** @typedef {{ type: string, beforeMount?: Function, mount: Function, afterMount?: Function, beforeDestroy?: Function, destroy: Function, getAnswer: Function, grade: Function }} QuestionModule */

export const ENGINE_VERSION = '1.1';

export const STANDARD_EVENTS = [
    'session.started',
    'session.completed',
    'question.enter',
    'question.leave',
    'question.correct',
    'question.wrong',
    'hint.opened',
    'answer.changed',
    'timer.expired',
    'result.sent',
];

export class EventBus {
    constructor() {
        this.listeners = new Map();
    }

    on(event, handler) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, new Set());
        }
        this.listeners.get(event).add(handler);
        return () => this.off(event, handler);
    }

    off(event, handler) {
        this.listeners.get(event)?.delete(handler);
    }

    emit(event, payload = {}) {
        this.listeners.get(event)?.forEach((handler) => {
            try {
                handler(payload);
            } catch (e) {
                console.error('[ILE EventBus]', event, e);
            }
        });
    }
}
