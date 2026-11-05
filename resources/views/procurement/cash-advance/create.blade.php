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
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Applicant Data</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Request Date</label>
                    <input type="date" name="request_date" value="{{ date('Y-m-d') }}" required class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Requested By</label>
                    <input type="text" name="requested_by" value="{{ auth()->user()->name }}" required class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Position</label>
                    <input type="text" name="position" value="{{ auth()->user()->position ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Department</label>
                    <input type="text" name="department" value="{{ auth()->user()->department->name ?? '' }}" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cost Department</label>
                    <input type="text" name="cost_department" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">NIK</label>
                    <input type="text" name="nik" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Delivery Address</label>
                    <input type="text" name="delivery_address" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Attachment</label>
                    <input type="text" name="attachment" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
            </div>
        </div>

        {{-- ITEMS TABLE --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between p-4 border-b bg-gray-50">
                <h2 class="text-sm font-semibold text-gray-800">Claim Details</h2>
                <button type="button" onclick="addRow()" class="rounded-md border border-blue-600 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">Add Row</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="px-3 py-2 font-medium min-w-[200px]">Item Description</th>
                            <th class="px-3 py-2 font-medium min-w-[150px]">Purpose</th>
                            <th class="px-3 py-2 font-medium w-24">Qty</th>
                            <th class="px-3 py-2 font-medium w-24">Unit</th>
                            <th class="px-3 py-2 font-medium w-36">Price</th>
                            <th class="px-3 py-2 font-medium w-36 text-right">Total</th>
                            <th class="px-3 py-2 w-12"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody" class="divide-y divide-gray-100">
                        <tr>
                            <td class="px-3 py-2"><input type="text" name="items[0][item_description]" required class="w-full rounded-md border-gray-300 text-sm"></td>
                            <td class="px-3 py-2"><input type="text" name="items[0][purpose]" class="w-full rounded-md border-gray-300 text-sm"></td>
                            <td class="px-3 py-2"><input type="number" name="items[0][qty]" value="1" min="1" required class="w-full rounded-md border-gray-300 text-sm text-center js-qty"></td>
                            <td class="px-3 py-2"><input type="text" name="items[0][unit]" value="Unit" required class="w-full rounded-md border-gray-300 text-sm"></td>
                            <td class="px-3 py-2"><input type="number" name="items[0][price]" value="0" min="0" required class="w-full rounded-md border-gray-300 text-sm text-right js-price"></td>
                            <td class="px-3 py-2 text-right font-medium js-total">0</td>
                            <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700">&times;</button></td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <th colspan="5" class="px-3 py-3 text-right font-semibold text-gray-700">Sub Total</th>
                            <th class="px-3 py-3 text-right font-bold text-gray-900" id="grandTotal">Rp 0</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- PAYMENT INFO --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-800 mb-4">Transfer To</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Bank Name</label>
                    <input type="text" name="bank_name" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Account Name</label>
                    <input type="text" name="account_name" class="w-full rounded-lg border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Account Number</label>
                    <input type="text" name="account_number" class="w-full rounded-lg border-gray-300 text-sm">
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

    function addRow() {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="px-3 py-2"><input type="text" name="items[${rowIndex}][item_description]" required class="w-full rounded-md border-gray-300 text-sm"></td>
            <td class="px-3 py-2"><input type="text" name="items[${rowIndex}][purpose]" class="w-full rounded-md border-gray-300 text-sm"></td>
            <td class="px-3 py-2"><input type="number" name="items[${rowIndex}][qty]" value="1" min="1" required class="w-full rounded-md border-gray-300 text-sm text-center js-qty"></td>
            <td class="px-3 py-2"><input type="text" name="items[${rowIndex}][unit]" value="Unit" required class="w-full rounded-md border-gray-300 text-sm"></td>
            <td class="px-3 py-2"><input type="number" name="items[${rowIndex}][price]" value="0" min="0" required class="w-full rounded-md border-gray-300 text-sm text-right js-price"></td>
            <td class="px-3 py-2 text-right font-medium js-total">0</td>
            <td class="px-3 py-2 text-center"><button type="button" onclick="removeRow(this)" class="text-red-500 hover:text-red-700">&times;</button></td>
        `;
        document.getElementById('itemsBody').appendChild(tr);
        rowIndex++;
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('#itemsBody tr');
        if (rows.length > 1) {
            btn.closest('tr').remove();
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