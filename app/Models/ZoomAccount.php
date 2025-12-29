<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ZoomAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'account_id',
        'client_id',
        'client_secret',
        'sdk_key',
        'sdk_secret',
        'redirect_uri',
        'is_default',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Relation to Creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get decrypted client secret
     */
    public function getDecryptedClientSecretAttribute(): ?string
    {
        if (!$this->client_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->client_secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted client secret
     */
    public function setClientSecretAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['client_secret'] = Crypt::encryptString($value);
        } else {
            $this->attributes['client_secret'] = null;
        }
    }

    /**
     * Get decrypted SDK secret
     */
    public function getDecryptedSdkSecretAttribute(): ?string
    {
        if (!$this->sdk_secret) {
            return null;
        }

        try {
            return Crypt::decryptString($this->sdk_secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set encrypted SDK secret
     */
    public function setSdkSecretAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['sdk_secret'] = Crypt::encryptString($value);
        } else {
            $this->attributes['sdk_secret'] = null;
        }
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeApi($query)
    {
        return $query->where('type', 'api');
    }

    public function scopeOauth($query)
    {
        return $query->where('type', 'oauth');
    }

    /**
     * Get default account
     */
    public static function getDefault(): ?self
    {
        return self::default()->active()->first() 
            ?? self::active()->first();
    }

    /**
     * Set as default (and unset others)
     */
    public function setAsDefault(): void
    {
        // Unset other defaults
        self::where('id', '!=', $this->id)->update(['is_default' => false]);
        
        // Set this as default
        $this->update(['is_default' => true]);
    }
}
