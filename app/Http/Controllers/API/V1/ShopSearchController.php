<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Http\Request;

class ShopSearchController extends Controller
{
    /**
     * جستجو در محصولات و خدمات فروشگاه
     *
     * @OA\Get(
     *     path="/api/v1/shop/search",
     *     summary="جستجو در فروشگاه (محصولات و خدمات)",
     *     tags={"Shop"},
     *     @OA\Parameter(name="q", in="query", required=true, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer", default=15)),
     *     @OA\Response(response=200, description="نتایج جستجو")
     * )
     */
    public function search(Request $request)
    {
        $q = $request->input('q');
        $perPage = $request->input('per_page', 15);
        if (!$q) {
            return response()->json(['message' => 'عبارت جستجو الزامی است'], 422);
        }
        $products = Product::where('name', 'like', "%$q%")
            ->orWhere('description', 'like', "%$q%")
            ->paginate($perPage, ['*'], 'products_page');
        $services = Service::where('title', 'like', "%$q%")
            ->orWhere('description', 'like', "%$q%")
            ->paginate($perPage, ['*'], 'services_page');
        return response()->json([
            'products' => $products,
            'services' => $services,
        ]);
    }
} 