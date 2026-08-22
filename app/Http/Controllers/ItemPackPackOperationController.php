<?php
// app/Http/Controllers/ItemPackPackOperationController.php

namespace App\Http\Controllers;

use App\Models\ItemPack;
use App\Models\PackOperation;
use Illuminate\Http\Request;

class ItemPackPackOperationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_pack_id' => 'required|exists:item_packs,id',
            'pack_operation_id' => 'required|exists:pack_operations,id',
        ]);

        $pack = ItemPack::findOrFail($request->item_pack_id);
        $pack->packOperations()->syncWithoutDetaching([$request->pack_operation_id]);

        return response()->json(['success' => true]);
    }

    public function destroy($itemPackId, $packOperationId)
    {
        $pack = ItemPack::findOrFail($itemPackId);
        $pack->packOperations()->detach($packOperationId);
        return response()->json(['success' => true]);
    }
}