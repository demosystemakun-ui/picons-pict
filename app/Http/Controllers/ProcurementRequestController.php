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
       INDEX — Daftar semua PR
    ═══════════════════════════════════════════════════════════════ */
    public function index()
    {
        $requests = ProcurementRequest::latest()->paginate(15);
        return view('procurement.index', compact('requests'));
    }

    /* ═══════════════════════════════════════════════════════════════
       CREATE — Form buat PR baru
    ═══════════════════════════════════════════════════════════════ */
    public function create()
    {
        $suggestedNo = ProcurementRequest::generateNumber();
        return view('procurement.create', compact('suggestedNo'));
    }

    /* ═══════════════════════════════════════════════════════════════
       STORE — Simpan PR baru
    ═══════════════════════════════════════════════════════════════ */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'no'                    => 'required|string|unique:procurement_requests,no',
            'date'                  => 'required|date',
            'department_id'         => 'nullable|exists:departments,id',
            'request_by'            => 'required|string|max:255',
            'division'              => 'required|string|max:255',
            'prepared_by'           => 'required|string|max:255',
            'description'           => 'required|string|max:255',
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

        $validated['user_id']         = auth()->id();
        $validated['is_new_order']    = ($orderType === 'new_order');
        $validated['is_repeat_order'] = ($orderType === 'repeat_order');
        $validated['is_goods']        = ($orderType === 'goods');
        $validated['is_services']     = ($orderType === 'services');
        $validated['status']          = 'pending';

        unset($validated['order_type']);

        $procurementRequest = ProcurementRequest::create($validated);

        return redirect()
            ->route('procurement.show', $procurementRequest)
            ->with('success', 'Procurement Request berhasil dibuat.');
    }

    /* ═══════════════════════════════════════════════════════════════
       SHOW — Detail PR
    ═══════════════════════════════════════════════════════════════ */
    public function show(ProcurementRequest $procurementRequest)
    {
        $procurementRequest->load('items', 'comparisons');
        return view('procurement.show', compact('procurementRequest'));
    }

    /* ═══════════════════════════════════════════════════════════════
       EDIT — Form edit PR
    ═══════════════════════════════════════════════════════════════ */
    public function edit(ProcurementRequest $procurementRequest)
    {
        $procurementRequest->load('items');
        $departments = Department::orderBy('name')->get();

        return view('procurement.edit', compact('procurementRequest', 'departments'));
    }

    /* ═══════════════════════════════════════════════════════════════
       UPDATE — Simpan perubahan PR
    ═══════════════════════════════════════════════════════════════ */
    public function update(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'date'                  => 'required|date',
            'department_id'         => 'nullable|exists:departments,id',
            'request_by'            => 'required|string|max:255',
            'division'              => 'required|string|max:255',
            'prepared_by'           => 'required|string|max:255',
            'description'           => 'required|string|max:255',
            'order_type'            => 'nullable|string|in:new_order,repeat_order,goods,services',
            'background'            => 'nullable|string',
            'purpose'               => 'nullable|string',
            'required_spec'         => 'nullable|string',
            'target_purchase'       => 'nullable|string',
            'pic_name'              => 'nullable|string|max:255',
            'ops_manager_name'      => 'nullable|string|max:255',
            'fem_manager_name'      => 'nullable|string|max:255',
            'coo_name'              => 'nullable|string|max:255',

            'items'                         => 'required|array|min:1',
            'items.*.item_name'             => 'required|string|max:255',
            'items.*.item_requirement_note' => 'nullable|string',
            'items.*.quantity'              => 'required|numeric|min:0.01',
            'items.*.unit'                  => 'nullable|string|max:50',
            'items.*.picture'               => 'nullable|image|max:2048',
            'items.*.existing_picture'      => 'nullable|string',
        ]);

        $orderType = $request->input('order_type');

        $validated['is_new_order']    = ($orderType === 'new_order');
        $validated['is_repeat_order'] = ($orderType === 'repeat_order');
        $validated['is_goods']        = ($orderType === 'goods');
        $validated['is_services']     = ($orderType === 'services');

        $items = $validated['items'];
        unset($validated['order_type'], $validated['items']);

        /* ═══ Ringkasan legacy untuk kolom item_name / quantity ═══ */
        $validated['item_name'] = count($items) > 1
            ? '(Multiple Items - Lihat Detail Lampiran)'
            : $items[0]['item_name'];

        $validated['item_requirement_note'] = count($items) > 1
            ? '(Multiple Items - Lihat Detail Lampiran)'
            : ($items[0]['item_requirement_note'] ?? null);

        $validated['quantity'] = collect($items)->sum('quantity');

        $procurementRequest->update($validated);

        /* ═══ Replace items ═══ */
        $procurementRequest->items()->delete();

        foreach ($request->items as $index => $itemData) {
            $picturePath = $itemData['existing_picture'] ?? null;

            if ($request->hasFile("items.{$index}.picture")) {
                if ($picturePath && Storage::disk('public')->exists($picturePath)) {
                    Storage::disk('public')->delete($picturePath);
                }

                $pic     = $request->file("items.{$index}.picture");
                $picName = time() . '_' . $index . '_' . preg_replace('/\s+/', '_', $pic->getClientOriginalName());
                $picturePath = $pic->storeAs('attachments/procurement-items', $picName, 'public');
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
            ->with('success', 'Procurement Request berhasil diperbarui.');
    }

    /* ═══════════════════════════════════════════════════════════════
       DESTROY — Hapus PR
    ═══════════════════════════════════════════════════════════════ */
    public function destroy(ProcurementRequest $procurementRequest)
    {
        $procurementRequest->delete();
        return redirect()->route('procurement.index')->with('success', 'Data dihapus.');
    }

    /* ═══════════════════════════════════════════════════════════════
       PRINT — Preview print (HTML)
    ═══════════════════════════════════════════════════════════════ */
    public function print(ProcurementRequest $procurementRequest)
    {
        $procurement = $this->mapToPrintData($procurementRequest);
        return view('procurement.print', compact('procurement'));
    }

    /* ═══════════════════════════════════════════════════════════════
       PDF — Generate PDF (tanpa comparison)
    ═══════════════════════════════════════════════════════════════ */
    public function pdf(ProcurementRequest $procurementRequest)
    {
        $procurement = $this->mapToPrintData($procurementRequest);

        $pdf = Pdf::loadView('procurement.pdf', compact('procurement'))
            ->setPaper('a4', 'portrait');

        $cleanNo  = preg_replace('/[^A-Za-z0-9_\-]/', '-', $procurementRequest->no);
        $filename = 'PR-' . $cleanNo . '.pdf';
        $folder   = 'procurement_documents/' . date('Y/m');

        $publicDir = public_path($folder);
        if (! is_dir($publicDir)) {
            mkdir($publicDir, 0755, true);
        }

        $pdf->save($publicDir . '/' . $filename);

        return redirect()->away(asset($folder . '/' . $filename));
    }

    /* ═══════════════════════════════════════════════════════════════
       PDF FINAL — Generate PDF Final (dengan comparison vendor)
    ═══════════════════════════════════════════════════════════════ */
  public function pdfFinal(ProcurementRequest $procurementRequest)
{
    $procurement = $this->mapToPrintData($procurementRequest);
    $procurementRequest->load('vendors.items', 'winnerVendor');

    /* ═══ Semua vendor (urut dari termurah) ═══ */
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

    /* ═══ Vendor pemenang ═══ */
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

    /* ═══ Field tambahan untuk PDF ═══ */
    $procurement['budget_type']          = $procurementRequest->budget_type;
    $procurement['reason_choose_vendor'] = $procurementRequest->reason_choose_vendor;
    $procurement['accounting_mgr_name']  = $procurementRequest->accounting_mgr_name;
    $procurement['ceo_name']             = $procurementRequest->ceo_name;
    $procurement['cfo_name']             = $procurementRequest->cfo_name;

    /* ═══ Render PDF ═══ */
    $pdf = Pdf::loadView('procurement.pdf-final', compact('procurement'))
        ->setPaper('a4', 'portrait');

    $cleanNo  = preg_replace('/[^A-Za-z0-9_\-]/', '-', $procurementRequest->no);
    $filename = 'PR-FINAL-' . $cleanNo . '.pdf';
    $folder   = 'procurement_documents/' . date('Y/m');

    $publicDir = public_path($folder);
    if (! is_dir($publicDir)) {
        mkdir($publicDir, 0755, true);
    }

    $pdf->save($publicDir . '/' . $filename);

    return redirect()->away(asset($folder . '/' . $filename));
}

    /* ═══════════════════════════════════════════════════════════════
       UPDATE STATUS — Approve / Reject / Revision
    ═══════════════════════════════════════════════════════════════ */
    public function updateStatus(Request $request, ProcurementRequest $procurementRequest)
    {
        $validated = $request->validate([
            'status'       => 'required|in:approved,rejected,revision,pending',
            'review_notes' => 'nullable|string|max:1000',
        ]);

        $procurementRequest->update([
            'status'       => $validated['status'],
            'review_notes' => $validated['review_notes'] ?? null,
        ]);

        $messages = [
            'approved' => 'Pengajuan berhasil disetujui.',
            'rejected' => 'Pengajuan telah ditolak.',
            'revision' => 'Pengajuan berhasil dikembalikan untuk revisi.',
            'pending'  => 'Status pengajuan dikembalikan ke pending.',
        ];

        return back()->with('success', $messages[$validated['status']] ?? 'Status berhasil diperbarui.');
    }

    /* ═══════════════════════════════════════════════════════════════
       MAP TO PRINT DATA
       Mapping model → array data untuk view print & PDF.
       Required spec auto-generate dari detail item.
    ═══════════════════════════════════════════════════════════════ */
    private function mapToPrintData(ProcurementRequest $model): array
    {
        $model->loadMissing('items');

        /* ═══ Bangun REQUIRED SPEC dari detail item ═══ */
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