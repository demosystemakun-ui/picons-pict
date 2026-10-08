<?php

namespace App\Http\Controllers;

use App\Models\Signer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SignerController extends Controller
{
    public function index()
    {
        $signers = Signer::orderBy('slot')->orderBy('name')->get();
        return view('signers.index', compact('signers'));
    }

    public function store(Request $request)
    {
        Signer::create($this->validated($request));
        return redirect()->route('signers.index')->with('success', 'Penandatangan ditambahkan.');
    }

    public function edit(Signer $signer)
    {
        return view('signers.edit', compact('signer'));
    }

    public function update(Request $request, Signer $signer)
    {
        $signer->update($this->validated($request));
        return redirect()->route('signers.index')->with('success', 'Penandatangan diperbarui.');
    }

    public function destroy(Signer $signer)
    {
        $signer->delete();
        return redirect()->route('signers.index')->with('success', 'Penandatangan dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name'  => 'required|string|max:150',
            'title' => 'required|string|max:150',
            'slot'  => ['required', Rule::in(array_keys(Signer::SLOTS))],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}