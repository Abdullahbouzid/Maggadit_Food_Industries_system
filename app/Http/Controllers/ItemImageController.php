<?php
// app/Http/Controllers/ItemImageController.php

namespace App\Http\Controllers;

use App\Models\ItemImage;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ItemImageController extends Controller
{
    public function index(Request $request)
    {
        $query = ItemImage::with('item');
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('item', function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")->orWhere('name_en', 'like', "%{$search}%");
            });
        }
        $perPage = $request->get('per_page', 20);
        $images = $query->orderBy('sort_order')->paginate($perPage);
        return view('dashboard.data_definition.data_product.item_images.index', compact('images'));
    }

    public function create()
    {
        $items = Item::orderBy('name_en')->get();
        return view('dashboard.data_definition.data_product.item_images.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'image' => 'required|image|max:2048',
            'alt_text' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        $path = $request->file('image')->store('item_images', 'public');
        ItemImage::create([
            'item_id' => $validated['item_id'],
            'image_path' => $path,
            'alt_text' => $validated['alt_text'],
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('item-images.index')->with('success', 'Image added successfully.');
    }

    public function show(ItemImage $itemImage)
    {
        return view('dashboard.data_definition.data_product.item_images.show', compact('itemImage'));
    }

    public function edit(ItemImage $itemImage)
    {
        $items = Item::orderBy('name_en')->get();
        return view('dashboard.data_definition.data_product.item_images.edit', compact('itemImage', 'items'));
    }

    public function update(Request $request, ItemImage $itemImage)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'image' => 'nullable|image|max:2048',
            'alt_text' => 'nullable|string',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($itemImage->image_path);
            $path = $request->file('image')->store('item_images', 'public');
            $itemImage->image_path = $path;
        }
        $itemImage->item_id = $validated['item_id'];
        $itemImage->alt_text = $validated['alt_text'];
        $itemImage->sort_order = $validated['sort_order'] ?? 0;
        $itemImage->save();

        return redirect()->route('item-images.index')->with('success', 'Image updated.');
    }

    public function destroy(ItemImage $itemImage)
    {
        Storage::disk('public')->delete($itemImage->image_path);
        $itemImage->delete();
        return redirect()->route('item-images.index')->with('success', 'Image deleted.');
    }
}