<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description,
            'reference' => $this->reference,
            'metadata' => $this->metadata,
            'created_at' => $this->created_at,
            'formatted_amount' => number_format($this->amount, 2) . ' EGP',
            'formatted_date' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
