<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReferralCodeResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'is_active' => $this->is_active,
            'usage_count' => $this->usage_count,
            'max_usage' => $this->max_usage,
            'expires_at' => $this->expires_at,
            'created_at' => $this->created_at,
            'generator' => $this->whenLoaded('generator', function () {
                return [
                    'id' => $this->generator->id,
                    'name' => $this->generator->name,
                    'type' => class_basename($this->generator),
                ];
            }),
            'is_usable' => $this->isUsable(),
            'formatted_expires_at' => $this->expires_at ? $this->expires_at->format('Y-m-d H:i:s') : null,
        ];
    }
}
