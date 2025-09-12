<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance',
        'held_balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'held_balance' => 'decimal:2',
    ];

    public function walletable()
    {
        return $this->morphTo();
    }

    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->held_balance;
    }

    public function credit(float $amount, string $description, ?string $reference = null): Transaction
    {
        $this->increment('balance', $amount);

        return $this->walletable->transactions()->create([
            'type' => 'credit',
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
        ]);
    }

    public function debit(float $amount, string $description, ?string $reference = null): Transaction
    {
        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $this->decrement('balance', $amount);

        return $this->walletable->transactions()->create([
            'type' => 'debit',
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
        ]);
    }

    public function hold(float $amount, string $description, ?string $reference = null): Transaction
    {
        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $this->increment('held_balance', $amount);

        return $this->walletable->transactions()->create([
            'type' => 'hold',
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
        ]);
    }

    public function release(float $amount, string $description, ?string $reference = null): Transaction
    {
        if ($this->held_balance < $amount) {
            throw new \Exception('Insufficient held balance');
        }

        $this->decrement('held_balance', $amount);
        $this->decrement('balance', $amount);

        return $this->walletable->transactions()->create([
            'type' => 'release',
            'amount' => $amount,
            'description' => $description,
            'reference' => $reference,
        ]);
    }
}
