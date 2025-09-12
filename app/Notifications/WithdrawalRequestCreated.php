<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $withdrawalRequest;

    public function __construct(WithdrawalRequest $withdrawalRequest)
    {
        $this->withdrawalRequest = $withdrawalRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $requesterType = class_basename($this->withdrawalRequest->requester);
        $requesterName = $this->withdrawalRequest->requester->name;

        return (new MailMessage)
            ->subject('New Withdrawal Request')
            ->line("A new withdrawal request has been created.")
            ->line("Requester: {$requesterName} ({$requesterType})")
            ->line("Amount: " . number_format((float) $this->withdrawalRequest->amount, 2) . " EGP")
            ->action('Review Request', url('/admin/withdrawals/' . $this->withdrawalRequest->id))
            ->line('Please review and process this request.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'withdrawal_request_created',
            'title' => 'New Withdrawal Request',
            'message' => "New withdrawal request for " . number_format((float) $this->withdrawalRequest->amount, 2) . " EGP",
            'withdrawal_request_id' => $this->withdrawalRequest->id,
            'requester_type' => class_basename($this->withdrawalRequest->requester),
            'requester_name' => $this->withdrawalRequest->requester->name,
            'amount' => $this->withdrawalRequest->amount,
            'created_at' => $this->withdrawalRequest->created_at,
        ];
    }
}
