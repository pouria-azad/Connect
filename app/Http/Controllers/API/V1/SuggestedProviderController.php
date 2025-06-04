<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ProviderResource;
use App\Models\Provider;
use Illuminate\Http\Request;

class SuggestedProviderController extends Controller
{
    /**
     * لیست خدمات‌دهنده‌های پیشنهادی
     *
     * @OA\Get(
     *     path="/api/v1/providers/suggested",
     *     summary="لیست خدمات‌دهنده‌های پیشنهادی (بر اساس امتیاز و سفارش موفق)",
     *     tags={"Provider"},
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="لیست خدمات‌دهنده‌های پیشنهادی")
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $providers = Provider::withCount('reviews', 'shops')
            ->orderByDesc('average_rating')
            ->orderByDesc('successful_orders_count')
            ->paginate($perPage);
        return ProviderResource::collection($providers);
    }
} 