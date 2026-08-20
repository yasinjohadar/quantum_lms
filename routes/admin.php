<?php

use App\Http\Controllers\Admin\AnalyticsDashboardController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\DistinguishedStudentController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\HeroSlideController;
use App\Http\Controllers\Admin\LessonAttachmentController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\LoginLogController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\NotificationPreferenceController as AdminNotificationPreferenceController;
use App\Http\Controllers\Admin\NotificationsInboxController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizAttemptController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\StageController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SubjectQuestionBankController;
use App\Http\Controllers\Admin\SubjectSectionController;
use App\Http\Controllers\Admin\UnitController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserSessionController;
use App\Http\Controllers\Api\SessionActivityController;
use Illuminate\Support\Facades\Route;

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
        Route::post('classes/{class}/toggle-status', [ClassController::class, 'toggleStatus'])
            ->name('classes.toggle-status');
        Route::post('classes/reorder', [ClassController::class, 'reorder'])
            ->name('classes.reorder');

        // شرائح Hero (سلايدر الصفحة الرئيسية)
        Route::resource('hero-slides', HeroSlideController::class);

        // الطلاب المتميزون (الصفحة الرئيسية)
        Route::get('distinguished-students/students-by-class', [DistinguishedStudentController::class, 'studentsByClass'])
            ->name('distinguished-students.students-by-class');
        Route::resource('distinguished-students', DistinguishedStudentController::class);

        // روابط التواصل الاجتماعي (ديناميكية)
        Route::resource('social-links', SocialLinkController::class);

        // المواد الدراسية
        Route::get('subjects/linkable/subjects-by-class', [SubjectController::class, 'linkableSubjectsByClass'])
            ->name('subjects.linkable.subjects-by-class');
        Route::get('subjects/linkable/sections', [SubjectController::class, 'linkableSectionsBySubject'])
            ->name('subjects.linkable.sections');
        Route::get('subjects/linkable/units', [SubjectController::class, 'linkableUnits'])
            ->name('subjects.linkable.units');
        Route::resource('subjects', SubjectController::class);
        Route::get('subjects/{subject}/enrolled-students', [SubjectController::class, 'enrolledStudents'])
            ->name('subjects.enrolled-students');
        Route::post('subjects/{subject}/toggle-status', [SubjectController::class, 'toggleStatus'])
            ->name('subjects.toggle-status');
        Route::post('subjects/{subject}/toggle-free-override', [SubjectController::class, 'toggleFreeOverride'])
            ->name('subjects.toggle-free-override');
        Route::post('subjects/reorder', [SubjectController::class, 'reorder'])
            ->name('subjects.reorder');

        Route::get('subjects/{subject}/questions', [SubjectQuestionBankController::class, 'index'])
            ->name('subjects.questions.index');
        Route::get('subjects/{subject}/questions/create', [SubjectQuestionBankController::class, 'create'])
            ->name('subjects.questions.create');
        Route::get('subjects/{subject}/questions/import', [SubjectQuestionBankController::class, 'import'])
            ->name('subjects.questions.import');
        Route::get('subjects/{subject}/questions/ai-create', [SubjectQuestionBankController::class, 'aiCreate'])
            ->name('subjects.questions.ai-create');
        Route::get('subjects/{subject}/questions/ai-create-from-image', [SubjectQuestionBankController::class, 'aiCreateFromImage'])
            ->name('subjects.questions.ai-create-from-image');
        Route::get('subjects/{subject}/quizzes-for-add', [SubjectQuestionBankController::class, 'quizzesForAdd'])
            ->name('subjects.quizzes.for-add');
        Route::delete('subjects/{subject}/questions/destroy-multiple', [SubjectQuestionBankController::class, 'destroyMultiple'])
            ->name('subjects.questions.destroy-multiple');
        Route::post('subjects/{subject}/questions/export-word', [SubjectQuestionBankController::class, 'exportWord'])
            ->name('subjects.questions.export-word');
        Route::get('subjects/{subject}/questions/bulk-ids', [SubjectQuestionBankController::class, 'bulkSelectableIds'])
            ->name('subjects.questions.bulk-ids');

        // أقسام المواد (داخل كل مادة)
        Route::post('subjects/{subject}/sections', [SubjectSectionController::class, 'store'])
            ->name('subjects.sections.store');
        Route::post('subjects/{subject}/sections/reorder', [SubjectSectionController::class, 'reorder'])
            ->name('subjects.sections.reorder');
        Route::put('subject-sections/{section}', [SubjectSectionController::class, 'update'])
            ->name('subject-sections.update');
        Route::delete('subject-sections/{section}', [SubjectSectionController::class, 'destroy'])
            ->name('subject-sections.destroy');
        Route::get('sections/{section}/linked-subjects', [SubjectSectionController::class, 'getLinkedSubjects'])
            ->name('sections.linked-subjects');
        Route::post('sections/{section}/link-subjects', [SubjectSectionController::class, 'linkSubjects'])
            ->name('sections.link-subjects');

        // الوحدات (داخل كل قسم)
        Route::post('sections/{section}/units', [UnitController::class, 'store'])
            ->name('sections.units.store');
        Route::post('sections/{section}/units/reorder', [UnitController::class, 'reorder'])
            ->name('sections.units.reorder');
        Route::put('units/{unit}', [UnitController::class, 'update'])
            ->name('units.update');
        Route::delete('units/{unit}', [UnitController::class, 'destroy'])
            ->name('units.destroy');

        // الدروس
        Route::get('lessons', [LessonController::class, 'index'])
            ->name('lessons.index');
        Route::post('units/{unit}/lessons', [LessonController::class, 'store'])
            ->name('units.lessons.store');
        Route::post('sections/{section}/lessons', [LessonController::class, 'storeForSection'])
            ->name('sections.lessons.store');
        Route::post('units/{unit}/lessons/reorder', [LessonController::class, 'reorder'])
            ->name('units.lessons.reorder');
        Route::get('lessons/{lesson}', [LessonController::class, 'show'])
            ->name('lessons.show');
        Route::get('lessons/{lesson}/edit', [LessonController::class, 'edit'])
            ->name('lessons.edit');
        Route::put('lessons/{lesson}', [LessonController::class, 'update'])
            ->name('lessons.update');
        Route::get('lessons/{lesson}/linked-units', [LessonController::class, 'getLinkedUnits'])
            ->name('lessons.linked-units');
        Route::post('lessons/{lesson}/link-units', [LessonController::class, 'linkUnits'])
            ->name('lessons.link-units');
        Route::delete('lessons/{lesson}', [LessonController::class, 'destroy'])
            ->name('lessons.destroy');
        // مراجعة الدروس
        Route::post('lessons/{lesson}/approve-review', [LessonController::class, 'approveReview'])
            ->name('lessons.approve-review');
        Route::post('lessons/{lesson}/reject-review', [LessonController::class, 'rejectReview'])
            ->name('lessons.reject-review');

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
        Route::get('questions/ajax/classes/{schoolClass}/subjects', [QuestionController::class, 'ajaxSubjectsByClass'])
            ->name('questions.ajax.subjects-by-class');
        Route::get('questions/ajax/subjects/{subject}/units', [QuestionController::class, 'ajaxUnitsBySubject'])
            ->name('questions.ajax.units-by-subject');
        Route::delete('questions/destroy-multiple', [QuestionController::class, 'destroyMultiple'])
            ->name('questions.destroy-multiple');
        Route::resource('questions', QuestionController::class);
        Route::post('questions/math-preview', [QuestionController::class, 'mathPreview'])
            ->name('questions.math-preview');
        Route::post('questions/upload-image', [QuestionController::class, 'uploadImage'])
            ->name('questions.upload-image');
        Route::post('questions/{question}/duplicate', [QuestionController::class, 'duplicate'])
            ->name('questions.duplicate');
        Route::post('questions/{question}/toggle-status', [QuestionController::class, 'toggleStatus'])
            ->name('questions.toggle-status');
        Route::get('questions-export', [QuestionController::class, 'export'])
            ->name('questions.export');
        Route::post('questions-export-word', [QuestionController::class, 'exportWord'])
            ->name('questions.export-word');
        Route::get('questions/bulk-ids', [QuestionController::class, 'bulkSelectableIds'])
            ->name('questions.bulk-ids');
        Route::get('questions-export-template', [QuestionController::class, 'exportTemplate'])
            ->name('questions.export.template');
        Route::get('questions-import', [QuestionController::class, 'showImport'])
            ->name('questions.import.show');
        Route::post('questions-import', [QuestionController::class, 'import'])
            ->name('questions.import');
        Route::post('questions-import/nerve-test/parse', [\App\Http\Controllers\Admin\NerveTestQuestionImportController::class, 'parse'])
            ->name('questions.nerve-test.parse');
        Route::post('questions-import/nerve-test/import', [\App\Http\Controllers\Admin\NerveTestQuestionImportController::class, 'import'])
            ->name('questions.nerve-test.import');
        Route::post('questions-import/question-pack/parse', [\App\Http\Controllers\Admin\QuestionPackImportController::class, 'parse'])
            ->name('questions.question-pack.parse');
        Route::post('questions-import/question-pack/import', [\App\Http\Controllers\Admin\QuestionPackImportController::class, 'import'])
            ->name('questions.question-pack.import');
        Route::post('questions-import/math/parse', [\App\Http\Controllers\Admin\MathQuestionImportController::class, 'parse'])
            ->name('questions.math.parse');
        Route::post('questions-import/math/import', [\App\Http\Controllers\Admin\MathQuestionImportController::class, 'import'])
            ->name('questions.math.import');
        Route::get('questions-math-backfill/status', [\App\Http\Controllers\Admin\QuestionMathBackfillController::class, 'status'])
            ->name('questions.math-backfill.status');
        Route::post('questions-math-backfill/process-batch', [\App\Http\Controllers\Admin\QuestionMathBackfillController::class, 'processBatch'])
            ->name('questions.math-backfill.process-batch');
        Route::get('questions-math-backfill/ai-repair-status', [\App\Http\Controllers\Admin\QuestionMathBackfillController::class, 'aiRepairStatus'])
            ->name('questions.math-backfill.ai-repair-status');
        Route::post('questions-math-backfill/ai-repair-batch', [\App\Http\Controllers\Admin\QuestionMathBackfillController::class, 'processAiRepairBatch'])
            ->name('questions.math-backfill.ai-repair-batch');

        // لوحة تحكم Analytics الموحدة
        Route::get('analytics-dashboard', [AnalyticsDashboardController::class, 'index'])
            ->name('analytics.dashboard');

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
        Route::get('ai/question-generations/ajax/classes/{schoolClass}/subjects', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'ajaxSubjectsByClass'])->name('ai.question-generations.ajax.subjects-by-class');
        Route::get('ai/question-generations/ajax/subjects/{subject}/lessons', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'ajaxLessonsBySubject'])->name('ai.question-generations.ajax.lessons-by-subject');
        Route::get('ai/question-generations/ajax/subjects/{subject}/units', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'ajaxUnitsBySubject'])->name('ai.question-generations.ajax.units-by-subject');
        Route::post('ai/question-generations/store-advanced', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'storeAdvanced'])->name('ai.question-generations.store-advanced');
        Route::get('ai/question-generations/create-from-image', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'createFromImage'])->name('ai.question-generations.create-from-image');
        Route::post('ai/question-generations/store-from-image', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'storeFromImage'])->name('ai.question-generations.store-from-image');
        Route::get('ai/question-generations/{generation}/source-image', [\App\Http\Controllers\Admin\AIQuestionGenerationController::class, 'sourceImage'])->name('ai.question-generations.source-image');
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
        // stats قبل {backup} حتى لا يُلتقط كمعرّف نسخة
        Route::get('backups/stats', [\App\Http\Controllers\Admin\BackupController::class, 'stats'])->name('backups.stats');
        Route::resource('backups', \App\Http\Controllers\Admin\BackupController::class)
            ->except(['edit', 'update']);
        Route::post('backups/{backup}/restore', [\App\Http\Controllers\Admin\BackupController::class, 'restore'])->name('backups.restore');
        Route::get('backups/{backup}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('backups.download');

        Route::resource('backup-schedules', \App\Http\Controllers\Admin\BackupScheduleController::class)
            ->parameters(['backup-schedules' => 'schedule'])
            ->except(['show']);
        Route::post('backup-schedules/{schedule}/execute', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'execute'])->name('backup-schedules.execute');
        Route::post('backup-schedules/{schedule}/toggle-active', [\App\Http\Controllers\Admin\BackupScheduleController::class, 'toggleActive'])->name('backup-schedules.toggle-active');

        // أماكن تخزين النسخ أُلغيت — تُستخدم أماكن التخزين العامة مع redirect للتوافق
        Route::redirect('backup-storage', '/admin/app-storage/configs')->name('backup-storage.index');
        Route::redirect('backup-storage/create', '/admin/app-storage/configs/create')->name('backup-storage.create');
        Route::redirect('backup-storage/analytics', '/admin/app-storage/analytics')->name('backup-storage.analytics');

        // تفضيلات إشعارات الطلاب (عرض فقط)
        Route::get('students/{user}/notification-preferences', [AdminNotificationPreferenceController::class, 'show'])
            ->name('students.notification-preferences.show');

        // App Storage
        Route::prefix('app-storage')->name('app-storage.')->group(function () {
            Route::resource('configs', \App\Http\Controllers\Admin\AppStorageController::class);
            Route::post('configs/{config}/test', [\App\Http\Controllers\Admin\AppStorageController::class, 'test'])->name('configs.test');
            Route::get('analytics', [\App\Http\Controllers\Admin\AppStorageAnalyticsController::class, 'index'])->name('analytics');
        });

        // Storage Migration
        Route::prefix('storage-migration')->name('storage-migration.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'index'])->name('index');
            Route::get('analyze/{disk?}', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'analyze'])->name('analyze');
            Route::post('migrate', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'startMigration'])->name('migrate');
            Route::post('migrate-all', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'startAllMigration'])->name('migrate-all');
            Route::get('batch/{batchId}', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'batchStatus'])->name('batch-status');
            Route::post('batch/{batchId}/cancel', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'cancelBatch'])->name('batch-cancel');
            Route::get('verify/{diskName}', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'verify'])->name('verify');
            Route::post('cleanup/{diskName}', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'cleanup'])->name('cleanup');
            Route::get('batches', [\App\Http\Controllers\Admin\StorageMigrationController::class, 'batches'])->name('batches');
        });

        // Media Monitoring
        Route::prefix('media-monitoring')->name('media-monitoring.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MediaMonitoringController::class, 'index'])->name('index');
            Route::post('retry-conversion/{conversion}', [\App\Http\Controllers\Admin\MediaMonitoringController::class, 'retryConversion'])->name('retry-conversion');
            Route::post('retry-dead-letter/{deadLetter}', [\App\Http\Controllers\Admin\MediaMonitoringController::class, 'retryDeadLetter'])->name('retry-dead-letter');
            Route::post('cleanup-orphans', [\App\Http\Controllers\Admin\MediaMonitoringController::class, 'cleanupOrphans'])->name('cleanup-orphans');
            Route::post('cleanup-soft-deleted', [\App\Http\Controllers\Admin\MediaMonitoringController::class, 'cleanupSoftDeleted'])->name('cleanup-soft-deleted');
        });

        // Media Management
        Route::prefix('media')->name('media.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('index');
            Route::get('/dead-letters', [\App\Http\Controllers\Admin\MediaController::class, 'deadLetters'])->name('dead-letters');
            Route::post('/dead-letters/{deadLetter}/retry', [\App\Http\Controllers\Admin\MediaController::class, 'retryDeadLetter'])->name('dead-letters.retry');
            Route::delete('/dead-letters/{deadLetter}', [\App\Http\Controllers\Admin\MediaController::class, 'deleteDeadLetter'])->name('dead-letters.delete');
            Route::post('/dead-letters/resolve-all', [\App\Http\Controllers\Admin\MediaController::class, 'resolveAllDeadLetters'])->name('dead-letters.resolve-all');
            Route::get('/conversions', [\App\Http\Controllers\Admin\MediaController::class, 'conversions'])->name('conversions');
            Route::post('/conversions/{conversion}/retry', [\App\Http\Controllers\Admin\MediaController::class, 'retryConversion'])->name('retry-conversion');
            Route::delete('/conversions/{conversion}', [\App\Http\Controllers\Admin\MediaController::class, 'deleteConversion'])->name('delete-conversion');
            Route::get('/orphans', [\App\Http\Controllers\Admin\MediaController::class, 'orphans'])->name('orphans');
            Route::post('/orphans/delete', [\App\Http\Controllers\Admin\MediaController::class, 'deleteOrphans'])->name('delete-orphans');
            Route::get('/{medium}', [\App\Http\Controllers\Admin\MediaController::class, 'show'])->name('show');
            Route::delete('/{medium}', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->name('destroy');
            Route::delete('/{medium}/soft', [\App\Http\Controllers\Admin\MediaController::class, 'softDelete'])->name('soft-delete');
            Route::post('/{medium}/restore', [\App\Http\Controllers\Admin\MediaController::class, 'restore'])->name('restore');
            Route::post('/{medium}/sync', [\App\Http\Controllers\Admin\MediaController::class, 'syncNow'])->name('sync');
        });

        Route::resource('storage-disk-mappings', \App\Http\Controllers\Admin\StorageDiskMappingController::class);

        // الاختبارات
        // يجب أن يكون هذا الـ route قبل Route::resource لتجنب التعارض مع quizzes/{quiz}
        Route::get('quizzes/get-classes-by-stage', [QuizController::class, 'getClassesByStage'])
            ->name('quizzes.get-classes-by-stage');
        Route::get('quizzes/get-subjects-by-class', [QuizController::class, 'getSubjectsByClass'])
            ->name('quizzes.get-subjects-by-class');
        Route::post('sections/{section}/quizzes', [QuizController::class, 'storeForSection'])
            ->name('sections.quizzes.store');
        Route::resource('quizzes', QuizController::class);
        Route::get('quizzes/{quiz}/import-excel', [QuizController::class, 'showImportExcel'])
            ->name('quizzes.import-excel.show');
        Route::post('quizzes/{quiz}/import-excel', [QuizController::class, 'importExcel'])
            ->name('quizzes.import-excel.store');
        Route::post('quizzes/{quiz}/import/nerve-test', [QuizController::class, 'importNerveTest'])
            ->name('quizzes.import-nerve-test.store');
        Route::post('quizzes/{quiz}/import/question-pack', [QuizController::class, 'importQuestionPack'])
            ->name('quizzes.import-question-pack.store');
        Route::post('quizzes/{quiz}/import/math', [QuizController::class, 'importMath'])
            ->name('quizzes.import-math.store');
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
        Route::get('quizzes/{quiz}/preview', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'start'])
            ->name('quizzes.preview');
        Route::get('quizzes/{quiz}/preview/take', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'show'])
            ->name('quizzes.preview.show');
        Route::post('quizzes/{quiz}/preview/answer', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'saveAnswer'])
            ->name('quizzes.preview.save-answer');
        Route::post('quizzes/{quiz}/preview/submit', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'submit'])
            ->name('quizzes.preview.submit');
        Route::get('quizzes/{quiz}/preview/result', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'result'])
            ->name('quizzes.preview.result');
        Route::get('quizzes/{quiz}/preview/time', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'time'])
            ->name('quizzes.preview.time');
        Route::get('quizzes/{quiz}/preview/exit', [\App\Http\Controllers\Admin\QuizPreviewController::class, 'exit'])
            ->name('quizzes.preview.exit');
        Route::get('quizzes/{quiz}/results', [QuizController::class, 'results'])
            ->name('quizzes.results');
        Route::get('quizzes/{quiz}/export-results', [QuizController::class, 'exportResults'])
            ->name('quizzes.export-results');
        Route::get('quizzes-get-units', [QuizController::class, 'getUnits'])
            ->name('quizzes.get-units');
        Route::get('quizzes-get-sections', [QuizController::class, 'getSectionsBySubject'])
            ->name('quizzes.get-sections');
        Route::get('quizzes-get-lessons-by-unit', [QuizController::class, 'getLessonsByUnit'])
            ->name('quizzes.get-lessons-by-unit');
        Route::post('quizzes/{quiz}/submit-for-review', [QuizController::class, 'submitForReview'])
            ->name('quizzes.submit-for-review');
        Route::post('quizzes/{quiz}/approve-review', [QuizController::class, 'approveReview'])
            ->name('quizzes.approve-review');
        Route::post('quizzes/{quiz}/reject-review', [QuizController::class, 'rejectReview'])
            ->name('quizzes.reject-review');
        Route::get('quizzes/{quiz}/linked-units', [QuizController::class, 'getLinkedUnits'])
            ->name('quizzes.linked-units');
        Route::post('quizzes/{quiz}/link-units', [QuizController::class, 'linkUnits'])
            ->name('quizzes.link-units');

        // قائمة المراجعة
        Route::get('review-queue', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'index'])
            ->name('review-queue.index');
        Route::get('review-queue/lessons', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'lessons'])
            ->name('review-queue.lessons');
        Route::get('review-queue/quizzes', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'quizzes'])
            ->name('review-queue.quizzes');
        Route::get('review-queue/learning-experiences', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'learningExperiences'])
            ->name('review-queue.learning-experiences');
        Route::post('review-queue/lessons/bulk-approve', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'bulkApproveLessons'])
            ->name('review-queue.lessons.bulk-approve');
        Route::post('review-queue/quizzes/bulk-approve', [\App\Http\Controllers\Admin\ReviewQueueController::class, 'bulkApproveQuizzes'])
            ->name('review-queue.quizzes.bulk-approve');

        // الملاحظات
        Route::post('review-comments', [\App\Http\Controllers\Admin\ReviewCommentController::class, 'store'])
            ->name('review-comments.store');
        Route::put('review-comments/{comment}', [\App\Http\Controllers\Admin\ReviewCommentController::class, 'update'])
            ->name('review-comments.update');
        Route::delete('review-comments/{comment}', [\App\Http\Controllers\Admin\ReviewCommentController::class, 'destroy'])
            ->name('review-comments.destroy');
        Route::post('review-comments/{comment}/reply', [\App\Http\Controllers\Admin\ReviewCommentController::class, 'reply'])
            ->name('review-comments.reply');
        Route::post('review-comments/{comment}/resolve', [\App\Http\Controllers\Admin\ReviewCommentController::class, 'resolve'])
            ->name('review-comments.resolve');
        Route::post('review-comments/{comment}/unresolve', [\App\Http\Controllers\Admin\ReviewCommentController::class, 'unresolve'])
            ->name('review-comments.unresolve');

        // مراقبة تقدم الطلاب
        Route::get('student-progress/get-subjects-by-class', [\App\Http\Controllers\Admin\AdminStudentProgressController::class, 'getSubjectsByClass'])
            ->name('student-progress.get-subjects-by-class');
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
        Route::post('enrollments/assign-class-to-user', [EnrollmentController::class, 'assignClassToUser'])
            ->name('enrollments.assign-class-to-user');
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
        Route::post('enrollments/clean-stale-pending', [EnrollmentController::class, 'cleanStalePendingEnrollments'])
            ->name('enrollments.clean-stale-pending');

        // طلبات الانضمام للصف
        Route::get('enrollments/class-pending', [EnrollmentController::class, 'classPendingRequests'])
            ->name('enrollments.class-pending');
        Route::post('enrollments/class/{classEnrollment}/approve', [EnrollmentController::class, 'approveClassEnrollment'])
            ->name('enrollments.class.approve');
        Route::post('enrollments/class/{classEnrollment}/reject', [EnrollmentController::class, 'rejectClassEnrollment'])
            ->name('enrollments.class.reject');
        Route::post('enrollments/class/approve-multiple', [EnrollmentController::class, 'approveMultipleClassEnrollments'])
            ->name('enrollments.class.approve-multiple');
        Route::post('enrollments/class/approve-all', [EnrollmentController::class, 'approveAllPendingClassEnrollments'])
            ->name('enrollments.class.approve-all');
        Route::post('enrollments/class/reject-multiple', [EnrollmentController::class, 'rejectMultipleClassEnrollments'])
            ->name('enrollments.class.reject-multiple');
        Route::post('enrollments/class/clean-stale-pending', [EnrollmentController::class, 'cleanStalePendingClassEnrollments'])
            ->name('enrollments.class.clean-stale-pending');
        Route::post('enrollments/destroy-multiple', [EnrollmentController::class, 'destroyMultiple'])
            ->name('enrollments.destroy-multiple');
        Route::get('enrollments/destroy-multiple', function () {
            return redirect()->route('admin.enrollments.index');
        })->name('enrollments.destroy-multiple.redirect');
        Route::get('enrollments/by-class', [EnrollmentController::class, 'enrollmentsByClass'])
            ->name('enrollments.by-class');
        Route::get('enrollments/count-by-class', [EnrollmentController::class, 'countByClass'])
            ->name('enrollments.count-by-class');
        Route::get('enrollments/count-by-subject', [EnrollmentController::class, 'countBySubject'])
            ->name('enrollments.count-by-subject');
        Route::post('enrollments/destroy-by-class', [EnrollmentController::class, 'destroyByClass'])
            ->name('enrollments.destroy-by-class');
        Route::post('enrollments/destroy-by-subject', [EnrollmentController::class, 'destroyBySubject'])
            ->name('enrollments.destroy-by-subject');

        Route::resource('enrollments', EnrollmentController::class)->except(['show', 'edit', 'update']);

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

        Route::post('users/bulk-update-roles', [UserController::class, 'bulkUpdateRoles'])
            ->name('users.bulk-update-roles');

        // قائمة المدراء (users with role=admin)
        Route::get('admins', [UserController::class, 'adminsIndex'])
            ->name('admins.index');

        // صفحة إدارة جميع المستخدمين (غير المؤرشفين)
        Route::get('users-management', [UserController::class, 'manageIndex'])
            ->name('users.manage');

        // ===============================================
        // المستخدمون المحذوفون سوفت (Soft Deleted Users)
        // ===============================================
        Route::get('users-trashed', [UserController::class, 'trashedIndex'])
            ->name('users.trashed.index');

        Route::delete('users-trashed/{user}/force-delete', [UserController::class, 'forceDestroy'])
            ->name('users.trashed.force-delete');

        // حذف نهائي مباشر من قائمة إدارة المستخدمين
        Route::delete('users/{user}/force-delete', [UserController::class, 'forceDestroyDirect'])
            ->name('users.force-delete');

        // السنوات الدراسية والأسابيع
        Route::resource('academic-years', \App\Http\Controllers\Admin\AcademicYearController::class);
        Route::post('academic-years/{academic_year}/activate', [\App\Http\Controllers\Admin\AcademicYearController::class, 'activate'])
            ->name('academic-years.activate');
        Route::resource('academic-weeks', \App\Http\Controllers\Admin\AcademicWeekController::class);
        Route::post('academic-years/{academic_year}/weeks/generate', [\App\Http\Controllers\Admin\AcademicWeekController::class, 'generate'])
            ->name('academic-years.weeks.generate');

        // تخصيص المعلمين
        Route::get('my-approved-lessons', [\App\Http\Controllers\Admin\TeacherOwnProgressController::class, 'approvedLessonsDetail'])
            ->name('my-approved-lessons');
        Route::get('teachers/progress', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'index'])
            ->name('teachers.progress.index');
        Route::get('teachers/{teacher}/approved-lessons', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'approvedLessonsDetail'])
            ->name('teachers.approved-lessons');
        Route::get('teachers/{teacher}/progress/material-pages', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'materialPages'])
            ->name('teachers.progress.material-pages');
        Route::get('teachers/{teacher}/progress', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'show'])
            ->name('teachers.progress.show');
        Route::get('teachers/{teacher}/progress-history', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'history'])
            ->name('teachers.progress.history');
        Route::post('teachers/{teacher}/week-target', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'storeWeekTarget'])
            ->name('teachers.week-target.store');
        Route::post('teachers/{teacher}/week-targets-bulk', [\App\Http\Controllers\Admin\TeacherProgressController::class, 'storeWeekTargetsBulk'])
            ->name('teachers.week-targets.bulk.store');
        Route::get('teachers/assignments', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'index'])
            ->name('teachers.assignments.index');
        Route::get('teachers/{teacher}/assignments', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'show'])
            ->name('teachers.assignments');
        Route::put('teachers/{teacher}/assignments', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'update'])
            ->name('teachers.assignments.update');
        Route::post('teachers/{teacher}/assignments/classes', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'attachClass'])
            ->name('teachers.assignments.attach-class');
        Route::delete('teachers/{teacher}/assignments/classes/{schoolClass}', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'detachClass'])
            ->name('teachers.assignments.detach-class');
        Route::post('teachers/{teacher}/assignments/subjects', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'attachSubject'])
            ->name('teachers.assignments.attach-subject');
        Route::delete('teachers/{teacher}/assignments/subjects/{subject}', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'detachSubject'])
            ->name('teachers.assignments.detach-subject');
        Route::patch('teachers/{teacher}/assignments/subjects/{subject}/required-pages', [\App\Http\Controllers\Admin\TeacherAssignmentController::class, 'patchSubjectRequiredPages'])
            ->name('teachers.assignments.subject-required-pages');

        // تخصيص المشرفين
        Route::get('supervisors/assignments/subjects-by-class', [\App\Http\Controllers\Admin\SupervisorAssignmentController::class, 'getSubjectsByClass'])
            ->name('supervisors.assignments.subjects-by-class');
        Route::get('supervisors/assignments', [\App\Http\Controllers\Admin\SupervisorAssignmentController::class, 'index'])
            ->name('supervisors.assignments.index');
        Route::get('supervisors/{supervisor}/overview', [\App\Http\Controllers\Admin\SupervisorAssignmentController::class, 'overview'])
            ->name('supervisors.overview');
        Route::get('supervisors/{supervisor}/assignments', [\App\Http\Controllers\Admin\SupervisorAssignmentController::class, 'show'])
            ->name('supervisors.assignments');
        Route::put('supervisors/{supervisor}/assignments', [\App\Http\Controllers\Admin\SupervisorAssignmentController::class, 'update'])
            ->name('supervisors.assignments.update');

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

        // ===============================================
        // Interactive Learning Engine (مستقل عن الاختبارات التقليدية)
        // ===============================================
        Route::post('learning-experiences/{learning_experience}/transition', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'transition'])
            ->name('learning-experiences.transition');
        Route::post('learning-experiences/{learning_experience}/submit-for-review', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'submitForReview'])
            ->name('learning-experiences.submit-for-review');
        Route::post('learning-experiences/{learning_experience}/approve-review', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'approveReview'])
            ->name('learning-experiences.approve-review');
        Route::post('learning-experiences/{learning_experience}/reject-review', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'rejectReview'])
            ->name('learning-experiences.reject-review');
        Route::post('learning-experiences/{learning_experience}/questions', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'addQuestion'])
            ->name('learning-experiences.questions.add');
        Route::post('learning-experiences/{learning_experience}/ai/patch', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'aiPatch'])
            ->name('learning-experiences.ai.patch');
        Route::post('learning-experiences/{learning_experience}/ai/apply', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'aiApply'])
            ->name('learning-experiences.ai.apply');
        Route::post('learning-experiences/{learning_experience}/ai/generate', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'aiGenerate'])
            ->name('learning-experiences.ai.generate');
        Route::post('learning-experiences/{learning_experience}/ai/generate-apply', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'aiGenerateApply'])
            ->name('learning-experiences.ai.generate-apply');
        Route::post('learning-experiences/{learning_experience}/ai/source/extract', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'aiSourceExtract'])
            ->name('learning-experiences.ai.source.extract');
        Route::post('learning-experiences/{learning_experience}/ai/source/generate', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'aiGenerateFromSource'])
            ->name('learning-experiences.ai.source.generate');
        Route::post('learning-experiences/{learning_experience}/import/parse', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'importParse'])
            ->name('learning-experiences.import.parse');
        Route::post('learning-experiences/{learning_experience}/import/apply', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'importApply'])
            ->name('learning-experiences.import.apply');
        Route::get('learning-experiences/import/template', [\App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class, 'importTemplate'])
            ->name('learning-experiences.import.template');
        Route::resource('learning-experiences', \App\InteractiveLearning\Http\Controllers\Admin\LearningExperienceController::class)
            ->except(['show']);

        // ===============================================
        // AI HTML Quizzes (صفحات اختبار مستقلة مولَّدة بالذكاء الاصطناعي)
        // ===============================================
        Route::post('ai-html-quizzes/{aiHtmlQuiz}/transition', [\App\AiHtmlQuiz\Http\Controllers\Admin\AiHtmlQuizController::class, 'transition'])
            ->name('ai-html-quizzes.transition');
        Route::get('ai-html-quizzes/{aiHtmlQuiz}/preview', [\App\AiHtmlQuiz\Http\Controllers\Admin\AiHtmlQuizController::class, 'previewBundle'])
            ->name('ai-html-quizzes.preview');
        Route::post('ai-html-quizzes/{aiHtmlQuiz}/ai/generate', [\App\AiHtmlQuiz\Http\Controllers\Admin\AiHtmlQuizController::class, 'aiGenerate'])
            ->name('ai-html-quizzes.ai.generate');
        Route::post('ai-html-quizzes/{aiHtmlQuiz}/ai/refine', [\App\AiHtmlQuiz\Http\Controllers\Admin\AiHtmlQuizController::class, 'aiRefine'])
            ->name('ai-html-quizzes.ai.refine');
        Route::post('ai-html-quizzes/{aiHtmlQuiz}/ai/apply', [\App\AiHtmlQuiz\Http\Controllers\Admin\AiHtmlQuizController::class, 'aiApply'])
            ->name('ai-html-quizzes.ai.apply');
        Route::resource('ai-html-quizzes', \App\AiHtmlQuiz\Http\Controllers\Admin\AiHtmlQuizController::class)
            ->except(['show'])
            ->parameters(['ai-html-quizzes' => 'aiHtmlQuiz']);

        Route::resource('badges', \App\Http\Controllers\Admin\BadgeController::class);
        Route::resource('achievements', \App\Http\Controllers\Admin\AchievementController::class);
        Route::resource('levels', \App\Http\Controllers\Admin\LevelController::class);
        Route::resource('challenges', \App\Http\Controllers\Admin\ChallengeController::class);
        Route::resource('rewards', \App\Http\Controllers\Admin\RewardController::class);
        Route::resource('daily-tasks', \App\Http\Controllers\Admin\DailyTaskController::class);
        Route::resource('weekly-tasks', \App\Http\Controllers\Admin\WeeklyTaskController::class);

        Route::prefix('leaderboards')->as('leaderboards.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\LeaderboardController::class, 'index'])->name('index');
            Route::get('/create', [\App\Http\Controllers\Admin\LeaderboardController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\LeaderboardController::class, 'store'])->name('store');
            Route::get('/{leaderboard}/edit', [\App\Http\Controllers\Admin\LeaderboardController::class, 'edit'])->name('edit');
            Route::put('/{leaderboard}', [\App\Http\Controllers\Admin\LeaderboardController::class, 'update'])->name('update');
            Route::post('/{leaderboard}/refresh', [\App\Http\Controllers\Admin\LeaderboardController::class, 'refresh'])->name('refresh');
        });

        // الإشعارات المخصصة + صندوق إشعارات الطاقم
        Route::prefix('notifications')->as('notifications.')->group(function () {
            Route::get('inbox', [NotificationsInboxController::class, 'index'])->name('inbox');
            Route::post('inbox/read-all', [NotificationsInboxController::class, 'markAllAsRead'])->name('inbox.read-all');
            Route::get('inbox/unread-count', [NotificationsInboxController::class, 'unreadCount'])->name('inbox.unread-count');
            Route::post('inbox/{notification}/read', [NotificationsInboxController::class, 'markAsRead'])->name('inbox.read');
            Route::post('inbox/{notification}/unread', [NotificationsInboxController::class, 'markAsUnread'])->name('inbox.unread');
            Route::delete('inbox/{notification}', [NotificationsInboxController::class, 'destroy'])->name('inbox.destroy');
            Route::get('/create', [NotificationController::class, 'create'])->name('create');
            Route::post('/', [NotificationController::class, 'store'])->name('store');
            Route::get('/target-users', [NotificationController::class, 'getTargetUsers'])->name('target-users');
            Route::get('/all-users', [NotificationController::class, 'getAllUsers'])->name('all-users');
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
            Route::get('/subjects-by-class', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'subjectsByClass'])->name('subjects-by-class');
            Route::delete('/destroy-multiple', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'destroyMultiple'])->name('destroy-multiple');
            Route::delete('/destroy-by-filter', [\App\Http\Controllers\Admin\WhatsAppMessageController::class, 'destroyByFilter'])->name('destroy-by-filter');
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

        // WhatsApp Templates Routes
        Route::resource('whatsapp-templates', \App\Http\Controllers\Admin\WhatsAppTemplateController::class);
        Route::post('whatsapp-templates/{whatsappTemplate}/preview', [\App\Http\Controllers\Admin\WhatsAppTemplateController::class, 'preview'])->name('whatsapp-templates.preview');

        // WhatsApp Settings Routes
        Route::prefix('whatsapp-settings')->name('whatsapp-settings.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'update'])->name('update');
            Route::post('/test-connection', [\App\Http\Controllers\Admin\WhatsAppSettingsController::class, 'testConnection'])->name('test-connection');
        });

        // المدفوعات
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('index');
            Route::post('pending-purchases/{purchase}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approvePendingPurchase'])->name('pending-purchases.approve');
            Route::post('pending-purchases/{purchase}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'rejectPendingPurchase'])->name('pending-purchases.reject');
            Route::get('{payment}', [\App\Http\Controllers\Admin\PaymentController::class, 'show'])->name('show');
            Route::post('{payment}/review', [\App\Http\Controllers\Admin\PaymentController::class, 'reviewPayment'])->name('review');
            Route::post('{payment}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approvePayment'])->name('approve');
            Route::post('{payment}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'rejectPayment'])->name('reject');
            Route::get('{payment}/download-receipt', [\App\Http\Controllers\Admin\PaymentController::class, 'downloadReceipt'])->name('download-receipt');
        });

        // وسائل الدفع المخصصة
        Route::resource('custom-payment-methods', \App\Http\Controllers\Admin\CustomPaymentMethodController::class);

        // العملات
        Route::resource('currencies', \App\Http\Controllers\Admin\CurrencyController::class);

        // أسعار الصرف
        Route::resource('exchange-rates', \App\Http\Controllers\Admin\ExchangeRateController::class);

        // مسارات المشرف - الصفوف والمواد المخصصة
        Route::get('my-classes', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'myClasses'])
            ->name('my-classes');
        Route::get('my-classes/{class}', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'showClass'])
            ->name('my-classes.show');
        Route::get('my-subjects', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'mySubjects'])
            ->name('my-subjects');
        Route::get('my-subjects/{subject}', [\App\Http\Controllers\Supervisor\SupervisorDashboardController::class, 'showSubject'])
            ->name('my-subjects.show');
    });

// Chrome Extension API (NotebookLM) — يُحمَّل مع admin.php على السيرفر
require __DIR__.'/extension-api.php';
