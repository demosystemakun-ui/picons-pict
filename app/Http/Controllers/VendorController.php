<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:master.vendors.manage');
    }

    public function index(Request $request)
    {
        $vendors = Vendor::when($request->vendor_type, fn ($q) => $q->where('vendor_type', $request->vendor_type))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('master.vendors.index', compact('vendors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:vendors,code',
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|in:monotaro,shopee,tokopedia,local',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['rating'] = $validated['rating'] ?? 0;

        Vendor::create($validated);

        return back()->with('success', 'Vendor berhasil ditambahkan.');
    }

    public function update(Request $request, Vendor $vendor)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:vendors,code,'.$vendor->id,
            'name' => 'required|string|max:255',
            'vendor_type' => 'required|in:monotaro,shopee,tokopedia,local',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'rating' => 'nullable|numeric|min:0|max:5',
            'is_active' => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['rating'] = $validated['rating'] ?? 0;

        $vendor->update($validated);

        return back()->with('success', 'Vendor berhasil diperbarui.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return back()->with('success', 'Vendor berhasil dihapus.');
    }
}
