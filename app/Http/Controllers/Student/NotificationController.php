<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GamificationNotification;
use App\Services\GamificationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {}

    /**
     * عرض قائمة الإشعارات
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // الفلترة
        $type = $request->get('type', 'all');
        $status = $request->get('status', 'all'); // all, read, unread
        
        $query = GamificationNotification::where('user_id', $user->id);
        
        // فلترة حسب النوع
        if ($type !== 'all') {
            $query->where('type', $type);
        }
        
        // فلترة حسب الحالة
        if ($status === 'read') {
            $query->where('is_read', true);
        } elseif ($status === 'unread') {
            $query->where('is_read', false);
        }
        
        $notifications = $query->orderBy('created_at', 'desc')
            ->paginate(20);
        
        // الإحصائيات
        $stats = [
            'total' => GamificationNotification::where('user_id', $user->id)->count(),
            'unread' => $this->notificationService->getUnreadCount($user),
            'read' => GamificationNotification::where('user_id', $user->id)->where('is_read', true)->count(),
        ];
        
        // إحصائيات حسب النوع
        $typeStats = [];
        foreach (GamificationNotification::TYPES as $typeKey => $typeName) {
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
        
        // إضافة أنواع جديدة من Events
        $additionalTypes = [
            'lesson_attended' => 'حضور درس',
            'lesson_completed' => 'إكمال درس',
            'quiz_started' => 'بدء اختبار',
            'quiz_completed' => 'إكمال اختبار',
            'question_answered' => 'إجابة سؤال',
            'task_completed' => 'إكمال مهمة',
            'points_awarded' => 'منح نقاط',
        ];
        
        foreach ($additionalTypes as $typeKey => $typeName) {
            if (!isset($typeStats[$typeKey])) {
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
        }

        return view('student.pages.notifications.index', [
            'notifications' => $notifications,
            'stats' => $stats,
            'typeStats' => $typeStats,
            'currentType' => $type,
            'currentStatus' => $status,
            'types' => array_merge(GamificationNotification::TYPES, $additionalTypes),
        ]);
    }

    /**
     * تحديد كمقروء
     */
    public function markAsRead($notificationId)
    {
        $user = Auth::user();
        $notification = GamificationNotification::where('user_id', $user->id)
            ->findOrFail($notificationId);

        $this->notificationService->markAsRead($notification);

        return response()->json(['success' => true]);
    }

    /**
     * تحديد الكل كمقروء
     */
    public function markAllAsRead()
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return redirect()->back()->with('success', "تم تحديد {$count} إشعار كمقروء");
    }

    /**
     * تحديد كغير مقروء
     */
    public function markAsUnread($notificationId)
    {
        $user = Auth::user();
        $notification = GamificationNotification::where('user_id', $user->id)
            ->findOrFail($notificationId);

        $notification->is_read = false;
        $notification->read_at = null;
        $notification->save();

        return response()->json(['success' => true]);
    }

    /**
     * حذف إشعار
     */
    public function destroy($notificationId)
    {
        $user = Auth::user();
        $notification = GamificationNotification::where('user_id', $user->id)
            ->findOrFail($notificationId);

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * جلب عدد غير المقروءة (API)
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user);

        return response()->json(['count' => $count]);
    }
}

