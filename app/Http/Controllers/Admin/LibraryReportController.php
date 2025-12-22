<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryItem;
use App\Services\LibraryStatsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class LibraryReportController extends Controller
{
    public function __construct(
        private LibraryStatsService $statsService
    ) {}

    /**
     * تصدير تقرير أكثر العناصر تحميلاً
     */
    public function exportMostDownloaded(Request $request)
    {
        $limit = $request->input('limit', 50);
        $format = $request->input('format', 'csv');
        
        $items = LibraryItem::with(['category', 'subject', 'uploader'])
                           ->orderBy('download_count', 'desc')
                           ->limit($limit)
                           ->get();

        if ($format === 'csv') {
            return $this->exportToCSV($items, 'most_downloaded');
        } elseif ($format === 'excel') {
            return $this->exportToExcel($items, 'most_downloaded');
        }

        return redirect()->back()->with('error', 'صيغة التصدير غير مدعومة');
    }

    /**
     * تصدير تقرير أكثر العناصر مشاهدة
     */
    public function exportMostViewed(Request $request)
    {
        $limit = $request->input('limit', 50);
        $format = $request->input('format', 'csv');
        
        $items = LibraryItem::with(['category', 'subject', 'uploader'])
                           ->orderBy('view_count', 'desc')
                           ->limit($limit)
                           ->get();

        if ($format === 'csv') {
            return $this->exportToCSV($items, 'most_viewed');
        } elseif ($format === 'excel') {
            return $this->exportToExcel($items, 'most_viewed');
        }

        return redirect()->back()->with('error', 'صيغة التصدير غير مدعومة');
    }

    /**
     * تصدير تقرير أكثر التصنيفات استخداماً
     */
    public function exportCategoriesUsage(Request $request)
    {
        $format = $request->input('format', 'csv');
        
        $categories = \App\Models\LibraryCategory::withCount('items')
                                                 ->withSum('items', 'download_count')
                                                 ->withSum('items', 'view_count')
                                                 ->orderBy('items_download_count_sum', 'desc')
                                                 ->get();

        if ($format === 'csv') {
            return $this->exportCategoriesToCSV($categories);
        } elseif ($format === 'excel') {
            return $this->exportCategoriesToExcel($categories);
        }

        return redirect()->back()->with('error', 'صيغة التصدير غير مدعومة');
    }

    /**
     * تصدير إلى CSV
     */
    private function exportToCSV($items, $type)
    {
        $filename = 'library_' . $type . '_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($items) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'العنوان',
                'التصنيف',
                'المادة',
                'النوع',
                'عدد التحميلات',
                'عدد المشاهدات',
                'التقييم المتوسط',
                'عدد التقييمات',
                'من رفع',
                'تاريخ الإنشاء'
            ]);

            // Data
            foreach ($items as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->title,
                    $item->category->name ?? '-',
                    $item->subject->name ?? 'عام',
                    \App\Models\LibraryItem::TYPES[$item->type] ?? $item->type,
                    $item->download_count,
                    $item->view_count,
                    $item->average_rating,
                    $item->total_ratings,
                    $item->uploader->name ?? '-',
                    $item->created_at->format('Y-m-d H:i')
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * تصدير إلى Excel (بسيط - CSV مع امتداد xlsx)
     */
    private function exportToExcel($items, $type)
    {
        // للبساطة، سنستخدم CSV مع امتداد xlsx
        // يمكن لاحقاً استخدام مكتبة مثل Maatwebsite/Excel
        return $this->exportToCSV($items, $type);
    }

    /**
     * تصدير التصنيفات إلى CSV
     */
    private function exportCategoriesToCSV($categories)
    {
        $filename = 'library_categories_usage_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($categories) {
            $file = fopen('php://output', 'w');
            
            // BOM for UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Headers
            fputcsv($file, [
                'ID',
                'اسم التصنيف',
                'عدد العناصر',
                'إجمالي التحميلات',
                'إجمالي المشاهدات',
                'الحالة'
            ]);

            // Data
            foreach ($categories as $category) {
                fputcsv($file, [
                    $category->id,
                    $category->name,
                    $category->items_count ?? 0,
                    $category->items_download_count_sum ?? 0,
                    $category->items_view_count_sum ?? 0,
                    $category->is_active ? 'نشط' : 'غير نشط'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * تصدير التصنيفات إلى Excel
     */
    private function exportCategoriesToExcel($categories)
    {
        return $this->exportCategoriesToCSV($categories);
    }
}
