<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

class CertificateTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'name' => 'قالب إكمال كورس - افتراضي',
                'description' => 'قالب افتراضي لشهادة إكمال الكورس',
                'type' => 'course_completion',
                'template_html' => $this->getDefaultCourseCompletionTemplate(),
                'settings' => [
                    'paper_size' => 'a4',
                    'orientation' => 'landscape',
                    'background_color' => '#ffffff',
                    'text_color' => '#000000',
                ],
                'is_active' => true,
                'is_default' => true,
            ],
            [
                'name' => 'قالب التفوق',
                'description' => 'قالب لشهادة التفوق',
                'type' => 'excellence',
                'template_html' => $this->getExcellenceTemplate(),
                'settings' => [
                    'paper_size' => 'a4',
                    'orientation' => 'landscape',
                    'background_color' => '#f8f9fa',
                    'text_color' => '#212529',
                ],
                'is_active' => true,
                'is_default' => false,
            ],
            [
                'name' => 'قالب الحضور',
                'description' => 'قالب لشهادة الحضور',
                'type' => 'attendance',
                'template_html' => $this->getAttendanceTemplate(),
                'settings' => [
                    'paper_size' => 'a4',
                    'orientation' => 'landscape',
                    'background_color' => '#ffffff',
                    'text_color' => '#000000',
                ],
                'is_active' => true,
                'is_default' => false,
            ],
        ];

        foreach ($templates as $template) {
            CertificateTemplate::create($template);
        }
    }

    private function getDefaultCourseCompletionTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .certificate { border: 10px solid #007bff; padding: 40px; background: #fff; }
        .title { font-size: 48px; color: #007bff; margin-bottom: 20px; }
        .subtitle { font-size: 24px; color: #666; margin-bottom: 40px; }
        .name { font-size: 36px; color: #000; margin: 30px 0; font-weight: bold; }
        .text { font-size: 18px; color: #333; margin: 20px 0; }
        .date { font-size: 16px; color: #666; margin-top: 40px; }
        .number { font-size: 14px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="title">شهادة إكمال</div>
        <div class="subtitle">Certificate of Completion</div>
        <div class="text">هذه الشهادة تثبت أن</div>
        <div class="name">{{USER_NAME}}</div>
        <div class="text">قد أكمل بنجاح</div>
        <div class="text" style="font-weight: bold;">{{SUBJECT_NAME}}</div>
        <div class="date">تاريخ الإصدار: {{ISSUED_DATE}}</div>
        <div class="number">رقم الشهادة: {{CERTIFICATE_NUMBER}}</div>
    </div>
</body>
</html>';
    }

    private function getExcellenceTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
        .certificate { border: 15px solid #ffc107; padding: 50px; background: #fff; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .title { font-size: 52px; color: #ffc107; margin-bottom: 20px; font-weight: bold; }
        .subtitle { font-size: 28px; color: #666; margin-bottom: 40px; }
        .name { font-size: 40px; color: #000; margin: 30px 0; font-weight: bold; }
        .text { font-size: 20px; color: #333; margin: 20px 0; }
        .date { font-size: 18px; color: #666; margin-top: 40px; }
        .number { font-size: 16px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="title">شهادة التفوق</div>
        <div class="subtitle">Certificate of Excellence</div>
        <div class="text">هذه الشهادة تثبت تفوق</div>
        <div class="name">{{USER_NAME}}</div>
        <div class="text">في</div>
        <div class="text" style="font-weight: bold; font-size: 24px;">{{SUBJECT_NAME}}</div>
        <div class="date">تاريخ الإصدار: {{ISSUED_DATE}}</div>
        <div class="number">رقم الشهادة: {{CERTIFICATE_NUMBER}}</div>
    </div>
</body>
</html>';
    }

    private function getAttendanceTemplate(): string
    {
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .certificate { border: 10px solid #28a745; padding: 40px; background: #fff; }
        .title { font-size: 48px; color: #28a745; margin-bottom: 20px; }
        .subtitle { font-size: 24px; color: #666; margin-bottom: 40px; }
        .name { font-size: 36px; color: #000; margin: 30px 0; font-weight: bold; }
        .text { font-size: 18px; color: #333; margin: 20px 0; }
        .date { font-size: 16px; color: #666; margin-top: 40px; }
        .number { font-size: 14px; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="title">شهادة حضور</div>
        <div class="subtitle">Certificate of Attendance</div>
        <div class="text">هذه الشهادة تثبت حضور</div>
        <div class="name">{{USER_NAME}}</div>
        <div class="text">في</div>
        <div class="text" style="font-weight: bold;">{{SUBJECT_NAME}}</div>
        <div class="date">تاريخ الإصدار: {{ISSUED_DATE}}</div>
        <div class="number">رقم الشهادة: {{CERTIFICATE_NUMBER}}</div>
    </div>
</body>
</html>';
    }
}

