<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'admin_permissions');
    }

    public function wallet()
    {
        return $this->morphOne(Wallet::class, 'walletable');
    }

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function withdrawalRequests()
    {
        return $this->morphMany(WithdrawalRequest::class, 'requester');
    }

    public function processedWithdrawals()
    {
        return $this->hasMany(WithdrawalRequest::class, 'processed_by');
    }

    public function processedTopups()
    {
        return $this->hasMany(TopupRequest::class, 'processed_by');
    }

    public function referralCodes()
    {
        return $this->morphMany(ReferralCode::class, 'generator');
    }

    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    public function givePermission(string $permission): void
    {
        $permissionModel = Permission::firstOrCreate(['name' => $permission]);
        $this->permissions()->syncWithoutDetaching([$permissionModel->id]);
    }

    public function removePermission(string $permission): void
    {
        $permissionModel = Permission::where('name', $permission)->first();
        if ($permissionModel) {
            $this->permissions()->detach($permissionModel->id);
        }
    }

    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet ?: $this->wallet()->create();
    }
}
