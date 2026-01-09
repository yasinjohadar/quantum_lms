<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ReportBuilderService;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentReportController extends Controller
{
    protected $reportBuilder;

    public function __construct(ReportBuilderService $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * قائمة التقارير المتاحة للطالب
     */
    public function index(Request $request)
    {
        try {
            $params = array_merge($request->except(['_token', '_method']), [
                'user_id' => Auth::id(),
                'period' => $request->get('period', 'month'),
            ]);

            // جمع البيانات مباشرة بدون template
            $report = [
                'data' => $this->reportBuilder->collectStudentDataDirectly($params),
                'params' => $params,
            ];

            return view('student.pages.reports.index', compact('report'));
        } catch (\Exception $e) {
            \Log::error('Error loading student reports: ' . $e->getMessage());
            return view('student.pages.reports.index', [
                'report' => [
                    'data' => [
                        'student' => Auth::user(),
                        'progress' => [],
                        'analytics' => [],
                        'charts' => [],
                        'quizzes' => ['list' => [], 'statistics' => []],
                        'assignments' => ['list' => [], 'statistics' => []],
                        'grades' => [],
                        'attendance' => [],
                    ],
                    'params' => [],
                ],
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * عرض تقرير الطالب
     */
    public function show(Request $request, $id)
    {
        $template = ReportTemplate::findOrFail($id);
        
        // التأكد من أن التقرير للطلاب فقط
        if ($template->type !== 'student') {
            abort(403, 'غير مصرح بالوصول إلى هذا التقرير');
        }

        $params = array_merge($request->except(['_token', '_method']), [
            'user_id' => Auth::id(),
        ]);

        try {
            $report = $this->reportBuilder->generateReport($template->id, $params);
            
            // Debug: Log report data structure
            \Log::info('Report data structure:', [
                'has_student' => isset($report['data']['student']),
                'has_progress' => isset($report['data']['progress']),
                'progress_count' => isset($report['data']['progress']) ? count($report['data']['progress']) : 0,
                'has_charts' => isset($report['data']['charts']),
                'has_analytics' => isset($report['data']['analytics']),
            ]);
            
            return view('student.pages.reports.show', compact('report', 'template'));
        } catch (\Exception $e) {
            \Log::error('Error generating report: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    
    /**
     * تصدير تقرير الطالب
     */
    public function export(Request $request, $id, $format)
    {
        $template = ReportTemplate::findOrFail($id);
        
        if ($template->type !== 'student') {
            abort(403, 'غير مصرح بالوصول إلى هذا التقرير');
        }

        $params = array_merge($request->except(['_token', '_method']), [
            'user_id' => Auth::id(),
        ]);

        try {
            $report = $this->reportBuilder->generateReport($template->id, $params);
            $reportGenerator = app(\App\Services\ReportGeneratorService::class);

            switch ($format) {
                case 'pdf':
                    $pdf = $reportGenerator->generatePDF($report['data'], $template);
                    $fileName = 'report_' . str_replace(' ', '_', $template->name) . '_' . now()->format('Y-m-d_His') . '.pdf';
                    return $pdf->download($fileName);
                    
                case 'excel':
                    return $reportGenerator->generateExcel($report['data'], $template);
                    
                case 'print':
                    $html = $reportGenerator->generatePrintView($report['data'], $template);
                    return view('admin.pages.reports.print', ['html' => $html, 'template' => $template]);
                    
                default:
                    return redirect()->back()->with('error', 'صيغة التصدير غير مدعومة');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
