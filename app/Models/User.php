<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'referred_by_code',
        'referred_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'referred_at' => 'datetime',
    ];

    // Relationships
    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function topupRequests()
    {
        return $this->hasMany(TopupRequest::class);
    }

    public function withdrawalRequests()
    {
        return $this->morphMany(WithdrawalRequest::class, 'requester');
    }

    public function referralCodes()
    {
        return $this->morphMany(ReferralCode::class, 'generator');
    }
    public function generatedReferralCodes()
    {
        return $this->morphMany(ReferralCode::class, 'generator');
    }

    public function referralUses()
    {
        return $this->hasMany(ReferralUse::class);
    }

    public function getOrCreateWallet(array $attributes = []): Wallet
    {
        $wallet = $this->wallet()->first();

        if ($wallet) {
            if (!empty($attributes)) {
                $wallet->update($attributes);
            }
            return $wallet;
        }

        return $this->wallet()->create($attributes);
    }

}
