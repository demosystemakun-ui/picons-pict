@extends('layouts.app')

@section('title', 'Create Final PR')
@section('page-title', 'Create Final Purchase Request')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-12">

    {{-- ═══ HEADER ═══ --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">Create Final PR</h1>
            <p class="mt-1 text-sm text-gray-500">
                Initial PR: <span class="font-mono font-medium text-gray-700">{{ $procurement->no }}</span>
                — {{ $procurement->description }}
            </p>
        </div>
        <a href="{{ route('procurement.comparisons.index', $procurement) }}"
           class="inline-flex items-center gap-1.5 self-start rounded-lg border border-gray-300 bg-white px-3.5 py-2 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Comparison
        </a>
    </div>

    {{-- ═══ ALERTS ═══ --}}
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4">
            <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl bg-rose-50 border border-rose-200 px-5 py-4">
            <ul class="text-sm text-rose-700 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ═══ INFO PR & WINNER ═══ --}}
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <header class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-purple-50/50 flex items-center gap-3">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Procurement Request Info</h2>
                <p class="text-xs text-gray-500">Data from Initial PR &amp; winning vendor</p>
            </div>
        </header>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 text-sm">
            <dl class="space-y-2.5">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Initial PR Number</dt>
                    <dd class="font-semibold text-gray-900 font-mono">{{ $procurement->no ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Description</dt>
                    <dd class="text-gray-700 text-right">{{ $procurement->description ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Department</dt>
                    <dd class="text-gray-700">{{ $procurement->department->name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Request Date</dt>
                    <dd class="text-gray-700">{{ $procurement->created_at?->format('d/m/Y') ?? '-' }}</dd>
                </div>
            </dl>
            <dl class="space-y-2.5">
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Winning Vendor</dt>
                    <dd class="font-semibold text-emerald-600">{{ $procurement->winnerVendor->vendor_name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Marketplace</dt>
                    <dd class="text-gray-700">{{ $procurement->winnerVendor->marketplace ?? '-' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Total Price</dt>
                    <dd class="font-bold text-emerald-600">Rp {{ number_format($procurement->winnerVendor->total_price ?? 0, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-gray-500">Est. Delivery</dt>
                    <dd class="text-gray-700">{{ $procurement->winnerVendor->estimated_delivery ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </section>

    {{-- ═══ FORM FINAL PR ═══ --}}
    <form action="{{ route('procurement.comparisons.final-pr.store', $procurement) }}" method="POST" class="space-y-6">
        @csrf

        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-teal-50 to-emerald-50/50 flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 text-teal-600">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Final PR Details</h2>
                    <p class="text-xs text-gray-500">Complete the data to generate the Final PR PDF</p>
                </div>
            </header>

            <div class="p-6 space-y-4">
                
                {{-- BARIS 1: DATE & BUDGET TYPE --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="request_date"
                               value="{{ old('request_date', $procurement->request_date ?? date('Y-m-d')) }}"
                               class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('request_date') border-rose-400 @enderror"
                               required>
                        @error('request_date')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Budget Type <span class="text-rose-500">*</span>
                        </label>
                        <select name="budget_type"
                                class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('budget_type') border-rose-400 @enderror"
                                required>
                            <option value="">-- Select --</option>
                            <option value="Budgeted"     {{ old('budget_type', $procurement->budget_type) === 'Budgeted' ? 'selected' : '' }}>Budgeted</option>
                            <option value="Non Budgeted" {{ old('budget_type', $procurement->budget_type) === 'Non Budgeted' ? 'selected' : '' }}>Non Budgeted</option>
                        </select>
                        @error('budget_type')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- BARIS 2: REQUEST BY & DIVISION --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Request By <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="request_by"
                               value="{{ old('request_by', $procurement->request_by) }}"
                               placeholder="e.g. drjandak"
                               class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('request_by') border-rose-400 @enderror"
                               required>
                        @error('request_by')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Division <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="division"
                               value="{{ old('division', $procurement->division) }}"
                               placeholder="e.g. HRGA"
                               class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('division') border-rose-400 @enderror"
                               required>
                        @error('division')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- BARIS 3: PREPARED BY & FINAL DESCRIPTION --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Prepared By <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="prepared_by"
                               value="{{ old('prepared_by', $procurement->prepared_by) }}"
                               placeholder="e.g. adadadad"
                               class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('prepared_by') border-rose-400 @enderror"
                               required>
                        @error('prepared_by')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Final Description <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="final_description"
                               value="{{ old('final_description', $procurement->description) }}"
                               maxlength="255"
                               class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('final_description') border-rose-400 @enderror"
                               required>
                        @error('final_description')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- BARIS 4: FINAL PR NUMBER --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Final PR Number <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="final_number"
                           value="{{ old('final_number', $procurement->final_number ?? $suggestedFinalNumber) }}"
                           placeholder="e.g. 07/PR/OPS-PICT/XI-2026"
                           class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 font-mono focus:border-teal-500 focus:ring-teal-500 @error('final_number') border-rose-400 @enderror"
                           required>
                    <p class="mt-1.5 text-xs text-gray-500">The month and year format are generated automatically. You only need to adjust the sequence number at the front (e.g., 07).</p>
                    @error('final_number')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                        Reason for Choosing Vendor <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="reason_choose_vendor" rows="3" maxlength="2000"
                              placeholder="Example: This vendor offers the lowest price with specifications matching the requirements."
                              class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500 @error('reason_choose_vendor') border-rose-400 @enderror"
                              required>{{ old('reason_choose_vendor', $procurement->reason_choose_vendor) }}</textarea>
                    @error('reason_choose_vendor')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-3">Approver Names (Optional)</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">Accounting Manager</label>
                            <input type="text" name="accounting_mgr_name"
                                   value="{{ old('accounting_mgr_name', $procurement->accounting_mgr_name) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">CFO</label>
                            <input type="text" name="cfo_name"
                                   value="{{ old('cfo_name', $procurement->cfo_name) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1.5">CEO</label>
                            <input type="text" name="ceo_name"
                                   value="{{ old('ceo_name', $procurement->ceo_name) }}"
                                   class="w-full rounded-lg border-gray-300 text-sm px-3 py-2 focus:border-teal-500 focus:ring-teal-500">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ═══ WINNER ITEMS (READ-ONLY) ═══ --}}
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <header class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                <h2 class="text-sm font-semibold text-gray-900">Winning Items</h2>
                <p class="text-xs text-gray-500">{{ $procurement->winnerVendor->items->count() }} item(s) — auto-populated from winning vendor</p>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/80">
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 w-12">No</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500">Product</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-center">Qty</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Unit Price</th>
                            <th class="px-5 py-3 text-xs font-semibold uppercase text-gray-500 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($procurement->winnerVendor->items as $i => $item)
                            <tr>
                                <td class="px-5 py-3 text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-3">
                                    <div class="font-medium text-gray-900">{{ $item->product_name }}</div>
                                    @if($item->variant)
                                        <div class="text-xs text-gray-500">{{ $item->variant }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-center">{{ $item->quantity }}</td>
                                <td class="px-5 py-3 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500">No items</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-gray-50/80">
                        <tr class="border-t border-gray-200">
                            <th colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Subtotal</th>
                            <th class="px-5 py-2 text-right text-sm font-medium">Rp {{ number_format($procurement->winnerVendor->subtotal ?? 0, 0, ',', '.') }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Shipping</th>
                            <th class="px-5 py-2 text-right text-sm font-medium">Rp {{ number_format($procurement->winnerVendor->shipping_cost ?? 0, 0, ',', '.') }}</th>
                        </tr>
                        @if(($procurement->winnerVendor->discount_voucher ?? 0) > 0)
                        <tr>
                            <th colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Discount</th>
                            <th class="px-5 py-2 text-right text-sm font-medium text-rose-600">- Rp {{ number_format($procurement->winnerVendor->discount_voucher, 0, ',', '.') }}</th>
                        </tr>
                        @endif
                        <tr>
                            <th colspan="4" class="px-5 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Tax (VAT)</th>
                            <th class="px-5 py-2 text-right text-sm font-medium">Rp {{ number_format($procurement->winnerVendor->tax_ppn ?? 0, 0, ',', '.') }}</th>
                        </tr>
                        <tr class="border-t-2 border-gray-300">
                            <th colspan="4" class="px-5 py-3 text-right text-xs font-bold text-gray-900 uppercase">Total</th>
                            <th class="px-5 py-3 text-right text-base font-bold text-emerald-600">Rp {{ number_format($procurement->winnerVendor->total_price ?? 0, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </section>

        {{-- ═══ ACTION ═══ --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <a href="{{ route('procurement.comparisons.index', $procurement) }}"
               class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Cancel
            </a>
            <button type="submit"
                    onclick="return confirm('Save Final PR and generate PDF?')"
                    class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-teal-600 to-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-teal-700 hover:to-emerald-700 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                </svg>
                Save &amp; Generate PDF
            </button>
        </div>
    </form>
</div>
@endsection