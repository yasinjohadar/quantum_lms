<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Symfony\Component\HttpFoundation\Session\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
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
            'password' => 'hashed',
            'is_active' => 'boolean',
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
     * العلاقة مع المواد (Many-to-Many through enrollments)
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'enrollments', 'user_id', 'subject_id')
                    ->withPivot(['enrolled_by', 'enrolled_at', 'status', 'notes'])
                    ->withTimestamps();
    }

    /**
     * العلاقة مع المجموعات
     */
    public function groups()
    {
        return $this->belongsToMany(Group::class, 'group_user')
                    ->withPivot(['added_by', 'added_at', 'notes'])
                    ->withTimestamps();
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
     * العلاقة مع محاولات الأسئلة المنفصلة
     */
    public function questionAttempts()
    {
        return $this->hasMany(QuestionAttempt::class);
    }

    /**
     * العلاقة مع التقييمات
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
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

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
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
     * نطاق الطلاب فقط
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'student');
        });
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