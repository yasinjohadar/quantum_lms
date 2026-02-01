<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassFeature extends Model
{
    use HasFactory;

    protected $table = 'class_features';

    protected $fillable = [
        'class_id',
        'label',
        'order',
    ];

    protected $casts = [
        'class_id' => 'integer',
        'order' => 'integer',
    ];

    /**
     * العلاقة مع الصف الدراسي.
     */
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
