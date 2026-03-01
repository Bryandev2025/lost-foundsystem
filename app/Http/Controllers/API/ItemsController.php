<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Items\StoreItemRequest;
use App\Http\Requests\API\Items\UpdateItemRequest;
use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\Audit\ActivityLogger;

class ItemsController extends Controller
{
    // Staff/Admin can view all; User can view all (public feed) OR restrict later if you want.
    public function index(Request $request)
    {
        $q = Item::query()->with(['reporter:id,name,role', 'finder:id,name,role']);

        // optional filters
        if ($request->filled('type'))
            $q->where('type', $request->string('type'));
        if ($request->filled('status'))
            $q->where('status', $request->string('status'));
        if ($request->filled('category'))
            $q->where('category', $request->string('category'));

        $items = $q->latest()->paginate(15);

        return response()->json($items);
    }

    public function store(StoreItemRequest $request)
    {
        $data = $request->validated();

        // Users create reports; status defaults to pending
        unset($data['status']);

        $item = Item::create([
            ...$data,
            'status' => 'pending',
            'reported_by' => $request->user()->id,
            'found_by' => $data['type'] === 'found' ? $request->user()->id : null,
        ]);

        ActivityLogger::log(
            $request->user()->id,
            'ITEM_CREATED',
            'item',
            $item->id,
            ['type' => $item->type, 'status' => $item->status]
        );

        return response()->json([
            'message' => 'Item report created.',
            'item' => $item->load(['reporter:id,name,role', 'finder:id,name,role']),
        ], 201);
    }

    public function show(Item $item)
    {
        return response()->json([
            'item' => $item->load(['reporter:id,name,role', 'finder:id,name,role', 'claims']),
        ]);
    }

    public function update(UpdateItemRequest $request, Item $item)
    {
        $user = $request->user();

        // Basic rule:
        // - user can edit only their own item (and not status)
        // - staff/admin can edit anything including status
        $data = $request->validated();

        if ($user->role === 'user') {
            if ($item->reported_by !== $user->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
            unset($data['status']); // users cannot change status
        }

        $item->update($data);

        ActivityLogger::log(
            $request->user()->id,
            'ITEM_UPDATED',
            'item',
            $item->id,
            ['changed' => array_keys($data)]
        );

        return response()->json([
            'message' => 'Item updated.',
            'item' => $item->fresh()->load(['reporter:id,name,role', 'finder:id,name,role']),
        ]);
    }

    public function destroy(Request $request, Item $item)
    {
        $user = $request->user();

        // user can delete their own pending report; staff/admin can delete any
        if ($user->role === 'user') {
            if ($item->reported_by !== $user->id)
                return response()->json(['message' => 'Forbidden.'], 403);
            if ($item->status !== 'pending')
                return response()->json(['message' => 'Only pending items can be deleted.'], 422);
        }

        $item->delete();

        ActivityLogger::log(
            $request->user()->id,
            'ITEM_DELETED',
            'item',
            $item->id,
            ['title' => $item->title, 'status' => $item->status]
        );

        return response()->json(['message' => 'Item deleted.']);
    }

    public function myItems(Request $request)
    {
        $items = Item::where('reported_by', $request->user()->id)
            ->latest()
            ->paginate(15);

        return response()->json($items);
    }
}