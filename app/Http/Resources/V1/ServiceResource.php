<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


/**
 * @OA\Schema(
 *     schema="ServiceResource",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1, description="Service ID"),
 *     @OA\Property(property="title", type="string", example="Web Development", description="Title of the service"),
 *     @OA\Property(property="description", type="string", example="Professional web development services", description="Description of the service", nullable=true),
 *     @OA\Property(
 *         property="category",
 *         ref="#/components/schemas/ServiceCategoryResource",
 *         description="Service category"
 *     ),
 *     @OA\Property(
 *         property="parent",
 *         ref="#/components/schemas/ServiceResource",
 *         description="Parent service, if any",
 *         nullable=true
 *     ),
 *     @OA\Property(property="price", type="number", format="float", example=100.00, description="Price from pivot table, if applicable", nullable=true),
 *     @OA\Property(property="custom_description", type="string", example="Custom description for provider", description="Custom description from pivot table, if applicable", nullable=true),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-13T21:16:00+03:00", description="Service creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-13T21:16:00+03:00", description="Service update timestamp")
 * )
*/
class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'pivot'       => [
                'price'       => $this->pivot->price ?? null,
                'custom_description' => $this->pivot->custom_description ?? null,
            ],
            'children'    => ServiceResource::collection($this->whenLoaded('children')),
            'created_at'  => $this->created_at->toDateTimeString(),
            'updated_at'  => $this->updated_at->toDateTimeString(),
        ];
    }
}
