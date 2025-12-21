<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentLessonController;
use App\Http\Controllers\Student\StudentEnrollmentController;
use App\Http\Controllers\Student\StudentQuestionController;
use App\Http\Controllers\Student\StudentQuizController;
use App\Http\Controllers\Student\StudentProgressController;

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
    Route::get('/quizzes/{quiz}/start', [StudentQuizController::class, 'startQuiz'])->name('quizzes.start');
    Route::get('/quizzes/{quiz}/attempt/{attempt}', [StudentQuizController::class, 'showQuiz'])->name('quizzes.show');
    
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
        Route::get('/stream', [\App\Http\Controllers\Student\NotificationStreamController::class, 'stream'])
            ->middleware('throttle:10,1')
            ->name('stream');
        Route::post('/{notification}/read', [\App\Http\Controllers\Student\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/{notification}/unread', [\App\Http\Controllers\Student\NotificationController::class, 'markAsUnread'])->name('unread');
        Route::delete('/{notification}', [\App\Http\Controllers\Student\NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/read-all', [\App\Http\Controllers\Student\NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::get('/unread-count', [\App\Http\Controllers\Student\NotificationController::class, 'getUnreadCount'])->name('unread-count');
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
});
