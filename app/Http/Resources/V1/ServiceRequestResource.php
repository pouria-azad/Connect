<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceRequestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer' => [
                'id' => $this->customer->id,
                'username' => $this->customer->username,
                'full_name' => $this->customer->full_name,
            ],
            'service_provider' => $this->serviceProvider ? [
                'id' => $this->serviceProvider->id,
                'username' => $this->serviceProvider->username,
                'full_name' => $this->serviceProvider->full_name,
            ] : null,
            'service_category' => $this->serviceCategory ? [
                'id' => $this->serviceCategory->id,
                'name' => $this->serviceCategory->name,
            ] : null,
            'subject' => $this->subject,
            'description' => $this->description,
            'initial_fee_amount' => $this->initial_fee_amount,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'rejection_reason' => $this->rejection_reason,
            'request_type' => $this->request_type,
            'province' => $this->province ? [
                'id' => $this->province->id,
                'name' => $this->province->name,
            ] : null,
            'city' => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
            ] : null,
            'scope_type' => $this->scope_type,
            'accepted_service_provider' => $this->acceptedServiceProvider ? [
                'id' => $this->acceptedServiceProvider->id,
                'username' => $this->acceptedServiceProvider->username,
                'full_name' => $this->acceptedServiceProvider->full_name,
            ] : null,
            'accepted_at' => $this->accepted_at,
            'available_until' => $this->available_until,
            'completed_at' => $this->completed_at,
            'files' => RequestFileResource::collection($this->files),
            'review' => $this->when($this->review, new ReviewResource($this->review)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
} 