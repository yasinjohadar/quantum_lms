<?php

namespace App\InteractiveLearning\Support;

class QuestionTypeRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function all(): array
    {
        return [
            [
                'type' => 'true_false',
                'name' => 'صح / خطأ',
                'icon' => 'check',
                'color' => '#4CAF50',
                'category' => 'basic',
                'editorComponent' => 'TrueFalseEditor',
                'runtimeModule' => 'true_false',
            ],
            [
                'type' => 'single_choice',
                'name' => 'اختيار واحد',
                'icon' => 'list-ul',
                'color' => '#2196F3',
                'category' => 'basic',
                'editorComponent' => 'SingleChoiceEditor',
                'runtimeModule' => 'single_choice',
            ],
            [
                'type' => 'multiple_choice',
                'name' => 'اختيار متعدد',
                'icon' => 'list-check',
                'color' => '#9C27B0',
                'category' => 'basic',
                'editorComponent' => 'MultipleChoiceEditor',
                'runtimeModule' => 'multiple_choice',
            ],
            [
                'type' => 'drag_drop',
                'name' => 'سحب وإفلات',
                'icon' => 'arrows-move',
                'color' => '#FF9800',
                'category' => 'interactive',
                'editorComponent' => 'DragDropEditor',
                'runtimeModule' => 'drag_drop',
            ],
            [
                'type' => 'matching',
                'name' => 'مطابقة',
                'icon' => 'diagram-3',
                'color' => '#00BCD4',
                'category' => 'interactive',
                'editorComponent' => 'MatchingEditor',
                'runtimeModule' => 'matching',
            ],
            [
                'type' => 'ordering',
                'name' => 'ترتيب',
                'icon' => 'sort-numeric-down',
                'color' => '#00897B',
                'category' => 'interactive',
                'editorComponent' => 'OrderingEditor',
                'runtimeModule' => 'ordering',
            ],
            [
                'type' => 'fill_blank',
                'name' => 'ملء فراغ',
                'icon' => 'input-cursor-text',
                'color' => '#5C6BC0',
                'category' => 'interactive',
                'editorComponent' => 'FillBlankEditor',
                'runtimeModule' => 'fill_blank',
            ],
            [
                'type' => 'categorize',
                'name' => 'تصنيف',
                'icon' => 'columns-gap',
                'color' => '#8D6E63',
                'category' => 'interactive',
                'editorComponent' => 'CategorizeEditor',
                'runtimeModule' => 'categorize',
            ],
            [
                'type' => 'listen_choose',
                'name' => 'استمع واختر',
                'icon' => 'headphones',
                'color' => '#7E57C2',
                'category' => 'interactive',
                'editorComponent' => 'ListenChooseEditor',
                'runtimeModule' => 'listen_choose',
            ],
            [
                'type' => 'connect_lines',
                'name' => 'توصيل',
                'icon' => 'bezier2',
                'color' => '#26A69A',
                'category' => 'interactive',
                'editorComponent' => 'ConnectLinesEditor',
                'runtimeModule' => 'connect_lines',
            ],
            [
                'type' => 'memory_cards',
                'name' => 'بطاقات ذاكرة',
                'icon' => 'grid-3x3',
                'color' => '#EC407A',
                'category' => 'game',
                'editorComponent' => 'MemoryCardsEditor',
                'runtimeModule' => 'memory_cards',
            ],
            [
                'type' => 'hotspot',
                'name' => 'نقطة على صورة',
                'icon' => 'geo-alt',
                'color' => '#EF5350',
                'category' => 'interactive',
                'editorComponent' => 'HotspotEditor',
                'runtimeModule' => 'hotspot',
            ],
            [
                'type' => 'puzzle_pieces',
                'name' => 'أحجية قطع',
                'icon' => 'puzzle',
                'color' => '#AB47BC',
                'category' => 'game',
                'editorComponent' => 'PuzzlePiecesEditor',
                'runtimeModule' => 'puzzle_pieces',
            ],
            [
                'type' => 'numerical',
                'name' => 'إجابة رقمية',
                'icon' => '123',
                'color' => '#42A5F5',
                'category' => 'basic',
                'editorComponent' => 'NumericalEditor',
                'runtimeModule' => 'numerical',
            ],
            [
                'type' => 'short_answer',
                'name' => 'إجابة قصيرة',
                'icon' => 'chat-text',
                'color' => '#66BB6A',
                'category' => 'basic',
                'editorComponent' => 'ShortAnswerEditor',
                'runtimeModule' => 'short_answer',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function keyed(): array
    {
        $out = [];
        foreach (self::all() as $item) {
            $out[$item['type']] = $item;
        }

        return $out;
    }

    public static function has(string $type): bool
    {
        return isset(self::keyed()[$type]);
    }

    /**
     * @return list<string>
     */
    public static function types(): array
    {
        return array_keys(self::keyed());
    }
}
