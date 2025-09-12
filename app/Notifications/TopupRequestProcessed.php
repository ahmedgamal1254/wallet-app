<?php

namespace App\Notifications;

use App\Models\TopupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupRequestProcessed extends Notification implements ShouldQueue
{
    use Queueable;

    protected $topupRequest;

    public function __construct(TopupRequest $topupRequest)
    {
        $this->topupRequest = $topupRequest;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $status = ucfirst($this->topupRequest->status);
        $amount = number_format((float) $this->topupRequest->amount, 2);

        $mailMessage = (new MailMessage)
            ->subject("Top-up Request {$status}")
            ->line("Your top-up request has been {$this->topupRequest->status}.")
            ->line("Amount: {$amount} EGP")
            ->line("Request ID: #{$this->topupRequest->id}");

        if ($this->topupRequest->status === 'approved') {
            $mailMessage->line('The amount has been added to your wallet.');
        } elseif ($this->topupRequest->notes) {
            $mailMessage->line("Reason: {$this->topupRequest->notes}");
        }

        return $mailMessage->line('Thank you for using our service.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'topup_request_processed',
            'title' => 'Top-up Request ' . ucfirst($this->topupRequest->status),
            'message' => "Your top-up request for " . number_format((float) $this->topupRequest->amount, 2) . " EGP has been " . $this->topupRequest->status,
            'topup_request_id' => $this->topupRequest->id,
            'status' => $this->topupRequest->status,
            'amount' => $this->topupRequest->amount,
            'processed_at' => $this->topupRequest->processed_at,
            'notes' => $this->topupRequest->notes,
        ];
    }
}
