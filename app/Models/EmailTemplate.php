<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'subject',
        'body',
        'variables',
        'is_active',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($template) {
            if (empty($template->slug)) {
                $template->slug = Str::slug($template->name);
            }
        });
    }

    /**
     * Render the template with variables
     */
    public function render(array $variables = []): array
    {
        $subject = $this->subject;
        $body = $this->body;

        foreach ($variables as $key => $value) {
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
            $body = str_replace('{{' . $key . '}}', $value, $body);
        }

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }

    /**
     * Get available variables from the template
     */
    public function getAvailableVariables(): array
    {
        $variables = [];
        $subject = $this->subject;
        $body = $this->body;
        $content = $subject . ' ' . $body;

        preg_match_all('/\{\{(\w+)\}\}/', $content, $matches);

        if (!empty($matches[1])) {
            $variables = array_unique($matches[1]);
        }

        return $variables;
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find template by slug
     */
    public static function findBySlug(string $slug)
    {
        return static::where('slug', $slug)->first();
    }
}
