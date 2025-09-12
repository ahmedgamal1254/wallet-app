<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReferralReward extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amount;
    protected $referralCode;
    protected $referredUser;

    public function __construct(float $amount, string $referralCode, $referredUser = null)
    {
        $this->amount = $amount;
        $this->referralCode = $referralCode;
        $this->referredUser = $referredUser;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $mailMessage = (new MailMessage)
            ->subject('Referral Reward Received!')
            ->line('Congratulations! You have received a referral reward.')
            ->line("Amount: " . number_format($this->amount, 2) . " EGP")
            ->line("Referral Code: {$this->referralCode}");

        if ($this->referredUser) {
            $mailMessage->line("New User: {$this->referredUser->name}");
        }

        return $mailMessage
            ->line('The reward has been added to your wallet.')
            ->line('Thank you for spreading the word!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'referral_reward',
            'title' => 'Referral Reward Received',
            'message' => "You received " . number_format($this->amount, 2) . " EGP for referral code: {$this->referralCode}",
            'amount' => $this->amount,
            'referral_code' => $this->referralCode,
            'referred_user' => $this->referredUser ? $this->referredUser->name : null,
        ];
    }
}
