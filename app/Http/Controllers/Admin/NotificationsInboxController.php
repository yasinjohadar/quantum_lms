<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GamificationNotification;
use App\Services\GamificationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationsInboxController extends Controller
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * صندوق إشعارات المستخدم الحالي (معلم/مشرف/إدمن) في لوحة الإدارة.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all');

        $query = GamificationNotification::where('user_id', $user->id);

        if ($type !== 'all') {
            $query->where('type', $type);
        }

        if ($status === 'read') {
            $query->where('is_read', true);
        } elseif ($status === 'unread') {
            $query->where('is_read', false);
        }

        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total' => GamificationNotification::where('user_id', $user->id)->count(),
            'unread' => $this->notificationService->getUnreadCount($user),
            'read' => GamificationNotification::where('user_id', $user->id)->where('is_read', true)->count(),
        ];

        $types = array_merge(GamificationNotification::TYPES, [
            'lesson_review_submit_ack' => 'تأكيد إرسال درس للمراجعة',
            'quiz_review_submit_ack' => 'تأكيد إرسال اختبار للمراجعة',
            'student_lesson_available' => 'درس متاح للطلاب',
            'student_quiz_available' => 'اختبار متاح للطلاب',
            'lesson_attended' => 'حضور درس',
            'lesson_completed' => 'إكمال درس',
            'quiz_started' => 'بدء اختبار',
            'quiz_completed' => 'إكمال اختبار',
            'question_answered' => 'إجابة سؤال',
            'task_completed' => 'إكمال مهمة',
            'points_awarded' => 'منح نقاط',
        ]);

        $typeStats = [];
        foreach ($types as $typeKey => $typeName) {
            $typeStats[$typeKey] = [
                'name' => $typeName,
                'total' => GamificationNotification::where('user_id', $user->id)
                    ->where('type', $typeKey)
                    ->count(),
                'unread' => GamificationNotification::where('user_id', $user->id)
                    ->where('type', $typeKey)
                    ->where('is_read', false)
                    ->count(),
            ];
        }

        return view('admin.pages.notifications.inbox', [
            'notifications' => $notifications,
            'stats' => $stats,
            'typeStats' => $typeStats,
            'currentType' => $type,
            'currentStatus' => $status,
            'types' => $types,
        ]);
    }

    public function markAsRead(GamificationNotification $notification)
    {
        $this->assertOwns($notification);
        $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true]);
    }

    public function markAsUnread(GamificationNotification $notification)
    {
        $this->assertOwns($notification);
        $notification->is_read = false;
        $notification->read_at = null;
        $notification->save();

        return response()->json(['success' => true]);
    }

    public function destroy(GamificationNotification $notification)
    {
        $this->assertOwns($notification);
        $notification->delete();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return redirect()
            ->route('admin.notifications.inbox')
            ->with('success', "تم تحديد {$count} إشعار كمقروء");
    }

    public function unreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(Auth::user()),
        ]);
    }

    private function assertOwns(GamificationNotification $notification): void
    {
        if ((int) $notification->user_id !== (int) Auth::id()) {
            abort(403);
        }
    }
}
