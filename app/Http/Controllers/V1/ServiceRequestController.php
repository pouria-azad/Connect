<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\ServiceRequest\StoreServiceRequest;
use App\Http\Requests\V1\ServiceRequest\UpdateServiceRequest;
use App\Http\Resources\V1\ServiceRequestResource;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ServiceRequestController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/service-requests",
     *     summary="لیست درخواست‌های سرویس (User)",
     *     tags={"ServiceRequest (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="request_type", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="customer_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="service_provider_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="service_category_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="province_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="city_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="لیست درخواست‌ها")
     * )
     */
    public function index(Request $request)
    {
        $query = ServiceRequest::query();

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by request type
        if ($request->has('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_user_id', $request->customer_id);
        }

        // Filter by service provider
        if ($request->has('service_provider_id')) {
            $query->where('service_provider_user_id', $request->service_provider_id);
        }

        // Filter by service category
        if ($request->has('service_category_id')) {
            $query->where('service_category_id', $request->service_category_id);
        }

        // Filter by province
        if ($request->has('province_id')) {
            $query->where('province_id', $request->province_id);
        }

        // Filter by city
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Sort by created_at
        $query->orderBy('created_at', 'desc');

        $serviceRequests = $query->paginate(10);

        return ServiceRequestResource::collection($serviceRequests);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-requests",
     *     summary="ثبت درخواست سرویس جدید (User)",
     *     tags={"ServiceRequest (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=201, description="درخواست با موفقیت ثبت شد")
     * )
     */
    public function store(StoreServiceRequest $request)
    {
        try {
            DB::beginTransaction();

            $serviceRequest = ServiceRequest::create([
                'customer_user_id' => auth()->id(),
                'service_provider_user_id' => $request->service_provider_user_id,
                'service_category_id' => $request->service_category_id,
                'subject' => $request->subject,
                'description' => $request->description,
                'request_type' => $request->request_type,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'scope_type' => $request->scope_type,
                'status' => 'pending_payment',
                'initial_fee_amount' => 15000.00,
            ]);

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('service-requests/' . $serviceRequest->id, 'public');
                    
                    $serviceRequest->files()->create([
                        'file_url' => Storage::url($path),
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            return new ServiceRequestResource($serviceRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/service-requests/{serviceRequest}",
     *     summary="نمایش جزئیات یک درخواست خدمت (Admin & User)",
     *     tags={"ServiceRequest (Admin & User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="جزئیات درخواست خدمت")
     * )
     */
    public function show(ServiceRequest $serviceRequest)
    {
        return new ServiceRequestResource($serviceRequest);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/service-requests/{serviceRequest}",
     *     summary="ویرایش اطلاعات یک درخواست خدمت (Admin & User)",
     *     tags={"ServiceRequest (Admin & User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent()),
     *     @OA\Response(response=200, description="درخواست با موفقیت ویرایش شد")
     * )
     */
    public function update(UpdateServiceRequest $request, ServiceRequest $serviceRequest)
    {
        try {
            DB::beginTransaction();

            $serviceRequest->update($request->validated());

            // Handle file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('service-requests/' . $serviceRequest->id, 'public');
                    
                    $serviceRequest->files()->create([
                        'file_url' => Storage::url($path),
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                    ]);
                }
            }

            DB::commit();

            return new ServiceRequestResource($serviceRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/service-requests/{serviceRequest}",
     *     summary="حذف یک درخواست خدمت به همراه فایل‌های مرتبط (Admin & User)",
     *     tags={"ServiceRequest (Admin & User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="درخواست با موفقیت حذف شد")
     * )
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        try {
            DB::beginTransaction();

            // Delete associated files from storage
            foreach ($serviceRequest->files as $file) {
                Storage::delete(str_replace('/storage/', 'public/', $file->file_url));
            }

            $serviceRequest->delete();

            DB::commit();

            return response()->noContent();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-requests/{serviceRequest}/accept",
     *     summary="پذیرش درخواست سرویس (User)",
     *     tags={"ServiceRequest (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="درخواست با موفقیت پذیرفته شد")
     * )
     */
    public function accept(ServiceRequest $serviceRequest)
    {
        // فقط خدمات‌دهنده می‌تواند درخواست را بپذیرد
        if (auth()->user()->user_type !== 'provider') {
            return response()->json(['message' => 'شما مجاز به انجام این عملیات نیستید.'], 403);
        }
        if ($serviceRequest->status !== 'pending_sp_acceptance') {
            return response()->json(['message' => 'این درخواست قابل پذیرش نیست.'], 400);
        }

        if ($serviceRequest->request_type === 'public' && $serviceRequest->accepted_service_provider_user_id !== null) {
            return response()->json(['message' => 'این درخواست قبلاً توسط خدمات دهنده دیگری پذیرفته شده است.'], 400);
        }

        try {
            DB::beginTransaction();

            $serviceRequest->update([
                'status' => 'accepted_by_sp',
                'accepted_service_provider_user_id' => auth()->id(),
                'accepted_at' => now(),
            ]);

            DB::commit();

            return new ServiceRequestResource($serviceRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-requests/{serviceRequest}/reject",
     *     summary="رد درخواست سرویس (Admin)",
     *     tags={"ServiceRequest (Admin)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"rejection_reason"},
     *         @OA\Property(property="rejection_reason", type="string")
     *     )),
     *     @OA\Response(response=200, description="درخواست با موفقیت رد شد")
     * )
     */
    public function reject(Request $request, ServiceRequest $serviceRequest)
    {
        if (!in_array($serviceRequest->status, ['pending_sp_acceptance', 'accepted_by_sp'])) {
            return response()->json(['message' => 'این درخواست قابل رد نیست.'], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $serviceRequest->update([
                'status' => 'rejected_by_sp',
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return new ServiceRequestResource($serviceRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-requests/{serviceRequest}/complete",
     *     summary="تکمیل درخواست سرویس (User)",
     *     tags={"ServiceRequest (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="درخواست با موفقیت تکمیل شد")
     * )
     */
    public function complete(ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->status !== 'accepted_by_sp') {
            return response()->json(['message' => 'این درخواست قابل تکمیل نیست.'], 400);
        }

        try {
            DB::beginTransaction();

            $serviceRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            DB::commit();

            return new ServiceRequestResource($serviceRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/service-requests/{serviceRequest}/cancel",
     *     summary="لغو درخواست سرویس (User)",
     *     tags={"ServiceRequest (User)"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="serviceRequest", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(
     *         required={"rejection_reason"},
     *         @OA\Property(property="rejection_reason", type="string")
     *     )),
     *     @OA\Response(response=200, description="درخواست با موفقیت لغو شد")
     * )
     */
    public function cancel(Request $request, ServiceRequest $serviceRequest)
    {
        if (!in_array($serviceRequest->status, ['pending_payment', 'pending_admin_approval', 'pending_sp_acceptance'])) {
            return response()->json(['message' => 'این درخواست قابل لغو نیست.'], 400);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $serviceRequest->update([
                'status' => 'canceled_by_customer',
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return new ServiceRequestResource($serviceRequest);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 