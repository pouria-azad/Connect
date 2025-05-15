<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
/**
* @OA\Schema(
 *     schema="ServiceCategoryResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1, description="Category ID"),
 *     @OA\Property(property="name", type="string", example="Development", description="Name of the category"),
 *     @OA\Property(property="icon", type="string", example="fa-code", description="Icon for the category", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-13T21:16:00+03:00", description="Category creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-13T21:16:00+03:00", description="Category update timestamp")
* )
 */
class ServiceCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'icon'       => $this->icon,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
