<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    protected NotificationPreferenceService $preferenceService;

    public function __construct(NotificationPreferenceService $preferenceService)
    {
        $this->middleware(['auth', 'check.user.active', 'admin']);
        $this->preferenceService = $preferenceService;
    }

    /**
     * عرض تفضيلات طالب معيّن (لإدارة الدعم فقط)
     */
    public function show(User $user)
    {
        $preferences = $this->preferenceService->getUserPreferences($user);

        return view('admin.pages.notifications.preferences', compact('user', 'preferences'));
    }
}
