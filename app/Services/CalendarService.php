<?php

namespace App\Services;

use App\Models\User;
use App\Models\CalendarEvent;
use App\Models\Quiz;
use App\Models\Assignment;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarService
{
    /**
     * جمع جميع الأحداث للمستخدم
     */
    public function getEventsForUser(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        $events = collect();

        // أحداث التقويم العامة
        $calendarEvents = $this->getCalendarEvents($user, $startDate, $endDate);
        $events = $events->merge($calendarEvents);

        // أحداث الاختبارات
        $quizEvents = $this->getQuizzesEvents($user, $startDate, $endDate);
        $events = $events->merge($quizEvents);

        // أحداث الواجبات
        $assignmentEvents = $this->getAssignmentsEvents($user, $startDate, $endDate);
        $events = $events->merge($assignmentEvents);

        return $events->sortBy('start_date');
    }

    /**
     * جمع أحداث التقويم العامة
     */
    public function getCalendarEvents(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        $query = CalendarEvent::byDateRange($startDate, $endDate);

        // إذا كان طالب، فقط الأحداث العامة أو المرتبطة بمواده/صفه
        if ($user->hasRole('student')) {
            $query->where(function($q) use ($user) {
                $q->where('is_public', true)
                  ->orWhereHas('subject', function($subjectQuery) use ($user) {
                      $subjectQuery->whereHas('students', function($studentQuery) use ($user) {
                          $studentQuery->where('users.id', $user->id);
                      });
                  })
                  ->orWhereHas('class', function($classQuery) use ($user) {
                      $classQuery->whereHas('students', function($studentQuery) use ($user) {
                          $studentQuery->where('users.id', $user->id);
                      });
                  });
            });
        }

        return $query->get()->map(function($event) {
            return [
                'id' => 'calendar_' . $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'start' => $event->start_date->toIso8601String(),
                'end' => $event->end_date ? $event->end_date->toIso8601String() : null,
                'allDay' => $event->is_all_day,
                'color' => $event->getColor(),
                'location' => $event->location,
                'type' => 'calendar_event',
                'event_type' => $event->event_type,
                'event_id' => $event->id,
                'url' => null,
            ];
        });
    }

    /**
     * جمع أحداث الاختبارات
     */
    public function getQuizzesEvents(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        // الحصول على الاختبارات المتاحة للطالب
        $quizzes = Quiz::where('is_published', true)
                      ->where('is_active', true)
                      ->where(function($q) use ($startDate, $endDate) {
                          $q->whereBetween('available_from', [$startDate, $endDate])
                            ->orWhereBetween('available_to', [$startDate, $endDate])
                            ->orWhere(function($q2) use ($startDate, $endDate) {
                                $q2->where('available_from', '<=', $startDate)
                                   ->where('available_to', '>=', $endDate);
                            });
                      })
                      ->whereHas('subject', function($query) use ($user) {
                          $query->whereHas('students', function($studentQuery) use ($user) {
                              $studentQuery->where('users.id', $user->id);
                          });
                      })
                      ->get();

        return $quizzes->map(function($quiz) {
            return [
                'id' => 'quiz_' . $quiz->id,
                'title' => 'اختبار: ' . $quiz->title,
                'description' => $quiz->description,
                'start' => $quiz->available_from->toIso8601String(),
                'end' => $quiz->available_to ? $quiz->available_to->toIso8601String() : null,
                'allDay' => false,
                'color' => '#f59e0b',
                'location' => null,
                'type' => 'quiz',
                'event_type' => 'quiz',
                'event_id' => $quiz->id,
                'url' => route('student.quizzes.show', $quiz->id),
            ];
        });
    }

    /**
     * جمع أحداث الواجبات
     */
    public function getAssignmentsEvents(User $user, Carbon $startDate, Carbon $endDate): Collection
    {
        $assignments = Assignment::where('is_published', true)
                                 ->whereNotNull('due_date')
                                 ->whereBetween('due_date', [$startDate, $endDate])
                                 ->whereHas('assignable', function($query) use ($user) {
                                     // التحقق من أن الطالب مسجل في المادة/الوحدة/الدرس
                                     if ($query->getModel() instanceof \App\Models\Subject) {
                                         $query->whereHas('students', function($studentQuery) use ($user) {
                                             $studentQuery->where('users.id', $user->id);
                                         });
                                     }
                                 })
                                 ->get();

        return $assignments->map(function($assignment) {
            return [
                'id' => 'assignment_' . $assignment->id,
                'title' => 'واجب: ' . $assignment->title,
                'description' => $assignment->description,
                'start' => $assignment->due_date->toIso8601String(),
                'end' => $assignment->due_date->copy()->addHours(1)->toIso8601String(),
                'allDay' => false,
                'color' => '#ef4444',
                'location' => null,
                'type' => 'assignment',
                'event_type' => 'assignment',
                'event_id' => $assignment->id,
                'url' => route('student.assignments.show', $assignment->id),
            ];
        });
    }

    /**
     * تنسيق الأحداث للتقويم
     */
    public function formatEventsForCalendar(Collection $events): array
    {
        return $events->map(function($event) {
            return [
                'id' => $event['id'],
                'title' => $event['title'],
                'start' => $event['start'],
                'end' => $event['end'] ?? null,
                'allDay' => $event['allDay'] ?? false,
                'color' => $event['color'] ?? '#3b82f6',
                'extendedProps' => [
                    'description' => $event['description'] ?? null,
                    'location' => $event['location'] ?? null,
                    'type' => $event['type'] ?? null,
                    'event_type' => $event['event_type'] ?? null,
                    'event_id' => $event['event_id'] ?? null,
                    'url' => $event['url'] ?? null,
                ],
            ];
        })->toArray();
    }

    /**
     * التحقق من تعارض الأحداث
     */
    public function checkEventConflict(CalendarEvent $event): bool
    {
        return CalendarEvent::where('id', '!=', $event->id)
                           ->where(function($q) use ($event) {
                               $q->whereBetween('start_date', [$event->start_date, $event->end_date ?? $event->start_date])
                                 ->orWhereBetween('end_date', [$event->start_date, $event->end_date ?? $event->start_date])
                                 ->orWhere(function($q2) use ($event) {
                                     $q2->where('start_date', '<=', $event->start_date)
                                        ->where('end_date', '>=', $event->end_date ?? $event->start_date);
                                 });
                           })
                           ->exists();
    }
}

