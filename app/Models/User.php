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
     * نطاق الطلاب فقط
     */
    public function scopeStudents($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'student');
        });
    }
}