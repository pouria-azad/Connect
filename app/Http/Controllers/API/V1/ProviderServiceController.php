<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreProviderServiceRequest;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ProviderServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/providers/{providerId}/services",
     *     summary="Get list of services for a provider (paginated)",
     *     tags={"Provider Services"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated list of services",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ServiceResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Provider not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(Request $request, $providerId)
    {
        try {
            $provider = Provider::findOrFail($providerId);
            $this->authorize('view', $provider);

            $perPage = $request->query('per_page', 15);
            $services = $provider->services()->paginate($perPage);

            return ServiceResource::collection($services);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Provider not found'], 404);
        }
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
     *         response=200,
     *         description="Service added or updated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service added successfully")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Provider not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreProviderServiceRequest $request, $providerId)
    {
        try {
            $provider = Provider::findOrFail($providerId);
            $this->authorize('update', $provider);

            $data = $request->validated();
            $pivotExists = $provider->services()
                ->where('services.id', $data['service_id'])
                ->exists();

            // اضافه یا به‌روز‌رسانی
            $provider->services()->syncWithoutDetaching([
                $data['service_id'] => [
                    'price'              => $data['price'],
                    'custom_description' => $data['custom_description'] ?? null,
                ]
            ]);

            $message = $pivotExists
                ? 'Service updated successfully'
                : 'Service added successfully';

            return response()->json(['message' => $message], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Provider not found'], 404);
        }
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
        try {
            $provider = Provider::findOrFail($providerId);
            $this->authorize('update', $provider);

            $attached = $provider->services()
                ->where('services.id', $serviceId)
                ->exists();

            if (! $attached) {
                return response()->json(['message' => 'Service not found for this provider'], 404);
            }

            $provider->services()->detach($serviceId);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Provider not found'], 404);
        }
    }
}
