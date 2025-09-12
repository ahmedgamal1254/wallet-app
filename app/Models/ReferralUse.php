<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralUse extends Model
{
    use HasFactory;

    protected $fillable = [
        'referral_code_id',
        'user_id',
        'reward_amount',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
    ];

    public function referralCode()
    {
        return $this->belongsTo(ReferralCode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
