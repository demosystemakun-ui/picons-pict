<?php

namespace App\Http\Controllers;

use App\Models\Liquidation;
use App\Models\Department;
use App\Models\Signer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiquidationController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $query = Liquidation::withCount('items')->latest('request_date');

        if ($user->hasRole('Admin Departemen')) {
            $query->where('department', $user->department->name ?? '-');
        } elseif (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            $query->where('requested_by', $user->name);
        }

        $liquidations = $query->paginate(15);
        return view('procurement.liquidation.index', compact('liquidations'));
    }

    public function create(Request $request)
    {
        $procurementId = $request->query('procurement_request_id');
        $procurement   = $procurementId ? \App\Models\ProcurementRequest::find($procurementId) : null;

        return view('procurement.liquidation.create', [
            'number'        => Liquidation::generateNumber(),
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
            $lq = Liquidation::create($data);
            $lq->items()->createMany($items);
        });

        return redirect()->route('liquidations.index')
            ->with('success', 'Liquidation form created. Waiting for Admin approval.');
    }

    public function show(Liquidation $liquidation)
    {
        $liquidation->load('items');
        return view('procurement.liquidation.show', compact('liquidation'));
    }

    public function approve(Request $request, Liquidation $liquidation)
    {
        $request->validate([
            'number' => 'required|string|unique:liquidations,number,' . $liquidation->id,
        ], [
            'number.required' => 'The official number must be filled by Admin.',
            'number.unique'   => 'This number is already used.',
        ]);

        $liquidation->update([
            'number'      => $request->number,
            'status'      => 'approved',
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Liquidation approved with number: ' . $request->number);
    }

    public function pdf(Liquidation $liquidation)
    {
        $liquidation->load('items');
        return Pdf::loadView('procurement.liquidation.pdf', compact('liquidation'))
            ->setPaper('a4', 'portrait')
            ->stream('Liquidation-' . str_replace('/', '-', $liquidation->number) . '.pdf');
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
            'notes'                  => 'nullable|string',
            'signers'                => 'nullable|array',
            'items'                  => 'required|array|min:1',
            'items.*.description'    => 'required|string|max:255',
            'items.*.qty'            => 'required|integer|min:1',
            'items.*.unit'           => 'required|string|max:30',
            'items.*.price'          => 'required|numeric|min:0',
        ]);
    }
}