<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\Email\EmailTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EmailTemplateController extends Controller
{
    public function __construct(
        private EmailTemplateService $templateService
    ) {}

    /**
     * عرض قائمة القوالب
     */
    public function index()
    {
        $templates = EmailTemplate::orderBy('name')->get();
        return view('admin.pages.email-templates.index', compact('templates'));
    }

    /**
     * عرض قالب
     */
    public function show(EmailTemplate $emailTemplate)
    {
        return redirect()->route('admin.email-templates.edit', $emailTemplate);
    }

    /**
     * نموذج إنشاء قالب
     */
    public function create()
    {
        return view('admin.pages.email-templates.create');
    }

    /**
     * حفظ قالب جديد
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_templates,slug',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'subject.required' => 'موضوع الإيميل مطلوب',
            'body.required' => 'محتوى الإيميل مطلوب',
            'slug.unique' => 'هذا المعرف مستخدم بالفعل',
        ]);

        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            EmailTemplate::create($validated);

            return redirect()->route('admin.email-templates.index')
                           ->with('success', 'تم إنشاء القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error creating email template: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء إنشاء القالب: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * نموذج تعديل قالب
     */
    public function edit(EmailTemplate $emailTemplate)
    {
        return view('admin.pages.email-templates.edit', compact('emailTemplate'));
    }

    /**
     * تحديث قالب
     */
    public function update(Request $request, EmailTemplate $emailTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:email_templates,slug,' . $emailTemplate->id,
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'variables' => 'nullable|array',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'subject.required' => 'موضوع الإيميل مطلوب',
            'body.required' => 'محتوى الإيميل مطلوب',
            'slug.unique' => 'هذا المعرف مستخدم بالفعل',
        ]);

        try {
            $emailTemplate->update($validated);

            return redirect()->route('admin.email-templates.index')
                           ->with('success', 'تم تحديث القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error updating email template: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء تحديث القالب: ' . $e->getMessage())
                           ->withInput();
        }
    }

    /**
     * حذف قالب
     */
    public function destroy(EmailTemplate $emailTemplate)
    {
        try {
            $emailTemplate->delete();

            return redirect()->route('admin.email-templates.index')
                           ->with('success', 'تم حذف القالب بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting email template: ' . $e->getMessage());
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف القالب: ' . $e->getMessage());
        }
    }

    /**
     * معاينة القالب
     */
    public function preview(Request $request, EmailTemplate $emailTemplate)
    {
        $variables = $request->input('variables', []);
        $rendered = $emailTemplate->render($variables);

        return response()->json([
            'success' => true,
            'subject' => $rendered['subject'],
            'body' => nl2br(e($rendered['body'])),
        ]);
    }
}
