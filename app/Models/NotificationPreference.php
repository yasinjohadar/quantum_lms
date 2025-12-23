<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'via_database',
        'via_email',
        'via_sms',
        'muted',
    ];

    protected $casts = [
        'via_database' => 'boolean',
        'via_email' => 'boolean',
        'via_sms' => 'boolean',
        'muted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
