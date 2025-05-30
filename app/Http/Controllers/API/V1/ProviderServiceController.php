<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreProviderServiceRequest;
use App\Http\Resources\V1\ServiceResource;
use App\Http\Resources\V1\ProviderResource;
use App\Models\Provider;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;

class ProviderServiceController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/providers/{provider}/services",
     *     summary="لیست سرویس‌های ارائه‌شده توسط سرویس‌دهنده (User)",
     *     tags={"ProviderService (User)"},
     *     @OA\Parameter(
     *         name="providerId",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="تعداد آیتم‌ها در هر صفحه",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="لیست صفحه‌بندی شده خدمات با جزئیات ارائه‌دهنده",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="provider", ref="#/components/schemas/ProviderResource"),
     *             @OA\Property(property="services", type="array", @OA\Items(ref="#/components/schemas/ServiceResource")),
     *             @OA\Property(property="links", type="object"),
     *             @OA\Property(property="meta", type="object")
     *         )
     *     ),
     *     @OA\Response(response=404, description="ارائه‌دهنده یافت نشد"),
     *     security={{"sanctum": {}}}
     * )
     */
    public function index(Request $request, $providerId)
    {
        try {
            $provider = Provider::with(['occupation', 'province', 'city', 'medals', 'reviews'])
                ->findOrFail($providerId);
            $this->authorize('view', $provider);

            $perPage = $request->query('per_page', 15);
            $services = $provider->services()->paginate($perPage);

            return response()->json([
                'provider' => new ProviderResource($provider),
                'data' => ServiceResource::collection($services),
                'links' => $services->toArray()['links'] ?? [],
                'meta' => $services->toArray()['meta'] ?? [],
            ]);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => 'شما مجاز به مشاهده این اطلاعات نیستید'], 403);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'ارائه‌دهنده یافت نشد'], 404);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/providers/{provider}/services",
     *     summary="افزودن سرویس جدید به سرویس‌دهنده (Admin)",
     *     tags={"ProviderService (Admin)"},
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
     *             @OA\Property(property="custom_description", type="string", example="توضیحات سفارشی")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="خدمت با موفقیت افزوده یا به‌روزرسانی شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="خدمت با موفقیت افزوده شد")
     *         )
     *     ),
     *     @OA\Response(response=404, description="ارائه‌دهنده یافت نشد"),
     *     @OA\Response(response=422, description="خطای اعتبارسنجی"),
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

            $provider->services()->syncWithoutDetaching([
                $data['service_id'] => [
                    'price' => $data['price'],
                    'custom_description' => $data['custom_description'] ?? null,
                ]
            ]);

            $message = $pivotExists
                ? 'خدمت با موفقیت به‌روزرسانی شد'
                : 'خدمت با موفقیت افزوده شد';

            $status = $pivotExists ? 200 : 201;
            return response()->json(['message' => $message], $status);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'ارائه‌دهنده یافت نشد'], 404);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/providers/{provider}/services/{service}",
     *     summary="حذف سرویس از سرویس‌دهنده (Admin)",
     *     tags={"ProviderService (Admin)"},
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
     *     @OA\Response(response=204, description="خدمت حذف شد"),
     *     @OA\Response(response=404, description="ارائه‌دهنده یا خدمت یافت نشد"),
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

            if (!$attached) {
                return response()->json(['message' => 'خدمت برای این ارائه‌دهنده یافت نشد'], 404);
            }

            $provider->services()->detach($serviceId);

            return response()->noContent();
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'ارائه‌دهنده یافت نشد'], 404);
        }
    }

    /**
     * حذف provider service توسط ادمین
     */
    public function destroyByAdmin($id)
    {
        $providerService = \App\Models\ProviderService::find($id);
        if (! $providerService) {
            return response()->json(['message' => 'خدمت ارائه‌دهنده یافت نشد'], 404);
        }
        $providerService->delete();
        return response()->noContent();
    }
}
