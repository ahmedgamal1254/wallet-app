<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'notes',
        'payment_method',
        'payment_details',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_details' => 'array',
        'processed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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

        $wallet = $this->user->getOrCreateWallet();

        // Credit user's wallet
        $wallet->credit((float) $this->amount, "Top-up approved - Request #{$this->id}");

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

        $this->update([
            'status' => 'rejected',
            'processed_by' => $admin->id,
            'processed_at' => now(),
            'notes' => $reason,
        ]);

        return true;
    }
}
