<?php

namespace App\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportGeneratorService
{
    /**
     * إنشاء PDF
     */
    public function generatePDF($data, $template)
    {
        try {
            $html = View::make('admin.pages.reports.pdf-template', [
                'data' => $data,
                'template' => $template,
            ])->render();

            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            $pdf->setOption('isHtml5ParserEnabled', true);
            $pdf->setOption('isRemoteEnabled', true);

            return $pdf;
        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: ' . $e->getMessage());
            throw new \Exception('حدث خطأ أثناء إنشاء PDF: ' . $e->getMessage());
        }
    }

    /**
     * إنشاء Excel
     */
    public function generateExcel($data, $template)
    {
        try {
            $exportData = $this->prepareExcelData($data, $template);
            
            $fileName = 'report_' . $template->name . '_' . now()->format('Y-m-d_His') . '.xlsx';
            
            return Excel::download(new class($exportData) implements FromArray, WithHeadings, WithStyles {
                protected $data;

                public function __construct($data)
                {
                    $this->data = $data;
                }

                public function array(): array
                {
                    return $this->data['rows'] ?? [];
                }

                public function headings(): array
                {
                    return $this->data['headings'] ?? [];
                }

                public function styles(Worksheet $sheet)
                {
                    return [
                        1 => ['font' => ['bold' => true]],
                    ];
                }
            }, $fileName);
        } catch (\Exception $e) {
            \Log::error('Excel Generation Error: ' . $e->getMessage());
            throw new \Exception('حدث خطأ أثناء إنشاء Excel: ' . $e->getMessage());
        }
    }

    /**
     * إعداد بيانات Excel
     */
    protected function prepareExcelData($data, $template)
    {
        $headings = [];
        $rows = [];

        switch ($template->type) {
            case 'student':
                $headings = ['الكورس', 'التقدم الإجمالي (%)', 'الدروس المكتملة', 'الاختبارات المكتملة', 'الأسئلة المكتملة'];
                foreach ($data['progress'] ?? [] as $item) {
                    $progress = $item['progress'];
                    $rows[] = [
                        $item['subject']->name,
                        number_format($progress['overall_percentage'], 2),
                        $progress['lessons_completed'] . '/' . $progress['lessons_total'],
                        $progress['quizzes_completed'] . '/' . $progress['quizzes_total'],
                        $progress['questions_completed'] . '/' . $progress['questions_total'],
                    ];
                }
                break;

            case 'course':
                $headings = ['المعلومة', 'القيمة'];
                $stats = $data['statistics'] ?? [];
                $rows = [
                    ['إجمالي الطلاب', $stats['total_students'] ?? 0],
                    ['إجمالي الدروس', $stats['total_lessons'] ?? 0],
                    ['إجمالي الاختبارات', $stats['total_quizzes'] ?? 0],
                ];
                break;

            case 'system':
                $headings = ['المعلومة', 'القيمة'];
                $system = $data['system'] ?? [];
                $rows = [
                    ['إجمالي المستخدمين', $system['total_users'] ?? 0],
                    ['إجمالي الطلاب', $system['total_students'] ?? 0],
                    ['إجمالي الكورسات', $system['total_subjects'] ?? 0],
                    ['إجمالي الدروس', $system['total_lessons'] ?? 0],
                    ['إجمالي الاختبارات', $system['total_quizzes'] ?? 0],
                    ['إجمالي الأسئلة', $system['total_questions'] ?? 0],
                ];
                break;
        }

        return [
            'headings' => $headings,
            'rows' => $rows,
        ];
    }

    /**
     * إنشاء Print View
     */
    public function generatePrintView($data, $template)
    {
        return View::make('admin.pages.reports.print-template', [
            'data' => $data,
            'template' => $template,
        ])->render();
    }
}