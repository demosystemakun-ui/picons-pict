<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\JsonResponse;

class ItemLookupController extends Controller
{
    /**
     * Cari item berdasarkan barcode untuk autofill form request.
     */
    public function byBarcode(string $barcode): JsonResponse
    {
        $item = Item::with(['unit', 'category'])
            ->where('barcode', $barcode)
            ->where('status', 'active')
            ->first();

        if (!$item) {
            return response()->json([
                'found'   => false,
                'message' => 'Item dengan barcode tersebut tidak ditemukan atau tidak aktif.',
            ], 404);
        }

        return response()->json([
            'found' => true,
            'item'  => [
                'id'            => $item->id,
                'item_code'     => $item->item_code,
                'item_name'     => $item->item_name,
                'description'   => $item->description,
                'unit'          => $item->unit->name ?? null,
                'category'      => $item->category->name ?? null,
                'current_stock' => $item->current_stock,
                'minimum_stock' => $item->minimum_stock,
                'is_low_stock'  => $item->isLowStock(),
            ],
        ]);
    }
}