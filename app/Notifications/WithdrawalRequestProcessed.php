<?php

namespace App\Notifications;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalRequestProcessed extends Notification implements ShouldQueue
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
        $status = ucfirst($this->withdrawalRequest->status);
        $amount = number_format((float) $this->withdrawalRequest->amount, 2);

        $mailMessage = (new MailMessage)
            ->subject("Withdrawal Request {$status}")
            ->line("Your withdrawal request has been {$this->withdrawalRequest->status}.")
            ->line("Amount: {$amount} EGP")
            ->line("Request ID: #{$this->withdrawalRequest->id}");

        if ($this->withdrawalRequest->status === 'approved') {
            $mailMessage->line('The amount has been processed and will be transferred to your bank account.');
        } elseif ($this->withdrawalRequest->notes) {
            $mailMessage->line("Reason: {$this->withdrawalRequest->notes}");
        }

        return $mailMessage->line('Thank you for using our service.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'withdrawal_request_processed',
            'title' => 'Withdrawal Request ' . ucfirst($this->withdrawalRequest->status),
            'message' => "Your withdrawal request for " . number_format((float) $this->withdrawalRequest->amount, 2) . " EGP has been " . $this->withdrawalRequest->status,
            'withdrawal_request_id' => $this->withdrawalRequest->id,
            'status' => $this->withdrawalRequest->status,
            'amount' => $this->withdrawalRequest->amount,
            'processed_at' => $this->withdrawalRequest->processed_at,
            'notes' => $this->withdrawalRequest->notes,
        ];
    }
}
