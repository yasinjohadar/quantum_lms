<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\GamificationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * عرض صفحة إرسال الإشعارات
     */
    public function create()
    {
        // جلب المواد النشطة
        $subjects = Subject::where('is_active', true)
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        // جلب الصفوف النشطة
        $classes = SchoolClass::where('is_active', true)
            ->with('stage')
            ->orderBy('order')
            ->get();

        return view('admin.pages.notifications.create', compact('subjects', 'classes'));
    }

    /**
     * إرسال الإشعارات
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
            'target_type' => 'required|in:subject,class,individual',
            'subject_id' => 'nullable|required_if:target_type,subject|exists:subjects,id',
            'class_id' => 'nullable|required_if:target_type,class|exists:classes,id',
            'user_ids' => 'nullable|required_if:target_type,individual|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $userIds = $this->getTargetUserIds($validated);
            
            if (empty($userIds)) {
                return redirect()->back()
                    ->with('error', 'لم يتم العثور على أي طلاب للهدف المحدد')
                    ->withInput();
            }

            // إرسال الإشعارات
            $count = $this->notificationService->sendBulkNotification(
                $userIds,
                'custom_notification',
                $validated['title'],
                $validated['message'],
                [
                    'sent_by' => Auth::id(),
                    'target_type' => $validated['target_type'],
                    'target_id' => $validated['subject_id'] ?? $validated['class_id'] ?? null,
                ]
            );

            DB::commit();

            return redirect()->route('admin.notifications.create')
                ->with('success', "تم إرسال الإشعار بنجاح إلى {$count} طالب");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error sending notifications: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $validated,
            ]);

            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء إرسال الإشعارات: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * الحصول على IDs المستخدمين المستهدفين
     */
    private function getTargetUserIds(array $validated): array
    {
        $userIds = [];

        switch ($validated['target_type']) {
            case 'subject':
                // جلب جميع الطلاب المسجلين في المادة
                $userIds = User::whereHas('subjects', function($query) use ($validated) {
                    $query->where('subjects.id', $validated['subject_id'])
                        ->where('enrollments.status', 'active');
                })
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();
                break;

            case 'class':
                // جلب جميع الطلاب المسجلين في المواد التابعة للصف
                $userIds = User::whereHas('subjects', function($query) use ($validated) {
                    $query->where('subjects.class_id', $validated['class_id'])
                        ->where('enrollments.status', 'active');
                })
                ->where('is_active', true)
                ->distinct()
                ->pluck('id')
                ->toArray();
                break;

            case 'individual':
                $userIds = $validated['user_ids'] ?? [];
                break;
        }

        return array_values($userIds);
    }

    /**
     * API: جلب الطلاب حسب الهدف
     */
    public function getTargetUsers(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:subject,class',
            'target_id' => 'required|integer',
        ]);

        $userIds = $this->getTargetUserIds([
            'target_type' => $request->target_type,
            'subject_id' => $request->target_type === 'subject' ? $request->target_id : null,
            'class_id' => $request->target_type === 'class' ? $request->target_id : null,
        ]);

        $users = User::whereIn('id', $userIds)
            ->select('id', 'name', 'email')
            ->get();

        return response()->json([
            'success' => true,
            'count' => count($userIds),
            'users' => $users,
        ]);
    }

    /**
     * API: جلب جميع الطلاب
     */
    public function getAllUsers()
    {
        $users = User::role('student')
            ->where('is_active', true)
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }
}
