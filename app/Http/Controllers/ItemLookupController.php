<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemLookupController extends Controller
{
    public function byBarcode(string $barcode): JsonResponse
    {
        $item = Item::with('unit')
            ->where('barcode', $barcode)
            ->orWhere('item_code', $barcode)
            ->first();

        if (!$item) {
            return response()->json(['found' => false], 404);
        }

        return response()->json([
            'found' => true,
            'item' => [
                'id' => $item->id,
                'code' => $item->item_code,
                'name' => $item->item_name,
                'unit' => $item->unit->code ?? '-',
                'current_stock' => $item->current_stock,
            ],
        ]);
    }
}