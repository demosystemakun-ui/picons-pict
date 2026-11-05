@extends('layouts.app')

@section('title', 'Create Cash Advance')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pb-24">
    <form action="{{ route('cash-advances.store') }}" method="POST" id="caForm">
        @csrf
        @if($procurement)
            <input type="hidden" name="procurement_request_id" value="{{ $procurement->id }}">
        @endif
        <input type="hidden" name="number" value="{{ $number }}">

        {{-- HEADER --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Cash Advance</h1>
                <p class="text-sm text-gray-500 mt-1">Form No: <span class="font-mono">{{ $number }}</span></p>
            </div>
            <a href="{{ url()->previous() }}" class="text-sm text-gray-500 hover:text-gray-800">← Back</a>
        </div>

        @if($procurement)
            <div class="p-4 rounded-xl bg-purple-50 border border-purple-200 text-sm">
                <span class="font-semibold text-purple-800">Linked PR:</span>
                <span class="text-purple-700">{{ $procurement->no }} — {{ $procurement->description }}</span>
            </div>
        @endif

        {{-- APPLICANT DATA --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 pb-2 border-b">Applicant Data</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                {{-- Kolom Kiri --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Date</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="date" name="created_date" value="{{ date('Y-m-d') }}" readonly class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600 cursor-not-allowed">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Created By</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="text" name="created_by" value="{{ auth()->user()->name }}" readonly class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Department</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="text" name="department" value="{{ auth()->user()->department->name ?? '' }}" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Position</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="text" name="position" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Attachment</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="text" name="attachment" class="w-full rounded-md border-gray-300 text-sm" placeholder="e.g. PR">
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-4">
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Request Date</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            {{-- Diambil otomatis dari PR Final --}}
                            <input type="date" name="request_date" 
                                   value="{{ $procurement ? $procurement->date->format('Y-m-d') : date('Y-m-d') }}" 
                                   readonly
                                   class="w-full rounded-md border-gray-200 bg-gray-50 text-sm text-gray-600 cursor-not-allowed">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Request By</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="text" name="requested_by" value="{{ auth()->user()->name }}" required class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-center">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1">Cost Department</label>
                        <div class="col-span-2 flex items-center gap-2">
                            <span>:</span>
                            <input type="text" name="cost_department" class="w-full rounded-md border-gray-300 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-3 items-start">
                        <label class="text-xs font-bold text-gray-600 uppercase col-span-1 mt-2">Delivery Address</label>
                        <div class="col-span-2 flex items-start gap-2">
                            <span class="mt-2">:</span>
                            <textarea name="delivery_address" rows="3" class="w-full rounded-md border-gray-300 text-sm"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-purple-800 text-white p-3 flex items-center justify-between">
                <h2 class="text-sm font-bold uppercase tracking-wider">Claim Details</h2>
                <button type="button" onclick="addRow()" class="rounded bg-white text-purple-800 px-3 py-1 text-xs font-bold hover:bg-gray-100">+ Add Row</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-purple-700 text-white text-left">
                        <tr>
                            <th class="px-3 py-2 font-bold text-center w-12 border-r border-purple-600">NO</th>
                            <th class="px-3 py-2 font-bold border-r border-purple-600">ITEM DESCRIPTION</th>
                            <th class="px-3 py-2 font-bold border-r border-purple-600">PURPOSE</th>
                            <th class="px-3 py-2 font-bold text-center w-20 border-r border-purple-600">QTY</th>
                            <th class="px-3 py-2 font-bold text-center w-24 border-r border-purple-600">UNIT</th>
                            <th class="px-3 py-2 font-bold text-right w-32 border-r border-purple-600">PRICE</th>
                            <th class="px-3 py-2 font-bold text-right w-36">TOTAL PRICE</th>
                            <th class="px-3 py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-200">
                        <tr>
                            <td class="px-3 py-2 text-center font-bold row-num">1</td>
                            <td class="px-3 py-2 border-r"><input type="text" name="items[0][item_description]" required class="w-full border-0 focus:ring-0 p-0 text-sm"></td>
                            <td class="px-3 py-2 border-r"><input type="text" name="items[0][purpose]" class="w-full border-0 focus:ring-0 p-0 text-sm"></td>
                            <td class="px-3 py-2 border-r"><input type="number" name="items[0][qty]" value="1" min="1" required class="w-full border-0 focus:ring-0 p-0 text-sm text-center js-qty"></td>
                            <td class="px-3 py-2 border-r"><input type="text" name="items[0][unit]" value="Unit" required class="w-full border-0 focus:ring-0 p-0 text-sm text-center"></td>
                            <td class="px-3 py-2 border-r"><input type="number" name="items[0][price]" value="0" min="0" required class="w-full border-0 focus:ring-0 p-0 text-sm text-right js-price"></td>
                            <td class="px-3 py-2 text-right font-bold js-total">Rp 0</td>
                            <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700">&times;</button></td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 border-purple-800">
                        <tr>
                            <th colspan="6" class="px-3 py-3 text-right font-bold text-purple-900">Sub Total</th>
                            <th class="px-3 py-3 text-right font-bold text-purple-900" id="grandTotal">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- PAYMENT INFO --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-sm font-bold text-purple-800 uppercase mb-4">Transfer To</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Bank Name</label>
                    <input type="text" name="bank_name" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Bank Acc. No.</label>
                    <input type="text" name="account_number" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Acc. Name</label>
                    <input type="text" name="account_name" class="w-full rounded-md border-gray-300 text-sm">
                </div>
            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ url()->previous() }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-6 py-2.5 rounded-lg bg-purple-700 text-white text-sm font-semibold hover:bg-purple-800">Save Cash Advance</button>
        </div>
    </form>
</div>

<script>
    let rowIndex = 1;
    const fmt = n => new Intl.NumberFormat('id-ID').format(n);

    function updateRowNumbers() {
        document.querySelectorAll('#itemsBody tr').forEach((tr, i) => {
            tr.querySelector('.row-num').textContent = i + 1;
        });
    }

    function addRow() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-3 py-2 text-center font-bold row-num"></td>
            <td class="px-3 py-2 border-r"><input type="text" name="items[${rowIndex}][item_description]" required class="w-full border-0 focus:ring-0 p-0 text-sm"></td>
            <td class="px-3 py-2 border-r"><input type="text" name="items[${rowIndex}][purpose]" class="w-full border-0 focus:ring-0 p-0 text-sm"></td>
            <td class="px-3 py-2 border-r"><input type="number" name="items[${rowIndex}][qty]" value="1" min="1" required class="w-full border-0 focus:ring-0 p-0 text-sm text-center js-qty"></td>
            <td class="px-3 py-2 border-r"><input type="text" name="items[${rowIndex}][unit]" value="Unit" required class="w-full border-0 focus:ring-0 p-0 text-sm text-center"></td>
            <td class="px-3 py-2 border-r"><input type="number" name="items[${rowIndex}][price]" value="0" min="0" required class="w-full border-0 focus:ring-0 p-0 text-sm text-right js-price"></td>
            <td class="px-3 py-2 text-right font-bold js-total">Rp 0</td>
            <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700">&times;</button></td>
        `;
        document.getElementById('itemsBody').appendChild(tr);
        rowIndex++;
        updateRowNumbers();
        recalc();
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('#itemsBody tr');
        if (rows.length > 1) {
            btn.closest('tr').remove();
            updateRowNumbers();
            recalc();
        }
    }

    function recalc() {
        let sum = 0;
        document.querySelectorAll('#itemsBody tr').forEach(tr => {
            const qty = parseFloat(tr.querySelector('.js-qty')?.value) || 0;
            const price = parseFloat(tr.querySelector('.js-price')?.value) || 0;
            const total = qty * price;
            const cell = tr.querySelector('.js-total');
            if (cell) cell.textContent = 'Rp ' + fmt(total);
            sum += total;
        });
        document.getElementById('grandTotal').textContent = 'Rp ' + fmt(sum);
    }

    document.getElementById('itemsBody').addEventListener('input', e => {
        if (e.target.matches('.js-qty, .js-price')) recalc();
    });
    recalc();
</script>
@endsection