<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signer; // Pastikan Anda sudah punya Model Signer
use Illuminate\Validation\Rule;

class SignerController extends Controller
{
    /**
     * Menampilkan daftar signer.
     */
    public function index()
    {
        $signers = Signer::orderBy('name')->paginate(10);
        return view('signers.index', compact('signers'));
    }

    /**
     * Menyimpan signer baru ke database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'title' => 'required|string|max:255',
        ]);

        Signer::create($validated);

        return redirect()->route('signers.index')->with('success', 'Signer berhasil ditambahkan.');
    }

    /**
     * Menampilkan form edit (jika menggunakan modal, ini mungkin tidak diperlukan).
     */
    public function edit(Signer $signer)
    {
        // Jika Anda menggunakan modal, kembalikan JSON atau langsung view
        return view('signers.edit', compact('signer')); 
    }

    /**
     * Memperbarui data signer.
     */
    public function update(Request $request, Signer $signer)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'title' => 'required|string|max:255',
        ]);

        $signer->update($validated);

        return redirect()->route('signers.index')->with('success', 'Signer berhasil diperbarui.');
    }

    /**
     * Menghapus signer.
     */
    public function destroy(Signer $signer)
    {
        $signer->delete();
        return redirect()->route('signers.index')->with('success', 'Signer berhasil dihapus.');
    }
}