<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubjectSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'subject_sections';

    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'subject_id' => 'integer',
        'order' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع المادة.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * العلاقة مع الوحدات.
     */
    public function units()
    {
        return $this->hasMany(Unit::class, 'section_id')->orderBy('order');
    }
}


