<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SMSTemplate;
use App\Services\SMS\SMSTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SMSTemplateController extends Controller
{
    public function __construct(
        private SMSTemplateService $templateService
    ) {}

    /**
     * عرض قائمة القوالب
     */
    public function index()
    {
        $templates = SMSTemplate::orderBy('name')->get();
        return view('admin.pages.sms-templates.index', compact('templates'));
    }

    /**
     * عرض قالب
     */
    public function show(SMSTemplate $smsTemplate)
    {
        return redirect()->route('admin.sms-templates.edit', $smsTemplate);
    }

    /**
     * نموذج إنشاء قالب
     */
    public function create()
    {
        return view('admin.pages.sms-templates.create');
    }

    /**
     * حفظ قالب جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sms_templates,slug',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'body.required' => 'محتوى الرسالة مطلوب',
            'slug.unique' => 'هذا المعرف مستخدم بالفعل',
        ]);

        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            SMSTemplate::create($validated);

            return redirect()->route('admin.sms-templates.index')
                           ->with('success', 'تم إنشاء القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating SMS template: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء القالب: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * نموذج تعديل قالب
     */
    public function edit(SMSTemplate $smsTemplate)
    {
        return view('admin.pages.sms-templates.edit', compact('smsTemplate'));
    }

    /**
     * تحديث قالب
     */
    public function update(Request $request, SMSTemplate $smsTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:sms_templates,slug,' . $smsTemplate->id,
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'body.required' => 'محتوى الرسالة مطلوب',
            'slug.unique' => 'هذا المعرف مستخدم بالفعل',
        ]);

        try {
            $smsTemplate->update($validated);

            return redirect()->route('admin.sms-templates.index')
                           ->with('success', 'تم تحديث القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating SMS template: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث القالب: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف قالب
     */
    public function destroy(SMSTemplate $smsTemplate)
    {
        try {
            $smsTemplate->delete();

            return redirect()->route('admin.sms-templates.index')
                           ->with('success', 'تم حذف القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting SMS template: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف القالب: ' . $e->getMessage());
        }
    }

    /**
     * معاينة القالب
     */
    public function preview(Request $request, SMSTemplate $smsTemplate)
    {
        $variables = $request->input('variables', []);
        $rendered = $smsTemplate->render($variables);

        return response()->json([
            'success' => true,
            'body' => nl2br(e($rendered)),
        ]);
    }
}
