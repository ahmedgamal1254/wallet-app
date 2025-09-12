<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawalRequestResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'status' => $this->status,
            'bank_details' => $this->bank_details,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'processed_at' => $this->processed_at,
            'requester' => $this->whenLoaded('requester', function () {
                return [
                    'id' => $this->requester->id,
                    'name' => $this->requester->name,
                    'type' => class_basename($this->requester),
                ];
            }),
            'processed_by' => $this->whenLoaded('processedBy', function () {
                return [
                    'id' => $this->processedBy->id,
                    'name' => $this->processedBy->name,
                ];
            }),
            'formatted_amount' => number_format($this->amount, 2) . ' EGP',
            'formatted_created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'formatted_processed_at' => $this->processed_at ? $this->processed_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
