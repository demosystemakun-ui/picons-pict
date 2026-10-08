<?php

namespace App\Http\Controllers;

use App\Models\Reimbursement;
use Barryvdh\DomPDF\Facade\Pdf;          // composer require barryvdh/laravel-dompdf
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use App\Models\Signer;

class ReimbursementController extends Controller
{
    public function index()
    {
        $reimbursements = Reimbursement::withCount('items')->latest('request_date')->paginate(15);
        return view('procurement.reimbursement.index', compact('reimbursements'));
    }

     public function create()
    {
        return view('procurement.reimbursement.create', array_merge(
            ['number' => Reimbursement::generateNumber()],
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
            $r = Reimbursement::create($data);
            $r->items()->createMany(array_values($items));
            return $r;
        });

        return redirect()->route('reimbursement.show', $reimbursement)->with('success', 'Form reimbursement dibuat.');
    }

    public function show(Reimbursement $reimbursement)
    {
        $reimbursement->load('items');
        return view('procurement.reimbursement.show', compact('reimbursement'));
    }

  public function edit(Reimbursement $reimbursement)
    {
        $reimbursement->load('items');
        return view('procurement.reimbursement.edit', array_merge(
            compact('reimbursement'),
            $this->formData()
        ));
    }

    public function update(Request $request, Reimbursement $reimbursement)
    {
        $data = $this->validated($request, $reimbursement->id);

        DB::transaction(function () use ($data, $reimbursement) {
            $items = $data['items'];
            unset($data['items']);
            $reimbursement->update($data);
            $reimbursement->items()->delete();
            $reimbursement->items()->createMany(array_values($items));
        });

        return redirect()->route('reimbursement.show', $reimbursement)->with('success', 'Form reimbursement diperbarui.');
    }

    public function destroy(Reimbursement $reimbursement)
    {
        $reimbursement->delete();
        return redirect()->route('reimbursement.index')->with('success', 'Form reimbursement dihapus.');
    }

    public function print(Reimbursement $reimbursement)
    {
        $reimbursement->load('items');
        return view('procurement.reimbursement.print', compact('reimbursement'));
    }

    public function pdf(Reimbursement $reimbursement)
    {
        $reimbursement->load('items');

        return Pdf::loadView('procurement.reimbursement.pdf', compact('reimbursement'))
            ->setPaper('a4', 'portrait')
            ->stream('Reimbursement-' . str_replace('/', '-', $reimbursement->number) . '.pdf');
    }

    private function validated(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'number'          => 'required|string|unique:reimbursements,number' . ($ignoreId ? ",$ignoreId" : ''),
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