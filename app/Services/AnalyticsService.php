<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AnalyticsService
{
    /**
     * تتبع حدث
     */
    public function trackEvent($eventType, $userId = null, $data = [])
    {
        return AnalyticsEvent::create([
            'event_type' => $eventType,
            'user_id' => $userId,
            'subject_id' => $data['subject_id'] ?? null,
            'lesson_id' => $data['lesson_id'] ?? null,
            'quiz_id' => $data['quiz_id'] ?? null,
            'question_id' => $data['question_id'] ?? null,
            'metadata' => $data['metadata'] ?? [],
        ]);
    }

    /**
     * تحليلات الطالب
     */
    public function getStudentAnalytics($userId, $period = 'month')
    {
        $cacheKey = "student_analytics_{$userId}_{$period}";
        
        return Cache::remember($cacheKey, 300, function() use ($userId, $period) {
            $user = User::findOrFail($userId);
            $dateRange = $this->getDateRange($period);

            $events = AnalyticsEvent::forUser($userId)
                ->inPeriod($dateRange['start'], $dateRange['end'])
                ->get();

            $eventCounts = $events->groupBy('event_type')->map->count();

            return [
                'total_events' => $events->count(),
                'event_breakdown' => $eventCounts,
                'most_active_day' => $this->getMostActiveDay($events),
                'lessons_viewed' => $events->where('event_type', 'view_lesson')->count(),
                'quizzes_completed' => $events->where('event_type', 'complete_quiz')->count(),
                'activity_timeline' => $this->getActivityTimeline($events, $dateRange),
            ];
        });
    }

    /**
     * تحليلات الكورس
     */
    public function getCourseAnalytics($subjectId, $period = 'month')
    {
        $cacheKey = "course_analytics_{$subjectId}_{$period}";
        
        return Cache::remember($cacheKey, 300, function() use ($subjectId, $period) {
            $subject = Subject::findOrFail($subjectId);
            $dateRange = $this->getDateRange($period);

            $events = AnalyticsEvent::forSubject($subjectId)
                ->inPeriod($dateRange['start'], $dateRange['end'])
                ->get();

            $uniqueStudents = $events->pluck('user_id')->unique()->count();

            return [
                'total_events' => $events->count(),
                'unique_students' => $uniqueStudents,
                'lessons_viewed' => $events->where('event_type', 'view_lesson')->count(),
                'quizzes_completed' => $events->where('event_type', 'complete_quiz')->count(),
                'average_engagement' => $uniqueStudents > 0 ? $events->count() / $uniqueStudents : 0,
                'activity_timeline' => $this->getActivityTimeline($events, $dateRange),
            ];
        });
    }

    /**
     * تحليلات النظام
     */
    public function getSystemAnalytics($period = 'month')
    {
        $cacheKey = "system_analytics_{$period}";
        
        return Cache::remember($cacheKey, 300, function() use ($period) {
            $dateRange = $this->getDateRange($period);

            $events = AnalyticsEvent::inPeriod($dateRange['start'], $dateRange['end'])->get();

            $uniqueUsers = $events->pluck('user_id')->unique()->count();
            
            $dailyActiveUsers = $events->groupBy(function($event) {
                return $event->created_at->format('Y-m-d');
            })->map(function($dayEvents) {
                return $dayEvents->pluck('user_id')->unique()->count();
            });

            return [
                'total_events' => $events->count(),
                'unique_users' => $uniqueUsers,
                'daily_active_users' => $dailyActiveUsers,
                'event_breakdown' => $events->groupBy('event_type')->map->count(),
                'most_active_users' => $this->getMostActiveUsers($events, 10),
            ];
        });
    }

    /**
     * أنماط السلوك
     */
    public function getBehaviorPatterns($userId)
    {
        $events = AnalyticsEvent::forUser($userId)
            ->orderBy('created_at')
            ->get();

        return [
            'preferred_time' => $this->getPreferredTime($events),
            'preferred_day' => $this->getPreferredDay($events),
            'activity_frequency' => $this->getActivityFrequency($events),
            'learning_path' => $this->getLearningPath($events),
        ];
    }

    /**
     * Helper Methods
     */
    protected function getDateRange($period)
    {
        $end = Carbon::now();
        
        switch ($period) {
            case 'week':
                $start = $end->copy()->subWeek();
                break;
            case 'month':
                $start = $end->copy()->subMonth();
                break;
            case 'year':
                $start = $end->copy()->subYear();
                break;
            default:
                $start = $end->copy()->subMonth();
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    protected function getMostActiveDay($events)
    {
        if ($events->isEmpty()) {
            return null;
        }

        $dayCounts = $events->groupBy(function($event) {
            return $event->created_at->format('Y-m-d');
        })->map->count();

        return $dayCounts->sortDesc()->keys()->first();
    }

    protected function getActivityTimeline($events, $dateRange)
    {
        $timeline = [];
        $currentDate = $dateRange['start']->copy();

        while ($currentDate <= $dateRange['end']) {
            $dateStr = $currentDate->format('Y-m-d');
            $dayEvents = $events->filter(function($event) use ($dateStr) {
                return $event->created_at->format('Y-m-d') === $dateStr;
            });

            $timeline[$dateStr] = $dayEvents->count();
            $currentDate->addDay();
        }

        return $timeline;
    }

    protected function getMostActiveUsers($events, $limit = 10)
    {
        return $events->groupBy('user_id')
            ->map->count()
            ->sortDesc()
            ->take($limit)
            ->map(function($count, $userId) {
                $user = User::find($userId);
                return [
                    'user' => $user,
                    'count' => $count,
                ];
            })
            ->values();
    }

    protected function getPreferredTime($events)
    {
        if ($events->isEmpty()) {
            return null;
        }

        $hourCounts = $events->groupBy(function($event) {
            return $event->created_at->format('H');
        })->map->count();

        $preferredHour = $hourCounts->sortDesc()->keys()->first();
        return (int) $preferredHour;
    }

    protected function getPreferredDay($events)
    {
        if ($events->isEmpty()) {
            return null;
        }

        $dayCounts = $events->groupBy(function($event) {
            return $event->created_at->format('l');
        })->map->count();

        return $dayCounts->sortDesc()->keys()->first();
    }

    protected function getActivityFrequency($events)
    {
        if ($events->isEmpty()) {
            return 0;
        }

        $firstEvent = $events->first();
        $lastEvent = $events->last();
        $daysDiff = $firstEvent->created_at->diffInDays($lastEvent->created_at);

        return $daysDiff > 0 ? $events->count() / $daysDiff : $events->count();
    }

    protected function getLearningPath($events)
    {
        $path = [];
        
        foreach ($events as $event) {
            if ($event->lesson_id) {
                $path[] = ['type' => 'lesson', 'id' => $event->lesson_id];
            } elseif ($event->quiz_id) {
                $path[] = ['type' => 'quiz', 'id' => $event->quiz_id];
            }
        }

        return $path;
    }
}

