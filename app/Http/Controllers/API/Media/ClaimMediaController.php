<?php

namespace App\Http\Controllers\API\Media;

use App\Http\Controllers\Controller;
use App\Models\Claim;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\Audit\ActivityLogger;

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

        if ($claim->proof_image_path && Storage::disk('local')->exists($claim->proof_image_path)) {
            Storage::disk('local')->delete($claim->proof_image_path);
        }

        $file = $validated['image'];
        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('claims', $filename, 'local');

        $claim->update(['proof_image_path' => $path]);

        ActivityLogger::log(
            $request->user()->id,
            'CLAIM_PROOF_UPLOADED',
            'claim',
            $claim->id,
            ['proof_image_path' => $claim->proof_image_path]
        );

        return response()->json([
            'message' => 'Claim proof image uploaded.',
            'image_url' => url("/api/claims/{$claim->id}/proof-image"),
            'claim' => $claim->fresh(),
        ]);
    }
}