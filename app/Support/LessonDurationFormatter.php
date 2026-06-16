<?php

namespace App\Support;

use App\Models\Lesson;
use Illuminate\Support\Collection;

class LessonDurationFormatter
{
    /**
     * @param  iterable<int, Lesson|object{duration?: int|null}>  $lessons
     */
    public static function sumSecondsFromLessons(iterable $lessons): int
    {
        $total = 0;

        foreach ($lessons as $lesson) {
            $total += (int) ($lesson->duration ?? 0);
        }

        return $total;
    }

    /**
     * @param  Collection<int, int|string>|iterable<int, int|string>  $lessonIds
     */
    public static function sumDurationForLessonIds(Collection|iterable $lessonIds): int
    {
        $ids = collect($lessonIds)->unique()->filter()->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        return (int) Lesson::query()->whereIn('id', $ids)->sum('duration');
    }

    public static function formatHoursMinutes(int $seconds): string
    {
        if ($seconds <= 0) {
            return '0 د';
        }

        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        if ($hours > 0) {
            return $minutes > 0 ? "{$hours} س {$minutes} د" : "{$hours} س";
        }

        return "{$minutes} د";
    }
}
