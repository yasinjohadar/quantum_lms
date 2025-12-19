<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Student\StudentController;
use App\Http\Controllers\Student\StudentLessonController;
use App\Http\Controllers\Student\StudentEnrollmentController;
use App\Http\Controllers\Student\StudentQuestionController;
use App\Http\Controllers\Student\StudentQuizController;

Route::middleware(['auth', 'check.user.active'])->prefix('student')->as('student.')->group(function () {
    // لوحة تحكم الطالب
    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
    
    // الدروس والمواد الدراسية
    Route::get('/subjects', [StudentLessonController::class, 'subjects'])->name('subjects');
    Route::get('/subjects/{subject}', [StudentLessonController::class, 'showSubject'])->name('subjects.show');
    Route::get('/lessons/{lesson}', [StudentLessonController::class, 'showLesson'])->name('lessons.show');
    
    // طلبات الانضمام
    Route::get('/enrollments', [StudentEnrollmentController::class, 'index'])->name('enrollments.index');
    Route::get('/enrollments/class/{class}', [StudentEnrollmentController::class, 'showClass'])->name('enrollments.class.show');
    Route::post('/enrollments/request/{subject}', [StudentEnrollmentController::class, 'requestEnrollment'])->name('enrollments.request');
    Route::post('/enrollments/request-class/{class}', [StudentEnrollmentController::class, 'requestClassEnrollment'])->name('enrollments.request-class');
    Route::delete('/enrollments/cancel/{subject}', [StudentEnrollmentController::class, 'cancelRequest'])->name('enrollments.cancel');
    
    // الأسئلة المنفصلة
    Route::get('/questions/{question}/start', [StudentQuestionController::class, 'startAttempt'])->name('questions.start');
    Route::get('/questions/{question}/attempt/{attempt}', [StudentQuestionController::class, 'showQuestion'])->name('questions.show');
    Route::post('/questions/attempt/{attempt}/answer', [StudentQuestionController::class, 'saveAnswer'])->name('questions.save-answer');
    Route::post('/questions/attempt/{attempt}/submit', [StudentQuestionController::class, 'submitAnswer'])->name('questions.submit');
    Route::get('/questions/attempt/{attempt}/time', [StudentQuestionController::class, 'getRemainingTime'])->name('questions.time');
    
    // الاختبارات
    Route::get('/quizzes/{quiz}/start', [StudentQuizController::class, 'startQuiz'])->name('quizzes.start');
    Route::get('/quizzes/{quiz}/attempt/{attempt}', [StudentQuizController::class, 'showQuiz'])->name('quizzes.show');
    Route::post('/quizzes/attempt/{attempt}/answer', [StudentQuizController::class, 'saveAnswer'])->name('quizzes.save-answer');
    Route::post('/quizzes/attempt/{attempt}/submit', [StudentQuizController::class, 'submitQuiz'])->name('quizzes.submit');
    Route::get('/quizzes/{quiz}/attempt/{attempt}/result', [StudentQuizController::class, 'showResult'])->name('quizzes.result');
    Route::get('/quizzes/attempt/{attempt}/time', [StudentQuizController::class, 'getRemainingTime'])->name('quizzes.time');
});
