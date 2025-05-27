<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'duration_days' => $this->duration_days,
            'features' => $this->features,
            'is_active' => $this->is_active,
            'max_ads_count' => $this->max_ads_count,
            'max_services_count' => $this->max_services_count,
            'priority_level' => $this->priority_level,
            'can_highlight_ads' => $this->can_highlight_ads,
            'can_pin_ads' => $this->can_pin_ads,
            'can_use_advanced_features' => $this->can_use_advanced_features,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 