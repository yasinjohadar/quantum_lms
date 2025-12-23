<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\NotificationPreferenceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationPreferenceController extends Controller
{
    protected NotificationPreferenceService $preferenceService;

    public function __construct(NotificationPreferenceService $preferenceService)
    {
        $this->middleware(['auth', 'check.user.active']);
        $this->preferenceService = $preferenceService;
    }

    /**
     * صفحة تفضيلات الإشعارات للطالب
     */
    public function index()
    {
        $user = Auth::user();
        $preferences = $this->preferenceService->getUserPreferences($user);

        return view('student.pages.notifications.preferences', compact('user', 'preferences'));
    }

    /**
     * حفظ تفضيلات الطالب
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $data = $request->input('preferences', []);

        $this->preferenceService->saveUserPreferences($user, $data);

        return redirect()->route('student.notifications.preferences.index')
            ->with('success', 'تم حفظ تفضيلات الإشعارات بنجاح.');
    }
}
