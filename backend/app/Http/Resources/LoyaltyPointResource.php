<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoyaltyPointResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'points' => $this->points,
            'lifetime_points' => $this->lifetime_points,
            'tier' => $this->tier,
            'tier_multiplier' => $this->getTierMultiplier(),
            'tier_expires_at' => $this->tier_expires_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
