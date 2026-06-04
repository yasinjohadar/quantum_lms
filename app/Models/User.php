<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'is_active',
        'photo',
        'created_by',
        'last_login_at',
        'last_login_ip',
        'last_login_user_agent',
        'weekly_lessons_target',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'weekly_lessons_target' => 'integer',
        ];
    }

    public function sessions()
    {
        return $this->hasMany(\App\Models\Session::class, 'user_id');
    }

    /**
     * العلاقة مع الانضمامات
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'user_id');
    }

    /**
     * العلاقة مع انضمامات الصفوف
     */
    public function classEnrollments()
    {
        return $this->hasMany(ClassEnrollment::class, 'user_id');
    }

    /**
     * العلاقة مع الطلاب المتميزين (عروض الصفحة الرئيسية)
     */
    public function distinguishedStudents()
    {
        return $this->hasMany(DistinguishedStudent::class);
    }

    /**
     * العلاقة مع المواد (Many-to-Many through enrollments)
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'enrollments', 'user_id', 'subject_id')
            ->withPivot(['enrolled_by', 'enrolled_at', 'status', 'notes'])
            ->withTimestamps()
            ->whereNull('enrollments.deleted_at');
    }

    /**
     * هل يمكن للطالب الوصول لمحتوى المادة (تسجيل مادة نشط أو انضمام صف معتمد للمواد النشطة ضمن ذلك الصف).
     */
    public function canAccessSubjectAsStudent(Subject $subject): bool
    {
        if (! $subject->is_active) {
            return false;
        }

        return app(\App\Services\Pricing\AccessResolver::class)->hasSubjectAccess($this, $subject);
    }

    /**
     * هل اكتمل انضمام الطالب لجميع المواد النشطة في الصف (تسجيل active لكل مادة).
     * يُستخدم في صفحة طلب الانضمام فقط — وليس كبديل لـ hasSubjectAccess لمحتوى الدروس.
     */
    public function hasFullAccessToSchoolClass(SchoolClass $class): bool
    {
        $activeSubjectIds = $class->subjects()
            ->where('is_active', true)
            ->pluck('subjects.id');

        if ($activeSubjectIds->isEmpty()) {
            return false;
        }

        $enrolledSubjectIds = $this->enrollments()
            ->where('status', 'active')
            ->whereIn('subject_id', $activeSubjectIds)
            ->pluck('subject_id')
            ->unique();

        return $enrolledSubjectIds->count() >= $activeSubjectIds->count();
    }

    /**
     * العلاقة مع سجلات الدخول
     */
    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class, 'user_id');
    }

    /**
     * العلاقة مع جلسات المستخدم
     */
    public function userSessions()
    {
        return $this->hasMany(UserSession::class, 'user_id');
    }

    /**
     * هل لدى المستخدم جلسة نشطة (متصل الآن)
     */
    public function hasActiveSession(): bool
    {
        return UserSession::where('user_id', $this->id)->where('status', 'active')->exists();
    }

    /**
     * العلاقة مع محاولات الأسئلة المنفصلة
     */
    public function questionAttempts()
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    /**
     * العلاقة مع محاولات الاختبارات
     */
    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * العلاقة مع إكمالات الدروس
     */
    public function lessonCompletions()
    {
        return $this->hasMany(LessonCompletion::class);
    }

    /**
     * العلاقات مع نظام التحفيز
     */
    public function pointTransactions()
    {
        return $this->hasMany(PointTransaction::class);
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('earned_at', 'metadata')
            ->withTimestamps();
    }

    public function userBadges()
    {
        return $this->hasMany(UserBadge::class);
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('progress', 'completed_at', 'metadata')
            ->withTimestamps();
    }

    public function userAchievements()
    {
        return $this->hasMany(UserAchievement::class);
    }

    public function userLevel()
    {
        return $this->hasOne(UserLevel::class);
    }

    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'user_challenges')
            ->withPivot('progress', 'completed_at', 'reward_claimed')
            ->withTimestamps();
    }

    public function userChallenges()
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function rewards()
    {
        return $this->belongsToMany(Reward::class, 'user_rewards')
            ->withPivot('claimed_at', 'status', 'metadata')
            ->withTimestamps();
    }

    public function userRewards()
    {
        return $this->hasMany(UserReward::class);
    }

    public function leaderboardEntries()
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    public function gamificationNotifications()
    {
        return $this->hasMany(GamificationNotification::class);
    }

    public function userTasks()
    {
        return $this->hasMany(UserTask::class);
    }

    /**
     * العلاقات مع المكتبة الرقمية
     */
    public function libraryItems()
    {
        return $this->hasMany(LibraryItem::class, 'uploaded_by');
    }

    public function libraryDownloads()
    {
        return $this->hasMany(LibraryDownload::class, 'user_id');
    }

    public function libraryViews()
    {
        return $this->hasMany(LibraryView::class, 'user_id');
    }

    public function libraryRatings()
    {
        return $this->hasMany(LibraryRating::class, 'user_id');
    }

    public function libraryFavorites()
    {
        return $this->hasMany(LibraryFavorite::class, 'user_id');
    }

    /**
     * العلاقات مع التقويم
     */
    public function calendarEvents()
    {
        return $this->hasMany(CalendarEvent::class, 'created_by');
    }

    public function eventReminders()
    {
        return $this->hasMany(EventReminder::class, 'user_id');
    }

    /**
     * العلاقات مع الذكاء الاصطناعي
     */
    public function aiConversations()
    {
        return $this->hasMany(AIConversation::class, 'user_id');
    }

    public function aiQuestionGenerations()
    {
        return $this->hasMany(AIQuestionGeneration::class, 'user_id');
    }

    /**
     * نطاق الطلاب فقط (غير المؤرشفين)
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'student');
        })->where('is_archived', false);
    }

    /**
     * نطاق المستخدمين المؤرشفين
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * نطاق المستخدمين غير المؤرشفين
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Relationship to archived user record
     */
    public function archivedRecord()
    {
        return $this->hasOne(ArchivedUser::class, 'original_user_id');
    }

    /**
     * العلاقة مع المفضلة في المكتبة
     */
    public function favorites()
    {
        return $this->belongsToMany(LibraryItem::class, 'library_favorites', 'user_id', 'library_item_id')
            ->withTimestamps();
    }

    /**
     * العلاقة مع OTP codes
     */
    public function otpCodes()
    {
        return $this->hasMany(\App\Models\OTPCode::class);
    }

    /**
     * العلاقة مع المشتريات
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * العلاقة مع المحفظة الإلكترونية
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * العلاقة مع الصفوف المخصصة للمعلم
     */
    public function assignedClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'teacher_classes', 'teacher_id', 'class_id')
            ->withPivot(['assigned_by', 'assigned_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * العلاقة مع المواد المخصصة للمعلم
     */
    public function assignedSubjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects', 'teacher_id', 'subject_id')
            ->withPivot(['assigned_by', 'assigned_at', 'notes', 'required_pages'])
            ->withTimestamps();
    }

    /**
     * أهداف الدروس الأسبوعية المخصصة للمعلم (حسب الأسبوع الدراسي)
     */
    public function teacherWeekTargets()
    {
        return $this->hasMany(TeacherWeekTarget::class, 'teacher_id');
    }

    /**
     * التحقق من أن المعلم مسؤول عن صف معين
     */
    public function isAssignedToClass($classId)
    {
        return $this->assignedClasses()->where('classes.id', $classId)->exists();
    }

    /**
     * التحقق من أن المعلم مسؤول عن مادة معينة
     */
    public function isAssignedToSubject($subjectId)
    {
        return $this->assignedSubjects()->where('subjects.id', $subjectId)->exists();
    }

    /**
     * الحصول على جميع المواد التي يمكن للمعلم الوصول إليها
     * (من خلال الصفوف المخصصة له + المواد المخصصة مباشرة)
     */
    public function getAccessibleSubjects()
    {
        // المواد من الصفوف المخصصة
        $classIds = $this->assignedClasses()->pluck('classes.id');

        // المواد المخصصة مباشرة
        $directSubjectIds = $this->assignedSubjects()->pluck('subjects.id');

        // إرجاع query builder
        return Subject::where(function ($query) use ($classIds, $directSubjectIds) {
            if ($classIds->isNotEmpty()) {
                $query->whereIn('class_id', $classIds);
            }
            if ($directSubjectIds->isNotEmpty()) {
                if ($classIds->isNotEmpty()) {
                    $query->orWhereIn('id', $directSubjectIds);
                } else {
                    $query->whereIn('id', $directSubjectIds);
                }
            }
            // إذا لم يكن هناك أي تخصيصات، إرجاع query فارغ
            if ($classIds->isEmpty() && $directSubjectIds->isEmpty()) {
                $query->whereRaw('1 = 0'); // Always false condition
            }
        });
    }

    /**
     * الحصول على جميع الصفوف التي يمكن للمعلم الوصول إليها
     */
    public function getAccessibleClasses()
    {
        return $this->assignedClasses();
    }

    /**
     * الحصول على IDs الصفوف المسموح بها للمدرس
     * (صفوف مخصصة مباشرة + صفوف المواد المخصصة مباشرة)
     */
    public function getTeacherAllowedClassIds()
    {
        $directClassIds = $this->assignedClasses()->pluck('classes.id');
        $derivedClassIds = $this->assignedSubjects()->pluck('subjects.class_id');

        return $directClassIds
            ->merge($derivedClassIds)
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * الحصول على IDs المواد المسموح بها للمدرس
     * (مواد مخصصة مباشرة + مواد الصفوف المخصصة)
     */
    public function getTeacherAllowedSubjectIds()
    {
        $directSubjectIds = $this->assignedSubjects()->pluck('subjects.id');
        $classIds = $this->getTeacherAllowedClassIds();

        $classSubjectIds = collect();
        if ($classIds->isNotEmpty()) {
            $classSubjectIds = Subject::whereIn('class_id', $classIds)->pluck('id');
        }

        return $directSubjectIds
            ->merge($classSubjectIds)
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * العلاقة مع الصفوف المخصصة للمشرف
     */
    public function assignedClassesAsSupervisor()
    {
        return $this->belongsToMany(SchoolClass::class, 'supervisor_classes', 'supervisor_id', 'class_id')
            ->withPivot(['assigned_by', 'assigned_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * العلاقة مع المواد المخصصة للمشرف
     */
    public function assignedSubjectsAsSupervisor()
    {
        return $this->belongsToMany(Subject::class, 'supervisor_subjects', 'supervisor_id', 'subject_id')
            ->withPivot(['assigned_by', 'assigned_at', 'notes'])
            ->withTimestamps();
    }

    /**
     * التحقق من أن المشرف مسؤول عن صف معين
     */
    public function isAssignedToClassAsSupervisor($classId)
    {
        return $this->assignedClassesAsSupervisor()->where('classes.id', $classId)->exists();
    }

    /**
     * التحقق من أن المشرف مسؤول عن مادة معينة
     */
    public function isAssignedToSubjectAsSupervisor($subjectId)
    {
        return $this->assignedSubjectsAsSupervisor()->where('subjects.id', $subjectId)->exists();
    }

    /**
     * الحصول على جميع المواد التي يمكن للمشرف الوصول إليها
     * (من خلال الصفوف المخصصة له + المواد المخصصة مباشرة)
     */
    public function getAccessibleSubjectsAsSupervisor()
    {
        // المواد من الصفوف المخصصة
        $classIds = $this->assignedClassesAsSupervisor()->pluck('classes.id');

        // المواد المخصصة مباشرة
        $directSubjectIds = $this->assignedSubjectsAsSupervisor()->pluck('subjects.id');

        // إرجاع query builder
        return Subject::where(function ($query) use ($classIds, $directSubjectIds) {
            if ($classIds->isNotEmpty()) {
                $query->whereIn('class_id', $classIds);
            }
            if ($directSubjectIds->isNotEmpty()) {
                if ($classIds->isNotEmpty()) {
                    $query->orWhereIn('id', $directSubjectIds);
                } else {
                    $query->whereIn('id', $directSubjectIds);
                }
            }
            // إذا لم يكن هناك أي تخصيصات، إرجاع query فارغ
            if ($classIds->isEmpty() && $directSubjectIds->isEmpty()) {
                $query->whereRaw('1 = 0'); // Always false condition
            }
        });
    }

    /**
     * الحصول على جميع الصفوف التي يمكن للمشرف الوصول إليها
     */
    public function getAccessibleClassesAsSupervisor()
    {
        return $this->assignedClassesAsSupervisor();
    }

    /**
     * Scope للمعلمين فقط (staff_profile أو الاسم الافتراضي teacher للتوافق مع البيانات القديمة)
     */
    public function scopeTeachers($query)
    {
        $rolesTable = config('permission.table_names.roles', 'roles');

        return $query->whereHas('roles', function ($q) use ($rolesTable) {
            if (Schema::hasColumn($rolesTable, 'staff_profile')) {
                $q->where('staff_profile', 'teacher');
            } else {
                $q->where('name', 'teacher');
            }
        });
    }

    /**
     * هل يطابق المستخدم نفس معيار قائمة المعلمين في الإدارة (scopeTeachers)؟
     * يُستخدم في تخصيص المعلم والتقدم؛ يختلف عن hasTeacherStaffIdentity() التي تستبعد أدمن المنصة حتى مع رول معلم.
     */
    public function matchesAdminTeacherListingCriteria(): bool
    {
        $rolesTable = config('permission.table_names.roles', 'roles');

        return $this->roles()->where(function ($q) use ($rolesTable) {
            if (Schema::hasColumn($rolesTable, 'staff_profile')) {
                $q->where($rolesTable.'.staff_profile', 'teacher');
            } else {
                $q->where($rolesTable.'.name', 'teacher');
            }
        })->exists();
    }

    /**
     * Scope للمشرفين فقط (staff_profile أو الاسم الافتراضي supervisor)
     */
    public function scopeSupervisors($query)
    {
        $rolesTable = config('permission.table_names.roles', 'roles');

        return $query->whereHas('roles', function ($q) use ($rolesTable) {
            if (Schema::hasColumn($rolesTable, 'staff_profile')) {
                $q->where('staff_profile', 'supervisor');
            } else {
                $q->where('name', 'supervisor');
            }
        });
    }

    /**
     * الحصول على اسم الرول الأساسية (أول رول تم إسناده للمستخدم)
     */
    public function getPrimaryRoleName(): ?string
    {
        return $this->roles()
            ->orderBy('model_has_roles.role_id')
            ->value('name');
    }

    /**
     * الحصول على مسمى عربي للرول الأساسية
     */
    public function getPrimaryRoleLabelAttribute(): string
    {
        if ($this->isPlatformAdmin()) {
            return 'أدمن';
        }

        if ($this->hasSupervisorStaffIdentity()) {
            return 'مشرف';
        }

        if ($this->hasTeacherStaffIdentity()) {
            return 'معلم';
        }

        $roleName = $this->getPrimaryRoleName();

        return match ($roleName) {
            'admin' => 'أدمن',
            'supervisor' => 'مشرف',
            'teacher' => 'معلم',
            'student' => 'طالب',
            default => 'مستخدم',
        };
    }

    public function isPlatformAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function usesTeacherAssignmentScope(): bool
    {
        return $this->hasTeacherStaffIdentity() && ! $this->hasSupervisorStaffIdentity();
    }

    public function isTeacherBaseOnly(): bool
    {
        return $this->usesTeacherAssignmentScope() && ! $this->canAny([
            'unit-create',
            'unit-edit',
            'unit-delete',
            'subject-section-create',
            'subject-section-edit',
            'subject-section-delete',
            'lesson-create',
            'lesson-edit',
            'lesson-delete',
            'lesson-attachment-create',
            'quiz-create',
            'quiz-edit',
            'quiz-delete',
            'quiz-attempt-grade',
            'quiz-attempt-needs-grading',
            'question-create',
            'question-edit',
            'question-delete',
        ]);
    }

    public function hasTeacherExtendedCapabilities(): bool
    {
        return $this->usesTeacherAssignmentScope() && $this->canAny([
            'unit-create',
            'unit-edit',
            'unit-delete',
            'subject-section-create',
            'subject-section-edit',
            'subject-section-delete',
            'lesson-create',
            'lesson-edit',
            'lesson-delete',
            'lesson-attachment-create',
            'quiz-create',
            'quiz-edit',
            'quiz-delete',
            'quiz-attempt-grade',
            'quiz-attempt-needs-grading',
            'question-create',
            'question-edit',
            'question-delete',
        ]);
    }

    /**
     * هل يُعتبر المستخدم معلماً (رول باسم teacher أو staff_profile = teacher على أي دور)؟
     * الأدمن المنصّة لا يُعد معلماً لهذا الغرض.
     */
    public function hasTeacherStaffIdentity(): bool
    {
        if ($this->isPlatformAdmin()) {
            return false;
        }

        if ($this->hasRole('teacher')) {
            return true;
        }

        $rolesTable = config('permission.table_names.roles', 'roles');
        if (! Schema::hasColumn($rolesTable, 'staff_profile')) {
            return false;
        }

        return $this->roles()->where('staff_profile', 'teacher')->exists();
    }

    /**
     * هل يُعتبر المستخدم مشرفاً (رول باسم supervisor أو staff_profile = supervisor على أي دور)؟
     * الأدمن المنصّة لا يُعد مشرفاً لهذا الغرض.
     */
    public function hasSupervisorStaffIdentity(): bool
    {
        if ($this->isPlatformAdmin()) {
            return false;
        }

        $rolesTable = config('permission.table_names.roles', 'roles');
        if (Schema::hasColumn($rolesTable, 'staff_profile')) {
            return $this->roles()->where('staff_profile', 'supervisor')->exists();
        }

        return $this->hasRole('supervisor');
    }

    public function usesSupervisorAssignmentScope(): bool
    {
        return $this->hasSupervisorStaffIdentity();
    }

    public function canReviewContent(): bool
    {
        if ($this->isPlatformAdmin()) {
            return true;
        }

        return $this->canAny([
            'review-queue-list',
            'review-queue-lessons',
            'review-queue-quizzes',
            'lesson-approve-review',
            'lesson-reject-review',
            'quiz-approve-review',
            'quiz-reject-review',
        ]);
    }

    public function shouldSubmitContentForReview(): bool
    {
        return $this->usesTeacherAssignmentScope() && ! $this->canReviewContent();
    }

    public function canManageTeacherAssignments(): bool
    {
        return $this->isPlatformAdmin() || $this->canAny([
            'teacher-assignment-list',
            'teacher-assignment-show',
            'teacher-assignment-update',
            'teacher-assignment-manage-classes',
            'teacher-assignment-manage-subjects',
        ]);
    }

    public function canManageSupervisorAssignments(): bool
    {
        return $this->isPlatformAdmin() || $this->canAny([
            'supervisor-assignment-list',
            'supervisor-assignment-show',
            'supervisor-assignment-update',
            'supervisor-assignment-manage-classes',
            'supervisor-assignment-manage-subjects',
        ]);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }
}
