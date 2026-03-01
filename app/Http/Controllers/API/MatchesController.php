<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Matches\StoreMatchRequest;
use App\Models\Item;
use App\Models\MatchModel;
use Illuminate\Support\Facades\DB;
use App\Services\Audit\ActivityLogger;

class MatchesController extends Controller
{
    public function index()
    {
        $matches = MatchModel::with([
            'lostItem:id,title,type,status',
            'foundItem:id,title,type,status',
            'matcher:id,name,role',
        ])
            ->latest()
            ->paginate(15);

        return response()->json($matches);
    }

    public function store(StoreMatchRequest $request)
    {
        $data = $request->validated();

        $lost = Item::findOrFail($data['lost_item_id']);
        $found = Item::findOrFail($data['found_item_id']);

        if ($lost->type !== 'lost') {
            return response()->json(['message' => 'lost_item_id must be a LOST item.'], 422);
        }
        if ($found->type !== 'found') {
            return response()->json(['message' => 'found_item_id must be a FOUND item.'], 422);
        }

        $match = DB::transaction(function () use ($lost, $found, $data, $request) {
            $match = MatchModel::create([
                'lost_item_id' => $lost->id,
                'found_item_id' => $found->id,
                'matched_by' => $request->user()->id,
                'notes' => $data['notes'] ?? null,
                'matched_at' => now(),
            ]);

            ActivityLogger::log(
                $request->user()->id,
                'ITEM_MATCHED',
                'match',
                $match->id,
                ['lost_item_id' => $match->lost_item_id, 'found_item_id' => $match->found_item_id]
            );

            // Update statuses for clarity
            $lost->update(['status' => 'matched']);
            $found->update(['status' => 'matched']);

            return $match;
        });

        return response()->json([
            'message' => 'Items matched.',
            'match' => $match->load(['lostItem', 'foundItem', 'matcher']),
        ], 201);
    }
}