<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SMSLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SMSLogController extends Controller
{
    /**
     * عرض سجل SMS
     */
    public function index(Request $request)
    {
        $query = SMSLog::query();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('to', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')
                     ->paginate(20);

        return view('admin.pages.sms-logs.index', compact('logs'));
    }

    /**
     * عرض تفاصيل SMS
     */
    public function show(SMSLog $smsLog)
    {
        return view('admin.pages.sms-logs.show', compact('smsLog'));
    }
}
