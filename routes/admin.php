<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubjectSectionController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LessonAttachmentController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\QuizAttemptController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\UserSessionController;
use App\Http\Controllers\Api\SessionActivityController;

Route::middleware(['auth', 'check.user.active', 'admin'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // المراحل الدراسية
        Route::resource('stages', StageController::class);

        // الصفوف الدراسية
        Route::resource('classes', ClassController::class);
        Route::get('classes/{class}/enrolled-students', [ClassController::class, 'enrolledStudents'])
            ->name('classes.enrolled-students');

        // المواد الدراسية
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects/{subject}/enrolled-students', [SubjectController::class, 'enrolledStudents'])
            ->name('subjects.enrolled-students');

        // أقسام المواد (داخل كل مادة)
        Route::post('subjects/{subject}/sections', [SubjectSectionController::class, 'store'])
            ->name('subjects.sections.store');
        Route::put('subject-sections/{section}', [SubjectSectionController::class, 'update'])
            ->name('subject-sections.update');
        Route::delete('subject-sections/{section}', [SubjectSectionController::class, 'destroy'])
            ->name('subject-sections.destroy');

        // الوحدات (داخل كل قسم)
        Route::post('sections/{section}/units', [UnitController::class, 'store'])
            ->name('sections.units.store');
        Route::put('units/{unit}', [UnitController::class, 'update'])
            ->name('units.update');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])
            ->name('units.destroy');

        // الدروس (داخل كل وحدة)
        Route::post('units/{unit}/lessons', [LessonController::class, 'store'])
            ->name('units.lessons.store');
        Route::get('lessons/{lesson}', [LessonController::class, 'show'])
            ->name('lessons.show');
        Route::put('lessons/{lesson}', [LessonController::class, 'update'])
            ->name('lessons.update');
        Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])
            ->name('lessons.destroy');

        // مرفقات الدروس
        Route::post('lessons/{lesson}/attachments', [LessonAttachmentController::class, 'store'])
            ->name('lessons.attachments.store');
        Route::put('attachments/{attachment}', [LessonAttachmentController::class, 'update'])
            ->name('attachments.update');
        Route::delete('attachments/{attachment}', [LessonAttachmentController::class, 'destroy'])
            ->name('attachments.destroy');

        // ربط الأسئلة بالوحدات
        Route::get('units/{unit}/questions', [UnitController::class, 'questions'])
            ->name('units.questions');
        Route::post('units/{unit}/questions', [UnitController::class, 'attachQuestions'])
            ->name('units.questions.attach');
        Route::delete('units/{unit}/questions/{question}', [UnitController::class, 'detachQuestion'])
            ->name('units.questions.detach');
        Route::get('units/{unit}/available-questions', [UnitController::class, 'availableQuestions'])
            ->name('units.available-questions');

        // ===============================================
        // نظام الاختبارات
        // ===============================================

        // بنك الأسئلة
        Route::resource('questions', QuestionController::class);
        Route::post('questions/{question}/duplicate', [QuestionController::class, 'duplicate'])
            ->name('questions.duplicate');
        Route::post('questions/{question}/toggle-status', [QuestionController::class, 'toggleStatus'])
            ->name('questions.toggle-status');
        Route::get('questions-export', [QuestionController::class, 'export'])
            ->name('questions.export');
        Route::get('questions-export-template', [QuestionController::class, 'exportTemplate'])
            ->name('questions.export.template');
        Route::get('questions-import', [QuestionController::class, 'showImport'])
            ->name('questions.import.show');
        Route::post('questions-import', [QuestionController::class, 'import'])
            ->name('questions.import');

        // الاختبارات
        Route::resource('quizzes', QuizController::class);
        Route::get('quizzes/{quiz}/questions', [QuizController::class, 'questions'])
            ->name('quizzes.questions');
        Route::post('quizzes/{quiz}/add-question', [QuizController::class, 'addQuestion'])
            ->name('quizzes.add-question');
        Route::delete('quizzes/{quiz}/remove-question/{question}', [QuizController::class, 'removeQuestion'])
            ->name('quizzes.remove-question');
        Route::post('quizzes/{quiz}/reorder-questions', [QuizController::class, 'reorderQuestions'])
            ->name('quizzes.reorder-questions');
        Route::put('quizzes/{quiz}/questions/{question}/points', [QuizController::class, 'updateQuestionPoints'])
            ->name('quizzes.update-question-points');
        Route::post('quizzes/{quiz}/duplicate', [QuizController::class, 'duplicate'])
            ->name('quizzes.duplicate');
        Route::post('quizzes/{quiz}/toggle-publish', [QuizController::class, 'togglePublish'])
            ->name('quizzes.toggle-publish');
        Route::get('quizzes/{quiz}/preview', [QuizController::class, 'preview'])
            ->name('quizzes.preview');
        Route::get('quizzes/{quiz}/results', [QuizController::class, 'results'])
            ->name('quizzes.results');
        Route::get('quizzes/{quiz}/export-results', [QuizController::class, 'exportResults'])
            ->name('quizzes.export-results');
        Route::get('quizzes-get-units', [QuizController::class, 'getUnits'])
            ->name('quizzes.get-units');

        // محاولات الاختبارات
        Route::get('quiz-attempts/needs-grading', [QuizAttemptController::class, 'needsGrading'])
            ->name('quiz-attempts.needs-grading');
        Route::get('quizzes/{quiz}/attempts', [QuizAttemptController::class, 'index'])
            ->name('quiz-attempts.index');
        Route::get('quiz-attempts/{attempt}', [QuizAttemptController::class, 'show'])
            ->name('quiz-attempts.show');
        Route::get('quiz-attempts/{attempt}/grade', [QuizAttemptController::class, 'grade'])
            ->name('quiz-attempts.grade');
        Route::post('quiz-attempts/{attempt}/save-grade', [QuizAttemptController::class, 'saveGrade'])
            ->name('quiz-attempts.save-grade');
        Route::post('quiz-attempts/{attempt}/regrade', [QuizAttemptController::class, 'regrade'])
            ->name('quiz-attempts.regrade');
        Route::delete('quiz-attempts/{attempt}', [QuizAttemptController::class, 'destroy'])
            ->name('quiz-attempts.destroy');
        Route::post('quizzes/{quiz}/reset-user-attempts', [QuizAttemptController::class, 'resetUserAttempts'])
            ->name('quiz-attempts.reset-user');
        Route::get('quizzes/{quiz}/statistics', [QuizAttemptController::class, 'statistics'])
            ->name('quiz-attempts.statistics');

        // ===============================================
        // نظام الانضمامات
        // ===============================================
        // Routes المخصصة يجب أن تكون قبل resource route
        Route::get('enrollments/search-students', [EnrollmentController::class, 'searchStudents'])
            ->name('enrollments.search-students');
        Route::get('enrollments/get-subjects-by-class', [EnrollmentController::class, 'getSubjectsByClass'])
            ->name('enrollments.get-subjects-by-class');
        Route::get('enrollments/pending', [EnrollmentController::class, 'pendingRequests'])
            ->name('enrollments.pending');
        Route::post('enrollments/{enrollment}/approve', [EnrollmentController::class, 'approve'])
            ->name('enrollments.approve');
        Route::post('enrollments/{enrollment}/reject', [EnrollmentController::class, 'reject'])
            ->name('enrollments.reject');
        Route::post('enrollments/approve-multiple', [EnrollmentController::class, 'approveMultiple'])
            ->name('enrollments.approve-multiple');
        Route::post('enrollments/reject-multiple', [EnrollmentController::class, 'rejectMultiple'])
            ->name('enrollments.reject-multiple');

        Route::resource('enrollments', EnrollmentController::class)->except(['show', 'edit', 'update']);

        // ===============================================
        // نظام المجموعات
        // ===============================================
        Route::resource('groups', GroupController::class);
        Route::get('groups/{group}/manage-students', [GroupController::class, 'manageStudents'])
            ->name('groups.manage-students');
        Route::post('groups/{group}/add-students', [GroupController::class, 'addStudents'])
            ->name('groups.add-students');
        Route::delete('groups/{group}/remove-student/{user}', [GroupController::class, 'removeStudent'])
            ->name('groups.remove-student');

        Route::get('groups/{group}/manage-classes', [GroupController::class, 'manageClasses'])
            ->name('groups.manage-classes');
        Route::post('groups/{group}/add-classes', [GroupController::class, 'addClasses'])
            ->name('groups.add-classes');
        Route::delete('groups/{group}/remove-class/{class}', [GroupController::class, 'removeClass'])
            ->name('groups.remove-class');

        Route::get('groups/{group}/manage-subjects', [GroupController::class, 'manageSubjects'])
            ->name('groups.manage-subjects');
        Route::post('groups/{group}/add-subjects', [GroupController::class, 'addSubjects'])
            ->name('groups.add-subjects');
        Route::delete('groups/{group}/remove-subject/{subject}', [GroupController::class, 'removeSubject'])
            ->name('groups.remove-subject');

        // ===============================================
        // سجلات الدخول
        // ===============================================
        Route::get('login-logs', [LoginLogController::class, 'index'])
            ->name('login-logs.index');
        Route::get('login-logs/{log}', [LoginLogController::class, 'show'])
            ->name('login-logs.show');
        Route::get('login-logs/user/{user}', [LoginLogController::class, 'userLogs'])
            ->name('login-logs.user');
        Route::get('login-logs/ip/{ip}', [LoginLogController::class, 'ipLogs'])
            ->name('login-logs.ip');
        Route::delete('login-logs/{log}', [LoginLogController::class, 'destroy'])
            ->name('login-logs.destroy');
        Route::post('login-logs/clear-old', [LoginLogController::class, 'clearOld'])
            ->name('login-logs.clear-old');

        // ===============================================
        // جلسات المستخدمين
        // ===============================================
        Route::get('user-sessions', [UserSessionController::class, 'index'])
            ->name('user-sessions.index');
        Route::get('user-sessions/{session}', [UserSessionController::class, 'show'])
            ->name('user-sessions.show');
        Route::get('user-sessions/{session}/activities', [UserSessionController::class, 'activities'])
            ->name('user-sessions.activities');
        Route::get('user-sessions/user/{user}', [UserSessionController::class, 'userSessions'])
            ->name('user-sessions.user');
        Route::post('user-sessions/{session}/end', [UserSessionController::class, 'endSession'])
            ->name('user-sessions.end');
        Route::delete('user-sessions/{session}', [UserSessionController::class, 'destroy'])
            ->name('user-sessions.destroy');
        Route::post('user-sessions/clear-old', [UserSessionController::class, 'clearOld'])
            ->name('user-sessions.clear-old');

        // ===============================================
        // API لتسجيل أنشطة الجلسات
        // ===============================================
        Route::post('api/session-activities', [SessionActivityController::class, 'store'])
            ->name('api.session-activities.store'); // سيصبح admin.api.session-activities.store تلقائياً
    });
