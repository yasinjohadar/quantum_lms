<?php

namespace App\Services\SMS;

use App\Models\SMSTemplate;
use Illuminate\Support\Str;

class SMSTemplateService
{
    /**
     * Render a template by slug with provided variables.
     */
    public function render(string $templateSlug, array $variables): string
    {
        $template = SMSTemplate::where('slug', $templateSlug)->first();

        if (!$template || !$template->is_active) {
            // Fallback to a generic message
            return 'Notification: ' . json_encode($variables);
        }

        return $template->render($variables);
    }

    /**
     * Get a template by slug.
     */
    public function getTemplate(string $slug): ?SMSTemplate
    {
        return SMSTemplate::where('slug', $slug)->first();
    }

    /**
     * Get default SMS templates.
     */
    public function getDefaultTemplates(): array
    {
        return [
            [
                'name' => 'OTP Verification',
                'slug' => 'otp-verification',
                'body' => 'رمز التحقق الخاص بك هو: {{code}} - صالح لمدة 5 دقائق',
                'variables' => ['code'],
                'is_active' => true,
            ],
            [
                'name' => 'Welcome SMS',
                'slug' => 'welcome-sms',
                'body' => 'مرحباً {{user_name}}! أهلاً بك في {{academy_name}}',
                'variables' => ['user_name', 'academy_name'],
                'is_active' => true,
            ],
            [
                'name' => 'Quiz Reminder',
                'slug' => 'quiz-reminder',
                'body' => 'تذكير: لديك اختبار {{quiz_name}} في {{date}}',
                'variables' => ['quiz_name', 'date'],
                'is_active' => true,
            ],
            [
                'name' => 'Assignment Reminder',
                'slug' => 'assignment-reminder',
                'body' => 'تذكير: آخر موعد لتسليم {{assignment_name}} هو {{due_date}}',
                'variables' => ['assignment_name', 'due_date'],
                'is_active' => true,
            ],
        ];
    }
}

