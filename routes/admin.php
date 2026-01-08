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
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AssignmentController;
use App\Http\Controllers\Admin\AssignmentQuestionController;
use App\Http\Controllers\Admin\AssignmentSubmissionController;
use App\Http\Controllers\Api\SessionActivityController;
use App\Http\Controllers\Admin\AnalyticsDashboardController;
use App\Http\Controllers\Admin\NotificationPreferenceController as AdminNotificationPreferenceController;
use App\Http\Controllers\Admin\ZoomMeetingController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LiveSessionController;

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

        // الواجبات
        Route::resource('assignments', AssignmentController::class);
        Route::get('assignments/get-assignable-items', [AssignmentController::class, 'getAssignableItems'])
            ->name('assignments.get-assignable-items');
        Route::post('assignments/{assignment}/publish', [AssignmentController::class, 'publish'])
            ->name('assignments.publish');
        Route::post('assignments/{assignment}/unpublish', [AssignmentController::class, 'unpublish'])
            ->name('assignments.unpublish');
        Route::post('assignments/{assignment}/duplicate', [AssignmentController::class, 'duplicate'])
            ->name('assignments.duplicate');
        
        // أسئلة الواجبات
        Route::post('assignments/{assignment}/questions', [AssignmentQuestionController::class, 'store'])
            ->name('assignments.questions.store');
        Route::put('assignments/{assignment}/questions/{question}', [AssignmentQuestionController::class, 'update'])
            ->name('assignments.questions.update');
        Route::delete('assignments/{assignment}/questions/{question}', [AssignmentQuestionController::class, 'destroy'])
            ->name('assignments.questions.destroy');
        Route::post('assignments/{assignment}/questions/reorder', [AssignmentQuestionController::class, 'reorder'])
            ->name('assignments.questions.reorder');
        
        // إرسالات الواجبات
        Route::get('assignments/{assignment}/submissions', [AssignmentSubmissionController::class, 'index'])
            ->name('assignments.submissions.index');
        Route::get('assignments/{assignment}/submissions/{submission}', [AssignmentSubmissionController::class, 'show'])
            ->name('assignments.submissions.show');
        Route::post('assignments/{assignment}/submissions/{submission}/grade', [AssignmentSubmissionController::class, 'grade'])
            ->name('assignments.submissions.grade');
        Route::post('assignments/{assignment}/submissions/{submission}/return', [AssignmentSubmissionController::class, 'return'])
            ->name('assignments.submissions.return');
        Route::get('assignments/{assignment}/submissions/export', [AssignmentSubmissionController::class, 'export'])
            ->name('assignments.submissions.export');

        // ===============================================
        // المكتبة الرقمية
        // ===============================================
        Route::resource('library/categories', \App\Http\Controllers\Admin\LibraryCategoryController::class)->names([
            'index' => 'library.categories.index',
            'create' => 'library.categories.create',
            'store' => 'library.categories.store',
            'show' => 'library.categories.show',
            'edit' => 'library.categories.edit',
            'update' => 'library.categories.update',
            'destroy' => 'library.categories.destroy',
        ]);
        Route::get('library/items/subjects-by-class', [\App\Http\Controllers\Admin\LibraryItemController::class, 'getSubjectsByClass'])
            ->name('library.items.subjects-by-class');
        Route::resource('library/items', \App\Http\Controllers\Admin\LibraryItemController::class)->names([
            'index' => 'library.items.index',
            'create' => 'library.items.create',
            'store' => 'library.items.store',
            'show' => 'library.items.show',
            'edit' => 'library.items.edit',
            'update' => 'library.items.update',
            'destroy' => 'library.items.destroy',
        ]);
        Route::get('library/items/{item}/preview', [\App\Http\Controllers\Admin\LibraryItemController::class, 'preview'])
            ->name('library.items.preview');
        Route::get('library/items/{item}/download', [\App\Http\Controllers\Admin\LibraryItemController::class, 'download'])
            ->name('library.items.download');
        Route::get('library/items/{item}/stats', [\App\Http\Controllers\Admin\LibraryItemController::class, 'stats'])
            ->name('library.items.stats');
        Route::resource('library/tags', \App\Http\Controllers\Admin\LibraryTagController::class)->except(['create', 'edit', 'update', 'show'])->names([
            'index' => 'library.tags.index',
            'store' => 'library.tags.store',
            'destroy' => 'library.tags.destroy',
        ]);
        
        // تقارير المكتبة
        Route::get('library/reports/most-downloaded', [\App\Http\Controllers\Admin\LibraryReportController::class, 'exportMostDownloaded'])
            ->name('library.reports.most-downloaded');
        Route::get('library/reports/most-viewed', [\App\Http\Controllers\Admin\LibraryReportController::class, 'exportMostViewed'])
            ->name('library.reports.most-viewed');
        Route::get('library/reports/categories-usage', [\App\Http\Controllers\Admin\LibraryReportController::class, 'exportCategoriesUsage'])
            ->name('library.reports.categories-usage');

        // لوحة إحصائيات المكتبة
        Route::get('library/dashboard', [\App\Http\Controllers\Admin\LibraryDashboardController::class, 'index'])
            ->name('library.dashboard');

        // لوحة تحكم Analytics الموحدة
        Route::get('analytics-dashboard', [AnalyticsDashboardController::class, 'index'])
            ->name('analytics.dashboard');

        // ===============================================
        // التقويم والجدولة
        // ===============================================
        Route::get('calendar', [\App\Http\Controllers\Admin\CalendarController::class, 'index'])
            ->name('calendar.index');
        Route::get('calendar/events-api', [\App\Http\Controllers\Admin\CalendarController::class, 'getEvents'])
            ->name('calendar.events-api');
        Route::resource('calendar/events', \App\Http\Controllers\Admin\CalendarController::class)->names([
            'index' => 'calendar.events.index',
            'create' => 'calendar.events.create',
            'store' => 'calendar.events.store',
            'edit' => 'calendar.events.edit',
            'update' => 'calendar.events.update',
            'destroy' => 'calendar.events.destroy',
        ]);
        Route::resource('calendar/reminders', \App\Http\Controllers\Admin\ReminderController::class)->names([
            'index' => 'calendar.reminders.index',
            'create' => 'calendar.reminders.create',
            'store' => 'calendar.reminders.store',
            'edit' => 'calendar.reminders.edit',
            'update' => 'calendar.reminders.update',
            'destroy' => 'calendar.reminders.destroy',
        ]);

        // ===============================================
        // نظام الذكاء الاصطناعي
        // ===============================================
        Route::resource('ai/models', \App\Http\Controllers\Admin\AIModelController::class)->names([
            'index' => 'ai.models.index',
            'create' => 'ai.models.create',
            'store' => 'ai.models.store',
            'edit' => 'ai.models.edit',
            'update' => 'ai.models.update',
            'destroy' => 'ai.models.destroy',
        ]);
        Route::post('ai/models/{model}/test', [\App\Http\Controllers\Admin\AIModelController::class, 'test'])->name('ai.models.test');
        Route::post('ai/models/test-temp', [\App\Http\Controllers\Admin\AIModelController::class, 'testTemp'])->name('ai.models.test-temp');
        Route::post('ai/models/{model}/set-default', [\App\Http\Controllers\Admin\AIModelController::class, 'setDefault'])->name('ai.models.set-default');
        Route::post('ai/models/{model}/toggle-active', [\App\Http\Controllers\Admin\AIModelController::class, 'toggleActive'])->name('ai.models.toggle-active');

        Route::get('ai/question-generations/create-advanced', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'createAdvanced'])->name('ai.question-generations.create-advanced');
        Route::post('ai/question-generations/store-advanced', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'storeAdvanced'])->name('ai.question-generations.store-advanced');
        Route::resource('ai/question-generations', \App\Http\Controllers\Admin\AIQuestionGenerationController::class)->names([
            'index' => 'ai.question-generations.index',
            'create' => 'ai.question-generations.create',
            'store' => 'ai.question-generations.store',
            'show' => 'ai.question-generations.show',
        ]);
        Route::post('ai/question-generations/{generation}/process', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'process'])->name('ai.question-generations.process');
        Route::post('ai/question-generations/{generation}/save', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'save'])->name('ai.question-generations.save');
        Route::post('ai/question-generations/{generation}/save-selected', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'saveSelected'])->name('ai.question-generations.save-selected');
        Route::post('ai/question-generations/{generation}/regenerate', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'regenerate'])->name('ai.question-generations.regenerate');

        Route::resource('ai/question-solutions', \App\Http\Controllers\Admin\AIQuestionSolvingController::class)->names([
            'index' => 'ai.question-solutions.index',
            'show' => 'ai.question-solutions.show',
        ]);
        Route::post('ai/question-solutions/solve/{question}', [\App\Http\Controllers\Admin\AIQuestionSolvingController::class, 'solve'])->name('ai.question-solutions.solve');
        Route::post('ai/question-solutions/solve-multiple', [\App\Http\Controllers\Admin\AIQuestionSolvingController::class, 'solveMultiple'])->name('ai.question-solutions.solve-multiple');
        Route::post('ai/question-solutions/{solution}/verify', [\App\Http\Controllers\Admin\AIQuestionSolvingController::class, 'verify'])->name('ai.question-solutions.verify');

        // AI Content Routes
        Route::post('ai/content/summarize', [\App\Http\Controllers\Admin\AIContentController::class, 'summarize'])->name('ai.content.summarize');
        Route::get('lessons/{lesson}/summary', [\App\Http\Controllers\Admin\AIContentController::class, 'lessonSummary'])->name('lessons.summary');
        Route::post('ai/content/improve', [\App\Http\Controllers\Admin\AIContentController::class, 'improve'])->name('ai.content.improve');
        Route::post('ai/content/grammar-check', [\App\Http\Controllers\Admin\AIContentController::class, 'grammarCheck'])->name('ai.content.grammar-check');

        // AI Student Feedback Routes
        Route::get('ai/student-feedback', [\App\Http\Controllers\Admin\AIStudentFeedbackController::class, 'index'])->name('ai.student-feedback.index');
        Route::get('ai/student-feedback/{studentFeedback}', [\App\Http\Controllers\Admin\AIStudentFeedbackController::class, 'show'])->name('ai.student-feedback.show');
        Route::post('students/{student}/ai-feedback', [\App\Http\Controllers\Admin\AIStudentFeedbackController::class, 'generateFeedback'])->name('ai.student-feedback.generate');

        Route::get('ai/settings', [\App\Http\Controllers\Admin\AISettingsController::class, 'index'])->name('ai.settings.index');
        Route::put('ai/settings', [\App\Http\Controllers\Admin\AISettingsController::class, 'update'])->name('ai.settings.update');

        // ===============================================
        // نظام النسخ الاحتياطي
        // ===============================================
        Route::resource('backups', \App\Http\Controllers\Admin\BackupController::class);
        Route::post('backups/{backup}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
        Route::get('backups/{backup}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');
        Route::get('backups/stats', [\App\Http\Controllers\Admin\BackupController::class, 'stats'])->name('backups.stats');

        Route::resource('backup-schedules', \App\Http\Controllers\Admin\BackupScheduleController::class);
        Route::post('backup-schedules/{schedule}/execute', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'execute'])->name('backup-schedules.execute');
        Route::post('backup-schedules/{schedule}/toggle-active', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'toggleActive'])->name('backup-schedules.toggle-active');

        Route::resource('backup-storage', \App\Http\Controllers\Admin\BackupStorageController::class, ['except' => ['show']])->parameters(['backup-storage' => 'config']);
        Route::post('backup-storage/{config}/test', [\App\Http\Controllers\Admin\BackupStorageController::class, 'test'])->name('backup-storage.test');
        Route::post('backup-storage/test-connection', [\App\Http\Controllers\Admin\BackupStorageController::class, 'testConnection'])->name('backup-storage.test-connection');
        Route::get('backup-storage/analytics', [\App\Http\Controllers\Admin\BackupStorageAnalyticsController::class, 'index'])->name('backup-storage.analytics');

        // تفضيلات إشعارات الطلاب (عرض فقط)
        Route::get('students/{user}/notification-preferences', [AdminNotificationPreferenceController::class, 'show'])
            ->name('students.notification-preferences.show');

        // App Storage
        Route::prefix('app-storage')->name('app-storage.')->group(function() {
            Route::resource('configs', \App\Http\Controllers\Admin\AppStorageController::class);
            Route::post('configs/{config}/test', [\App\Http\Controllers\Admin\AppStorageController::class, 'test'])->name('configs.test');
            Route::get('analytics', [\App\Http\Controllers\Admin\AppStorageAnalyticsController::class, 'index'])->name('analytics');
        });

        Route::resource('storage-disk-mappings', \App\Http\Controllers\Admin\StorageDiskMappingController::class);

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

        // مراقبة تقدم الطلاب
        Route::get('student-progress', [\App\Http\Controllers\Admin\AdminStudentProgressController::class, 'index'])
            ->name('student-progress.index');
        Route::get('student-progress/{user}', [\App\Http\Controllers\Admin\AdminStudentProgressController::class, 'showStudent'])
            ->name('student-progress.show');
        Route::get('student-progress/{user}/subject/{subject}', [\App\Http\Controllers\Admin\AdminStudentProgressController::class, 'showStudentSubject'])
            ->name('student-progress.subject');

        // التقارير
        Route::resource('reports', \App\Http\Controllers\Admin\ReportController::class);
        Route::get('reports/{id}/export/{format}', [\App\Http\Controllers\Admin\ReportController::class, 'export'])
            ->name('reports.export');
        Route::post('reports/{id}/schedule', [\App\Http\Controllers\Admin\ReportController::class, 'schedule'])
            ->name('reports.schedule');
        Route::get('reports/templates/list', [\App\Http\Controllers\Admin\ReportController::class, 'templates'])
            ->name('reports.templates');

        // قوالب التقارير - تم إزالتها مؤقتاً لأن الـ controller غير موجود
        // Route::resource('report-templates', \App\Http\Controllers\Admin\ReportTemplateController::class);
        // Route::post('report-templates/{id}/duplicate', [\App\Http\Controllers\Admin\ReportTemplateController::class, 'duplicate'])
        //     ->name('report-templates.duplicate');
        // Route::post('report-templates/{id}/set-default', [\App\Http\Controllers\Admin\ReportTemplateController::class, 'setDefault'])
        //     ->name('report-templates.set-default');

        // الإعدادات
        Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');
        Route::put('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
        Route::post('settings/{group}/reset', [\App\Http\Controllers\Admin\SettingsController::class, 'reset'])
            ->name('settings.reset');

        // لوحة التحكم
        Route::get('dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('dashboard/widgets', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'widgets'])
            ->name('dashboard.widgets');
        Route::post('dashboard/widgets/save', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'saveWidgets'])
            ->name('dashboard.widgets.save');

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
        Route::post('quiz-attempts/{attempt}/answers/{answer}/ai-grade', [QuizAttemptController::class, 'gradeWithAI'])
            ->name('quiz-attempts.ai-grade');
        Route::post('quiz-attempts/{attempt}/ai-grade-all', [QuizAttemptController::class, 'gradeMultipleWithAI'])
            ->name('quiz-attempts.ai-grade-all');
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
        
        // طلبات الانضمام للصف
        Route::get('enrollments/class-pending', [EnrollmentController::class, 'classPendingRequests'])
            ->name('enrollments.class-pending');
        Route::post('enrollments/class/{classEnrollment}/approve', [EnrollmentController::class, 'approveClassEnrollment'])
            ->name('enrollments.class.approve');
        Route::post('enrollments/class/{classEnrollment}/reject', [EnrollmentController::class, 'rejectClassEnrollment'])
            ->name('enrollments.class.reject');
        Route::post('enrollments/class/approve-multiple', [EnrollmentController::class, 'approveMultipleClassEnrollments'])
            ->name('enrollments.class.approve-multiple');
        Route::post('enrollments/class/reject-multiple', [EnrollmentController::class, 'rejectMultipleClassEnrollments'])
            ->name('enrollments.class.reject-multiple');

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
        // الأرشيف (Archived Users)
        // ===============================================
        Route::resource('archived-users', \App\Http\Controllers\Admin\ArchivedUserController::class)->except(['create', 'edit']);
        Route::post('archived-users/{archived_user}/restore', [\App\Http\Controllers\Admin\ArchivedUserController::class, 'restore'])
            ->name('archived-users.restore');
        Route::post('archived-users/bulk-restore', [\App\Http\Controllers\Admin\ArchivedUserController::class, 'bulkRestore'])
            ->name('archived-users.bulk-restore');
        Route::post('users/{user}/archive', [\App\Http\Controllers\Admin\ArchivedUserController::class, 'store'])
            ->name('users.archive');
        Route::post('users/bulk-archive', [\App\Http\Controllers\Admin\ArchivedUserController::class, 'bulkArchive'])
            ->name('users.bulk-archive');

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

        // ===============================================
        // نظام التحفيز (Gamification)
        // ===============================================
        Route::prefix('gamification')->as('gamification.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\GamificationController::class, 'index'])->name('index');
            Route::get('/settings', [\App\Http\Controllers\Admin\GamificationController::class, 'settings'])->name('settings');
            Route::post('/settings', [\App\Http\Controllers\Admin\GamificationController::class, 'saveSettings'])->name('settings.save');
            Route::post('/settings/reset', [\App\Http\Controllers\Admin\GamificationController::class, 'resetSettings'])->name('settings.reset');
            Route::get('/rules', [\App\Http\Controllers\Admin\GamificationController::class, 'rules'])->name('rules');
        });

        Route::resource('badges', \App\Http\Controllers\Admin\BadgeController::class);
        Route::resource('achievements', \App\Http\Controllers\Admin\AchievementController::class);
        Route::resource('levels', \App\Http\Controllers\Admin\LevelController::class);
        Route::resource('challenges', \App\Http\Controllers\Admin\ChallengeController::class);
        Route::resource('rewards', \App\Http\Controllers\Admin\RewardController::class);
        Route::resource('daily-tasks', \App\Http\Controllers\Admin\DailyTaskController::class);
        Route::resource('weekly-tasks', \App\Http\Controllers\Admin\WeeklyTaskController::class);
        
        Route::prefix('certificates')->as('certificates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CertificateController::class, 'index'])->name('index');
            Route::post('/generate', [\App\Http\Controllers\Admin\CertificateController::class, 'generate'])->name('generate');
            Route::get('/{certificate}/preview', [\App\Http\Controllers\Admin\CertificateController::class, 'preview'])->name('preview');
            Route::post('/{certificate}/regenerate', [\App\Http\Controllers\Admin\CertificateController::class, 'regenerate'])->name('regenerate');
            Route::get('/{certificate}/download', [\App\Http\Controllers\Admin\CertificateController::class, 'download'])->name('download');
            Route::get('/verify', [\App\Http\Controllers\Admin\CertificateController::class, 'verify'])->name('verify');
        });

        Route::prefix('leaderboards')->as('leaderboards.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LeaderboardController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\LeaderboardController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\LeaderboardController::class, 'store'])->name('store');
            Route::get('/{leaderboard}/edit', [\App\Http\Controllers\Admin\LeaderboardController::class, 'edit'])->name('edit');
            Route::put('/{leaderboard}', [\App\Http\Controllers\Admin\LeaderboardController::class, 'update'])->name('update');
            Route::post('/{leaderboard}/refresh', [\App\Http\Controllers\Admin\LeaderboardController::class, 'refresh'])->name('refresh');
        });

        // الإشعارات المخصصة
        Route::prefix('notifications')->as('notifications.')->group(function () {
            Route::get('/create', [NotificationController::class, 'create'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::get('/target-users', [NotificationController::class, 'getTargetUsers'])->name('target-users');
            Route::get('/all-users', [NotificationController::class, 'getAllUsers'])->name('all-users');
        });

        // التقييمات
        Route::prefix('reviews')->as('reviews.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('index');
            Route::get('/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('show');
            Route::post('/{review}/approve', [\App\Http\Controllers\Admin\ReviewController::class, 'approve'])->name('approve');
            Route::post('/{review}/reject', [\App\Http\Controllers\Admin\ReviewController::class, 'reject'])->name('reject');
            Route::delete('/{review}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('destroy');
            Route::post('/bulk-approve', [\App\Http\Controllers\Admin\ReviewController::class, 'bulkApprove'])->name('bulk-approve');
            Route::post('/bulk-reject', [\App\Http\Controllers\Admin\ReviewController::class, 'bulkReject'])->name('bulk-reject');
            Route::get('/settings', [\App\Http\Controllers\Admin\ReviewController::class, 'settings'])->name('settings');
            Route::post('/settings', [\App\Http\Controllers\Admin\ReviewController::class, 'saveSettings'])->name('settings.save');
        });

        // Live Sessions Routes
        Route::resource('live-sessions', LiveSessionController::class);

        // Zoom Settings Routes
        Route::prefix('zoom')->name('zoom.')->group(function () {
            Route::get('/settings', [\App\Http\Controllers\Admin\ZoomSettingsController::class, 'index'])->name('settings.index');
            Route::put('/settings', [\App\Http\Controllers\Admin\ZoomSettingsController::class, 'update'])->name('settings.update');
            
            // Account Management
            Route::post('/accounts', [\App\Http\Controllers\Admin\ZoomSettingsController::class, 'storeAccount'])->name('accounts.store');
            Route::put('/accounts/{account}', [\App\Http\Controllers\Admin\ZoomSettingsController::class, 'updateAccount'])->name('accounts.update');
            Route::delete('/accounts/{account}', [\App\Http\Controllers\Admin\ZoomSettingsController::class, 'deleteAccount'])->name('accounts.delete');
            Route::post('/accounts/{account}/set-default', [\App\Http\Controllers\Admin\ZoomSettingsController::class, 'setDefault'])->name('accounts.set-default');
        });

        // Zoom Integration Routes
        Route::prefix('live-sessions/{liveSession}/zoom')
            ->name('live-sessions.zoom.')
            ->group(function () {
                Route::post('/create', [ZoomMeetingController::class, 'create'])
                    ->name('create');
                Route::post('/update', [ZoomMeetingController::class, 'update'])
                    ->name('update');
                Route::post('/cancel', [ZoomMeetingController::class, 'cancel'])
                    ->name('cancel');
                Route::get('/sync', [ZoomMeetingController::class, 'sync'])
                    ->name('sync');
                Route::get('/manage', [ZoomMeetingController::class, 'manage'])
                    ->name('manage');
            });

        // Attendance management routes
        Route::prefix('live-sessions/{liveSession}/attendance')
            ->name('live-sessions.attendance.')
            ->group(function () {
                Route::get('/', [AttendanceController::class, 'index'])
                    ->name('index');
                Route::get('/users/{user}', [AttendanceController::class, 'show'])
                    ->name('show');
                Route::get('/export/{format}', [AttendanceController::class, 'export'])
                    ->name('export');
                Route::get('/stats', [AttendanceController::class, 'stats'])
                    ->name('stats');
            });

        // Email Settings Routes
        Route::prefix('email-settings')->name('email-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmailSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\EmailSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\EmailSettingsController::class, 'testConnection'])->name('test-connection');
            Route::post('/send-test', [\App\Http\Controllers\Admin\EmailSettingsController::class, 'sendTestEmail'])->name('send-test');
        });

        // WhatsApp Settings Routes
        Route::prefix('whatsapp-settings')->name('whatsapp-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'testConnection'])->name('test-connection');
        });

        // WhatsApp Messages Routes
        Route::prefix('whatsapp-messages')->name('whatsapp-messages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'index'])->name('index');
            Route::get('/send', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'create'])->name('create');
            Route::get('/search-students', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'searchStudents'])->name('search-students');
            Route::post('/send', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'send'])->name('send');
            Route::post('/broadcast', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'broadcast'])->name('broadcast');
            Route::get('/broadcast/students-count', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'getStudentsCount'])->name('broadcast.students-count');
            Route::get('/{message}', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'show'])->name('show');
        });

        // Email Logs Routes
        Route::resource('email-logs', \App\Http\Controllers\Admin\EmailLogController::class)->only(['index', 'show', 'destroy']);

        // Email Templates Routes
        Route::resource('email-templates', \App\Http\Controllers\Admin\EmailTemplateController::class);
        Route::post('email-templates/{emailTemplate}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('email-templates.preview');

        // SMS Settings Routes
        Route::prefix('sms-settings')->name('sms-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'testConnection'])->name('test-connection');
            Route::post('/send-test', [\App\Http\Controllers\Admin\SMSSettingsController::class, 'sendTestSMS'])->name('send-test');
        });

        // SMS Logs Routes
        Route::resource('sms-logs', \App\Http\Controllers\Admin\SMSLogController::class)->only(['index', 'show']);

        // SMS Templates Routes
        Route::resource('sms-templates', \App\Http\Controllers\Admin\SMSTemplateController::class);
        Route::post('sms-templates/{smsTemplate}/preview', [\App\Http\Controllers\Admin\SMSTemplateController::class, 'preview'])->name('sms-templates.preview');

        // WhatsApp Settings Routes
        Route::prefix('whatsapp-settings')->name('whatsapp-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'testConnection'])->name('test-connection');
        });

        // WhatsApp Messages Routes
        Route::prefix('whatsapp-messages')->name('whatsapp-messages.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'index'])->name('index');
            Route::get('/send', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'create'])->name('create');
            Route::post('/send', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'send'])->name('send');
            Route::post('/broadcast', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'broadcast'])->name('broadcast');
            Route::get('/broadcast/students-count', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'getStudentsCount'])->name('broadcast.students-count');
            Route::get('/{message}', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'show'])->name('show');
        });
    });
