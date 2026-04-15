<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WhatsAppTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'name',
        'slug',
        'content',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    public function render(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', (string) ($value ?? ''), $content);
        }

        return $content;
    }

    public function getAvailableVariables(): array
    {
        preg_match_all('/\{\{(\w+)\}\}/', $this->content, $matches);
        return !empty($matches[1]) ? array_values(array_unique($matches[1])) : [];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function supportedVariables(): array
    {
        return [
            'student_name',
            'student_email',
            'student_phone',
            'class_name',
            'subject_name',
        ];
    }
}
