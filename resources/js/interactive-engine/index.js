import './styles.css';
import { bootstrapRegistry } from './registry/index.js';
import { QuizSession } from './session/QuizSession.js';
import { ENGINE_VERSION } from './core/event-bus.js';

bootstrapRegistry();

function boot() {
    const root = document.getElementById('ile-root');
    const schema = window.__interactiveSchema;
    const config = window.__interactiveConfig || {};

    if (!root || !schema) {
        console.error('[ILE] missing root or schema');
        return;
    }

    if (!Array.isArray(schema.questions) || schema.questions.length === 0) {
        root.innerHTML = '<p style="padding:2rem;text-align:center">لا توجد أسئلة في هذه التجربة.</p>';
        return;
    }

    const session = new QuizSession(schema, {
        ...config,
        engineVersion: config.engineVersion || ENGINE_VERSION,
    });
    session.start(root);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
