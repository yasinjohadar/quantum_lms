<?php

namespace App\Http\Controllers;

use App\Models\GamificationNotification;
use App\Services\GamificationNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * واجهة JSON موحّدة لصندوق الإشعارات (طالب، معلم، مشرف، إدمن).
 */
class NotificationInboxController extends Controller
{
    public function __construct(
        private GamificationNotificationService $notificationService
    ) {
        $this->middleware(['auth', 'check.user.active']);
    }

    public function recent(Request $request)
    {
        $user = Auth::user();
        $limit = min((int) $request->get('limit', 10), 50);

        $notifications = GamificationNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function (GamificationNotification $notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'actor_name' => $notif->actor_name,
                    'actor_role' => $notif->actor_role,
                    'action_url' => $notif->action_url,
                    'created_at' => $notif->created_at->toIso8601String(),
                    'is_read' => $notif->is_read,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    public function unreadCount()
    {
        $user = Auth::user();

        return response()->json([
            'count' => $this->notificationService->getUnreadCount($user),
        ]);
    }

    public function markAllRead()
    {
        $user = Auth::user();
        $count = $this->notificationService->markAllAsRead($user);

        return redirect()->back()->with('success', "تم تحديد {$count} إشعار كمقروء");
    }
}
