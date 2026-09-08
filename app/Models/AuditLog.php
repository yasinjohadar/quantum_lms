<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'causer_role',
        'event_type',
        'ip_address',
        'user_agent',
        'url',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'class_id',
        'class_name',
        'curriculum_subject_id',
        'curriculum_subject_name',
        'metadata',
        'old_values',
        'new_values',
        'occurred_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'old_values' => 'array',
        'new_values' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
