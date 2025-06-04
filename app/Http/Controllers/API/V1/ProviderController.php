<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function getBusinessHours(): JsonResponse
    {
        $provider = auth()->user()->provider->senfi;
        return response()->json([
            'business_hours' => $provider->business_hours
        ]);
    }

    public function updateBusinessHours(Request $request): JsonResponse
    {
        $request->validate([
            'business_hours' => 'required|array'
        ]);

        $provider = auth()->user()->provider->senfi;
        $provider->update([
            'business_hours' => $request->business_hours
        ]);

        return response()->json([
            'message' => 'ساعات کاری با موفقیت به‌روز شد',
            'business_hours' => $provider->business_hours
        ]);
    }

    public function getPaymentMethods(): JsonResponse
    {
        $provider = auth()->user()->provider->senfi;
        return response()->json([
            'payment_methods' => $provider->accepted_payment_methods
        ]);
    }

    public function updatePaymentMethods(Request $request): JsonResponse
    {
        $request->validate([
            'payment_methods' => 'required|array'
        ]);

        $provider = auth()->user()->provider->senfi;
        $provider->update([
            'accepted_payment_methods' => $request->payment_methods
        ]);

        return response()->json([
            'message' => 'روش‌های پرداخت با موفقیت به‌روز شد',
            'payment_methods' => $provider->accepted_payment_methods
        ]);
    }

    public function getServiceAreas(): JsonResponse
    {
        $provider = auth()->user()->provider->canctyar;
        return response()->json([
            'service_areas' => $provider->service_areas
        ]);
    }

    public function updateServiceAreas(Request $request): JsonResponse
    {
        $request->validate([
            'service_areas' => 'required|array'
        ]);

        $provider = auth()->user()->provider->canctyar;
        $provider->update([
            'service_areas' => $request->service_areas
        ]);

        return response()->json([
            'message' => 'مناطق سرویس‌دهی با موفقیت به‌روز شد',
            'service_areas' => $provider->service_areas
        ]);
    }

    public function getAvailability(): JsonResponse
    {
        $provider = auth()->user()->provider->canctyar;
        return response()->json([
            'availability_hours' => $provider->availability_hours
        ]);
    }

    public function updateAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'availability_hours' => 'required|array'
        ]);

        $provider = auth()->user()->provider->canctyar;
        $provider->update([
            'availability_hours' => $request->availability_hours
        ]);

        return response()->json([
            'message' => 'ساعات در دسترس بودن با موفقیت به‌روز شد',
            'availability_hours' => $provider->availability_hours
        ]);
    }

    public function updateTravelSettings(Request $request): JsonResponse
    {
        $request->validate([
            'can_travel' => 'required|boolean',
            'travel_fee_per_km' => 'required_if:can_travel,true|numeric|min:0',
            'minimum_service_fee' => 'required|numeric|min:0'
        ]);

        $provider = auth()->user()->provider->canctyar;
        $provider->update($request->only([
            'can_travel',
            'travel_fee_per_km',
            'minimum_service_fee'
        ]));

        return response()->json([
            'message' => 'تنظیمات سفر با موفقیت به‌روز شد',
            'travel_settings' => [
                'can_travel' => $provider->can_travel,
                'travel_fee_per_km' => $provider->travel_fee_per_km,
                'minimum_service_fee' => $provider->minimum_service_fee
            ]
        ]);
    }

    public function profile(): JsonResponse
    {
        $provider = auth()->user()->provider;
        
        if (!$provider) {
            return response()->json(['message' => 'پروفایل ارائه‌دهنده یافت نشد'], 404);
        }

        $data = [
            'id' => $provider->id,
            'provider_type' => $provider->provider_type,
            'bio' => $provider->bio,
            'is_verified' => $provider->is_verified,
            'profile_image' => $provider->profile_image,
            'created_at' => $provider->created_at,
            'updated_at' => $provider->updated_at,
        ];

        // Add provider type specific data
        if ($provider->provider_type === 'senfi') {
            $data['senfi'] = $provider->senfi;
        } else if ($provider->provider_type === 'canctyar') {
            $data['canctyar'] = $provider->canctyar;
        }

        return response()->json($data);
    }
} 