<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ReportTemplate;
use App\Models\User;

class ReportTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::whereHas('roles', function($query) {
            $query->where('name', 'admin');
        })->first() ?? User::first();

        // قالب تقرير الطالب الشامل
        ReportTemplate::create([
            'name' => 'تقرير الطالب الشامل',
            'type' => 'student',
            'description' => 'تقرير شامل يوضح تقدم الطالب في جميع الكورسات مع التحليلات والإحصائيات',
            'config' => [
                'required_params' => ['user_id'],
                'charts' => ['progress'],
                'sections' => ['progress', 'analytics'],
            ],
            'is_active' => true,
            'is_default' => true,
            'created_by' => $admin?->id,
        ]);

        // قالب تقرير أداء الطالب
        ReportTemplate::create([
            'name' => 'تقرير أداء الطالب',
            'type' => 'student',
            'description' => 'تقرير يركز على أداء الطالب في الاختبارات والأنشطة',
            'config' => [
                'required_params' => ['user_id'],
                'charts' => ['performance'],
                'sections' => ['analytics'],
            ],
            'is_active' => true,
            'is_default' => false,
            'created_by' => $admin?->id,
        ]);

        // قالب تقرير الكورس الشامل
        ReportTemplate::create([
            'name' => 'تقرير الكورس الشامل',
            'type' => 'course',
            'description' => 'تقرير شامل يوضح إحصائيات الكورس ومشاركة الطلاب',
            'config' => [
                'required_params' => ['subject_id'],
                'charts' => ['statistics'],
                'sections' => ['statistics', 'analytics'],
            ],
            'is_active' => true,
            'is_default' => true,
            'created_by' => $admin?->id,
        ]);

        // قالب تقرير النظام
        ReportTemplate::create([
            'name' => 'تقرير النظام العام',
            'type' => 'system',
            'description' => 'تقرير شامل عن حالة النظام واستخدامه',
            'config' => [
                'required_params' => [],
                'charts' => ['usage'],
                'sections' => ['system', 'analytics'],
            ],
            'is_active' => true,
            'is_default' => true,
            'created_by' => $admin?->id,
        ]);
    }
}

