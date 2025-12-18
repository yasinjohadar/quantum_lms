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

Route::middleware(['auth', 'check.user.active'])
    ->prefix('admin')
    ->as('admin.')
    ->group(function () {
        // المراحل الدراسية
        Route::resource('stages', StageController::class);

        // الصفوف الدراسية
        Route::resource('classes', ClassController::class);

        // المواد الدراسية
        Route::resource('subjects', SubjectController::class);

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
    });
