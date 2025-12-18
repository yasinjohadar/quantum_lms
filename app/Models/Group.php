<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Group extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * العلاقة مع الطلاب
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'group_user')
                    ->withPivot(['added_by', 'added_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * العلاقة مع الصفوف
     */
    public function classes()
    {
        return $this->belongsToMany(SchoolClass::class, 'group_class', 'group_id', 'class_id')
                    ->withPivot(['added_by', 'added_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * العلاقة مع المواد
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'group_subject')
                    ->withPivot(['added_by', 'added_at', 'notes'])
                    ->withTimestamps();
    }

    /**
     * العلاقة مع منشئ المجموعة
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * نطاق المجموعات النشطة فقط
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * نطاق ترتيب المجموعات
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name', 'asc');
    }

    /**
     * نطاق البحث في المجموعات
     */
    public function scopeSearch($query, $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('description', 'like', '%' . $search . '%');
        });
    }
}
