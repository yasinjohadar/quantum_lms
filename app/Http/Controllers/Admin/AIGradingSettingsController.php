<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AIModel;
use Illuminate\Http\Request;

class AIGradingSettingsController extends Controller
{
    /**
     * عرض صفحة إعدادات AI Grading
     */
    public function index()
    {
        $settings = [
            'ai_essay_grading_enabled' => SystemSetting::get('ai_essay_grading_enabled', false),
            'ai_essay_grading_model_id' => SystemSetting::get('ai_essay_grading_model_id'),
            'ai_essay_grading_criteria' => SystemSetting::get('ai_essay_grading_criteria', []),
            'ai_essay_auto_grade' => SystemSetting::get('ai_essay_auto_grade', false),
        ];

        $models = AIModel::where('is_active', true)->get();

        return view('admin.pages.ai.settings.grading', compact('settings', 'models'));
    }

    /**
     * تحديث إعدادات AI Grading
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'ai_essay_grading_enabled' => 'boolean',
            'ai_essay_grading_model_id' => 'nullable|exists:ai_models,id',
            'ai_essay_auto_grade' => 'boolean',
        ]);

        try {
            SystemSetting::set('ai_essay_grading_enabled', $validated['ai_essay_grading_enabled'] ? '1' : '0', 'boolean', 'ai');
            SystemSetting::set('ai_essay_grading_model_id', $validated['ai_essay_grading_model_id'] ?? null, 'string', 'ai');
            SystemSetting::set('ai_essay_auto_grade', $validated['ai_essay_auto_grade'] ? '1' : '0', 'boolean', 'ai');

            return redirect()->route('admin.ai.settings.grading')
                ->with('success', 'تم تحديث الإعدادات بنجاح');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء تحديث الإعدادات: ' . $e->getMessage())
                ->withInput();
        }
    }
}




