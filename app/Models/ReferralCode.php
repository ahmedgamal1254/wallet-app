<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ReferralCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'is_active',
        'usage_count',
        'max_usage',
        'expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function generator()
    {
        return $this->morphTo();
    }

    public function uses()
    {
        return $this->hasMany(ReferralUse::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'referred_by_code');
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function isUsable(): bool
    {
        return $this->is_active &&
               (!$this->expires_at || $this->expires_at->isFuture()) &&
               (!$this->max_usage || $this->usage_count < $this->max_usage);
    }
}
