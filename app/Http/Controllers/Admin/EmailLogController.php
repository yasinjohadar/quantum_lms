<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    /**
     * عرض سجل الإيميلات
     */
    public function index(Request $request)
    {
        $query = EmailLog::query();

        // فلترة حسب الحالة
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // فلترة حسب التاريخ
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
                     ->paginate(20);

        return view('admin.pages.email-logs.index', compact('logs'));
    }

    /**
     * عرض تفاصيل إيميل
     */
    public function show(EmailLog $emailLog)
    {
        return view('admin.pages.email-logs.show', compact('emailLog'));
    }

    /**
     * حذف سجل
     */
    public function destroy(EmailLog $emailLog)
    {
        try {
            $emailLog->delete();

            return redirect()->route('admin.email-logs.index')
                           ->with('success', 'تم حذف السجل بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'حدث خطأ أثناء حذف السجل: ' . $e->getMessage());
        }
    }
}
