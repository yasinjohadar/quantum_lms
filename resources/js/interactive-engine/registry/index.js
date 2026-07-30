import { trueFalseModule } from '../modules/true_false.js';
import { singleChoiceModule } from '../modules/single_choice.js';
import { multipleChoiceModule } from '../modules/multiple_choice.js';
import { dragDropModule } from '../modules/drag_drop.js';
import { matchingModule } from '../modules/matching.js';
import { orderingModule } from '../modules/ordering.js';
import { fillBlankModule } from '../modules/fill_blank.js';
import { categorizeModule } from '../modules/categorize.js';
import { listenChooseModule } from '../modules/listen_choose.js';
import { connectLinesModule } from '../modules/connect_lines.js';
import { memoryCardsModule } from '../modules/memory_cards.js';
import { hotspotModule } from '../modules/hotspot.js';
import { puzzlePiecesModule } from '../modules/puzzle_pieces.js';
import { numericalModule } from '../modules/numerical.js';
import { shortAnswerModule } from '../modules/short_answer.js';

const modules = new Map();

export const registryMetadata = [
    { type: 'true_false', name: 'صح / خطأ', icon: 'check', color: '#4CAF50', category: 'basic' },
    { type: 'single_choice', name: 'اختيار واحد', icon: 'list-ul', color: '#2196F3', category: 'basic' },
    { type: 'multiple_choice', name: 'اختيار متعدد', icon: 'list-check', color: '#9C27B0', category: 'basic' },
    { type: 'drag_drop', name: 'سحب وإفلات', icon: 'arrows-move', color: '#FF9800', category: 'interactive' },
    { type: 'matching', name: 'مطابقة', icon: 'diagram-3', color: '#00BCD4', category: 'interactive' },
    { type: 'ordering', name: 'ترتيب', icon: 'sort-numeric-down', color: '#00897B', category: 'interactive' },
    { type: 'fill_blank', name: 'ملء فراغ', icon: 'input-cursor-text', color: '#5C6BC0', category: 'interactive' },
    { type: 'categorize', name: 'تصنيف', icon: 'columns-gap', color: '#8D6E63', category: 'interactive' },
    { type: 'listen_choose', name: 'استمع واختر', icon: 'headphones', color: '#7E57C2', category: 'interactive' },
    { type: 'connect_lines', name: 'توصيل', icon: 'bezier2', color: '#26A69A', category: 'interactive' },
    { type: 'memory_cards', name: 'بطاقات ذاكرة', icon: 'grid-3x3', color: '#EC407A', category: 'game' },
    { type: 'hotspot', name: 'نقطة على صورة', icon: 'geo-alt', color: '#EF5350', category: 'interactive' },
    { type: 'puzzle_pieces', name: 'أحجية قطع', icon: 'puzzle', color: '#AB47BC', category: 'game' },
    { type: 'numerical', name: 'إجابة رقمية', icon: '123', color: '#42A5F5', category: 'basic' },
    { type: 'short_answer', name: 'إجابة قصيرة', icon: 'chat-text', color: '#66BB6A', category: 'basic' },
];

export function registerModule(module) {
    modules.set(module.type, module);
}

export function getModule(type) {
    return modules.get(type) || null;
}

export function bootstrapRegistry() {
    [
        trueFalseModule,
        singleChoiceModule,
        multipleChoiceModule,
        dragDropModule,
        matchingModule,
        orderingModule,
        fillBlankModule,
        categorizeModule,
        listenChooseModule,
        connectLinesModule,
        memoryCardsModule,
        hotspotModule,
        puzzlePiecesModule,
        numericalModule,
        shortAnswerModule,
    ].forEach(registerModule);
}
