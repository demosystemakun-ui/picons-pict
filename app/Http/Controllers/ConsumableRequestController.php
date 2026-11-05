<?php

namespace App\Http\Controllers;

use App\Models\ConsumableRequest;
use App\Models\ProcurementRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsumableRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Admin Pusat & Super Admin bisa lihat semua
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin Utama')) {
            $requests = ConsumableRequest::with(['items'])->latest()->paginate(15);
        } 
        // Admin Departemen hanya bisa lihat request dari departemennya sendiri
        elseif ($user->hasRole('Admin Departemen')) {
            $requests = ConsumableRequest::with(['items'])
                ->whereHas('user', function($q) use ($user) {
                    $q->where('department_id', $user->department_id);
                })
                ->latest()->paginate(15);
        } 
        else {
            abort(403, 'Unauthorized action.');
        }

        return view('consumable-requests.index', compact('requests'));
    }

    public function create()
    {
        return view('consumable-requests.create');
    }

    public function createForm()
    {
        return view('consumable-requests._form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date'            => 'required|date',
            'subject'         => 'required|string|max:255',
            'general_note'    => 'nullable|string|max:255',
            'request_by'      => 'nullable|string|max:255',
            'approved_by'     => 'nullable|string|max:255',
            'verified_by'     => 'nullable|string|max:255',

            'items'                 => 'required|array|min:1',
            'items.*.item_name'     => 'required|string|max:255',
            'items.*.brand'         => 'nullable|string|max:255',
            'items.*.type'          => 'nullable|string|max:255',
            'items.*.model'         => 'nullable|string|max:255',
            'items.*.capacity'      => 'nullable|string|max:255',
            'items.*.specs'         => 'nullable|string',
            'items.*.purpose_1'     => 'required|string',
            'items.*.purpose_2'     => 'required|string',
            'items.*.purpose_3'     => 'required|string',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit'          => 'required|string|max:50',
            'items.*.picture'       => 'nullable|image|max:2048',
        ]);

        $consumableRequest = ConsumableRequest::create([
            'user_id'      => auth()->id(),
            'date'         => $validated['date'],
            'subject'      => $validated['subject'],
            'general_note' => $validated['general_note'] ?? null,
            'request_by'   => $validated['request_by'] ?? null,
            'approved_by'  => $validated['approved_by'] ?? null,
            'verified_by'  => $validated['verified_by'] ?? null,
            'status'       => 'pending',
        ]);

        foreach ($request->items as $index => $itemData) {
            $picturePath = null;
            if ($request->hasFile("items.{$index}.picture")) {
                $pic = $request->file("items.{$index}.picture");
                $picName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', $pic->getClientOriginalName());
                $picturePath = $pic->storeAs('attachments/consumable-items', $picName, 'supabase-consumable_request');
            }

            $consumableRequest->items()->create([
                'item_name' => $itemData['item_name'],
                'brand'     => $itemData['brand'] ?? null,
                'type'      => $itemData['type'] ?? null,
                'model'     => $itemData['model'] ?? null,
                'capacity'  => $itemData['capacity'] ?? null,
                'specs'     => $itemData['specs'] ?? null,
                'purpose_1' => $itemData['purpose_1'],
                'purpose_2' => $itemData['purpose_2'],
                'purpose_3' => $itemData['purpose_3'],
                'quantity'  => $itemData['quantity'],
                'unit'      => $itemData['unit'],
                'picture'   => $picturePath,
            ]);
        }

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'  => true,
                'message'  => 'Consumable Request berhasil dibuat.',
                'redirect' => route('consumable-requests.show', $consumableRequest),
            ]);
        }

        return redirect()
            ->route('consumable-requests.show', $consumableRequest)
            ->with('success', 'Consumable Request berhasil dibuat.');
    }

    public function show(ConsumableRequest $consumableRequest)
    {
        $user = auth()->user();
        
        // Super Admin / Admin Utama bisa lihat semua
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin Utama')) {
            // Izinkan
        } 
        // Admin Departemen hanya bisa lihat request dari departemennya
        elseif ($user->hasRole('Admin Departemen')) {
            if ($consumableRequest->user->department_id !== $user->department_id) {
                abort(403, 'Unauthorized action.');
            }
        } 
        // PIC/User hanya bisa lihat miliknya sendiri
        elseif ($consumableRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $consumableRequest->load(['items']);
        return view('consumable-requests.show', compact('consumableRequest'));
    }

    public function edit(ConsumableRequest $consumableRequest)
    {
        $user = auth()->user();

        if (!$user->hasRole('Super Admin')) {
            if ($consumableRequest->user_id !== auth()->id() || $consumableRequest->status !== 'revision') {
                abort(403, 'Hanya pengajuan dengan status revisi yang dapat diedit.');
            }
        }
        
        $consumableRequest->load('items');
        return view('consumable-requests.edit', compact('consumableRequest'));
    }

    public function update(Request $request, ConsumableRequest $consumableRequest)
    {
        $user = auth()->user();

        if (!$user->hasRole('Super Admin')) {
            if ($consumableRequest->user_id !== auth()->id() || $consumableRequest->status !== 'revision') {
                abort(403, 'Hanya pengajuan dengan status revisi yang dapat diperbarui.');
            }
        }

        $validated = $request->validate([
            'date'            => 'required|date',
            'subject'         => 'required|string|max:255',
            'general_note'    => 'nullable|string|max:255',
            'request_by'      => 'nullable|string|max:255',
            'approved_by'     => 'nullable|string|max:255',
            'verified_by'     => 'nullable|string|max:255',

            'items'                 => 'required|array|min:1',
            'items.*.item_name'     => 'required|string|max:255',
            'items.*.brand'         => 'nullable|string|max:255',
            'items.*.type'          => 'nullable|string|max:255',
            'items.*.model'         => 'nullable|string|max:255',
            'items.*.capacity'      => 'nullable|string|max:255',
            'items.*.specs'         => 'nullable|string',
            'items.*.purpose_1'     => 'required|string',
            'items.*.purpose_2'     => 'required|string',
            'items.*.purpose_3'     => 'required|string',
            'items.*.quantity'      => 'required|numeric|min:0.01',
            'items.*.unit'          => 'required|string|max:50',
            'items.*.picture'       => 'nullable|image|max:2048',
        ]);

        $consumableRequest->update([
            'date'                  => $validated['date'],
            'subject'               => $validated['subject'],
            'general_note'          => $validated['general_note'] ?? null,
            'request_by'            => $validated['request_by'] ?? null,
            'approved_by'           => $validated['approved_by'] ?? null,
            'verified_by'           => $validated['verified_by'] ?? null,
            'status'                => 'pending',
            'revision_note'         => null,
            'revision_requested_at' => null,
        ]);

        foreach ($consumableRequest->items as $oldItem) {
            if ($oldItem->picture && Storage::disk('supabase-consumable_request')->exists($oldItem->picture)) {
                Storage::disk('supabase-consumable_request')->delete($oldItem->picture);
            }
        }
        $consumableRequest->items()->delete();

        foreach ($request->items as $index => $itemData) {
            $picturePath = null;
            if ($request->hasFile("items.{$index}.picture")) {
                $pic = $request->file("items.{$index}.picture");
                $picName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', $pic->getClientOriginalName());
                $picturePath = $pic->storeAs('attachments/consumable-items', $picName, 'supabase-consumable_request');
            }

            $consumableRequest->items()->create([
                'item_name' => $itemData['item_name'],
                'brand'     => $itemData['brand'] ?? null,
                'type'      => $itemData['type'] ?? null,
                'model'     => $itemData['model'] ?? null,
                'capacity'  => $itemData['capacity'] ?? null,
                'specs'     => $itemData['specs'] ?? null,
                'purpose_1' => $itemData['purpose_1'],
                'purpose_2' => $itemData['purpose_2'],
                'purpose_3' => $itemData['purpose_3'],
                'quantity'  => $itemData['quantity'],
                'unit'      => $itemData['unit'],
                'picture'   => $picturePath,
            ]);
        }

        return redirect()
            ->route('consumable-requests.show', $consumableRequest)
            ->with('success', 'Consumable Request berhasil diperbarui dan diajukan kembali.');
    }

    public function destroy(ConsumableRequest $consumableRequest)
    {
        if (!auth()->user()->hasRole('Super Admin')) {
            abort(403, 'Unauthorized action.');
        }

        foreach ($consumableRequest->items as $item) {
            if ($item->picture && Storage::disk('supabase-consumable_request')->exists($item->picture)) {
                Storage::disk('supabase-consumable_request')->delete($item->picture);
            }
        }

        $consumableRequest->items()->delete();
        $consumableRequest->delete();

        return redirect()->route('consumable-requests.index')->with('success', 'Data berhasil dihapus.');
    }

    public function print(ConsumableRequest $consumableRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin Utama')) {
            // Izinkan
        } elseif ($user->hasRole('Admin Departemen')) {
            if ($consumableRequest->user->department_id !== $user->department_id) {
                abort(403, 'Unauthorized action.');
            }
        } elseif ($consumableRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $consumable = $this->mapToPrintData($consumableRequest);
        return view('consumable-requests.print', compact('consumable'));
    }

    public function pdf(ConsumableRequest $consumableRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin Utama')) {
            // Izinkan
        } elseif ($user->hasRole('Admin Departemen')) {
            if ($consumableRequest->user->department_id !== $user->department_id) {
                abort(403, 'Unauthorized action.');
            }
        } elseif ($consumableRequest->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $consumable = $this->mapToPrintData($consumableRequest);
        $pdf = Pdf::loadView('consumable-requests.pdf', compact('consumable'))->setPaper('a4', 'portrait');

        $cleanSubject = preg_replace('/[^A-Za-z0-9_\-]/', '-', $consumableRequest->subject);
        $filename = 'CR-' . $cleanSubject . '-' . time() . '.pdf';
        $folder   = 'consumable_documents/' . date('Y/m');
        $filePath = $folder . '/' . $filename;

        Storage::disk('supabase-consumable_request')->put($filePath, $pdf->output());

        return back()->with('success', 'File PDF berhasil disimpan ke Supabase');
    }

    public function updateStatus(Request $request, ConsumableRequest $consumableRequest)
    {
        // Admin Utama & Admin Departemen bisa menyetujui
        $user = auth()->user();
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Admin Utama') && !$user->hasRole('Admin Departemen')) {
            abort(403, 'Unauthorized action.');
        }

        // Admin Departemen hanya bisa update status request dari departemennya
        if ($user->hasRole('Admin Departemen') && $consumableRequest->user->department_id !== $user->department_id) {
            abort(403, 'You are not authorized to process requests from other departments.');
        }

        $validated = $request->validate([
            'status'        => 'required|in:approved,rejected,revision,pending',
            'revision_note' => 'required_if:status,revision|nullable|string|max:2000',
        ], [
          'revision_note.required_if' => 'Revision note is required when selecting the Revision status.',
            'revision_note.max'         => 'Revision note maximum 2000 characters.',
        ]);

        $payload = ['status' => $validated['status']];

        if ($validated['status'] === 'revision') {
            $payload['revision_note']         = $validated['revision_note'];
            $payload['revision_requested_at'] = now();
        } else {
            $payload['revision_note']         = null;
            $payload['revision_requested_at'] = null;
        }

        $consumableRequest->update($payload);

        $messages = [
           'approved' => 'Request has been approved.',
           'rejected' => 'Request has been rejected.',
            'revision' => 'Request has been returned for revision. Note has been sent to the applicant.',
            'pending'  => 'Request status has been returned to pending.',
        ];

        return back()->with('success', $messages[$validated['status']] ?? 'Status successfully updated.');
    }

    public function myRequests()
    {
        $requests = ConsumableRequest::where('user_id', auth()->id())
                        ->with(['items'])
                        ->latest()
                        ->paginate(15);
        return view('consumable-requests.my-index', compact('requests'));
    }

    private function mapToPrintData(ConsumableRequest $model): array
    {
        return [
            'no'          => $model->id,
            'date'        => \Carbon\Carbon::parse($model->date)->format('d/m/Y'),
            'description' => $model->subject,
            'notes'       => $model->general_note,
            'status'      => $model->status,

            'purchase_items' => $model->items->map(function ($item) {
                $requirement = collect([
                    "1. Why you want to purchase it? :\n" . ($item->purpose_1 ?? '-'),
                    "2. Which process or activity will the item support? :\n" . ($item->purpose_2 ?? '-'),
                    "3. What operational or financial efficiency will be gained? :\n" . ($item->purpose_3 ?? '-'),
                ])->implode("\n\n");

                return [
                    'item_name' => $item->item_name,
                    'brand'     => $item->brand,
                    'type'      => $item->type,
                    'model'     => $item->model,
                    'capacity'  => $item->capacity,
                    'specs'     => $item->specs,
                    'item_details' => $item->item_details,
                    'requirement' => $requirement,
                    'quantity'    => $item->quantity,
                    'unit'        => $item->unit,
                    'picture'     => $item->picture,
                ];
            })->toArray(),

            'request_by'  => $model->request_by,
            'approved_by' => $model->approved_by,
            'verified_by' => $model->verified_by,
        ];
    }

    public function consolidationIndex()
    {
        $user = auth()->user();
        
        // Super Admin & Admin Utama bisa lihat semua approved requests
        if ($user->hasRole('Super Admin') || $user->hasRole('Admin Utama')) {
            $approvedRequests = ConsumableRequest::where('status', 'approved')
                                    ->whereNull('pr_generated_at')
                                    ->with('items')
                                    ->get();
        } 
        // Admin Departemen hanya bisa lihat approved requests dari departemennya
        elseif ($user->hasRole('Admin Departemen')) {
            $approvedRequests = ConsumableRequest::where('status', 'approved')
                                    ->whereNull('pr_generated_at')
                                    ->whereHas('user', function($q) use ($user) {
                                        $q->where('department_id', $user->department_id);
                                    })
                                    ->with('items')
                                    ->get();
        } 
        else {
            abort(403, 'Unauthorized action.');
        }

        return view('consumable-requests.consolidation', compact('approvedRequests'));
    }

    public function generatePR(Request $request)
    {
        $request->validate([
            'request_ids' => 'required|array',
            'request_ids.*' => 'exists:consumable_requests,id',
        ]);

        ConsumableRequest::whereIn('id', $request->request_ids)->update([
            'status' => 'pr_created',
            'pr_generated_at' => now(),
        ]);

        return redirect()->route('purchase-requisitions.index')
            ->with('success', 'Successfully consolidated Consumable Requests into Purchase Requisition (PR).');
    }

    public function processConsolidation(Request $request)
    {
        $user = auth()->user();
        
        if (!$user->hasRole('Super Admin') && !$user->hasRole('Admin Utama')) {
            abort(403, 'Only Super Admin and Main Admin can perform cross-departmental consolidation.');
        }

        $validated = $request->validate([
            'no'                 => 'required|string|max:100',
            'background'         => 'nullable|string',
            'purpose'            => 'nullable|string',
            'required_spec'      => 'nullable|string',
            'request_ids'        => 'required|array|min:1',
            'request_ids.*'      => 'exists:consumable_requests,id',
        ]);

        $consumableRequests = ConsumableRequest::with('items', 'user')
            ->whereIn('id', $validated['request_ids'])
            ->where('status', 'approved')
            ->get();

        if ($consumableRequests->isEmpty()) {
            return back()->with('error', 'No valid approved requests available for consolidation.');
        }

        $itemsPayload = [];
        $totalQuantity = 0;

        foreach ($consumableRequests as $cr) {
            foreach ($cr->items as $item) {
                $totalQuantity += $item->quantity;
                
                $requirement = collect([
                    $item->purpose_1,
                    $item->purpose_2,
                    $item->purpose_3,
                ])->filter()->implode(' | ');

                $itemsPayload[] = [
                    'item_name'                  => $item->item_name,
                    'brand'                      => $item->brand,
                    'type'                       => $item->type,
                    'model'                      => $item->model,
                    'capacity'                   => $item->capacity,
                    'specs'                      => $item->specs,
                    'item_requirement_note'      => $requirement,
                    'quantity'                   => $item->quantity,
                    'unit'                       => $item->unit,
                    'picture'                    => $item->picture,
                    'consumable_request_item_id' => $item->id, 
                    'department_id'              => $cr->user->department_id, 
                ];
            }
        }

        // Siapkan data untuk mengisi form Create PR
        $prefillData = [
            'no'            => $validated['no'],
            'background'    => $validated['background'] ?: 'Consolidation automatically from ' . $consumableRequests->count() . ' Approved Consumable Requests.',
            'purpose'       => $validated['purpose'] ?: 'Meeting operational consumable needs.',
            'required_spec' => $validated['required_spec'] ?? null,
            'items'         => $itemsPayload,
            'request_ids'   => $validated['request_ids']
        ];

        // Simpan sementara ke session
        session()->flash('prefill_pr_data', $prefillData);

        // Arahkan ke halaman Create Procurement Request
        return redirect()->route('procurement.create')
            ->with('info', 'Data dari Consumable Request berhasil dimuat. Silakan periksa dan lengkapi form PR di bawah ini sebelum menyimpan.');
    }
}