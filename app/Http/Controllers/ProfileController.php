<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use App\Models\QuizAttempt;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        
        // إذا كان الطالب، اعرض البروفايل المتقدم
        if ($user->hasRole('student')) {
            return $this->showStudentProfile($request);
        }
        
        return view('profile.edit', [
            'user' => $user,
        ]);
    }
    
    /**
     * عرض البروفايل المتقدم للطالب
     */
    public function showStudentProfile(Request $request): View
    {
        $user = $request->user();
        
        // تحميل البيانات مع العلاقات
        $user->load([
            'subjects.class.stage',
            'groups',
            'enrollments.subject.class.stage',
            'loginLogs' => function($query) {
                $query->latest('login_at')->limit(10);
            },
            'userSessions' => function($query) {
                $query->latest('started_at')->limit(5);
            }
        ]);
        
        // إحصائيات الاختبارات
        $quizStats = [
            'total_attempts' => QuizAttempt::where('user_id', $user->id)->count(),
            'completed_attempts' => QuizAttempt::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'timed_out'])->count(),
            'passed_attempts' => QuizAttempt::where('user_id', $user->id)
                ->where('passed', true)->count(),
            'average_score' => QuizAttempt::where('user_id', $user->id)
                ->whereIn('status', ['completed', 'timed_out'])
                ->avg('percentage') ?? 0,
            'recent_attempts' => QuizAttempt::with(['quiz.subject'])
                ->where('user_id', $user->id)
                ->latest('started_at')
                ->limit(5)
                ->get(),
        ];
        
        // إحصائيات عامة
        $generalStats = [
            'total_subjects' => $user->subjects()->count(),
            'total_groups' => $user->groups()->count(),
            'total_enrollments' => $user->enrollments()->count(),
            'active_enrollments' => $user->enrollments()->where('status', 'active')->count(),
            'total_logins' => $user->loginLogs()->count(),
            'total_sessions' => $user->userSessions()->count(),
        ];
        
        return view('student.pages.profile.index', compact('user', 'quizStats', 'generalStats'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
