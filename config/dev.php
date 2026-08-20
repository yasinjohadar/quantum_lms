<?php

/*
|--------------------------------------------------------------------------
| إعدادات أدوات التطوير (Development Helpers)
|--------------------------------------------------------------------------
|
| هذه الإعدادات خاصة ببيئة التطوير فقط (local / development).
| بوابة الدخول السريع تُعطَّل تلقائياً في بيئة الإنتاج مهما كانت القيم هنا.
|
*/

return [

    // تشغيل/تعطيل بوابة الدخول السريع للمطورين (تعمل فقط في بيئة التطوير)
    'quick_login' => env('DEV_QUICK_LOGIN', true),

    // كلمة المرور الافتراضية لحسابات التطوير
    'password' => env('DEV_QUICK_LOGIN_PASSWORD', '123456789'),

    /*
    | الحسابات التجريبية.
    | key   : المعرف المستخدم في الرابط /dev/login/{key}
    | role  : الدور الذي سيُسند للمستخدم (يُنشأ إن لم يكن موجوداً)
    | تُنشأ هذه الحسابات عبر: php artisan db:seed --class=DevAccountsSeeder
    */
    'accounts' => [
        [
            'key' => 'admin',
            'label' => 'مدير النظام',
            'name' => 'مدير النظام',
            'email' => env('DEV_ADMIN_EMAIL', 'admin@admin.com'),
            'phone' => '0500000001',
            'role' => 'admin',
            'description' => 'صلاحيات كاملة على لوحة التحكم',
        ],
        [
            'key' => 'supervisor',
            'label' => 'مشرف',
            'name' => 'مشرف النظام',
            'email' => env('DEV_SUPERVISOR_EMAIL', 'supervisor@example.com'),
            'phone' => '0500000002',
            'role' => 'supervisor',
            'description' => 'لوحة المشرف (صفوفي)',
        ],
        [
            'key' => 'teacher',
            'label' => 'معلم',
            'name' => 'معلم النظام',
            'email' => env('DEV_TEACHER_EMAIL', 'teacher@example.com'),
            'phone' => '0500000003',
            'role' => 'teacher',
            'description' => 'لوحة المعلم بصلاحيات محدودة',
        ],
        [
            'key' => 'student',
            'label' => 'طالب',
            'name' => 'طالب تجريبي',
            'email' => env('DEV_STUDENT_EMAIL', 'student@student.com'),
            'phone' => '0500000004',
            'role' => 'student',
            'description' => 'لوحة الطالب (قد تطلب تسجيل في مادة)',
        ],
    ],

];
