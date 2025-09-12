<?php

namespace App\Notifications;

use App\Models\TopupRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TopupRequestCreated extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Top-up Request')
            ->line("A new top-up request has been created.")
            ->line("User: {$this->topupRequest->user->name}")
            ->line("Amount: " . number_format((float) $this->topupRequest->amount, 2) . " EGP")
            ->line("Payment Method: " . ($this->topupRequest->payment_method ?? 'Not specified'))
            ->action('Review Request', url('/admin/topups/' . $this->topupRequest->id))
            ->line('Please review and process this request.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'topup_request_created',
            'title' => 'New Top-up Request',
            'message' => "New top-up request for " . number_format((float) $this->topupRequest->amount, 2) . " EGP",
            'topup_request_id' => $this->topupRequest->id,
            'user_name' => $this->topupRequest->user->name,
            'user_id' => $this->topupRequest->user_id,
            'amount' => $this->topupRequest->amount,
            'payment_method' => $this->topupRequest->payment_method,
            'created_at' => $this->topupRequest->created_at,
        ];
    }
}
