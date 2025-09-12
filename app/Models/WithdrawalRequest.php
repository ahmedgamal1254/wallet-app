<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'status',
        'notes',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bank_details' => 'array',
        'processed_at' => 'datetime',
    ];

    public function requester()
    {
        return $this->morphTo();
    }

    public function processedBy()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }

    public function approve(Admin $admin): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $wallet = $this->requester->getOrCreateWallet();

        // Release held amount and deduct from balance
        $wallet->release($this->amount, "Withdrawal approved - Request #{$this->id}");

        $this->update([
            'status' => 'approved',
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);

        return true;
    }

    public function reject(Admin $admin, ?string $reason = null): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $wallet = $this->requester->getOrCreateWallet();

        // Return held amount to available balance
        $wallet->decrement('held_balance', $this->amount);

        $this->update([
            'status' => 'rejected',
            'processed_by' => $admin->id,
            'processed_at' => now(),
            'notes' => $reason,
        ]);

        return true;
    }
}
