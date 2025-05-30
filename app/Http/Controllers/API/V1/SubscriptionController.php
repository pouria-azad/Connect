<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\StoreSubscriptionRequest;
use App\Http\Requests\API\V1\UpdateSubscriptionRequest;
use App\Http\Resources\API\V1\SubscriptionResource;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * @OA\Tag(
 *     name="Subscriptions",
 *     description="API Endpoints for managing user subscriptions"
 * )
 */
class SubscriptionController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/subscriptions",
     *     summary="List user subscriptions (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of user subscriptions",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscriptions retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/SubscriptionResource")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscriptions = $user->subscriptions()->with('plan')->latest()->get();
        return response()->json([
            'data' => \App\Http\Resources\API\V1\SubscriptionResource::collection($subscriptions)
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions",
     *     summary="Create a new subscription (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSubscriptionRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Subscription created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription created successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(StoreSubscriptionRequest $request): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($request->plan_id);
        
        $subscription = $request->user()->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
            'amount' => $plan->price,
            'payment_status' => 'pending',
            'auto_renew' => $request->auto_renew ?? true
        ]);

        return response()->json([
            'message' => 'Subscription created successfully',
            'data' => new SubscriptionResource($subscription)
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/subscriptions/{subscription}",
     *     summary="Get subscription details (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription details",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription retrieved successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function show(Subscription $subscription): JsonResponse
    {
        if (auth()->id() !== $subscription->user_id) {
            return response()->json([
                'message' => 'شما مجاز به مشاهده این اشتراک نیستید'
            ], 403);
        }
        return response()->json([
            'subscription' => new \App\Http\Resources\API\V1\SubscriptionResource($subscription->load('plan'))
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/subscriptions/{subscription}",
     *     summary="Update subscription (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateSubscriptionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription updated successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function update(UpdateSubscriptionRequest $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('update', $subscription);

        $subscription->update($request->validated());

        return response()->json([
            'message' => 'Subscription updated successfully',
            'data' => new SubscriptionResource($subscription->load('plan'))
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/subscriptions/{subscription}",
     *     summary="Cancel subscription (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="cancellation_reason",
     *                 type="string",
     *                 example="Switching to a different plan"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription cancelled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function destroy(Request $request, Subscription $subscription): JsonResponse
    {
        $this->authorize('delete', $subscription);

        $subscription->cancel($request->cancellation_reason);

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'data' => new SubscriptionResource($subscription->load('plan'))
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/{subscription}/renew",
     *     summary="Renew subscription (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription renewed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription renewed successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function renew(Subscription $subscription): JsonResponse
    {
        $this->authorize('renew', $subscription);

        $subscription->renew();

        return response()->json([
            'message' => 'Subscription renewed successfully',
            'data' => new SubscriptionResource($subscription->load('plan'))
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/subscribe",
     *     summary="خرید اشتراک جدید (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreSubscriptionRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="اشتراک با موفقیت ایجاد شد"),
     *             @OA\Property(
     *                 property="subscription",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     )
     * )
     */
    public function subscribe(StoreSubscriptionRequest $request): JsonResponse
    {
        $plan = \App\Models\SubscriptionPlan::find($request->plan_id);
        if (!$plan) {
            return response()->json([
                'message' => 'طرح اشتراک یافت نشد'
            ], 404);
        }
        $user = $request->user();
        $active = $user->subscriptions()->where('subscription_plan_id', $plan->id)->where('status', 'active')->exists();
        if ($active) {
            return response()->json([
                'message' => 'شما در حال حاضر یک اشتراک فعال دارید'
            ], 400);
        }
        if (!$request->has('payment_method') || !$request->payment_method) {
            return response()->json([
                'message' => 'لطفا روش پرداخت را انتخاب کنید'
            ], 400);
        }
        $subscription = $user->subscriptions()->create([
            'subscription_plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
            'amount' => $plan->price,
            'status' => 'active',
            'payment_method' => $request->payment_method,
            'payment_id' => $request->payment_id,
            'payment_status' => 'completed',
            'auto_renew' => $request->auto_renew ?? true
        ]);
        return response()->json([
            'message' => 'اشتراک با موفقیت ایجاد شد',
            'subscription' => new \App\Http\Resources\API\V1\SubscriptionResource($subscription)
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/subscriptions/{subscription}/cancel",
     *     summary="لغو اشتراک (User)",
     *     tags={"Subscription (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subscription",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="cancellation_reason",
     *                 type="string",
     *                 example="Switching to a different plan"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Subscription cancelled successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Subscription cancelled successfully"),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/SubscriptionResource"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Subscription not found"
     *     )
     * )
     */
    public function cancel(Request $request, Subscription $subscription): JsonResponse
    {
        if (!$subscription) {
            return response()->json([
                'message' => 'اشتراک یافت نشد'
            ], 404);
        }
        if ($subscription->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'شما مجاز به لغو این اشتراک نیستید'
            ], 403);
        }
        if ($subscription->status === 'cancelled') {
            return response()->json([
                'message' => 'این اشتراک قبلاً لغو شده است'
            ], 400);
        }
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason
        ]);
        return response()->json([
            'message' => 'اشتراک با موفقیت لغو شد'
        ]);
    }
} 