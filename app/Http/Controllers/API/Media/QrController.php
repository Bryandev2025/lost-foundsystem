<?php

namespace App\Http\Controllers\API\Media;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /**
     * Staff/Admin generates a QR token for an item (if none yet).
     * QR token is stored in items.qr_token.
     */
    public function generate(Request $request, Item $item)
    {
        if (!$item->qr_token) {
            $item->update([
                'qr_token' => Str::uuid()->toString(),
                'qr_generated_at' => now(),
            ]);
        }

        return response()->json([
            'message' => 'QR ready.',
            'qr_token' => $item->qr_token,
            'qr_image_url' => route('items.qr.png', ['item' => $item->id]),
        ]);
    }

    /**
     * Returns QR code PNG image.
     */
    public function png(Item $item)
    {
        if (!$item->qr_token) {
            return response()->json(['message' => 'QR not generated yet.'], 422);
        }

        // Encode token (scan will use /scan/{token})
        $payload = $item->qr_token;

        $png = QrCode::format('png')
            ->size(300)
            ->margin(1)
            ->generate($payload);

        return response($png)->header('Content-Type', 'image/png');
    }

    /**
     * Scan endpoint: lookup item by token.
     * Staff/Admin can scan and open item data quickly.
     */
    public function scan(Request $request, string $token)
    {
        $item = Item::where('qr_token', $token)->first();

        if (!$item) {
            return response()->json(['message' => 'Invalid QR token.'], 404);
        }

        return response()->json([
            'item' => $item->load(['reporter:id,name,role', 'finder:id,name,role', 'claims']),
        ]);
    }
}