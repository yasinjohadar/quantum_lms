<?php

namespace App\Services\Email;

use App\Models\EmailTemplate;
use Exception;

class EmailTemplateService
{
    /**
     * Render template with variables
     */
    public function render(string $templateSlug, array $variables = []): ?array
    {
        $template = EmailTemplate::findBySlug($templateSlug);

        if (!$template || !$template->is_active) {
            return null;
        }

        return $template->render($variables);
    }

    /**
     * Get template by slug
     */
    public function getTemplate(string $slug): ?EmailTemplate
    {
        return EmailTemplate::findBySlug($slug);
    }

    /**
     * Get default templates
     */
    public function getDefaultTemplates(): array
    {
        return [
            [
                'name' => 'مرحباً',
                'slug' => 'welcome',
                'subject' => 'مرحباً بك في {{app_name}}',
                'body' => 'مرحباً {{user_name}}،\n\nشكراً لانضمامك إلى {{app_name}}.\n\nنتمنى لك تجربة ممتعة!',
                'variables' => ['user_name', 'app_name'],
                'is_active' => true,
            ],
            [
                'name' => 'إعادة تعيين كلمة المرور',
                'slug' => 'password-reset',
                'subject' => 'إعادة تعيين كلمة المرور',
                'body' => 'مرحباً {{user_name}}،\n\nلقد طلبت إعادة تعيين كلمة المرور.\n\nاضغط على الرابط التالي لإعادة تعيين كلمة المرور:\n{{reset_link}}\n\nإذا لم تطلب هذا، يرجى تجاهل هذه الرسالة.',
                'variables' => ['user_name', 'reset_link'],
                'is_active' => true,
            ],
            [
                'name' => 'إشعار عام',
                'slug' => 'notification',
                'subject' => '{{subject}}',
                'body' => 'مرحباً {{user_name}}،\n\n{{message}}\n\nشكراً لك.',
                'variables' => ['user_name', 'subject', 'message'],
                'is_active' => true,
            ],
            [
                'name' => 'تفعيل الحساب',
                'slug' => 'account-verification',
                'subject' => 'تفعيل حسابك في {{app_name}}',
                'body' => 'مرحباً {{user_name}}،\n\nشكراً لتسجيلك في {{app_name}}.\n\nيرجى الضغط على الرابط التالي لتفعيل حسابك:\n{{verification_link}}\n\nإذا لم تقم بالتسجيل، يرجى تجاهل هذه الرسالة.',
                'variables' => ['user_name', 'app_name', 'verification_link'],
                'is_active' => true,
            ],
        ];
    }
}
