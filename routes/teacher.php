<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Teacher\TeacherClassController;
use App\Http\Controllers\Teacher\TeacherSubjectController;

Route::middleware(['auth', 'check.user.active'])->prefix('teacher')->as('teacher.')->group(function () {
    // الصفوف المخصصة للمعلم
    Route::get('/classes', [TeacherClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/{class}', [TeacherClassController::class, 'show'])->name('classes.show');
    
    // المواد المخصصة للمعلم
    Route::get('/subjects', [TeacherSubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/{subject}', [TeacherSubjectController::class, 'show'])->name('subjects.show');
});
