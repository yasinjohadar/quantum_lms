<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsAppTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['permission:whats-app-template-list'])->only(['index', 'show']);
        $this->middleware(['permission:whats-app-template-create'])->only(['create', 'store']);
        $this->middleware(['permission:whats-app-template-edit'])->only(['edit', 'update', 'preview']);
        $this->middleware(['permission:whats-app-template-delete'])->only(['destroy']);
    }

    public function index()
    {
        $templates = WhatsAppTemplate::orderBy('name')->get();
        return view('admin.pages.whatsapp-templates.index', compact('templates'));
    }

    public function show(WhatsAppTemplate $whatsappTemplate)
    {
        return redirect()->route('admin.whatsapp-templates.edit', $whatsappTemplate);
    }

    public function create()
    {
        $supportedVariables = WhatsAppTemplate::supportedVariables();
        return view('admin.pages.whatsapp-templates.create', compact('supportedVariables'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:whatsapp_templates,slug',
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'content.required' => 'محتوى الرسالة مطلوب',
            'slug.unique' => 'هذا المعرف مستخدم بالفعل',
        ]);

        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['variables'] = $this->extractAndValidateVariables($validated['content']);

            WhatsAppTemplate::create($validated);

            return redirect()->route('admin.whatsapp-templates.index')
                ->with('success', 'تم إنشاء قالب WhatsApp بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->withErrors(['content' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error creating WhatsApp template: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء إنشاء القالب: ' . $e->getMessage());
        }
    }

    public function edit(WhatsAppTemplate $whatsappTemplate)
    {
        $supportedVariables = WhatsAppTemplate::supportedVariables();
        return view('admin.pages.whatsapp-templates.edit', compact('whatsappTemplate', 'supportedVariables'));
    }

    public function update(Request $request, WhatsAppTemplate $whatsappTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:whatsapp_templates,slug,' . $whatsappTemplate->id,
            'content' => 'required|string',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'اسم القالب مطلوب',
            'content.required' => 'محتوى الرسالة مطلوب',
            'slug.unique' => 'هذا المعرف مستخدم بالفعل',
        ]);

        try {
            if (empty($validated['slug'])) {
                $validated['slug'] = Str::slug($validated['name']);
            }

            $validated['is_active'] = $request->boolean('is_active', false);
            $validated['variables'] = $this->extractAndValidateVariables($validated['content']);

            $whatsappTemplate->update($validated);

            return redirect()->route('admin.whatsapp-templates.index')
                ->with('success', 'تم تحديث قالب WhatsApp بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->withErrors(['content' => $e->getMessage()]);
        } catch (\Exception $e) {
            Log::error('Error updating WhatsApp template: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'حدث خطأ أثناء تحديث القالب: ' . $e->getMessage());
        }
    }

    public function destroy(WhatsAppTemplate $whatsappTemplate)
    {
        try {
            $whatsappTemplate->delete();

            return redirect()->route('admin.whatsapp-templates.index')
                ->with('success', 'تم حذف قالب WhatsApp بنجاح.');
        } catch (\Exception $e) {
            Log::error('Error deleting WhatsApp template: ' . $e->getMessage());
            return redirect()->back()->with('error', 'حدث خطأ أثناء حذف القالب: ' . $e->getMessage());
        }
    }

    public function preview(Request $request, WhatsAppTemplate $whatsappTemplate)
    {
        $variables = $request->input('variables', []);
        $rendered = $whatsappTemplate->render($variables);

        return response()->json([
            'success' => true,
            'content' => nl2br(e($rendered)),
        ]);
    }

    private function extractAndValidateVariables(string $content): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $content, $matches);
        $usedVariables = !empty($matches[1]) ? array_values(array_unique($matches[1])) : [];
        $allowed = WhatsAppTemplate::supportedVariables();
        $invalid = array_diff($usedVariables, $allowed);

        if (!empty($invalid)) {
            throw new \InvalidArgumentException('المتغيرات التالية غير مدعومة: ' . implode(', ', $invalid));
        }

        return $usedVariables;
    }
}
