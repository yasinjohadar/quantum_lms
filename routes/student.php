<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentLessonController;
use App\Http\Controllers\Student\StudentEnrollmentController;
use App\Http\Controllers\Student\StudentQuestionController;
use App\Http\Controllers\Student\StudentQuizController;
use App\Http\Controllers\Student\StudentProgressController;
use App\Http\Controllers\Student\StudentAssignmentController;
use App\Http\Controllers\Student\NotificationPreferenceController as StudentNotificationPreferenceController;
use App\Http\Controllers\Student\ZoomJoinController;
use App\Http\Controllers\Student\AttendanceController;
use App\Http\Controllers\Student\LiveSessionController;

Route::middleware(['auth', 'check.user.active'])->prefix('student')->as('student.')->group(function () {
    // لوحة تحكم الطالب
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // الدروس والمواد الدراسية
    Route::get('/classes', [StudentLessonController::class, 'classes'])->name('classes');
    Route::get('/subjects', [StudentLessonController::class, 'subjects'])->name('subjects');
    Route::get('/subjects/{subject}', [StudentLessonController::class, 'showSubject'])->name('subjects.show');
    Route::get('/lessons/{lesson}', [StudentLessonController::class, 'showLesson'])->name('lessons.show');
    Route::post('/lessons/{lesson}/mark-status', [StudentLessonController::class, 'markLessonStatus'])->name('lessons.mark-status');
    
    // مراقبة التقدم
    Route::get('/progress', [StudentProgressController::class, 'index'])->name('progress.index');
    Route::get('/progress/subject/{subject}', [StudentProgressController::class, 'showSubject'])->name('progress.subject');
    Route::get('/progress/section/{section}', [StudentProgressController::class, 'showSection'])->name('progress.section');
    
    // الواجبات
    Route::get('/assignments', [StudentAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/{assignment}', [StudentAssignmentController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [StudentAssignmentController::class, 'submit'])->name('assignments.submit');
    Route::post('/assignments/{assignment}/resubmit', [StudentAssignmentController::class, 'resubmit'])->name('assignments.resubmit');
    Route::get('/assignments/{assignment}/submission', [StudentAssignmentController::class, 'viewSubmission'])->name('assignments.submission');
    Route::get('/assignments/{assignment}/submissions/{submission}/files/{fileId}/download', [StudentAssignmentController::class, 'downloadFile'])->name('assignments.files.download');
    
    // المكتبة الرقمية
    Route::get('/library', [\App\Http\Controllers\Student\StudentLibraryController::class, 'index'])->name('library.index');
    Route::get('/library/search', [\App\Http\Controllers\Student\StudentLibraryController::class, 'search'])->name('library.search');
    Route::get('/library/favorites', [\App\Http\Controllers\Student\StudentLibraryController::class, 'favorites'])->name('library.favorites');
    Route::get('/library/subject/{subject}', [\App\Http\Controllers\Student\StudentLibraryController::class, 'subjectLibrary'])->name('library.subject');
    Route::get('/library/{item}', [\App\Http\Controllers\Student\StudentLibraryController::class, 'show'])->name('library.show');
    Route::get('/library/{item}/preview', [\App\Http\Controllers\Student\StudentLibraryController::class, 'preview'])->name('library.preview');
    Route::post('/library/{item}/download', [\App\Http\Controllers\Student\StudentLibraryController::class, 'download'])->name('library.download');
    Route::post('/library/{item}/rate', [\App\Http\Controllers\Student\StudentLibraryController::class, 'rate'])->name('library.rate');
    Route::post('/library/{item}/toggle-favorite', [\App\Http\Controllers\Student\StudentLibraryController::class, 'toggleFavorite'])->name('library.toggle-favorite');
    
    // التقويم
    Route::get('/calendar', [\App\Http\Controllers\Student\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events-api', [\App\Http\Controllers\Student\CalendarController::class, 'getEvents'])->name('calendar.events-api');
    Route::get('/calendar/export', [\App\Http\Controllers\Student\CalendarController::class, 'export'])->name('calendar.export');
    Route::post('/calendar/events', [\App\Http\Controllers\Student\CalendarController::class, 'store'])->name('calendar.events.store');
    Route::get('/calendar/events/{event}', [\App\Http\Controllers\Student\CalendarController::class, 'show'])->name('calendar.events.show');
    Route::put('/calendar/events/{event}', [\App\Http\Controllers\Student\CalendarController::class, 'update'])->name('calendar.events.update');
    Route::delete('/calendar/events/{event}', [\App\Http\Controllers\Student\CalendarController::class, 'destroy'])->name('calendar.events.destroy');
    
    // المفكرة (Calendar Notes)
    Route::get('/calendar/notes-api', [\App\Http\Controllers\Student\CalendarController::class, 'getNotes'])->name('calendar.notes-api');
    Route::post('/calendar/notes', [\App\Http\Controllers\Student\CalendarController::class, 'storeNote'])->name('calendar.notes.store');
    Route::put('/calendar/notes/{note}', [\App\Http\Controllers\Student\CalendarController::class, 'updateNote'])->name('calendar.notes.update');
    Route::delete('/calendar/notes/{note}', [\App\Http\Controllers\Student\CalendarController::class, 'deleteNote'])->name('calendar.notes.destroy');
    Route::post('/calendar/notes/{note}/pin', [\App\Http\Controllers\Student\CalendarController::class, 'pinNote'])->name('calendar.notes.pin');
    
    // المساعد التعليمي (Chatbot)
    Route::resource('ai/chatbot', \App\Http\Controllers\Student\AIChatbotController::class)->names([
        'index' => 'ai.chatbot.index',
        'create' => 'ai.chatbot.create',
        'store' => 'ai.chatbot.store',
        'show' => 'ai.chatbot.show',
        'destroy' => 'ai.chatbot.destroy',
    ]);
    Route::post('ai/chatbot/{conversation}/send-message', [\App\Http\Controllers\Student\AIChatbotController::class, 'sendMessage'])->name('ai.chatbot.send-message');
    Route::get('ai/chatbot/{conversation}/history', [\App\Http\Controllers\Student\AIChatbotController::class, 'getHistory'])->name('ai.chatbot.history');
    Route::get('subjects/{subject}/lessons', function(\App\Models\Subject $subject) {
        $lessons = \App\Models\Lesson::whereHas('unit.section', function($q) use ($subject) {
            $q->where('subject_id', $subject->id);
        })->active()->get(['id', 'title']);
        return response()->json($lessons);
    })->name('subjects.lessons');
    
    // التقارير
    Route::get('/reports', [\App\Http\Controllers\Student\StudentReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{id}', [\App\Http\Controllers\Student\StudentReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{id}/export/{format}', [\App\Http\Controllers\Student\StudentReportController::class, 'export'])->name('reports.export');
    
    // طلبات الانضمام
    Route::get('/enrollments', [StudentEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/class/{class}', [StudentEnrollmentController::class, 'showClass'])->name('enrollments.class.show');
    Route::post('/enrollments/request/{subject}', [StudentEnrollmentController::class, 'requestEnrollment'])->name('enrollments.request');
    Route::post('/enrollments/request-class/{class}', [StudentEnrollmentController::class, 'requestClassEnrollment'])->name('enrollments.request-class');
    Route::delete('/enrollments/cancel/{subject}', [StudentEnrollmentController::class, 'cancelRequest'])->name('enrollments.cancel');
    
    // الأسئلة المنفصلة
    Route::get('/questions/start', [StudentQuestionController::class, 'startAttempt'])->name('questions.start');
    Route::get('/questions/{question}/start', [StudentQuestionController::class, 'startAttempt'])->name('questions.start.specific');
    Route::get('/questions/{question}/attempt/{attempt}', [StudentQuestionController::class, 'showQuestion'])->name('questions.show');
    Route::post('/questions/attempt/{attempt}/answer', [StudentQuestionController::class, 'saveAnswer'])->name('questions.save-answer');
    Route::post('/questions/attempt/{attempt}/submit', [StudentQuestionController::class, 'submitAnswer'])->name('questions.submit');
    Route::get('/questions/attempt/{attempt}/time', [StudentQuestionController::class, 'getRemainingTime'])->name('questions.time');
    Route::get('/lessons/{lesson}/questions/report', [StudentQuestionController::class, 'showReport'])->name('questions.report');
    
    // الاختبارات
    Route::get('/quizzes', [\App\Http\Controllers\Student\StudentQuizListController::class, 'index'])->name('quizzes.index');
    Route::get('/quizzes/results', [\App\Http\Controllers\Student\StudentQuizListController::class, 'results'])->name('quizzes.results');
    Route::get('/quizzes/{quiz}/start', [StudentQuizController::class, 'startQuiz'])->name('quizzes.start');
    Route::get('/quizzes/{quiz}/attempt/{attempt}', [StudentQuizController::class, 'showQuiz'])->name('quizzes.show');

    // AI Feedback
    Route::get('/ai-feedback', [\App\Http\Controllers\Student\AIStudentFeedbackController::class, 'index'])->name('ai-feedback.index');
    Route::get('/ai-feedback/{aiFeedback}', [\App\Http\Controllers\Student\AIStudentFeedbackController::class, 'show'])->name('ai-feedback.show');
    
    // نظام التحفيز
    Route::get('/tasks', [\App\Http\Controllers\Student\TaskController::class, 'index'])->name('tasks.index');
    
    Route::prefix('gamification')->as('gamification.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Student\GamificationController::class, 'dashboard'])->name('dashboard');
        Route::get('/badges', [\App\Http\Controllers\Student\GamificationController::class, 'badges'])->name('badges');
        Route::get('/achievements', [\App\Http\Controllers\Student\GamificationController::class, 'achievements'])->name('achievements');
        Route::get('/leaderboard', [\App\Http\Controllers\Student\GamificationController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/challenges', [\App\Http\Controllers\Student\GamificationController::class, 'challenges'])->name('challenges');
        Route::get('/rewards', [\App\Http\Controllers\Student\GamificationController::class, 'rewards'])->name('rewards');
        Route::post('/rewards/{reward}/claim', [\App\Http\Controllers\Student\GamificationController::class, 'claimReward'])->name('rewards.claim');
        Route::get('/certificates', [\App\Http\Controllers\Student\GamificationController::class, 'certificates'])->name('certificates');
        Route::get('/certificates/{certificate}/download', [\App\Http\Controllers\Student\GamificationController::class, 'downloadCertificate'])->name('certificates.download');
        Route::get('/stats', [\App\Http\Controllers\Student\GamificationController::class, 'stats'])->name('stats');
    });
    
    // الإشعارات
    Route::prefix('notifications')->as('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\NotificationController::class, 'index'])->name('index');
        Route::get('/latest', [\App\Http\Controllers\Student\NotificationController::class, 'latest'])->name('latest');
        Route::get('/stream', [\App\Http\Controllers\Student\NotificationStreamController::class, 'stream'])
            ->middleware('throttle:10,1')
            ->name('stream');
        Route::post('/{notification}/read', [\App\Http\Controllers\Student\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/{notification}/unread', [\App\Http\Controllers\Student\NotificationController::class, 'markAsUnread'])->name('unread');
        Route::delete('/{notification}', [\App\Http\Controllers\Student\NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/read-all', [\App\Http\Controllers\Student\NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::get('/unread-count', [\App\Http\Controllers\Student\NotificationController::class, 'getUnreadCount'])->name('unread-count');

        // تفضيلات الإشعارات
        Route::get('/preferences', [StudentNotificationPreferenceController::class, 'index'])->name('preferences.index');
        Route::post('/preferences', [StudentNotificationPreferenceController::class, 'update'])->name('preferences.update');
    });
    
    // التقييمات
    Route::prefix('reviews')->as('reviews.')->group(function () {
        Route::get('/create', [\App\Http\Controllers\Student\ReviewController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Student\ReviewController::class, 'store'])->name('store');
        Route::get('/{review}/edit', [\App\Http\Controllers\Student\ReviewController::class, 'edit'])->name('edit');
        Route::put('/{review}', [\App\Http\Controllers\Student\ReviewController::class, 'update'])->name('update');
        Route::delete('/{review}', [\App\Http\Controllers\Student\ReviewController::class, 'destroy'])->name('destroy');
        Route::post('/{review}/helpful', [\App\Http\Controllers\Student\ReviewController::class, 'toggleHelpful'])->name('toggle-helpful');
    });
    Route::post('/quizzes/attempt/{attempt}/answer', [StudentQuizController::class, 'saveAnswer'])->name('quizzes.save-answer');
    Route::post('/quizzes/attempt/{attempt}/submit', [StudentQuizController::class, 'submitQuiz'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/attempt/{attempt}/result', [StudentQuizController::class, 'showResult'])->name('quizzes.result');
    Route::get('/quizzes/attempt/{attempt}/time', [StudentQuizController::class, 'getRemainingTime'])->name('quizzes.time');

    // Live Sessions Routes
    Route::get('/live-sessions', [LiveSessionController::class, 'index'])->name('live-sessions.index');
    Route::get('/live-sessions/{liveSession}', [LiveSessionController::class, 'show'])->name('live-sessions.show');

    // Zoom Integration Routes
    Route::prefix('live-sessions/{liveSession}/zoom')
        ->name('live-sessions.zoom.')
        ->middleware(['throttle:10,1']) // 10 requests per minute
        ->group(function () {
            Route::post('/join-token', [ZoomJoinController::class, 'getJoinToken'])
                ->name('join-token');
            Route::get('/join', [ZoomJoinController::class, 'join'])
                ->name('join');
            Route::post('/on-join', [ZoomJoinController::class, 'onJoin'])
                ->name('on-join');
            Route::post('/on-leave', [ZoomJoinController::class, 'onLeave'])
                ->name('on-leave');
        });

    // Attendance routes for students
    Route::prefix('attendance')
        ->name('attendance.')
        ->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])
                ->name('index');
            Route::get('/sessions/{liveSession}', [AttendanceController::class, 'show'])
                ->name('show');
            Route::get('/stats', [AttendanceController::class, 'stats'])
                ->name('stats');
            Route::get('/stats/subject/{subject}', [AttendanceController::class, 'stats'])
                ->name('stats.subject');
        });
});