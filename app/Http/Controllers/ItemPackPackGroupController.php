<?php

// app/Http/Controllers/ItemPackPackGroupController.php

namespace App\Http\Controllers;

use App\Models\ItemPack;
use Illuminate\Http\Request;

class ItemPackPackGroupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'item_pack_id' => 'required|exists:item_packs,id',
            'pack_group_id' => 'required|exists:pack_groups,id',
        ]);

        $pack = ItemPack::findOrFail($request->item_pack_id);
        $pack->packGroups()->syncWithoutDetaching([$request->pack_group_id]);

        return response()->json(['success' => true]);
    }

    public function destroy($itemPackId, $packGroupId)
    {
        $pack = ItemPack::findOrFail($itemPackId);
        $pack->packGroups()->detach($packGroupId);

        return response()->json(['success' => true]);
    }
}
