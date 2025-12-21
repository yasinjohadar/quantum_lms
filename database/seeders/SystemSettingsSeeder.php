<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إعدادات عامة
        SystemSetting::set('site_name', 'Quantum LMS', 'string', 'general', 'اسم الموقع');
        SystemSetting::set('site_description', 'نظام إدارة التعلم الإلكتروني', 'text', 'general', 'وصف الموقع');
        
        // إعدادات التقارير
        SystemSetting::set('reports_auto_generate', 'false', 'boolean', 'reports', 'إنشاء التقارير تلقائياً');
        SystemSetting::set('reports_default_period', 'month', 'string', 'reports', 'الفترة الافتراضية للتقارير');
        SystemSetting::set('reports_cache_duration', '3600', 'integer', 'reports', 'مدة تخزين التقارير بالثواني');
        
        // إعدادات التحليلات
        SystemSetting::set('analytics_enabled', 'true', 'boolean', 'analytics', 'تفعيل نظام التحليلات');
        SystemSetting::set('analytics_retention_days', '365', 'integer', 'analytics', 'عدد أيام الاحتفاظ بالبيانات');
        SystemSetting::set('analytics_track_anonymous', 'false', 'boolean', 'analytics', 'تتبع المستخدمين المجهولين');
        
        // إعدادات لوحة التحكم
        SystemSetting::set('dashboard_refresh_interval', '300', 'integer', 'dashboard', 'فترة تحديث لوحة التحكم بالثواني');
        SystemSetting::set('dashboard_widgets_per_row', '3', 'integer', 'dashboard', 'عدد الودجت في كل صف');
        
        // إعدادات التصدير
        SystemSetting::set('export_pdf_orientation', 'portrait', 'string', 'export', 'اتجاه PDF (portrait/landscape)');
        SystemSetting::set('export_excel_include_charts', 'false', 'boolean', 'export', 'تضمين الرسوم البيانية في Excel');
    }
}

