<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\ProcurementRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProcurementRequestController extends Controller
{
    /* ═══════════════════════════════════════════════════════════════
       INDEX — List of all PRs & Filter Tabs
    ═══════════════════════════════════════════════════════════════ */
    public function index(Request $request)
    {
        $user  = auth()->user();
        $query = ProcurementRequest::latest();

        // RBAC: Admin Departemen only sees their own department's PRs
        if ($user->hasRole('Admin Departemen')) {
            $query->where('department_id', $user->department_id);
        }
        // Super Admin & Admin can see all

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        $requests = $query->paginate(15)->appends($request->all());
        
        return view('procurement.index', compact('requests'));
    }

    /* ═══════════════════════════════════════════════════════════════
       CREATE — Form for new PR
    ═══════════════════════════════════════════════════════════════ */
    public function create()
    {
        $suggestedNo = ProcurementRequest::generateNumber();
        $departments = Department::orderBy('name')->get(); 

        return view('procurement.create', compact('suggestedNo', 'departments'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Save new PR
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no'                    => 'required|string|unique:procurement_requests,no',
            'date'                  => 'required|date',
            'department_id'         => 'required|exists:departments,id',
            'request_by'            => 'required|string|max:255',
            'prepared_by'           => 'required|string|max:255',
            'description'           => 'required|string|max:255',
            'procurement_method'    => 'required|in:po,cash_advance,reimbursement', // NEW
            'order_type'            => 'nullable|string|in:new_order,repeat_order,goods,services',
            'background'            => 'nullable|string',
            'purpose'               => 'nullable|string',
            'required_spec'         => 'nullable|string',
            'item_name'             => 'required|string|max:255',
            'item_requirement_note' => 'nullable|string',
            'quantity'              => 'required|numeric|min:0.01',
            'target_purchase'       => 'nullable|string',
            'pic_name'              => 'nullable|string|max:255',
            'ops_manager_name'      => 'nullable|string|max:255',
            'fem_manager_name'      => 'nullable|string|max:255',
            'coo_name'              => 'nullable|string|max:255',
        ]);

        $orderType = $request->input('order_type');
        $department = Department::findOrFail($validated['department_id']);

        $validated['user_id']         = auth()->id();
        $validated['division']        = $department->name;
        $validated['is_new_order']    = ($orderType === 'new_order');
        $validated['is_repeat_order'] = ($orderType === 'repeat_order');
        $validated['is_goods']        = ($orderType === 'goods');
        $validated['is_services']     = ($orderType === 'services');
        $validated['status']          = 'pending';
        $validated['source']          = 'manual';

        unset($validated['order_type']);

        $procurementRequest = ProcurementRequest::create($validated);

        return redirect()
            ->route('procurement.show', $procurementRequest)
            ->with('success', 'Procurement Request created successfully.');
    }

    /* ═══════════════════════════════════════════════════════════════
       SHOW — PR Detail
    ═══════════════════════════════════════════════════════════════ */
    public function show(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'You do not have access to PRs from other departments.');
        }

        $procurementRequest->load('items', 'vendors', 'winnerVendor');
        return view('procurement.show', compact('procurementRequest'));
    }

    /* ═══════════════════════════════════════════════════════════════
       EDIT — PR Edit Form
    ═══════════════════════════════════════════════════════════════ */
    public function edit(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'You do not have access to edit PRs from other departments.');
        }

        $procurementRequest->load('items');
        $departments = Department::orderBy('name')->get(); 

        return view('procurement.edit', compact('procurementRequest', 'departments'));
    }

    /* ═══════════════════════════════════════════════════════════════
       UPDATE — Save PR Changes
    ═══════════════════════════════════════════════════════════════ */
    public function update(Request $request, ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'You do not have access to update PRs from other departments.');
        }

        $validated = $request->validate([
            'date'                  => 'required|date',
            'department_id'         => 'required|exists:departments,id',
            'request_by'            => 'required|string|max:255',
            'prepared_by'           => 'required|string|max:255',
            'description'           => 'required|string|max:255',
            'procurement_method'    => 'required|in:po,cash_advance,reimbursement', // NEW
            'order_type'            => 'nullable|string|in:new_order,repeat_order,goods,services',
            'background'            => 'nullable|string',
            'purpose'               => 'nullable|string',
            'required_spec'         => 'nullable|string',
            'target_purchase'       => 'nullable|string',
            'pic_name'              => 'nullable|string|max:255',
            'ops_manager_name'      => 'nullable|string|max:255',
            'fem_manager_name'      => 'nullable|string|max:255',
            'coo_name'              => 'nullable|string|max:255',

            'items'                       => 'required|array|min:1',
            'items.*.item_name'           => 'required|string|max:255',
            'items.*.item_requirement_note' => 'nullable|string',
            'items.*.quantity'            => 'required|numeric|min:0.01',
            'items.*.unit'                => 'nullable|string|max:50',
            'items.*.picture'             => 'nullable|image|max:2048',
            'items.*.existing_picture'    => 'nullable|string',
        ]);

        $orderType = $request->input('order_type');

        $department = Department::findOrFail($validated['department_id']);
        $validated['division'] = $department->name; 

        $validated['is_new_order']    = ($orderType === 'new_order');
        $validated['is_repeat_order'] = ($orderType === 'repeat_order');
        $validated['is_goods']        = ($orderType === 'goods');
        $validated['is_services']     = ($orderType === 'services');

        $items = $validated['items'];
        unset($validated['order_type'], $validated['items']);

        $validated['item_name'] = count($items) > 1
            ? '(Multiple Items - See Attachment Details)'
            : $items[0]['item_name'];

        $validated['item_requirement_note'] = count($items) > 1
            ? '(Multiple Items - See Attachment Details)'
            : ($items[0]['item_requirement_note'] ?? null);

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

                $pic     = $request->file("items.{$index}.picture");
                $picName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', $pic->getClientOriginalName());
                $picturePath = $pic->storeAs('attachments/procurement-items', $picName, 'supabase-procurement_request');
            }

            $procurementRequest->items()->create([
                'item_name'             => $itemData['item_name'],
                'item_requirement_note' => $itemData['item_requirement_note'] ?? null,
                'quantity'              => $itemData['quantity'],
                'unit'                  => $itemData['unit'] ?? null,
                'picture'               => $picturePath,
            ]);
        }

        return redirect()
            ->route('procurement.show', $procurementRequest)
            ->with('success', 'Procurement Request updated successfully.');
    }

    /* ═══════════════════════════════════════════════════════════════
       DESTROY — Delete PR
    ═══════════════════════════════════════════════════════════════ */
    public function destroy(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'You do not have access to delete PRs from other departments.');
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

    /* ═══════════════════════════════════════════════════════════════
       PRINT & PDF
    ═══════════════════════════════════════════════════════════════ */
    public function print(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $procurement = $this->mapToPrintData($procurementRequest);
        return view('procurement.print', compact('procurement'));
    }

    public function pdf(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $procurement = $this->mapToPrintData($procurementRequest);

        $pdf = Pdf::loadView('procurement.pdf', compact('procurement'))
            ->setPaper('a4', 'portrait');

        $cleanNo  = preg_replace('/[^A-Za-z0-9_\-]/', '-', $procurementRequest->no);
        $filename = 'PR-' . $cleanNo . '.pdf';
        $folder   = 'procurement_documents/' . date('Y/m');
        $filePath = $folder . '/' . $filename;

        Storage::disk('supabase-procurement_request')->put($filePath, $pdf->output());

        $projectId = 'thlvvgolwryzhdxjgyvz';
        $bucket    = env('SUPABASE_PROCUREMENT_REQUEST_BUCKET', 'procurement_request');
        
        $pdfUrl = "https://{$projectId}.supabase.co/storage/v1/object/public/{$bucket}/{$filePath}";

        return redirect()->away($pdfUrl);
    }

    public function pdfFinal(ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'Unauthorized action.');
        }

        $procurement = $this->mapToPrintData($procurementRequest);
        $procurementRequest->load('vendors.items', 'winnerVendor');

        $procurement['vendors'] = $procurementRequest->vendors()
            ->orderBy('total_price')
            ->get()
            ->map(fn ($v) => [
                'vendor_name'         => $v->vendor_name,
                'marketplace'         => $v->marketplace,
                'company_name'        => $v->company_name,
                'payment_method'      => $v->payment_method,
                'payment_bank'        => $v->payment_bank,
                'estimated_delivery'  => $v->estimated_delivery,
                'subtotal'            => $v->subtotal,
                'shipping_cost'       => $v->shipping_cost,
                'discount_voucher'    => $v->discount_voucher,
                'tax_ppn'             => $v->tax_ppn,
                'total_before_tax'    => $v->total_before_tax,
                'total_price'         => $v->total_price,
                'tax_note'            => $v->tax_note,
                'is_winner'           => $v->is_winner,
                'items'               => $v->items->map(fn ($it) => [
                    'product_name' => $it->product_name,
                    'variant'      => $it->variant,
                    'sku'          => $it->sku,
                    'quantity'     => $it->quantity,
                    'unit_price'   => $it->unit_price,
                    'subtotal'     => $it->subtotal,
                    'stock_status' => $it->stock_status,
                ])->toArray(),
            ])
            ->toArray();

        $winner = $procurementRequest->winnerVendor;

        $procurement['winner'] = $winner ? [
            'vendor_name'         => $winner->vendor_name,
            'marketplace'         => $winner->marketplace,
            'company_name'        => $winner->company_name,
            'payment_method'      => $winner->payment_method,
            'payment_bank'        => $winner->payment_bank,
            'estimated_delivery'  => $winner->estimated_delivery,
            'subtotal'            => $winner->subtotal,
            'shipping_cost'       => $winner->shipping_cost,
            'discount_voucher'    => $winner->discount_voucher,
            'tax_ppn'             => $winner->tax_ppn,
            'total_before_tax'    => $winner->total_before_tax,
            'total_price'         => $winner->total_price,
            'tax_note'            => $winner->tax_note,
        ] : null;

        $procurement['budget_type']          = $procurementRequest->budget_type;
        $procurement['reason_choose_vendor'] = $procurementRequest->reason_choose_vendor;
        $procurement['accounting_mgr_name']  = $procurementRequest->accounting_mgr_name;
        $procurement['ceo_name']             = $procurementRequest->ceo_name;
        $procurement['cfo_name']             = $procurementRequest->cfo_name;

        $pdf = Pdf::loadView('procurement.pdf-final', compact('procurement'))
            ->setPaper('a4', 'portrait');

        $cleanNo  = preg_replace('/[^A-Za-z0-9_\-]/', '-', $procurementRequest->no);
        $filename = 'PR-FINAL-' . $cleanNo . '.pdf';
        $folder   = 'procurement_documents/' . date('Y/m');
        $filePath = $folder . '/' . $filename;

        Storage::disk('supabase-procurement_request')->put($filePath, $pdf->output());

        $projectId = 'thlvvgolwryzhdxjgyvz';
        $bucket    = env('SUPABASE_PROCUREMENT_REQUEST_BUCKET', 'procurement_request');
        
        $pdfUrl = "https://{$projectId}.supabase.co/storage/v1/object/public/{$bucket}/{$filePath}";

        return redirect()->away($pdfUrl);
    }

    /* ═══════════════════════════════════════════════════════════════
       MANUAL SIGNATURE VALIDATION
    ═══════════════════════════════════════════════════════════════ */
    public function validateSignature(Request $request, ProcurementRequest $procurementRequest)
    {
        if (!$procurementRequest->winnerVendor) {
            return back()->with('error', 'No winning vendor. Please select a vendor first.');
        }

        if ($procurementRequest->status !== 'approved') {
            return back()->with('error', 'Invalid PR status for validation. Current status: ' . $procurementRequest->status);
        }

        $procurementRequest->update([
            'status'       => 'processed',
            'review_notes' => 'Final PR has been manually signed by Manager. Validated by Admin: ' . auth()->user()->name,
        ]);

        return redirect()
            ->route('procurement.comparisons.index', $procurementRequest)
            ->with('success', 'Signature validation successful. You can now proceed to PO process.');
    }

    /* ═══════════════════════════════════════════════════════════════
       UPDATE STATUS — Approve / Reject / Revision
    ═══════════════════════════════════════════════════════════════ */
    public function updateStatus(Request $request, ProcurementRequest $procurementRequest)
    {
        $user = auth()->user();
        
        if ($user->hasRole('Admin Departemen') && $procurementRequest->department_id !== $user->department_id) {
            abort(403, 'You cannot process PRs from other departments.');
        }

        $validated = $request->validate([
            'status'       => 'required|in:approved,rejected,revision,pending,processed,received,completed',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $allowedTransitions = [
            'pending' => ['approved', 'rejected', 'revision'],
            'revision' => ['pending'],
            'approved' => ['processed'],
            'processed' => ['received'],
            'received' => ['completed'],
        ];
        $targetStatus = $validated['status'];

        if (!in_array($targetStatus, $allowedTransitions[$procurementRequest->status] ?? [], true)) {
            return back()->with('error', "Status cannot be changed from {$procurementRequest->status} to {$targetStatus}.");
        }

        $procurementRequest->update([
            'status'       => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        $messages = [
            'approved'  => 'Request has been approved.',
            'rejected'  => 'Request has been rejected.',
            'revision'  => 'Request has been returned for revision.',
            'pending'   => 'Request status has been reset to pending.',
            'processed' => 'Request has been processed and purchased.',
            'received'  => 'Goods or services have been received.',
            'completed' => 'Procurement request has been completed.',
        ];

        return back()->with('success', $messages[$validated['status']] ?? 'Status updated successfully.');
    }

    /* ═══════════════════════════════════════════════════════════════
       MAP TO PRINT DATA
    ═══════════════════════════════════════════════════════════════ */
    private function mapToPrintData(ProcurementRequest $model): array
    {
        $model->loadMissing('items');

        $specBlocks = [];
        $itemCount  = $model->items->count();

        foreach ($model->items as $item) {
            $lines = [];

            if ($itemCount > 1 && !empty($item->item_name)) {
                $lines[] = $item->item_name;
            }

            if (!empty($item->brand))    $lines[] = 'Brand : ' . $item->brand;
            if (!empty($item->type))     $lines[] = 'Type : ' . $item->type;
            if (!empty($item->model))    $lines[] = 'Model : ' . $item->model;
            if (!empty($item->capacity)) $lines[] = 'Capacity : ' . $item->capacity;

            if (!empty($item->specs)) {
                $specsLines = array_filter(array_map('trim', explode("\n", $item->specs)));
                $lines = array_merge($lines, $specsLines);
            }

            if (count($lines)) {
                $specBlocks[] = implode("\n", $lines);
            }
        }

        $autoSpec = !empty($specBlocks) ? implode("\n\n", $specBlocks) : '';

        $requiredSpec = !empty($model->required_spec)
            ? $model->required_spec
            : $autoSpec;

        return [
            'no'          => ltrim($model->no ?? '', '/'),
            'date'        => optional($model->date)->format('d F Y'),
            'request_by'  => $model->request_by,
            'division'    => $model->division,
            'prepared_by' => $model->prepared_by,
            'description' => $model->description,

            'order_type' => [
                'new_order'    => (bool) $model->is_new_order,
                'repeat_order' => (bool) $model->is_repeat_order,
                'goods'        => (bool) $model->is_goods,
                'services'     => (bool) $model->is_services,
            ],

            'background'    => $model->background,
            'purpose'       => $model->purpose,
            'required_spec' => $requiredSpec,

            'purchase_items' => $model->items->isNotEmpty()
                ? $model->items->map(fn ($item) => [
                    'item'        => $item->item_name,
                    'requirement' => $item->item_requirement_note,
                    'quantity'    => $item->quantity,
                ])->toArray()
                : [[
                    'item'        => $model->item_name,
                    'requirement' => $model->item_requirement_note,
                    'quantity'    => $model->quantity,
                ]],

            'target_purchase' => $model->target_purchase,

            'signatures' => [
                ['role' => 'PIC',         'name' => $model->pic_name],
                ['role' => 'Ops Manager', 'name' => $model->ops_manager_name],
                ['role' => 'FEM Manager', 'name' => $model->fem_manager_name],
                ['role' => 'COO',         'name' => $model->coo_name],
            ],
        ];
    }
}