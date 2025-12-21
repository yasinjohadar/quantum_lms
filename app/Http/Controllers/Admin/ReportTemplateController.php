<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportTemplateController extends Controller
{
    /**
     * عرض قائمة القوالب
     */
    public function index()
    {
        $templates = ReportTemplate::with('creator')->orderBy('type')->orderBy('name')->get();
        return view('admin.pages.reports.templates.index', compact('templates'));
    }

    /**
     * عرض نموذج إنشاء قالب
     */
    public function create()
    {
        return view('admin.pages.reports.templates.create');
    }

    /**
     * حفظ قالب جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:student,course,system',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // إذا تم تعيين كافتراضي، إلغاء الافتراضي من القوالب الأخرى من نفس النوع
        if ($request->is_default) {
            ReportTemplate::ofType($request->type)
                ->update(['is_default' => false]);
        }

        $template = ReportTemplate::create([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'config' => $request->config ?? [],
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('admin.report-templates.index')
            ->with('success', 'تم إنشاء القالب بنجاح');
    }

    /**
     * عرض قالب
     */
    public function show($id)
    {
        $template = ReportTemplate::with('creator')->findOrFail($id);
        return view('admin.pages.reports.templates.show', compact('template'));
    }

    /**
     * عرض نموذج تعديل قالب
     */
    public function edit($id)
    {
        $template = ReportTemplate::findOrFail($id);
        return view('admin.pages.reports.templates.edit', compact('template'));
    }

    /**
     * تحديث قالب
     */
    public function update(Request $request, $id)
    {
        $template = ReportTemplate::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:student,course,system',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        // إذا تم تعيين كافتراضي، إلغاء الافتراضي من القوالب الأخرى من نفس النوع
        if ($request->is_default && !$template->is_default) {
            ReportTemplate::ofType($request->type)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $template->update([
            'name' => $request->name,
            'type' => $request->type,
            'description' => $request->description,
            'config' => $request->config ?? $template->config,
            'is_active' => $request->has('is_active'),
            'is_default' => $request->has('is_default'),
        ]);

        return redirect()->route('admin.report-templates.index')
            ->with('success', 'تم تحديث القالب بنجاح');
    }

    /**
     * حذف قالب
     */
    public function destroy($id)
    {
        $template = ReportTemplate::findOrFail($id);
        $template->delete();

        return redirect()->route('admin.report-templates.index')
            ->with('success', 'تم حذف القالب بنجاح');
    }

    /**
     * نسخ قالب
     */
    public function duplicate($id)
    {
        $template = ReportTemplate::findOrFail($id);

        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (نسخة)';
        $newTemplate->is_default = false;
        $newTemplate->created_by = Auth::id();
        $newTemplate->save();

        return redirect()->route('admin.report-templates.index')
            ->with('success', 'تم نسخ القالب بنجاح');
    }

    /**
     * تعيين كافتراضي
     */
    public function setDefault($id)
    {
        $template = ReportTemplate::findOrFail($id);

        // إلغاء الافتراضي من القوالب الأخرى من نفس النوع
        ReportTemplate::ofType($template->type)
            ->where('id', '!=', $id)
            ->update(['is_default' => false]);

        $template->update(['is_default' => true]);

        return redirect()->back()->with('success', 'تم تعيين القالب كافتراضي بنجاح');
    }
}

