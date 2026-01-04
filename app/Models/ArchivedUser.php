<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ArchivedUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'archived_users';

    protected $fillable = [
        'original_user_id',
        'name',
        'email',
        'phone',
        'password',
        'email_verified_at',
        'avatar',
        'student_id',
        'date_of_birth',
        'gender',
        'last_login_at',
        'last_login_ip',
        'last_device_type',
        'is_active',
        'is_connected',
        'address',
        'archived_at',
        'archived_by',
        'archive_reason',
        'restored_at',
        'restored_by',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'is_connected' => 'boolean',
            'archived_at' => 'datetime',
            'restored_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship to original user (including soft deleted)
     */
    public function originalUser()
    {
        return $this->belongsTo(User::class, 'original_user_id')->withTrashed();
    }

    /**
     * Relationship to user who archived
     */
    public function archivedByUser()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    /**
     * Relationship to user who restored
     */
    public function restoredByUser()
    {
        return $this->belongsTo(User::class, 'restored_by');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeNotRestored($query)
    {
        return $query->whereNull('restored_at');
    }

    public function scopeRestored($query)
    {
        return $query->whereNotNull('restored_at');
    }
}