<?php

namespace App\Services\WhatsApp;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Enrollment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BroadcastWhatsAppMessage
{
    protected SendWhatsAppMessage $sendService;

    public function __construct(SendWhatsAppMessage $sendService)
    {
        $this->sendService = $sendService;
    }

    /**
     * Get students by criteria
     */
    public function getStudentsByCriteria(?int $classId = null, ?int $subjectId = null): Collection
    {
        $query = User::role('student')
            ->whereNotNull('phone')
            ->where('phone', '!=', '')
            ->where('is_active', true);

        if ($subjectId) {
            // Get students enrolled in the subject (active enrollments only)
            $query->whereHas('enrollments', function ($q) use ($subjectId) {
                $q->where('subject_id', $subjectId)
                  ->where('status', 'active');
            });
        } elseif ($classId) {
            // Get students enrolled in any subject of this class
            $query->whereHas('enrollments', function ($q) use ($classId) {
                $q->where('status', 'active')
                  ->whereHas('subject', function ($sq) use ($classId) {
                      $sq->where('class_id', $classId);
                  });
            });
        }

        // Filter by valid phone format (E.164 format)
        return $query->get()->filter(function ($user) {
            return preg_match('/^\+[1-9][0-9]{1,14}$/', $user->phone);
        })->values();
    }

    /**
     * Replace placeholders in message template
     */
    public function replacePlaceholders(
        string $template,
        User $student,
        ?Subject $subject = null,
        ?SchoolClass $class = null
    ): string {
        $variables = $this->buildTemplateVariables($student, $subject, $class);

        // Supports both modern {{variable}} and legacy {variable} placeholders.
        foreach ($variables as $key => $value) {
            $template = str_replace('{{' . $key . '}}', (string) ($value ?? ''), $template);
            $template = str_replace('{' . $key . '}', (string) ($value ?? ''), $template);
        }

        return $template;
    }

    public function buildTemplateVariables(
        User $student,
        ?Subject $subject = null,
        ?SchoolClass $class = null
    ): array {
        if (!$subject && $class) {
            $enrollment = $student->enrollments()
                ->with('subject')
                ->whereHas('subject', function ($q) use ($class) {
                    $q->where('class_id', $class->id);
                })
                ->active()
                ->first();

            if ($enrollment && $enrollment->subject) {
                $subject = $enrollment->subject;
            }
        } elseif (!$subject) {
            $enrollment = $student->enrollments()->with('subject')->active()->first();
            if ($enrollment && $enrollment->subject) {
                $subject = $enrollment->subject;
            }
        }

        if ($subject && !$class && $subject->schoolClass) {
            $class = $subject->schoolClass;
        }

        return [
            'student_name' => $student->name ?? '',
            'student_email' => $student->email ?? '',
            'student_phone' => $student->phone ?? '',
            'subject_name' => $subject?->name ?? '',
            'class_name' => $class?->name ?? '',
        ];
    }
}

