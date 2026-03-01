<?php

namespace App\Http\Controllers\API\Media;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Audit\ActivityLogger;

class ItemMediaController extends Controller
{
    public function uploadImage(Request $request, Item $item)
    {
        $user = $request->user();

        // user can upload only for their own report; staff/admin can upload for any
        if ($user->role === 'user' && $item->reported_by !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        // delete old image if exists
        if ($item->image_path && Storage::disk('public')->exists($item->image_path)) {
            Storage::disk('public')->delete($item->image_path);
        }

        $file = $validated['image'];
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('items', $filename, 'public');

        $item->update(['image_path' => $path]);

        ActivityLogger::log(
            $request->user()->id,
            'ITEM_IMAGE_UPLOADED',
            'item',
            $item->id,
            ['image_path' => $item->image_path]
        );

        return response()->json([
            'message' => 'Item image uploaded.',
            'image_url' => asset('storage/' . $path),
            'item' => $item->fresh(),
        ]);
    }
}