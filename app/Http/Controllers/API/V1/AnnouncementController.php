<?php
namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Announcement\storeAnnouncementRequest;
use App\Http\Requests\V1\Announcement\updateAnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AnnouncementController extends Controller
{
    use AuthorizesRequests;
    /**
     * @OA\Get(
     *     path="/api/v1/announcements",
     *     summary="دریافت لیست اعلان‌های فعال",
     *     tags={"Announcements (User)"},
     *     @OA\Response(
     *         response=200,
     *         description="لیست اعلان‌های فعال",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="اعلان مهم"),
     *                 @OA\Property(property="message", type="string", example="متن اعلان"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="published_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function index()
    {
        return response()->json(
            Announcement::where('is_active', true)->latest()->get()
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/announcements",
     *     summary="ایجاد اعلان جدید",
     *     tags={"Announcements (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="اعلان مهم"),
     *             @OA\Property(property="message", type="string", example="متن اعلان"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اعلان با موفقیت ایجاد شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="اعلان ایجاد شد"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="اعلان مهم"),
     *                 @OA\Property(property="message", type="string", example="متن اعلان"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="published_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function store(storeAnnouncementRequest $request)
    {
        $user = $request->user();
        if (! $user instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $data = $request->validated();
        $data['published_at'] = now();

        $announcement = Announcement::create($data);

        return response()->json(['message' => 'اعلان ایجاد شد', 'data' => $announcement]);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/admin/announcements/{id}",
     *     summary="به‌روزرسانی اعلان",
     *     tags={"Announcements (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="title", type="string", example="اعلان به‌روزرسانی‌شده"),
     *             @OA\Property(property="message", type="string", example="متن جدید"),
     *             @OA\Property(property="is_active", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اعلان با موفقیت به‌روزرسانی شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="اعلان به‌روزرسانی شد"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="title", type="string", example="اعلان به‌روزرسانی‌شده"),
     *                 @OA\Property(property="message", type="string", example="متن جدید"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="published_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-05-09T12:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-05-09T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="اعلان یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     )
     * )
     */
    public function update(updateAnnouncementRequest $request, $id)
    {
        $user = $request->user();
        if (! $user instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $announcement = Announcement::findOrFail($id);
        $announcement->update($request->validated());

        return response()->json(['message' => 'اعلان به‌روزرسانی شد', 'data' => $announcement]);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/admin/announcements/{id}",
     *     summary="حذف اعلان",
     *     tags={"Announcements (Admin)"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="اعلان با موفقیت حذف شد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="اعلان حذف شد")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="دسترسی غیرمجاز",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="اعلان یافت نشد",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     )
     * )
     */
    public function destroy($id)
    {
        $user = auth()->user();
        if (! $user instanceof \App\Models\Admin) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $announcement = Announcement::findOrFail($id);
        $announcement->delete();

        return response()->json(['message' => 'اعلان حذف شد']);
    }
}
