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
        $query = Item::query()
            ->with(['reporter:id,name,role', 'finder:id,name,role'])
            ->latest();

        // Filter: mine (only show user's own items)
        if ($request->boolean('mine')) {
            $query->where('reported_by', $request->user()->id);
        }

        // Filter: type (lost/found)
        if ($request->filled('type')) {
            $query->where('type', $request->string('type'));
        }

        // Filter: status (pending/matched/claimed/archived)
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Filter: category
        if ($request->filled('category')) {
            $query->where('category', $request->string('category'));
        }

        // Search: q
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));

            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhere('location', 'like', "%{$q}%")
                    ->orWhere('category', 'like', "%{$q}%")
                    ->orWhere('brand', 'like', "%{$q}%")
                    ->orWhere('color', 'like', "%{$q}%");
            });
        }

        // Pagination
        $perPage = (int) $request->query('per_page', 12);
        $perPage = max(1, min($perPage, 50)); // limit 1-50

        return response()->json(
            $query->paginate($perPage)->withQueryString()
        );
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
        $this->authorize('update', $item);

        $user = $request->user();
        $data = $request->validated();

        if ($user->role === 'user') {
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
        $this->authorize('delete', $item);

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