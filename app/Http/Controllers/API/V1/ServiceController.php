<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Service\StoreServiceRequest;
use App\Http\Requests\V1\Service\UpdateServiceRequest;
use App\Http\Resources\V1\ServiceResource;
use App\Models\Service;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="API endpoints for managing services"
 * )
 */
class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/services",
     *     summary="Get a tree list of all services",
     *     tags={"Services"},
     *     @OA\Response(
     *         response=200,
     *         description="List of root services with their children",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/ServiceResource"))
     *     )
     * )
     */
    public function index()
    {
        $services = Service::with('children')->whereNull('parent_id')->get();
        return ServiceResource::collection($services);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/services/{serviceId}",
     *     summary="Get details of a service including its children",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service details",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceResource")
     *     ),
     *     @OA\Response(response=404, description="Service not found")
     * )
     */
    public function show($serviceId)
    {
        $service = Service::with('children')->findOrFail($serviceId);
        return new ServiceResource($service);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/services",
     *     summary="Create a new service",
     *     tags={"Services"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreServiceRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service created",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceResource")
     *     ),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function store(StoreServiceRequest $request)
    {
        $this->authorize('create', Service::class);
        $data = $request->validated();
        $service = Service::create($data);
        return (new ServiceResource($service))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/services/{serviceId}",
     *     summary="Update a service",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateServiceRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Service updated",
     *         @OA\JsonContent(ref="#/components/schemas/ServiceResource")
     *     ),
     *     @OA\Response(response=404, description="Service not found"),
     *     @OA\Response(response=422, description="Validation error"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function update(UpdateServiceRequest $request, $serviceId)
    {
        $service = Service::findOrFail($serviceId);
        $this->authorize('update', $service);
        $service->update($request->validated());
        return new ServiceResource($service);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/services/{serviceId}",
     *     summary="Delete a service",
     *     tags={"Services"},
     *     @OA\Parameter(
     *         name="serviceId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=204, description="Service deleted"),
     *     @OA\Response(response=404, description="Service not found"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function destroy($serviceId)
    {
        $service = Service::find($serviceId);

        if (! $service) {
            return response()->json(['message' => 'سرویس یافت نشد'], 404);
        }

        $this->authorize('delete', $service);
        $service->delete();

        return response()->noContent(); // 204 No Content
    }
}
