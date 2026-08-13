<?php

namespace App\InteractiveLearning\Services;

use App\InteractiveLearning\Models\LearningExperience;

/**
 * تصحيح محاولة التجربة التفاعلية على الخادم.
 *
 * المتصفح يُرسل علامته المحسوبة، لكنها **لا تُصدَّق**: هذه الخدمة تعيد التصحيح
 * من إجابات الطالب الخام مقابل المخطط المحفوظ، تماماً كما تفعل الاختبارات
 * العادية (QuizAnswer::autoGrade + QuizAttempt::calculateScore).
 *
 * منطق كل نوع منسوخ عن دالة grade() في وحدة المحرّك المقابلة
 * (resources/js/interactive-engine/modules/*.js) — أي تعديل هناك يجب أن يُطبَّق هنا.
 */
class ExperienceGradingService
{
    /** أنواع تُمنح علامة جزئية بنسبة العناصر الصحيحة. */
    private const PARTIAL_CREDIT_TYPES = ['drag_drop', 'categorize', 'matching', 'connect_lines'];

    /**
     * @param  array<int, mixed>  $answers  صفوف answers القادمة من المشغّل
     * @return array{score: float, total: float, percentage: float, passed: bool, perQuestion: array<int, array<string, mixed>>}
     */
    public function grade(LearningExperience $experience, array $answers): array
    {
        $schema = is_array($experience->schema_json) ? $experience->schema_json : [];
        $questions = is_array($schema['questions'] ?? null) ? $schema['questions'] : [];

        // فهرسة إجابات الطالب بمعرّف السؤال
        $byQuestion = [];
        foreach ($answers as $row) {
            if (! is_array($row)) {
                continue;
            }
            $qid = (string) ($row['questionId'] ?? '');
            if ($qid !== '') {
                $byQuestion[$qid] = $row;
            }
        }

        $score = 0.0;
        $total = 0.0;
        $perQuestion = [];

        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }

            $classic = $this->toClassic($question);
            $qid = (string) ($classic['id'] ?? '');
            $type = (string) ($classic['type'] ?? '');
            $points = $this->toFloat($classic['points'] ?? 1, 1.0);
            $payload = is_array($classic['payload'] ?? null) ? $classic['payload'] : [];

            // المجموع يشمل كل أسئلة المخطط، والسؤال بلا إجابة صفر — مطابقة للمحرّك
            $total += $points;

            $answered = array_key_exists($qid, $byQuestion);
            $studentAnswer = $answered ? ($byQuestion[$qid]['answer'] ?? null) : null;

            $result = $answered
                ? $this->gradeOne($type, $studentAnswer, $payload, $points)
                : ['correct' => false, 'score' => 0.0];

            $score += $result['score'];

            $perQuestion[] = [
                'questionId' => $qid,
                'type' => $type,
                'answered' => $answered,
                'correct' => $result['correct'],
                'score' => $result['score'],
                'max' => $points,
            ];
        }

        $score = round($score, 2);
        $total = round($total, 2);
        $percentage = $total > 0 ? round(($score / $total) * 100, 2) : 0.0;
        $passing = $this->toFloat($experience->passing_score ?? 50, 50.0);

        return [
            'score' => $score,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $total > 0 && $percentage >= $passing,
            'perQuestion' => $perQuestion,
        ];
    }

    /**
     * توحيد شكل السؤال الديناميكي مع الكلاسيكي (مرآة toClassicQuestion.js).
     *
     * @param  array<string, mixed>  $question
     * @return array<string, mixed>
     */
    private function toClassic(array $question): array
    {
        if (is_array($question['interaction'] ?? null)) {
            $question['type'] = $question['interaction']['type'] ?? ($question['type'] ?? '');
            $question['payload'] = is_array($question['interaction']['payload'] ?? null)
                ? $question['interaction']['payload']
                : ($question['payload'] ?? []);
        }

        return $question;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{correct: bool, score: float}
     */
    private function gradeOne(string $type, mixed $answer, array $payload, float $points): array
    {
        if (in_array($type, self::PARTIAL_CREDIT_TYPES, true)) {
            return $this->gradeMap($type, $answer, $payload, $points);
        }

        $correct = match ($type) {
            'single_choice', 'listen_choose', 'hotspot' => $this->matchesId($answer, $payload['correctId'] ?? null),
            'multiple_choice' => $this->matchesIdSet($answer, $payload['correctIds'] ?? []),
            'true_false' => is_bool($answer) && $answer === (bool) ($payload['correct'] ?? false),
            'numerical' => $this->matchesNumber($answer, $payload),
            'short_answer' => $this->matchesAccepted($answer, $this->acceptedAnswers($payload)),
            'ordering', 'puzzle_pieces' => $this->matchesSequence($answer, $payload['correctOrder'] ?? []),
            'fill_blank' => $this->gradeFillBlank($answer, $payload),
            'memory_cards' => $this->gradeMemoryCards($answer, $payload),
            default => false,
        };

        return ['correct' => $correct, 'score' => $correct ? $points : 0.0];
    }

    /**
     * الأنواع الخريطية: علامة جزئية بنسبة المفاتيح الصحيحة (مرآة drag_drop/matching/…).
     *
     * @param  array<string, mixed>  $payload
     * @return array{correct: bool, score: float}
     */
    private function gradeMap(string $type, mixed $answer, array $payload, float $points): array
    {
        $expected = match ($type) {
            'drag_drop' => $payload['assignments'] ?? [],
            'categorize' => $payload['correct'] ?? [],
            default => $payload['pairs'] ?? [], // matching / connect_lines
        };

        if (! is_array($expected) || $expected === []) {
            return ['correct' => false, 'score' => 0.0];
        }

        $given = is_array($answer) ? $answer : [];
        $okCount = 0;
        foreach ($expected as $key => $want) {
            $got = $given[$key] ?? null;
            if ($got !== null && (string) $got === (string) $want) {
                $okCount++;
            }
        }

        // ملاحظة: القسمة في PHP تُرجع int عند القابلية للقسمة (4/4 === int(1))،
        // فمقارنة === 1.0 تفشل. نُجبر float ونقارن بـ >= لتفادي دقّة العشرية.
        $ratio = (float) $okCount / (float) count($expected);

        return [
            'correct' => $ratio >= 1.0,
            'score' => round($ratio * $points, 2),
        ];
    }

    private function matchesId(mixed $answer, mixed $correctId): bool
    {
        if ($answer === null || $correctId === null) {
            return false;
        }

        return (string) $answer === (string) $correctId;
    }

    /** التطابق التام للمجموعة: نفس العدد ونفس العناصر. */
    private function matchesIdSet(mixed $answer, mixed $correctIds): bool
    {
        if (! is_array($answer) || ! is_array($correctIds)) {
            return false;
        }

        $got = array_map('strval', array_values($answer));
        $want = array_map('strval', array_values($correctIds));
        sort($got);
        sort($want);

        return $got === $want;
    }

    private function matchesSequence(mixed $answer, mixed $correctOrder): bool
    {
        if (! is_array($answer) || ! is_array($correctOrder) || $correctOrder === []) {
            return false;
        }

        $got = array_map(fn ($v) => $v === null ? '' : (string) $v, array_values($answer));
        $want = array_map('strval', array_values($correctOrder));

        return $got === $want;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function matchesNumber(mixed $answer, array $payload): bool
    {
        if ($answer === null || $answer === '' || ! is_numeric($answer)) {
            return false;
        }

        $tolerance = $this->toFloat($payload['tolerance'] ?? 0, 0.0);

        return abs((float) $answer - $this->toFloat($payload['correct'] ?? 0, 0.0)) <= $tolerance;
    }

    /**
     * @param  list<string>  $accepted
     */
    private function matchesAccepted(mixed $answer, array $accepted): bool
    {
        $got = mb_strtolower(trim((string) ($answer ?? '')));
        if ($got === '' || $accepted === []) {
            return false;
        }

        return in_array($got, $accepted, true);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function acceptedAnswers(array $payload): array
    {
        $raw = is_array($payload['acceptedAnswers'] ?? null)
            ? $payload['acceptedAnswers']
            : [$payload['correct'] ?? ''];

        $out = [];
        foreach ($raw as $value) {
            $normalized = mb_strtolower(trim((string) $value));
            if ($normalized !== '') {
                $out[] = $normalized;
            }
        }

        return array_values(array_unique($out));
    }

    /**
     * أكمل الفراغ: نمط نصي أو اختيار. في نمط الاختيار قد تكون payload.correct
     * معرّف خيار **أو** نص تسميته (محتوى مولَّد بالذكاء الاصطناعي)، لذا نحل
     * الاثنين كما تفعل وحدة fill_blank.js بالضبط.
     *
     * @param  array<string, mixed>  $payload
     */
    private function gradeFillBlank(mixed $answer, array $payload): bool
    {
        if ((string) ($payload['mode'] ?? 'choice') === 'text') {
            return $this->matchesAccepted($answer, $this->acceptedAnswers($payload));
        }

        $selected = $answer === null ? '' : (string) $answer;
        if ($selected === '') {
            return false;
        }

        $correctRaw = trim((string) ($payload['correct'] ?? ''));
        if ($correctRaw === '') {
            return false;
        }

        $options = is_array($payload['options'] ?? null) ? $payload['options'] : [];

        // 1) القيمة معرّف خيار مباشرةً
        foreach ($options as $option) {
            if (is_array($option) && (string) ($option['id'] ?? '') === $correctRaw) {
                return $selected === $correctRaw;
            }
        }

        // 2) القيمة نص تسمية → نحوّلها لمعرّف
        foreach ($options as $option) {
            if (! is_array($option)) {
                continue;
            }
            $label = trim((string) ($option['label'] ?? ''));
            if ($label === $correctRaw || mb_strtolower($label) === mb_strtolower($correctRaw)) {
                return $selected === (string) ($option['id'] ?? '');
            }
        }

        // 3) لا خيارات معروفة → مقارنة مباشرة
        return $selected === $correctRaw;
    }

    /**
     * بطاقات الذاكرة تُطابَق في المتصفح ولا تُرسل تفاصيل كل زوج، بل {matched,total}
     * فقط (memory_cards.js:137-140). لا يمكن التحقّق منها كاملاً على الخادم، لذا
     * نقبل التقرير مع تقييده بعدد البطاقات في المخطط حتى لا يُرسل رقماً مبالغاً.
     *
     * @param  array<string, mixed>  $payload
     */
    private function gradeMemoryCards(mixed $answer, array $payload): bool
    {
        if (! is_array($answer)) {
            return false;
        }

        $pairs = is_array($payload['pairs'] ?? null) ? $payload['pairs'] : [];
        $left = is_array($payload['left'] ?? null) ? $payload['left'] : [];
        $expectedCards = ($pairs !== [] ? count($pairs) : count($left)) * 2;

        $matched = (int) ($answer['matched'] ?? 0);
        $reportedTotal = (int) ($answer['total'] ?? 0);

        if ($expectedCards > 0 && $reportedTotal !== $expectedCards) {
            return false;
        }

        return $reportedTotal > 0 && $matched >= $reportedTotal;
    }

    private function toFloat(mixed $value, float $default = 0.0): float
    {
        return is_numeric($value) ? (float) $value : $default;
    }
}
