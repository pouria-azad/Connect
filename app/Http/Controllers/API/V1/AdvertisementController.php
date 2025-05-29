<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Advertisement\StoreAdvertisementRequest;
use App\Http\Requests\V1\Advertisement\UpdateAdvertisementRequest;
use App\Http\Resources\API\V1\AdvertisementResource;
use App\Models\Advertisement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class AdvertisementController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/advertisements",
     *     summary="Get list of active advertisements",
     *     tags={"Advertisements"},
     *     @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Filter by advertisement type",
     *         @OA\Schema(type="string", enum={"banner", "popup", "sidebar"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of active advertisements",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/AdvertisementResource")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $query = Advertisement::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $advertisements = $query->get();

        return response()->json(AdvertisementResource::collection($advertisements));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/advertisements",
     *     summary="Create a new advertisement",
     *     tags={"Advertisements (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreAdvertisementRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Advertisement created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AdvertisementResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function store(StoreAdvertisementRequest $request): JsonResponse
    {
        if (!Gate::allows('isAdmin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $data['created_by_admin_id'] = auth()->id();
        $data['display_count'] = 0;
        $data['click_count'] = 0;

        $advertisement = Advertisement::create($data);

        return response()->json([
            'message' => 'Advertisement created successfully',
            'data' => new AdvertisementResource($advertisement)
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/advertisements/{id}",
     *     summary="Update an advertisement",
     *     tags={"Advertisements (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateAdvertisementRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advertisement updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/AdvertisementResource")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advertisement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement not found")
     *         )
     *     )
     * )
     */
    public function update(UpdateAdvertisementRequest $request, Advertisement $advertisement): JsonResponse
    {
        if (!Gate::allows('isAdmin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $advertisement->update($request->validated());

        return response()->json([
            'message' => 'Advertisement updated successfully',
            'data' => new AdvertisementResource($advertisement)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/advertisements/{id}",
     *     summary="Delete an advertisement",
     *     tags={"Advertisements (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Advertisement deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advertisement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement not found")
     *         )
     *     )
     * )
     */
    public function destroy(Advertisement $advertisement): JsonResponse
    {
        if (!Gate::allows('isAdmin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $advertisement->delete();

        return response()->json(['message' => 'Advertisement deleted successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/advertisements/{id}/click",
     *     summary="Record an advertisement click",
     *     tags={"Advertisements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Click recorded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Click recorded successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advertisement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement not found")
     *         )
     *     )
     * )
     */
    public function recordClick(Advertisement $advertisement): JsonResponse
    {
        if (!$advertisement->isActive()) {
            return response()->json(['message' => 'Advertisement is not active'], 400);
        }

        $advertisement->incrementClickCount();

        return response()->json(['message' => 'Click recorded successfully']);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/advertisements/{id}/display",
     *     summary="Record an advertisement display",
     *     tags={"Advertisements"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Display recorded successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Display recorded successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Advertisement not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Advertisement not found")
     *         )
     *     )
     * )
     */
    public function recordDisplay(Advertisement $advertisement): JsonResponse
    {
        if (!$advertisement->isActive()) {
            return response()->json(['message' => 'Advertisement is not active'], 400);
        }

        $advertisement->incrementDisplayCount();

        return response()->json(['message' => 'Display recorded successfully']);
    }
} 