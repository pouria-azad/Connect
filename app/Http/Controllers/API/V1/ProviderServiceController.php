<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreProviderServiceRequest;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Provider;

/**
 * @OA\Tag(
 *     name="Provider Services",
 *     description="API endpoints for managing provider services"
 * )
 */
class ProviderServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/providers/{providerId}/services",
     *     summary="Get list of services for a provider",
     *     tags={"Provider Services"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of services",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ServiceResource"))
     *     ),
     *     @OA\Response(response=404, description="Provider not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index($providerId)
    {
        $provider = Provider::findOrFail($providerId);
        $this->authorize('view', $provider);
        $services = $provider->services;
        return ServiceResource::collection($services);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/providers/{providerId}/services",
     *     summary="Add or update a service for a provider",
     *     tags={"Provider Services"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="service_id", type="integer", example=1),
     *             @OA\Property(property="price", type="number", format="float", example=100.00),
     *             @OA\Property(property="custom_description", type="string", example="Custom description")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service added or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="سرویس با موفقیت اضافه یا ویرایش شد")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Provider not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreProviderServiceRequest $request, $providerId)
    {
        $provider = Provider::findOrFail($providerId);
        $this->authorize('update', $provider);

        $data = $request->validated();

        $provider->services()->syncWithoutDetaching([
            $data['service_id'] => [
                'price'              => $data['price'],
                'custom_description' => $data['custom_description'] ?? null,
            ]
        ]);

        return response()->json([
            'message' => 'سرویس با موفقیت اضافه یا ویرایش شد'
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/providers/{providerId}/services/{serviceId}",
     *     summary="Remove a service from a provider",
     *     tags={"Provider Services"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Service removed"),
     *     @OA\Response(response=404, description="Provider or service not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy($providerId, $serviceId)
    {
        $provider = Provider::findOrFail($providerId);
        $this->authorize('update', $provider);

        $attachedService = $provider->services()
            ->where('services.id', $serviceId)
            ->exists();

        if (! $attachedService) {
            return response()->json([
                'message' => 'Service not found for this provider'
            ], 404);
        }

        $provider->services()->detach($serviceId);

        return response()->noContent(); // 204 No Content
    }
}
