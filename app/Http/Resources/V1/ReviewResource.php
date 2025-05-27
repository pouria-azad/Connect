<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'service_request' => [
                'id' => $this->serviceRequest->id,
                'title' => $this->serviceRequest->title,
                'status' => $this->serviceRequest->status,
            ],
            'customer' => [
                'id' => $this->customer->id,
                'username' => $this->customer->username,
                'full_name' => $this->customer->full_name,
            ],
            'service_provider' => [
                'id' => $this->serviceProvider->id,
                'username' => $this->serviceProvider->username,
                'full_name' => $this->serviceProvider->full_name,
            ],
            'rating' => $this->rating,
            'comment' => $this->comment,
            'rating_details' => $this->rating_details,
            'is_verified' => $this->is_verified,
            'is_visible' => $this->is_visible,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 