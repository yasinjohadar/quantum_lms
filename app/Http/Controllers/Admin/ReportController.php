<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportBuilderService;
use App\Services\ReportGeneratorService;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    protected $reportBuilder;
    protected $reportGenerator;

    public function __construct(ReportBuilderService $reportBuilder, ReportGeneratorService $reportGenerator)
    {
        $this->reportBuilder = $reportBuilder;
        $this->reportGenerator = $reportGenerator;
    }

    /**
     * قائمة التقارير
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $templates = $this->reportBuilder->getAvailableTemplates($type);

        return view('admin.pages.reports.index', compact('templates', 'type'));
    }

    /**
     * إنشاء تقرير جديد
     */
    public function create(Request $request)
    {
        $type = $request->input('type');
        $templateId = $request->input('template');
        $templates = $this->reportBuilder->getAvailableTemplates($type);
        $selectedTemplate = $templateId ? ReportTemplate::find($templateId) : null;

        return view('admin.pages.reports.create', compact('templates', 'type', 'selectedTemplate'));
    }

    /**
     * عرض تقرير
     */
    public function show(Request $request, $id)
    {
        $template = ReportTemplate::findOrFail($id);
        $params = $request->except(['_token', '_method']);

        try {
            $report = $this->reportBuilder->generateReport($template->id, $params);
            return view('admin.pages.reports.show', compact('report', 'template'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * تصدير تقرير
     */
    public function export(Request $request, $id, $format)
    {
        $template = ReportTemplate::findOrFail($id);
        $params = $request->except(['_token', '_method']);

        try {
            $report = $this->reportBuilder->generateReport($template->id, $params);

            switch ($format) {
                case 'pdf':
                    $pdf = $this->reportGenerator->generatePDF($report['data'], $template);
                    $fileName = 'report_' . str_replace(' ', '_', $template->name) . '_' . now()->format('Y-m-d_His') . '.pdf';
                    return $pdf->download($fileName);
                    
                case 'excel':
                    return $this->reportGenerator->generateExcel($report['data'], $template);
                    
                case 'print':
                    $html = $this->reportGenerator->generatePrintView($report['data'], $template);
                    return view('admin.pages.reports.print', ['html' => $html, 'template' => $template]);
                    
                default:
                    return redirect()->back()->with('error', 'صيغة التصدير غير مدعومة');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * جدولة تقرير
     */
    public function schedule(Request $request, $id)
    {
        $template = ReportTemplate::findOrFail($id);

        $request->validate([
            'frequency' => 'required|in:daily,weekly,monthly',
            'recipients' => 'nullable|array',
        ]);

        $schedule = \App\Models\ReportSchedule::create([
            'template_id' => $template->id,
            'frequency' => $request->frequency,
            'recipients' => $request->recipients ?? [],
            'next_run_at' => $this->calculateNextRun($request->frequency),
            'is_active' => true,
            'created_by' => Auth::id(),
        ]);

        return redirect()->back()->with('success', 'تم جدولة التقرير بنجاح');
    }

    /**
     * إدارة القوالب
     */
    public function templates()
    {
        $templates = ReportTemplate::with('creator')->orderBy('type')->orderBy('name')->get();
        return view('admin.pages.reports.templates.index', compact('templates'));
    }

    /**
     * حساب موعد التشغيل التالي
     */
    protected function calculateNextRun($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return now()->addDay();
            case 'weekly':
                return now()->addWeek();
            case 'monthly':
                return now()->addMonth();
            default:
                return now()->addDay();
        }
    }
}

