<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Signer;

class ReimbursementController extends Controller
{
    /**
     * Helper: Cek apakah user berhak mengakses reimbursement ini
     */
    private function authorizeAccess(Reimbursement $reimbursement)
    {
        $user = auth()->user();

        // Super Admin & Admin Utama: Bebas akses
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin Utama')) {
            return true;
        }

        // Admin Departemen: Hanya bisa akses departemennya sendiri
        if ($user->hasRole('Admin Departemen')) {
            if ($reimbursement->department !== $user->department->name) {
                abort(403, 'Anda tidak memiliki akses ke reimbursement departemen lain.');
            }
            return true;
        }

        // PIC/User: Hanya bisa akses miliknya sendiri
        if ($reimbursement->requested_by !== $user->name) {
            abort(403, 'Anda tidak memiliki akses ke reimbursement ini.');
        }

        return true;
    }

    public function index()
    {
        $user = auth()->user();
        $query = Reimbursement::withCount('items')->latest('request_date');

        // Filter Berdasarkan Role
        if ($user->hasRole('Admin Departemen')) {
            $query->where('department', $user->department->name);
        } elseif (!$user->hasRole('Super Admin') && !$user->hasRole('Admin Utama')) {
            $query->where('requested_by', $user->name);
        }

        $reimbursements = $query->paginate(15);
        return view('procurement.reimbursement.index', compact('reimbursements'));
    }

    public function create()
    {
        $user = auth()->user();
        
        return view('procurement.reimbursement.create', array_merge(
            [
                'number'        => Reimbursement::generateNumber(),
                'userDeptName'  => $user->department->name ?? '',
                'userName'      => $user->name,
            ],
            $this->formData()
        ));
    }

    private function formData(): array
    {
        return [
            'departments'   => Department::orderBy('name')->pluck('name'),
            'signerOptions' => Signer::where('is_active', true)->orderBy('name')->get()->groupBy('slot'),
        ];
    }
    
    public function store(Request $request)
    {
        $data = $this->validated($request);

        $reimbursement = DB::transaction(function () use ($data) {
            $items = $data['items'];
            unset($data['items']);
            
            // Set status default ke pending saat dibuat
            $data['status'] = 'pending'; 
            
            $r = Reimbursement::create($data);
            $r->items()->createMany(array_values($items));
            return $r;
        });

        return redirect()->route('reimbursement.show', $reimbursement)
            ->with('success', 'Form reimbursement dibuat. Menunggu validasi Admin & Nomor Surat.');
    }

    public function show(Reimbursement $reimbursement)
    {
        $this->authorizeAccess($reimbursement);
        
        $reimbursement->load('items');
        return view('procurement.reimbursement.show', compact('reimbursement'));
    }

    public function edit(Reimbursement $reimbursement)
    {
        $this->authorizeAccess($reimbursement);

        // Hanya bisa edit jika status masih pending
        if ($reimbursement->status !== 'pending') {
            return back()->with('error', 'Form yang sudah di-approve tidak dapat diedit.');
        }

        $reimbursement->load('items');
        return view('procurement.reimbursement.edit', array_merge(
            compact('reimbursement'),
            $this->formData()
        ));
    }

    public function update(Request $request, Reimbursement $reimbursement)
    {
        $this->authorizeAccess($reimbursement);

        if ($reimbursement->status !== 'pending') {
            return back()->with('error', 'Form yang sudah di-approve tidak dapat diubah.');
        }

        $data = $this->validated($request, $reimbursement->id);

        DB::transaction(function () use ($data, $reimbursement) {
            $items = $data['items'];
            unset($data['items']);
            $reimbursement->update($data);
            $reimbursement->items()->delete();
            $reimbursement->items()->createMany(array_values($items));
        });

        return redirect()->route('reimbursement.show', $reimbursement)
            ->with('success', 'Form reimbursement diperbarui.');
    }

    public function destroy(Reimbursement $reimbursement)
    {
        $this->authorizeAccess($reimbursement);

        if ($reimbursement->status !== 'pending') {
            return back()->with('error', 'Form yang sudah di-approve tidak dapat dihapus.');
        }

        $reimbursement->delete();
        return redirect()->route('reimbursement.index')->with('success', 'Form reimbursement dihapus.');
    }

    /**
     * FITUR APPROVE: Admin Input Nomor Surat & Validasi
     */
    public function approve(Request $request, Reimbursement $reimbursement)
    {
        $user = auth()->user();

        // Hanya Admin Utama, Super Admin, dan Admin Departemen yang boleh approve
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Admin Utama') && !$user->hasRole('Admin Departemen')) {
            abort(403, 'Anda tidak memiliki hak untuk menyetujui reimbursement.');
        }

        // Admin Departemen hanya bisa approve departemennya sendiri
        if ($user->hasRole('Admin Departemen') && $reimbursement->department !== $user->department->name) {
            abort(403, 'Anda hanya bisa menyetujui reimbursement departemen Anda sendiri.');
        }

        // Pastikan status masih pending
        if ($reimbursement->status !== 'pending') {
            return back()->with('error', 'Form ini sudah diproses sebelumnya.');
        }

        // Validasi wajib isi nomor surat
        $request->validate([
            'number' => 'required|string|unique:reimbursements,number,' . $reimbursement->id,
        ], [
            'number.required' => 'Nomor surat wajib diisi oleh Admin sebelum approve.',
            'number.unique'   => 'Nomor surat ini sudah digunakan.',
        ]);

        $reimbursement->update([
            'number'      => $request->number,
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Reimbursement berhasil di-approve dengan nomor surat: ' . $request->number);
    }

    public function print(Reimbursement $reimbursement)
    {
        $this->authorizeAccess($reimbursement);

        $reimbursement->load('items');
        return view('procurement.reimbursement.print', compact('reimbursement'));
    }

    public function pdf(Reimbursement $reimbursement)
    {
        $this->authorizeAccess($reimbursement);

        $reimbursement->load('items');

        return Pdf::loadView('procurement.reimbursement.pdf', compact('reimbursement'))
            ->setPaper('a4', 'portrait')
            ->stream('Reimbursement-' . str_replace('/', '-', $reimbursement->number) . '.pdf');
    }

    private function validated(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            // 'number' dibuat nullable karena diisi manual oleh Admin saat Approve
            'number'          => 'nullable|string|unique:reimbursements,number' . ($ignoreId ? ",$ignoreId" : ''),
            'request_date'    => 'required|date',
            'requested_by'    => 'required|string|max:150',
            'nik'             => 'nullable|string|max:50',
            'position'        => 'nullable|string|max:100',
            'department'      => 'nullable|string|max:100',
            'period_start'    => 'nullable|date',
            'period_end'      => 'nullable|date|after_or_equal:period_start',
            'notes'           => 'nullable|string',
            'bank_name'       => 'nullable|string|max:100',
            'account_name'    => 'nullable|string|max:150',
            'account_number'  => 'nullable|string|max:50',
            'signers'         => 'nullable|array',
            'signers.*.name'  => 'nullable|string|max:150',
            'signers.*.title' => 'nullable|string|max:150',
            'items'                => 'required|array|min:1',
            'items.*.date_from'    => 'nullable|date',
            'items.*.date_to'      => 'nullable|date|after_or_equal:items.*.date_from',
            'items.*.description'  => 'required|string|max:255',
            'items.*.receipt_no'   => 'nullable|string|max:100',
            'items.*.purpose'      => 'nullable|string|max:255',
            'items.*.qty'          => 'required|integer|min:1',
            'items.*.unit'         => 'required|string|max:30',
            'items.*.price'        => 'required|integer|min:0',
        ]);
    }
}