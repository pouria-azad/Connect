<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Reviews",
 *     description="API Endpoints for managing product reviews"
 * )
 */
class ReviewController extends Controller
{
    /**
     * Display a listing of the reviews.
     *
     * @OA\Get(
     *     path="/api/v1/reviews",
     *     summary="دریافت نظرات کاربران برای یک سرویس‌دهنده (User)",
     *     tags={"Review (User)"},
     *     @OA\Parameter(
     *         name="product_id",
     *         in="query",
     *         description="Filter reviews by product ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="Filter reviews by user ID",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="rating",
     *         in="query",
     *         description="Filter reviews by rating",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1, maximum=5)
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Sort field (created_at, rating)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="sort_direction",
     *         in="query",
     *         description="Sort direction (asc, desc)",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reviews",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Review")),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $query = Review::with(['user', 'product']);

        // Filter by product if product_id is provided
        if ($request->has('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by user if user_id is provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by rating if rating is provided
        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        // Sort by created_at by default
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        $reviews = $query->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $reviews
        ]);
    }

    /**
     * Store a newly created review.
     *
     * @OA\Post(
     *     path="/api/v1/reviews",
     *     summary="ثبت نظر جدید برای سرویس‌دهنده (User)",
     *     tags={"Review (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"product_id", "rating", "comment"},
     *             @OA\Property(property="product_id", type="integer", example=1),
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=5),
     *             @OA\Property(property="comment", type="string", minLength=10, maxLength=1000, example="This product is amazing!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Review created successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or already reviewed",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You have already reviewed this product")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - User hasn't purchased the product",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You can only review products you have purchased")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user has already reviewed this product
        $existingReview = Review::where('user_id', Auth::id())
            ->where('product_id', $request->product_id)
            ->first();

        if ($existingReview) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already reviewed this product'
            ], 400);
        }

        // Check if user has purchased the product
        $hasPurchased = Auth::user()->orders()
            ->whereHas('items', function ($query) use ($request) {
                $query->where('product_id', $request->product_id);
            })
            ->where('status', 'completed')
            ->exists();

        if (!$hasPurchased) {
            return response()->json([
                'status' => 'error',
                'message' => 'You can only review products you have purchased'
            ], 403);
        }

        $serviceRequest = \App\Models\ServiceRequest::find($request->service_request_id);
        $review = Review::create([
            'user_id' => Auth::id(),
            'customer_user_id' => Auth::id(),
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'service_request_id' => $request->service_request_id,
            'service_provider_user_id' => $serviceRequest ? $serviceRequest->service_provider_user_id : null,
        ]);

        // Update product average rating
        $this->updateProductRating($request->product_id);

        $review = $review->fresh();

        return response()->json([
            'status' => 'success',
            'message' => 'Review created successfully',
            'data' => $review
        ], 201);
    }

    /**
     * Display the specified review.
     *
     * @OA\Get(
     *     path="/api/v1/reviews/{id}",
     *     summary="Get a specific review (User)",
     *     tags={"Review (User)"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review details",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Review not found"
     *     )
     * )
     */
    public function show(Review $review)
    {
        $review->load(['user', 'product']);

        return response()->json([
            'status' => 'success',
            'data' => $review
        ]);
    }

    /**
     * Update the specified review.
     *
     * @OA\Put(
     *     path="/api/v1/reviews/{review}",
     *     summary="ویرایش نظر ثبت‌شده (User)",
     *     tags={"Review (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="review",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5, example=4),
     *             @OA\Property(property="comment", type="string", minLength=10, maxLength=1000, example="Updated review comment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Review updated successfully"),
     *             @OA\Property(property="data", ref="#/components/schemas/Review")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - User doesn't own the review",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to update this review")
     *         )
     *     )
     * )
     */
    public function update(Request $request, Review $review)
    {
        // Check if user owns the review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to update this review'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'sometimes|string|min:10|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $review->update($request->only(['rating', 'comment']));

        // Update product average rating
        $this->updateProductRating($review->product_id);

        return response()->json([
            'status' => 'success',
            'message' => 'Review updated successfully',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified review.
     *
     * @OA\Delete(
     *     path="/api/v1/reviews/{review}",
     *     summary="حذف نظر ثبت‌شده (User)",
     *     tags={"Review (User)"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="review",
     *         in="path",
     *         required=true,
     *         description="Review ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Review deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Review deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized - User doesn't own the review",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="You are not authorized to delete this review")
     *         )
     *     )
     * )
     */
    public function destroy(Review $review)
    {
        // Check if user owns the review
        if ($review->user_id !== Auth::id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'You are not authorized to delete this review'
            ], 403);
        }

        $productId = $review->product_id;
        $review->delete();

        // Update product average rating
        $this->updateProductRating($productId);

        return response()->json([
            'status' => 'success',
            'message' => 'Review deleted successfully'
        ]);
    }

    /**
     * Update the average rating of a product.
     *
     * @param int $productId
     * @return void
     */
    private function updateProductRating($productId)
    {
        $product = Product::find($productId);
        if ($product) {
            $averageRating = Review::where('product_id', $productId)->avg('rating');
            $product->update(['average_rating' => round($averageRating, 1)]);
        }
    }
} 