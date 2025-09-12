<?php

// app/Services/WalletService.php
namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Models\TopupRequest;
use App\Models\ReferralCode;
use App\Models\ReferralUse;
use App\Notifications\WithdrawalRequestCreated;
use App\Notifications\WithdrawalRequestProcessed;
use App\Notifications\TopupRequestCreated;
use App\Notifications\TopupRequestProcessed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class WalletService
{
    const REFERRAL_REWARD = 10.00;

    public function createWithdrawalRequest($requester, float $amount): WithdrawalRequest
    {
        return DB::transaction(function () use ($requester, $amount) {
            $wallet = $requester->getOrCreateWallet();

            if ($wallet->available_balance < $amount) {
                throw new \Exception('Insufficient balance');
            }

            // تعليق الاموال لحين قبولها من  الادمن
            $wallet->hold($amount, "Withdrawal request pending");

            // عمل طلب سحب
            $request = $requester->withdrawalRequests()->create([
                'amount' => $amount
            ]);

            // Notify all admins about new withdrawal request
            $this->notifyAdminsAboutWithdrawalRequest($request);

            return $request;
        });
    }

    public function processWithdrawalRequest(WithdrawalRequest $request, Admin $admin, string $action, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($request, $admin, $action, $reason) {
            $result = $action === 'approve'
                ? $request->approve($admin)
                : $request->reject($admin, $reason);

            if ($result) {
                // Notify the requester
                $request->requester->notify(new WithdrawalRequestProcessed($request));
            }

            return $result;
        });
    }

    public function createTopupRequest($user_id, float $amount): TopupRequest
    {
        $request = TopupRequest::create([
            'user_id' => $user_id,
            'amount' => $amount
        ]);

        // Notify the user
        $user = User::find($user_id);
        $user->notify(new TopupRequestCreated($request));

        return $request;
    }

    public function processTopupRequest(TopupRequest $request, Admin $admin, string $action, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($request, $admin, $action, $reason) {
            $result = $action === 'approve'
                ? $request->approve($admin)
                : $request->reject($admin, $reason);

            if ($result) {
                // Notify the user
                $request->user->notify(new TopupRequestProcessed($request));
            }

            return $result;
        });
    }

    public function processReferral(User $newUser, string $referralCode): void
    {
        DB::transaction(function () use ($newUser, $referralCode) {
            $referralCodeModel = ReferralCode::where('code', $referralCode)->first();

            if (!$referralCodeModel || !$referralCodeModel->isUsable()) {
                throw new \Exception('Invalid or expired referral code');
            }

            // Update user's referral info
            $newUser->update([
                'referred_by_code' => $referralCodeModel->id,
                'referred_at' => now(),
            ]);

            // Create referral use record
            ReferralUse::create([
                'referral_code_id' => $referralCodeModel->id,
                'user_id' => $newUser->id,
                'reward_amount' => self::REFERRAL_REWARD,
            ]);

            // Reward the new user
            $newUserWallet = $newUser->getOrCreateWallet();
            $newUserWallet->credit(
                self::REFERRAL_REWARD,
                "Referral reward for using code: {$referralCode}"
            );

            // Reward the code generator
            $generator = $referralCodeModel->generator;
            $generatorWallet = $generator->getOrCreateWallet();
            $generatorWallet->credit(
                self::REFERRAL_REWARD,
                "Referral reward for code: {$referralCode} used by {$newUser->name}"
            );

            // Update usage count
            $referralCodeModel->increment('usage_count');
        });
    }

    public function generateReferralCode($generator, ?int $maxUsage = null, ?\Carbon\Carbon $expiresAt = null): ReferralCode
    {
        return $generator->referralCodes()->create([
            'code' => ReferralCode::generateUniqueCode(),
            'max_usage' => $maxUsage,
            'expires_at' => $expiresAt,
        ]);
    }

    protected function notifyAdminsAboutWithdrawalRequest(WithdrawalRequest $request): void
    {
        $admins = Admin::where('is_active', true)->get();
        Notification::send($admins, new WithdrawalRequestCreated($request));
    }

    protected function notifyAdminsAboutTopupRequest(TopupRequest $request): void
    {
        $admins = Admin::where('is_active', true)->get();
        Notification::send($admins, new TopupRequestCreated($request));
    }
}
