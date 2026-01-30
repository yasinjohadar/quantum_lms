<?php

use Illuminate\Support\Facades\Route;

// المعلم يستخدم نفس رابط الأدمن (admin) مع صلاحيات مخصصة فقط
// إعادة توجيه الروابط القديمة /teacher/* إلى /admin/* للتوافق مع الإشارات المرجعية
Route::middleware(['auth', 'check.user.active'])->prefix('teacher')->as('teacher.')->group(function () {
    Route::get('/classes', fn () => redirect()->route('admin.classes.index'))->name('classes.index');
    Route::get('/classes/{class}', fn ($class) => redirect()->route('admin.classes.show', $class))->name('classes.show');
    Route::get('/subjects', fn () => redirect()->route('admin.subjects.index'))->name('subjects.index');
    Route::get('/subjects/{subject}', fn ($subject) => redirect()->route('admin.subjects.show', $subject))->name('subjects.show');
});
