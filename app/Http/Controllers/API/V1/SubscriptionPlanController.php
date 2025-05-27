<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SubscriptionPlan\StoreSubscriptionPlanRequest;
use App\Http\Requests\V1\SubscriptionPlan\UpdateSubscriptionPlanRequest;
use App\Http\Resources\V1\SubscriptionPlanResource;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubscriptionPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-plans",
     *     summary="دریافت لیست پلن‌های اشتراک",
     *     tags={"Subscription Plans"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست پلن‌های اشتراک",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/SubscriptionPlanResource")
     *         )
     *     )
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();
        return SubscriptionPlanResource::collection($plans);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/subscription-plans",
     *     summary="ایجاد پلن اشتراک جدید",
     *     tags={"Subscription Plans (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSubscriptionPlanRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="پلن اشتراک با موفقیت ایجاد شد",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionPlanResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز"
     *     )
     * )
     */
    public function store(StoreSubscriptionPlanRequest $request): JsonResponse
    {
        $user = auth()->guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$user->is_admin) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }

        $plan = SubscriptionPlan::create($request->validated());

        return response()->json([
            'message' => 'طرح اشتراک با موفقیت ایجاد شد',
            'plan' => new SubscriptionPlanResource($plan)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscription-plans/{id}",
     *     summary="دریافت جزئیات پلن اشتراک",
     *     tags={"Subscription Plans"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="جزئیات پلن اشتراک",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionPlanResource")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="پلن اشتراک یافت نشد"
     *     )
     * )
     */
    public function show(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        return response()->json([
            'plan' => new SubscriptionPlanResource($subscriptionPlan)
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/subscription-plans/{id}",
     *     summary="به‌روزرسانی پلن اشتراک",
     *     tags={"Subscription Plans (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSubscriptionPlanRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="پلن اشتراک با موفقیت به‌روزرسانی شد",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionPlanResource")
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="پلن اشتراک یافت نشد"
     *     )
     * )
     */
    public function update(UpdateSubscriptionPlanRequest $request, $id): JsonResponse
    {
        $user = auth()->guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$user->is_admin) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }

        $subscriptionPlan = SubscriptionPlan::find($id);
        if (!$subscriptionPlan) {
            return response()->json([
                'message' => 'طرح اشتراک یافت نشد'
            ], 404);
        }

        $data = $request->validated();
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = array_values($data['features']);
        }
        $subscriptionPlan->update($data);

        return response()->json([
            'message' => 'طرح اشتراک با موفقیت ویرایش شد',
            'plan' => new SubscriptionPlanResource($subscriptionPlan)
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/subscription-plans/{id}",
     *     summary="حذف پلن اشتراک",
     *     tags={"Subscription Plans (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="پلن اشتراک با موفقیت حذف شد"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="پلن اشتراک یافت نشد"
     *     )
     * )
     */
    public function destroy(SubscriptionPlan $subscriptionPlan): JsonResponse
    {
        $user = auth()->guard('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated.'
            ], 401);
        }

        if (!$user->is_admin) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }

        if ($subscriptionPlan->getActiveSubscriptionsCount() > 0) {
            return response()->json([
                'message' => 'این طرح دارای اشتراک فعال است و قابل حذف نیست'
            ], 400);
        }

        $subscriptionPlan->delete();

        return response()->json([
            'message' => 'طرح اشتراک با موفقیت حذف شد'
        ], 200);
    }

    public function statistics(Request $request, $id): JsonResponse
    {
        $user = auth()->guard('sanctum')->user();
        if (!$user) {
            return response()->json([
                'message' => 'This action is unauthorized.'
            ], 403);
        }
        $plan = SubscriptionPlan::find($id);
        if (!$plan) {
            return response()->json([
                'message' => 'طرح اشتراک یافت نشد'
            ], 404);
        }
        $totalSubscriptions = $plan->subscriptions()->count();
        $activeSubscriptions = $plan->subscriptions()->where('status', 'active')->count();
        $cancelledSubscriptions = $plan->subscriptions()->where('status', 'cancelled')->count();
        $totalRevenue = $plan->subscriptions()->sum('amount');
        return response()->json([
            'total_subscriptions' => $totalSubscriptions,
            'active_subscriptions' => $activeSubscriptions,
            'cancelled_subscriptions' => $cancelledSubscriptions,
            'total_revenue' => $totalRevenue,
        ]);
    }
} 