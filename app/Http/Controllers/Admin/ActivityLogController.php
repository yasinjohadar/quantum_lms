<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    private const SUBJECT_LABELS = [
        'stage' => 'مرحلة',
        'class' => 'صف',
        'subject' => 'مادة',
        'subject_section' => 'قسم',
        'unit' => 'وحدة',
        'lesson' => 'درس',
        'question' => 'سؤال',
        'quiz' => 'اختبار',
    ];

    private const EVENT_LABELS = [
        'created' => 'إنشاء',
        'updated' => 'تعديل',
        'deleted' => 'حذف',
        'force_deleted' => 'حذف نهائي',
        'restored' => 'استعادة',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $logsQuery = AuditLog::with('user')->whereNotNull('subject_type');

        if ($request->filled('subject_type')) {
            $logsQuery->where('subject_type', $request->input('subject_type'));
        }

        if ($request->filled('event')) {
            $logsQuery->where('event_type', 'like', '%.' . $request->input('event'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $logsQuery->where(function ($q) use ($search) {
                $q->where('subject_label', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('date_from')) {
            $logsQuery->whereDate('occurred_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $logsQuery->whereDate('occurred_at', '<=', $request->input('date_to'));
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));
        $logs = $logsQuery->latest('occurred_at')->paginate($perPage)->withQueryString();

        $subjectLabels = self::SUBJECT_LABELS;
        $eventLabels = self::EVENT_LABELS;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.activity-log.partials.table-rows', compact('logs', 'subjectLabels', 'eventLabels'))->render(),
                'pagination' => view('admin.pages.activity-log.partials.pagination-links', compact('logs'))->render(),
                'total_matching' => $logs->total(),
            ]);
        }

        $stats = [
            'total' => AuditLog::whereNotNull('subject_type')->count(),
            'today' => AuditLog::whereNotNull('subject_type')->whereDate('occurred_at', today())->count(),
            'created' => AuditLog::where('event_type', 'like', '%.created')->count(),
            'deleted' => AuditLog::where('event_type', 'like', '%.deleted')
                ->orWhere('event_type', 'like', '%.force_deleted')
                ->count(),
        ];

        return view('admin.pages.activity-log.index', compact('logs', 'stats', 'subjectLabels', 'eventLabels'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $log = AuditLog::with('user')->findOrFail($id);
            $subjectLabels = self::SUBJECT_LABELS;
            $eventLabels = self::EVENT_LABELS;

            return view('admin.pages.activity-log.show', compact('log', 'subjectLabels', 'eventLabels'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('admin.activity-log.index')
                ->with('error', 'سجل النشاط المطلوب غير موجود');
        }
    }
}
