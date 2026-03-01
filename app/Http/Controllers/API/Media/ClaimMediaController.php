<?php

namespace App\Http\Controllers\API\Media;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ClaimMediaController extends Controller
{
    public function uploadProofImage(Request $request, Claim $claim)
    {
        $user = $request->user();

        // only claimer can upload proof (or staff/admin can assist)
        if ($user->role === 'user' && $claim->claimer_id !== $user->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $validated = $request->validate([
            'image' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if ($claim->proof_image_path && Storage::disk('public')->exists($claim->proof_image_path)) {
            Storage::disk('public')->delete($claim->proof_image_path);
        }

        $file = $validated['image'];
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('claims', $filename, 'public');

        $claim->update(['proof_image_path' => $path]);

        return response()->json([
            'message' => 'Claim proof image uploaded.',
            'image_url' => asset('storage/' . $path),
            'claim' => $claim->fresh(),
        ]);
    }
}