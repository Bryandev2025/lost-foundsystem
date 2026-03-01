<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Claims\StoreClaimRequest;
use App\Http\Requests\API\Claims\ApproveClaimRequest;
use App\Http\Requests\API\Claims\DenyClaimRequest;
use App\Http\Requests\API\Claims\ReleaseClaimRequest;
use App\Models\Claim;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\Notification\NotificationService;
use App\Services\Audit\ActivityLogger;

class ClaimsController extends Controller
{
    // Staff/Admin: list all claims
    public function index(Request $request)
    {
        $claims = Claim::with([
            'item:id,title,type,status',
            'claimer:id,name,email,phone,role',
            'reviewer:id,name,role',
        ])
            ->latest()
            ->paginate(15);

        return response()->json($claims);
    }

    // User: list own claims
    public function myClaims(Request $request)
    {
        $claims = Claim::where('claimer_id', $request->user()->id)
            ->with(['item:id,title,type,status'])
            ->latest()
            ->paginate(15);

        return response()->json($claims);
    }

    public function store(StoreClaimRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        $item = Item::findOrFail($data['item_id']);

        // Basic safety checks
        if ($item->status === 'archived') {
            return response()->json(['message' => 'Item is archived and cannot be claimed.'], 422);
        }

        // prevent duplicate pending claims by same user for same item
        $exists = Claim::where('item_id', $item->id)
            ->where('claimer_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You already have an active claim for this item.'], 422);
        }

        $claim = DB::transaction(function () use ($item, $user, $data, $request) {
            $claim = Claim::create([
                'item_id' => $item->id,
                'claimer_id' => $user->id,
                'status' => 'pending',
                'proof_details' => $data['proof_details'] ?? null,
            ]);

            ActivityLogger::log(
                $request->user()->id,
                'CLAIM_SUBMITTED',
                'claim',
                $claim->id,
                ['item_id' => $claim->item_id]
            );

            return $claim;
        });

        return response()->json([
            'message' => 'Claim submitted.',
            'claim' => $claim->load(['item:id,title,type,status']),
        ], 201);
    }

    public function show(Claim $claim)
    {
        $this->authorize('view', $claim);

        return response()->json([
            'claim' => $claim->load([
                'item',
                'claimer:id,name,email,phone,role',
                'reviewer:id,name,role',
            ]),
        ]);
    }

    public function downloadProof(Claim $claim)
    {
        $this->authorize('view', $claim);

        if (!$claim->proof_image_path || !Storage::disk('local')->exists($claim->proof_image_path)) {
            return response()->json(['message' => 'Proof image not found.'], 404);
        }

        ActivityLogger::log(
            request()->user()->id,
            'VIEWED_PROOF_IMAGE',
            'claim',
            $claim->id,
            ['claim_id' => $claim->id]
        );

        return Storage::disk('local')->download($claim->proof_image_path);
    }

    // Staff/Admin actions
    public function approve(ApproveClaimRequest $request, Claim $claim)
    {
        $this->authorize('updateStatus', $claim);

        if ($claim->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending claims can be approved.'
            ], 422);
        }

        $claim->update([
            'status' => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->validated()['review_notes'] ?? null,
        ]);

        ActivityLogger::log(
            $request->user()->id,
            'CLAIM_APPROVED',
            'claim',
            $claim->id,
            ['item_id' => $claim->item_id, 'claimer_id' => $claim->claimer_id]
        );

        // Send notifications
        $notification = new NotificationService();

        $notification->sendEmail(
            $claim->claimer,
            'Claim Approved',
            'Your claim for item "' . $claim->item->title . '" has been approved. Please visit the Lost and Found office to claim it.'
        );

        if ($claim->claimer->phone) {
            $notification->sendSMS(
                $claim->claimer,
                'Lost & Found: Your claim for "' . $claim->item->title . '" is approved.'
            );
        }

        return response()->json([
            'message' => 'Claim approved.',
            'claim' => $claim->fresh()->load(['item', 'claimer', 'reviewer']),
        ]);
    }
    public function deny(DenyClaimRequest $request, Claim $claim)
    {
        $this->authorize('updateStatus', $claim);

        if ($claim->status !== 'pending') {
            return response()->json(['message' => 'Only pending claims can be denied.'], 422);
        }

        $claim->update([
            'status' => 'denied',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'review_notes' => $request->validated()['review_notes'],
        ]);

        ActivityLogger::log(
            $request->user()->id,
            'CLAIM_DENIED',
            'claim',
            $claim->id,
            ['item_id' => $claim->item_id, 'claimer_id' => $claim->claimer_id]
        );

        return response()->json([
            'message' => 'Claim denied.',
            'claim' => $claim->fresh(),
        ]);
    }

    public function release(ReleaseClaimRequest $request, Claim $claim)
    {
        $this->authorize('updateStatus', $claim);

        if ($claim->status !== 'approved') {
            return response()->json(['message' => 'Only approved claims can be released.'], 422);
        }

        DB::transaction(function () use ($claim, $request) {
            $claim->update([
                'status' => 'released',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'review_notes' => $request->validated()['review_notes'] ?? $claim->review_notes,
                'released_at' => now(),
            ]);

            // Mark item as claimed
            $claim->item()->update(['status' => 'claimed']);

            ActivityLogger::log(
                $request->user()->id,
                'CLAIM_RELEASED',
                'claim',
                $claim->id,
                ['item_id' => $claim->item_id, 'claimer_id' => $claim->claimer_id]
            );
        });

        return response()->json([
            'message' => 'Item released to claimant.',
            'claim' => $claim->fresh()->load('item'),
        ]);
    }
}