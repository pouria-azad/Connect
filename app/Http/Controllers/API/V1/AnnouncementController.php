<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return response()->json(Announcement::where('is_active', true)->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'message' => 'required|string', // به جای body
            'is_active' => 'boolean',
        ]);
        $announcement = Announcement::create($data);

        return response()->json(['message' => 'اعلان ایجاد شد', 'data' => $announcement]);
    }

    public function update(Request $request, $id)
    {
        $announcement = Announcement::findOrFail($id);

        $data = $request->validate([
            'title' => 'string',
            'message' => 'string', // به جای body
            'is_active' => 'boolean',
        ]);

        $announcement->update($data);

        return response()->json(['message' => 'اعلان به‌روزرسانی شد', 'data' => $announcement]);
    }

    public function destroy($id)
    {
        Announcement::destroy($id);
        return response()->json(['message' => 'اعلان حذف شد']);
    }
}

