<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ProcurementRequest;
use App\Models\ConsumableRequest;
use App\Models\ProcurementComparison;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WhatsAppService;

class ProcurementRequestController extends Controller
{
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = ProcurementRequest::latest();

        if ($user->hasAnyRole(['Super Admin', 'Admin'])) {
            // Lihat semua
        } elseif ($user->hasRole('Admin Departemen')) {
            $query->where('department_id', $user->department_id);
        } else {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('stage')) {
            if ($request->stage === 'tahap1') {
                // Tahap 1: Belum punya data final, status di tabel PR 1
                $query->whereDoesntHave('finalComparison')
                      ->whereIn('status', ['pending', 'approved', 'revision', 'processed']);
            } elseif ($request->stage === 'final') {
                // Tahap 2: Sudah punya data final, cek status di tabel comparison
                $query->whereHas('finalComparison', function($q) {
                    $q->whereIn('status', ['pending_review_final', 'pending_final_sign', 'final_approved', 'received', 'completed']);
                });
            }
        }

        $requests = $query->paginate(15)->appends($request->all());
        return view('procurement.index', compact('requests'));
    }

    public function create()
    {
        $suggestedNo = ProcurementRequest::generateNumber();
        $departments = Department::orderBy('name')->get();
        $prefill = session('prefill_pr_data');
        return view('procurement.create', compact('suggestedNo', 'departments', 'prefill'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->hasAnyRole(['Super Admin', 'Admin']);

        $rules = [
            'date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'request_by' => 'required|string|max:255',
            'prepared_by' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'procurement_method' => 'required|in:po,cash_advance,reimbursement',
            'order_type' => 'nullable|string|in:new_order,repeat_order,goods,services',
            'background' => 'nullable|string',
            'purpose' => 'nullable|string',
            'required_spec' => 'nullable|string',
            'vendor_suggestion' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'ops_manager_name' => 'nullable|string|max:255',
            'fem_manager_name' => 'nullable|string|max:255',
            'coo_name' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.*.item_name' => 'required_with:items|string|max:255',
            'items.*.item_requirement_note' => 'nullable|string',
            'items.*.quantity' => 'required_with:items|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'consumable_request_ids' => 'nullable|array',
            'consumable_request_ids.*' => 'exists:consumable_requests,id'
        ];

        if ($isAdmin) {
            $rules['no'] = [
                'required', 'string',
                function ($attribute, $value, $fail) {
                    $existsInNo = \App\Models\ProcurementRequest::where('no', $value)->exists();
                    $existsInFinal = \App\Models\ProcurementComparison::where('final_number', $value)->exists();
                    if ($existsInNo || $existsInFinal) {
                        $fail('Nomor PR ini sudah digunakan.');
                    }
                },
            ];
        }

        $validated = $request->validate($rules);

        DB::beginTransaction();
        try {
            $orderType  = $request->input('order_type');
            $department = Department::findOrFail($validated['department_id']);

            $validated['user_id'] = auth()->id();
            $validated['division'] = $department->name;
            $validated['is_new_order'] = ($orderType === 'new_order');
            $validated['is_repeat_order'] = ($orderType === 'repeat_order');
            $validated['is_goods'] = ($orderType === 'goods');
            $validated['is_services'] = ($orderType === 'services');
            $validated['status'] = 'pending';
            $validated['source'] = $request->has('consumable_request_ids') ? 'consumable' : 'manual';

            if (!$isAdmin || empty($validated['no'])) {
                $validated['no'] = 'DRAFT-' . date('Ymd') . '-' . str_pad(ProcurementRequest::whereDate('created_at', today())->count() + 1, 3, '0', STR_PAD_LEFT);
            }

            $itemsData = $validated['items'] ?? [];
            if (empty($itemsData) && $request->filled('item_name')) {
                $itemsData[] = [
                    'item_name' => $request->input('item_name'),
                    'item_requirement_note' => $request->input('item_requirement_note'),
                    'quantity' => $request->input('quantity', 1),
                    'unit' => $request->input('unit', 'Pcs'),
                ];
            }

            $validated['item_name'] = count($itemsData) > 1 ? '(Multiple Items - View Details)' : ($itemsData[0]['item_name'] ?? '-');
            $validated['item_requirement_note'] = count($itemsData) > 1 ? '(Multiple Items - View Details)' : ($itemsData[0]['item_requirement_note'] ?? null);
            $validated['quantity'] = collect($itemsData)->sum('quantity');

            unset($validated['order_type'], $validated['items'], $validated['consumable_request_ids']);

            $procurementRequest = ProcurementRequest::create($validated);

            foreach ($itemsData as $item) {
                $procurementRequest->items()->create([
                    'item_name' => $item['item_name'],
                    'item_requirement_note' => $item['item_requirement_note'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'Pcs',
                ]);
            }

            if ($request->has('consumable_request_ids')) {
                ConsumableRequest::whereIn('id', $request->consumable_request_ids)
                    ->update(['status' => 'converted', 'pr_generated_at' => now()]);
            }

            $this->notifyAdminsNewPr($procurementRequest);

            DB::commit();
            return redirect()->route('procurement.show', $procurementRequest)->with('success', 'Procurement Request created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Store PR Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Failed to create PR: ' . $e->getMessage());
        }
    }

    public function updateNumber(Request $request, ProcurementRequest $procurementRequest)
    {
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Admin'])) {
            abort(403, 'Only Admin can edit PR number.');
        }

        $fullNumber = $request->input('no_prefix') . '/PR/' . $request->input('no_suffix');

        $request->validate([
            'no_prefix' => [
                'required', 'string',
                function ($attribute, $value, $fail) use ($fullNumber, $procurementRequest) {
                    $existsInNo = \App\Models\ProcurementRequest::where('no', $fullNumber)->where('id', '!=', $procurementRequest->id)->exists();
                    $existsInFinal = \App\Models\ProcurementComparison::where('final_number', $fullNumber)->exists();
                    if ($existsInNo || $existsInFinal) {
                        $fail('Nomor PR ini sudah digunakan.');
                    }
                },
            ],
        ], ['no_prefix.required' => 'Nomor urut PR wajib diisi.']);

        $procurementRequest->update(['no' => $fullNumber]);
        return back()->with('success', 'Nomor PR berhasil diperbarui menjadi ' . $fullNumber);
    }

    public function show(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'You do not have access to PRs from other departments.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'You do not have access to this PR.');
            }
        }
        $procurementRequest->load('items', 'vendors', 'winnerVendor', 'finalComparison');
        return view('procurement.show', compact('procurementRequest'));
    }

    public function edit(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'You do not have access to edit PRs from other departments.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'You do not have access to edit this PR.');
            }
        }
        $procurementRequest->load('items');
        $departments = Department::orderBy('name')->get();
        return view('procurement.edit', compact('procurementRequest', 'departments'));
    }

    public function update(Request $request, ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'You do not have access to update PRs from other departments.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'You do not have access to update this PR.');
            }
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'request_by' => 'required|string|max:255',
            'prepared_by' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'procurement_method' => 'required|in:po,cash_advance,reimbursement',
            'order_type' => 'nullable|string|in:new_order,repeat_order,goods,services',
            'background' => 'nullable|string',
            'purpose' => 'nullable|string',
            'required_spec' => 'nullable|string',
            'vendor_suggestion' => 'nullable|string',
            'pic_name' => 'nullable|string|max:255',
            'ops_manager_name' => 'nullable|string|max:255',
            'fem_manager_name' => 'nullable|string|max:255',
            'coo_name' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.item_requirement_note' => 'nullable|string',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.picture' => 'nullable|image|max:2048',
            'items.*.existing_picture' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $orderType = $request->input('order_type');
            $department = Department::findOrFail($validated['department_id']);

            $validated['division'] = $department->name;
            $validated['is_new_order'] = ($orderType === 'new_order');
            $validated['is_repeat_order'] = ($orderType === 'repeat_order');
            $validated['is_goods'] = ($orderType === 'goods');
            $validated['is_services'] = ($orderType === 'services');

            $items = $validated['items'];
            unset($validated['order_type'], $validated['items']);

            $validated['item_name'] = count($items) > 1 ? '(Multiple Items - See Attachment Details)' : $items[0]['item_name'];
            $validated['item_requirement_note'] = count($items) > 1 ? '(Multiple Items - See Attachment Details)' : ($items[0]['item_requirement_note'] ?? null);
            $validated['quantity'] = collect($items)->sum('quantity');

            $procurementRequest->update($validated);

            foreach ($procurementRequest->items as $oldItem) {
                if ($oldItem->picture && Storage::disk('supabase-procurement_request')->exists($oldItem->picture)) {
                    Storage::disk('supabase-procurement_request')->delete($oldItem->picture);
                }
            }

            $procurementRequest->items()->delete();

            foreach ($request->items as $index => $itemData) {
                $picturePath = $itemData['existing_picture'] ?? null;
                if ($request->hasFile("items.{$index}.picture")) {
                    if ($picturePath && Storage::disk('supabase-procurement_request')->exists($picturePath)) {
                        Storage::disk('supabase-procurement_request')->delete($picturePath);
                    }
                    $pic = $request->file("items.{$index}.picture");
                    $picName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', $pic->getClientOriginalName());
                    $picturePath = $pic->storeAs('attachments/procurement-items', $picName, 'supabase-procurement_request');
                }

                $procurementRequest->items()->create([
                    'item_name' => $itemData['item_name'],
                    'item_requirement_note' => $itemData['item_requirement_note'] ?? null,
                    'quantity' => $itemData['quantity'],
                    'unit' => $itemData['unit'] ?? null,
                    'picture' => $picturePath,
                ]);
            }

            DB::commit();
            return redirect()->route('procurement.show', $procurementRequest)->with('success', 'Procurement Request updated successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update PR: ' . $e->getMessage());
        }
    }

    public function destroy(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'You do not have access to delete PRs from other departments.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'You do not have access to delete this PR.');
            }
        }
        foreach ($procurementRequest->items as $item) {
            if ($item->picture && Storage::disk('supabase-procurement_request')->exists($item->picture)) {
                Storage::disk('supabase-procurement_request')->delete($item->picture);
            }
        }
        $procurementRequest->items()->delete();
        $procurementRequest->delete();
        return redirect()->route('procurement.index')->with('success', 'Data deleted successfully.');
    }

    public function print(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'Unauthorized action.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'Unauthorized action.');
            }
        }
        $procurement = $this->mapToPrintData($procurementRequest);
        $isDraft = $procurementRequest->status === 'pending';
        return view('procurement.print', compact('procurement', 'isDraft'));
    }

    public function printFinal(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'Unauthorized action.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'Unauthorized action.');
            }
        }

        $procurement = $this->mapToPrintData($procurementRequest);
        
        $procurementRequest->load('vendors.items', 'winnerVendor', 'finalComparison');

        $procurement['vendors'] = $procurementRequest->vendors()->get()->toArray();
        $winner = $procurementRequest->winnerVendor;
        $procurement['winner'] = $winner ? $winner->toArray() : null;

        // ═══ WATERMARK & STAMP TTD (Cek Status PR 2) ═══
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            $isDraft = true;
        } else {
            $statusPR2 = $procurementRequest->finalComparison?->status;
            $isDraft = !in_array($statusPR2, ['final_approved', 'received', 'completed']);
        }

        $procurement['signatures'] = [
            ['role' => 'Ops. MGR', 'name' => $procurementRequest->ops_manager_name, 'approved' => (bool) $procurementRequest->is_ops_approved],
            ['role' => 'FEM. MGR', 'name' => $procurementRequest->fem_manager_name, 'approved' => (bool) $procurementRequest->is_fem_approved],
            ['role' => 'COO',      'name' => $procurementRequest->coo_name,         'approved' => (bool) $procurementRequest->is_coo_approved],
        ];

        $procurement['bottom_signatures'] = [
            ['role' => 'Accounting MGR', 'name' => $procurementRequest->finalComparison?->accounting_mgr_name, 'approved' => (bool) $procurementRequest->finalComparison?->is_accounting_approved],
            ['role' => 'CFO',            'name' => $procurementRequest->finalComparison?->cfo_name,            'approved' => (bool) $procurementRequest->finalComparison?->is_cfo_approved],
            ['role' => 'CEO',            'name' => $procurementRequest->finalComparison?->ceo_name,            'approved' => (bool) $procurementRequest->finalComparison?->is_ceo_approved],
        ];

        return view('procurement.print-final', compact('procurement', 'isDraft'));
    }

    public function pdf(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'Unauthorized action.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'Unauthorized action.');
            }
        }
        $procurement = $this->mapToPrintData($procurementRequest);
        $isDraft = $procurementRequest->status === 'pending';

        $pdf = Pdf::loadView('procurement.pdf', compact('procurement', 'isDraft'))->setPaper('a4', 'portrait');
        $cleanNo = preg_replace('/[^A-Za-z0-9_\-]/', '-', $procurementRequest->no);
        $filename = 'PR-' . $cleanNo . '.pdf';
        $folder = 'procurement_documents/' . date('Y/m');
        $filePath = $folder . '/' . $filename;

        Storage::disk('supabase-procurement_request')->put($filePath, $pdf->output());
        $projectId = 'thlvvgolwryzhdxjgyvz';
        $bucket = env('SUPABASE_PROCUREMENT_REQUEST_BUCKET', 'procurement_request');
        $pdfUrl = "https://{$projectId}.supabase.co/storage/v1/object/public/{$bucket}/{$filePath}";

        return redirect()->away($pdfUrl);
    }

    public function pdfFinal(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'Unauthorized action.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'Unauthorized action.');
            }
        }

        $procurement = $this->mapToPrintData($procurementRequest);
        
        $procurementRequest->load('vendors.items', 'winnerVendor', 'finalComparison');

        $procurement['vendors'] = $procurementRequest->vendors()->orderBy('total_price')->get()->map(fn ($v) => [
            'vendor_name' => $v->vendor_name,
            'marketplace' => $v->marketplace,
            'company_name' => $v->company_name,
            'payment_method' => $v->payment_method,
            'payment_bank' => $v->payment_bank,
            'estimated_delivery' => $v->estimated_delivery,
            'subtotal' => $v->subtotal,
            'shipping_cost' => $v->shipping_cost,
            'discount_voucher' => $v->discount_voucher,
            'tax_ppn' => $v->tax_ppn,
            'total_before_tax' => $v->total_before_tax,
            'total_price' => $v->total_price,
            'tax_note' => $v->tax_note,
            'is_winner' => $v->is_winner,
            'items' => $v->items->map(fn ($it) => [
                'product_name' => $it->product_name,
                'variant' => $it->variant,
                'sku' => $it->sku,
                'quantity' => $it->quantity,
                'unit_price' => $it->unit_price,
                'subtotal' => $it->subtotal,
                'stock_status' => $it->stock_status,
            ])->toArray(),
        ])->toArray();

        $winner = $procurementRequest->winnerVendor;
        $procurement['winner'] = $winner ? [
            'vendor_name' => $winner->vendor_name,
            'marketplace' => $winner->marketplace,
            'company_name' => $winner->company_name,
            'payment_method' => $winner->payment_method,
            'payment_bank' => $winner->payment_bank,
            'estimated_delivery' => $winner->estimated_delivery,
            'subtotal' => $winner->subtotal,
            'shipping_cost' => $winner->shipping_cost,
            'discount_voucher' => $winner->discount_voucher,
            'tax_ppn' => $winner->tax_ppn,
            'total_before_tax' => $winner->total_before_tax,
            'total_price' => $winner->total_price,
            'tax_note' => $winner->tax_note,
        ] : null;

        $procurement['budget_type'] = $procurementRequest->finalComparison?->budget_type;
        $procurement['reason_choose_vendor'] = $procurementRequest->finalComparison?->reason_choose_vendor;
        $procurement['accounting_mgr_name'] = $procurementRequest->finalComparison?->accounting_mgr_name;
        $procurement['ceo_name'] = $procurementRequest->finalComparison?->ceo_name;
        $procurement['cfo_name'] = $procurementRequest->finalComparison?->cfo_name;
        $procurement['final_number'] = $procurementRequest->finalComparison?->final_number;

        // ═══ WATERMARK & STAMP TTD (Cek Status PR 2) ═══
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            $isDraft = true;
        } else {
            $statusPR2 = $procurementRequest->finalComparison?->status;
            $isDraft = !in_array($statusPR2, ['final_approved', 'received', 'completed']);
        }

        $procurement['signatures'] = [
            ['role' => 'Ops. MGR', 'name' => $procurementRequest->ops_manager_name, 'approved' => (bool) $procurementRequest->is_ops_approved],
            ['role' => 'FEM. MGR', 'name' => $procurementRequest->fem_manager_name, 'approved' => (bool) $procurementRequest->is_fem_approved],
            ['role' => 'COO',      'name' => $procurementRequest->coo_name,         'approved' => (bool) $procurementRequest->is_coo_approved],
        ];

        $procurement['bottom_signatures'] = [
            ['role' => 'Accounting MGR', 'name' => $procurementRequest->finalComparison?->accounting_mgr_name, 'approved' => (bool) $procurementRequest->finalComparison?->is_accounting_approved],
            ['role' => 'CFO',            'name' => $procurementRequest->finalComparison?->cfo_name,            'approved' => (bool) $procurementRequest->finalComparison?->is_cfo_approved],
            ['role' => 'CEO',            'name' => $procurementRequest->finalComparison?->ceo_name,            'approved' => (bool) $procurementRequest->finalComparison?->is_ceo_approved],
        ];

        $pdf = Pdf::loadView('procurement.pdf-final', compact('procurement', 'isDraft'))->setPaper('a4', 'portrait');

        $cleanNo = preg_replace('/[^A-Za-z0-9_\-]/', '-', $procurementRequest->no);
        $filename = 'PR-FINAL-' . $cleanNo . '.pdf';
        $folder = 'procurement_documents/' . date('Y/m');
        $filePath = $folder . '/' . $filename;

        Storage::disk('supabase-procurement_request')->put($filePath, $pdf->output());
        $projectId = 'thlvvgolwryzhdxjgyvz';
        $bucket = env('SUPABASE_PROCUREMENT_REQUEST_BUCKET', 'procurement_request');
        $pdfUrl = "https://{$projectId}.supabase.co/storage/v1/object/public/{$bucket}/{$filePath}";

        return redirect()->away($pdfUrl);
    }

    public function validateSignature(Request $request, ProcurementRequest $procurementRequest)
    {
        if (!$procurementRequest->winnerVendor) return back()->with('error', 'No winning vendor. Please select a vendor first.');
        if ($procurementRequest->status !== 'approved') return back()->with('error', 'Invalid PR status for validation. Current status: ' . $procurementRequest->status);
        $procurementRequest->update(['status' => 'processed']);

        $this->sendWhatsAppNotification($procurementRequest, 'processed');

        return redirect()->route('procurement.comparisons.index', $procurementRequest)->with('success', 'PR Awal validation successful. You can now create PR Final.');
    }

    public function validateFinalSignature(Request $request, ProcurementRequest $procurementRequest)
    {
        $request->validate([
            'final_number' => [
                'required', 'string',
                function ($attribute, $value, $fail) use ($procurementRequest) {
                    $existsInFinal = \App\Models\ProcurementComparison::where('final_number', $value)
                        ->where('procurement_request_id', '!=', $procurementRequest->id)
                        ->exists();
                    $existsInNo = \App\Models\ProcurementRequest::where('no', $value)->exists();
                    if ($existsInFinal || $existsInNo) $fail('Nomor Final PR ini sudah digunakan.');
                },
            ],
        ]);

        $comparison = $procurementRequest->finalComparison;
        
        // Cek status dari tabel comparison
        if (!$comparison || $comparison->status !== 'pending_final_sign') {
            return back()->with('error', 'PR Final can only be validated after Format Review is approved.');
        }
        
        if (!$procurementRequest->winnerVendor) {
            return back()->with('error', 'No winning vendor. Please select a vendor first.');
        }

        // 1. Update Status PR 2 (Final)
        $comparison->update([
            'status' => 'final_approved',
            'final_number' => $request->input('final_number'),
        ]);

        // 2. Opsional: Simpan catatan di PR 1
        $procurementRequest->update([
            'review_notes' => 'PR Final signed by Manager. Final Number: ' . $request->input('final_number'),
        ]);

        $this->sendWhatsAppNotification($procurementRequest, 'final_approved');

        return redirect()->route('procurement.pdf.final', $procurementRequest)->with('success', 'PR Final validated with number: ' . $request->input('final_number'));
    }

    public function updateSignatureStatus(Request $request, ProcurementRequest $procurementRequest)
    {
        $request->validate(['signature_field' => 'required|string|in:pic_name,ops_manager_name,fem_manager_name,coo_name']);
        $field = $request->input('signature_field');
        $columnMap = ['pic_name' => 'is_pic_approved', 'ops_manager_name' => 'is_ops_approved', 'fem_manager_name' => 'is_fem_approved', 'coo_name' => 'is_coo_approved'];
        if (!array_key_exists($field, $columnMap)) return response()->json(['error' => 'Invalid field'], 400);
        $statusColumn = $columnMap[$field];
        $procurementRequest->update([$statusColumn => !$procurementRequest->$statusColumn]);
        return response()->json(['success' => true, 'status' => $procurementRequest->$statusColumn]);
    }

    public function approveAllSignatures(Request $request, ProcurementRequest $procurementRequest)
    {
        $procurementRequest->update([
            'is_pic_approved' => true, 'is_ops_approved' => true, 'is_fem_approved' => true, 'is_coo_approved' => true,
        ]);
        return back()->with('success', 'Semua TTD berhasil divalidasi. Silakan lanjut ke Vendor Comparison untuk memilih pemenang.');
    }

    public function updateStatus(Request $request, ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        if (!$user->hasAnyRole(['Super Admin', 'Admin'])) {
            if ($user->hasRole('Admin Departemen')) {
                if ($procurementRequest->department_id !== $user->department_id) abort(403, 'You cannot process PRs from other departments.');
            } else {
                if ($procurementRequest->user_id !== $user->id) abort(403, 'You cannot process this PR.');
            }
        }

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,revision,pending,processed,final_approved,received,completed',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $comparison = $procurementRequest->finalComparison;

        // ═══ LOGIKA BARU: JIKA PR SUDAH MASUK TAHAP FINAL (PR 2) ═══
        if ($comparison) {
            $allowedTransitions = [
                'pending_review_final' => ['pending_final_sign'],
                'pending_final_sign'   => ['final_approved'],
                'final_approved'       => ['received'],
                'received'             => ['completed'],
            ];

            $currentStatus = $comparison->status;

            if (!in_array($validated['status'], $allowedTransitions[$currentStatus] ?? [], true)) {
                return back()->with('error', "Status PR Final tidak bisa diubah dari {$currentStatus} ke {$validated['status']}.");
            }

            $comparison->update(['status' => $validated['status']]);

        } 
        // ═══ JIKA PR MASIH DI TAHAP AWAL (PR 1) ═══
        else {
            $allowedTransitions = [
                'pending' => ['approved', 'rejected', 'revision'],
                'revision' => ['pending'],
                'approved' => ['processed'],
            ];

            $currentStatus = $procurementRequest->status;

            if (!in_array($validated['status'], $allowedTransitions[$currentStatus] ?? [], true)) {
                return back()->with('error', "Status PR Awal tidak bisa diubah dari {$currentStatus} ke {$validated['status']}.");
            }

            $procurementRequest->update([
                'status' => $validated['status'], 
                'review_notes' => $validated['review_notes'] ?? null
            ]);
        }

        $this->sendWhatsAppNotification($procurementRequest, $validated['status']);

        $messages = [
            'approved' => 'Request has been approved.',
            'rejected' => 'Request has been rejected.',
            'revision' => 'Request has been returned for revision.',
            'pending' => 'Request status has been reset to pending.',
            'processed' => 'PR Awal has been processed.',
            'final_approved' => 'PR Final has been validated.',
            'received' => 'Goods or services have been received.',
            'completed' => 'Procurement request has been completed.',
        ];

        return back()->with('success', $messages[$validated['status']] ?? 'Status updated successfully.');
    }

    private function sendWhatsAppNotification(ProcurementRequest $pr, string $newStatus): void
    {
        try {
            $user = $pr->user;
            if (!$user || empty($user->phone_number)) {
                return;
            }

            $wa   = new WhatsAppService();
            $prNo = $pr->finalComparison?->final_number ?? $pr->no;

            $messages = [
                'approved'       => "Halo {$user->name}, PR Anda ({$prNo}) telah *disetujui* oleh Admin. Silakan ambil dokumen fisik di Admin.",
                'rejected'       => "Halo {$user->name}, PR Anda ({$prNo}) telah *ditolak*. Catatan: {$pr->review_notes}",
                'revision'       => "Halo {$user->name}, PR Anda ({$prNo}) *perlu revisi*. Catatan: {$pr->review_notes}",
                'processed'      => "Halo {$user->name}, PR Anda ({$prNo}) telah *diproses*. Lanjut ke tahap berikutnya.",
                'final_approved' => "Halo {$user->name}, PR Final Anda ({$prNo}) telah *divalidasi*. Silakan ambil dokumen fisik di Admin.",
                'received'       => "Halo {$user->name}, barang untuk PR ({$prNo}) telah *diterima* oleh Admin.",
                'completed'      => "Halo {$user->name}, PR Anda ({$prNo}) telah *selesai*.",
            ];

            $message = $messages[$newStatus] ?? null;
            if ($message) {
                $wa->send($user->phone_number, $message);
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notification failed for PR #' . $pr->id . ': ' . $e->getMessage());
        }
    }

    private function notifyAdminsNewPr(ProcurementRequest $pr): void
    {
        try {
            $wa = new WhatsAppService();
            $prNo = $pr->no;
            $pemohon = auth()->user()->name ?? 'Staff';
            
            $admins = \App\Models\User::role(['Super Admin', 'Admin'])->get();

            foreach ($admins as $admin) {
                if (!empty($admin->phone_number)) {
                    $pesan = "Halo *{$admin->name}*,\n\nAda pengajuan *PR Baru* yang perlu direview:\n\n";
                    $pesan .= "No: `{$prNo}`\n";
                    $pesan .= "Pemohon: {$pemohon}\n";
                    $pesan .= "Divisi: " . ($pr->division ?? '-') . "\n";
                    $pesan .= "Deskripsi: " . ($pr->description ?? '-') . "\n\n";
                    $pesan .= "Silakan cek sistem untuk melakukan persetujuan.";

                    $wa->send($admin->phone_number, $pesan);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp notify admins (New PR) failed for PR #' . $pr->id . ': ' . $e->getMessage());
        }
    }

    private function mapToPrintData(ProcurementRequest $model): array
    {
        $model->loadMissing('items', 'finalComparison');
        $specBlocks = [];
        $itemCount = $model->items->count();

        foreach ($model->items as $item) {
            $lines = [];
            if ($itemCount > 1 && !empty($item->item_name)) $lines[] = $item->item_name;
            if (!empty($item->brand)) $lines[] = 'Brand : ' . $item->brand;
            if (!empty($item->type)) $lines[] = 'Type : ' . $item->type;
            if (!empty($item->model)) $lines[] = 'Model : ' . $item->model;
            if (!empty($item->capacity)) $lines[] = 'Capacity : ' . $item->capacity;
            if (!empty($item->specs)) {
                $specsLines = array_filter(array_map('trim', explode("\n", $item->specs)));
                $lines = array_merge($lines, $specsLines);
            }
            if (count($lines)) $specBlocks[] = implode("\n", $lines);
        }

        $autoSpec = !empty($specBlocks) ? implode("\n\n", $specBlocks) : '';
        $requiredSpec = !empty($model->required_spec) ? $model->required_spec : $autoSpec;

        return [
            'no' => ltrim($model->finalComparison?->final_number ?? $model->no ?? '', '/'),
            'date' => optional($model->date)->format('d F Y'),
            'request_by' => $model->request_by,
            'division' => $model->division,
            'prepared_by' => $model->prepared_by,
            'description' => $model->description,
            'order_type' => [
                'new_order' => (bool) $model->is_new_order,
                'repeat_order' => (bool) $model->is_repeat_order,
                'goods' => (bool) $model->is_goods,
                'services' => (bool) $model->is_services,
            ],
            'background' => $model->background,
            'purpose' => $model->purpose,
            'required_spec' => $requiredSpec,
            'purchase_items' => $model->items->isNotEmpty()
                ? $model->items->map(fn ($item) => [
                    'item' => $item->item_name,
                    'requirement' => $item->item_requirement_note,
                    'quantity' => $item->quantity,
                ])->toArray()
                : [[
                    'item' => $model->item_name,
                    'requirement' => $model->item_requirement_note,
                    'quantity' => $model->quantity,
                ]],
            'target_purchase' => $model->target_purchase,
            'signatures' => [
                ['role' => 'PIC', 'name' => $model->pic_name, 'approved' => (bool) $model->is_pic_approved],
                ['role' => 'Ops Manager', 'name' => $model->ops_manager_name, 'approved' => (bool) $model->is_ops_approved],
                ['role' => 'FEM Manager', 'name' => $model->fem_manager_name, 'approved' => (bool) $model->is_fem_approved],
                ['role' => 'COO', 'name' => $model->coo_name, 'approved' => (bool) $model->is_coo_approved],
            ],
        ];
    }
}