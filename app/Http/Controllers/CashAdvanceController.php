<?php

namespace App\Http\Controllers;

use App\Models\CashAdvance;
use App\Models\Department;
use App\Models\Signer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CashAdvanceController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $query = CashAdvance::withCount('items')->latest('request_date');

        if ($user->hasRole('Admin Departemen')) {
            $query->where('department', $user->department->name ?? '-');
        } elseif (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            $query->where('requested_by', $user->name);
        }

        $cashAdvances = $query->paginate(15);
        return view('procurement.cash-advance.index', compact('cashAdvances'));
    }

    public function create(Request $request)
    {
        $procurementId = $request->query('procurement_request_id');
        $procurement   = $procurementId ? \App\Models\ProcurementRequest::find($procurementId) : null;

        return view('procurement.cash-advance.create', [
            'number'        => CashAdvance::generateNumber(),
            'departments'   => Department::orderBy('name')->pluck('name'),
            'signerOptions' => Signer::where('is_active', true)->get()->groupBy('slot'),
            'procurement'   => $procurement,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['status'] = 'pending';

        $items = $data['items'];
        unset($data['items']);

        DB::transaction(function () use ($data, $items) {
            $ca = CashAdvance::create($data);
            $ca->items()->createMany($items);
        });

        return redirect()->route('cash-advances.index')
            ->with('success', 'Cash Advance form created. Waiting for Admin approval.');
    }

    /**
     * Display the specified cash advance.
     */
    public function show(CashAdvance $cashAdvance)
    {
        $cashAdvance->load('items');
        return view('procurement.cash-advance.show', compact('cashAdvance'));
    }

    /**
     * Approve the cash advance with official number.
     */
    public function approve(Request $request, CashAdvance $cashAdvance)
    {
        $request->validate([
            'number' => 'required|string|unique:cash_advances,number,' . $cashAdvance->id,
        ], [
            'number.required' => 'The official number must be filled by Admin.',
            'number.unique'   => 'This number is already used.',
        ]);

        $cashAdvance->update([
            'number'      => $request->number,
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Cash Advance approved with number: ' . $request->number);
    }

    /**
     * Generate PDF for the cash advance.
     */
    public function pdf(CashAdvance $cashAdvance)
    {
        $cashAdvance->load('items');
        return Pdf::loadView('procurement.cash-advance.pdf', compact('cashAdvance'))
            ->setPaper('a4', 'portrait')
            ->stream('CashAdvance-' . str_replace('/', '-', $cashAdvance->number) . '.pdf');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'number'                 => 'nullable|string',
            'procurement_request_id' => 'nullable|exists:procurement_requests,id',
            'request_date'           => 'required|date',
            'requested_by'           => 'required|string|max:150',
            'nik'                    => 'nullable|string|max:50',
            'position'               => 'nullable|string|max:100',
            'department'             => 'nullable|string|max:100',
            'cost_department'        => 'nullable|string|max:100',
            'delivery_address'       => 'nullable|string',
            'attachment'             => 'nullable|string',
            'notes'                  => 'nullable|string',
            'bank_name'              => 'nullable|string|max:100',
            'account_name'           => 'nullable|string|max:150',
            'account_number'         => 'nullable|string|max:50',
            'signers'                => 'nullable|array',
            'items'                  => 'required|array|min:1',
            'items.*.item_description' => 'required|string|max:255',
            'items.*.purpose'        => 'nullable|string|max:255',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit'           => 'required|string|max:30',
            'items.*.price'          => 'required|numeric|min:0',
        ]);
    }
}