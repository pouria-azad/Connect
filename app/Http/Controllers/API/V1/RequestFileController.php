<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\RequestFileResource;
use App\Models\RequestFile;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RequestFileController extends Controller
{
    // لیست فایل‌های یک درخواست
    public function index($serviceRequestId)
    {
        $serviceRequest = ServiceRequest::findOrFail($serviceRequestId);
        return RequestFileResource::collection($serviceRequest->files);
    }

    // حذف یک فایل
    public function destroy($id)
    {
        $file = RequestFile::findOrFail($id);
        $this->authorize('delete', $file);
        Storage::disk('public')->delete(str_replace('/storage/', '', $file->file_url));
        $file->delete();
        return response()->json(['message' => 'فایل با موفقیت حذف شد']);
    }

    // دانلود فایل
    public function download($id)
    {
        $file = RequestFile::findOrFail($id);
        $this->authorize('view', $file);
        $path = str_replace('/storage/', '', $file->file_url);
        if (!Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'فایل یافت نشد'], 404);
        }
        return Storage::disk('public')->download($path, $file->file_name);
    }
} 