@php
    $r = $reimbursement ?? null;

    // Optional signers
    $signers = old('signers', $r->signers ?? []);
    $sg = fn ($key, $field) => data_get($signers, "$key.$field", '') ?? '';

    $defaultRow = [
        'date_from' => '', 'date_to' => '', 'description' => '',
        'receipt_no' => '', 'purpose' => '', 'qty' => 1,
        'unit' => 'Day', 'price' => 0,
    ];

    $rows = old('items');
    if (!$rows) {
        $rows = $r && $r->relationLoaded('items') && $r->items->isNotEmpty()
            ? $r->items->map(fn ($it) => [
                'date_from'   => $it->date_from?->format('Y-m-d'),
                'date_to'     => $it->date_to?->format('Y-m-d'),
                'description' => $it->description,
                'receipt_no'  => $it->receipt_no,
                'purpose'     => $it->purpose,
                'qty'         => $it->qty,
                'unit'        => $it->unit,
                'price'       => $it->price,
            ])->all()
            : [$defaultRow];
    }
    $rows = array_values((array) $rows);

    $val = fn ($f, $default = '') => old($f, $r?->$f ?? $default);
    $dateVal = fn ($f, $default = null) => old($f, $r?->$f?->format('Y-m-d') ?? $default);

    // Departments
    $deptCurrent = $val('department');
    $deptList = collect($departments ?? []);
    if ($deptCurrent !== '' && !$deptList->contains($deptCurrent)) {
        $deptList = $deptList->prepend($deptCurrent);
    }

    $in   = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $inSm = 'w-full rounded-md border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $lb   = 'mb-1 block text-sm font-medium text-gray-700';
    $card = 'rounded-xl border border-gray-200 bg-white shadow-sm';
    $cardHead = 'border-b border-gray-200 px-5 py-3 text-sm font-semibold text-gray-800';
@endphp

@csrf
@if($r) @method('PUT') @endif

@if($errors->any())
    <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc space-y-0.5 pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

{{-- Applicant data --}}
<div class="{{ $card }}">
    <div class="{{ $cardHead }}">Applicant Data</div>
    <div class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">
        <div>
            <label class="{{ $lb }}">Form No.</label>
            <input type="text" name="number" class="{{ $in }}" value="{{ $val('number', $number ?? '') }}" required>
        </div>
        <div>
            <label class="{{ $lb }}">Request Date</label>
            <input type="date" name="request_date" class="{{ $in }}" value="{{ $dateVal('request_date', now()->format('Y-m-d')) }}" required>
        </div>
        <div>
            <label class="{{ $lb }}">Requester Name</label>
            <input type="text" name="requested_by" class="{{ $in }}" value="{{ $val('requested_by') }}" required>
        </div>
        <div>
            <label class="{{ $lb }}">NIK</label>
            <input type="text" name="nik" class="{{ $in }}" value="{{ $val('nik') }}">
        </div>
        <div>
            <label class="{{ $lb }}">Position</label>
            <input type="text" name="position" class="{{ $in }}" value="{{ $val('position', 'Internship') }}">
        </div>
        <div>
            <label class="{{ $lb }}">Department</label>
            <select name="department" class="{{ $in }}">
                <option value="">— Select department —</option>
                @foreach($deptList as $dept)
                    <option value="{{ $dept }}" @selected($deptCurrent === $dept)>{{ $dept }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $lb }}">Period Start</label>
            <input type="date" name="period_start" class="{{ $in }}" value="{{ $dateVal('period_start') }}">
        </div>
        <div>
            <label class="{{ $lb }}">Period End</label>
            <input type="date" name="period_end" class="{{ $in }}" value="{{ $dateVal('period_end') }}">
        </div>
    </div>
</div>

{{-- Claim details --}}
<div class="{{ $card }}">
    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-3">
        <span class="text-sm font-semibold text-gray-800">Claim Details</span>
        <button type="button" id="add-row" class="rounded-md border border-blue-600 px-3 py-1.5 text-xs font-medium text-blue-700 hover:bg-blue-50">Add Row</button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full text-sm" id="items-table">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-3 py-2.5 font-medium" style="min-width:150px">From</th>
                    <th class="px-3 py-2.5 font-medium" style="min-width:150px">To</th>
                    <th class="px-3 py-2.5 font-medium" style="min-width:220px">Description</th>
                    <th class="px-3 py-2.5 font-medium" style="min-width:130px">Receipt No.</th>
                    <th class="px-3 py-2.5 font-medium" style="min-width:200px">Purpose</th>
                    <th class="px-3 py-2.5 font-medium text-center" style="min-width:90px">Qty</th>
                    <th class="px-3 py-2.5 font-medium" style="min-width:110px">Unit</th>
                    <th class="px-3 py-2.5 font-medium" style="min-width:150px">Price</th>
                    <th class="px-3 py-2.5 font-medium text-right" style="min-width:150px">Total</th>
                    <th class="px-3 py-2.5" style="min-width:60px"></th>
                </tr>
            </thead>
            <tbody id="items-body" class="divide-y divide-gray-100">
                @foreach($rows as $i => $row)
                    <tr>
                        <td class="px-3 py-2.5"><input type="date" name="items[{{ $i }}][date_from]" class="{{ $inSm }}" value="{{ $row['date_from'] ?? '' }}"></td>
                        <td class="px-3 py-2.5"><input type="date" name="items[{{ $i }}][date_to]" class="{{ $inSm }}" value="{{ $row['date_to'] ?? '' }}"></td>
                        <td class="px-3 py-2.5"><input name="items[{{ $i }}][description]" class="{{ $inSm }}" value="{{ $row['description'] ?? '' }}" required></td>
                        <td class="px-3 py-2.5"><input name="items[{{ $i }}][receipt_no]" class="{{ $inSm }}" value="{{ $row['receipt_no'] ?? '' }}"></td>
                        <td class="px-3 py-2.5"><input name="items[{{ $i }}][purpose]" class="{{ $inSm }}" value="{{ $row['purpose'] ?? '' }}"></td>
                        <td class="px-3 py-2.5"><input type="number" min="1" name="items[{{ $i }}][qty]" class="{{ $inSm }} js-qty text-center" value="{{ $row['qty'] ?? 1 }}" required></td>
                        <td class="px-3 py-2.5"><input name="items[{{ $i }}][unit]" class="{{ $inSm }}" value="{{ $row['unit'] ?? 'Day' }}" required></td>
                        <td class="px-3 py-2.5"><input type="number" min="0" name="items[{{ $i }}][price]" class="{{ $inSm }} js-price text-right" value="{{ $row['price'] ?? 0 }}" required></td>
                        <td class="px-3 py-2.5 text-right text-gray-800 js-total">0</td>
                        <td class="px-3 py-2.5 text-center"><button type="button" class="js-remove rounded-md border border-red-300 px-2 py-1.5 text-red-600 hover:bg-red-50" aria-label="Remove row">&times;</button></td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <th colspan="8" class="px-3 py-3 text-right font-semibold text-gray-700">Total</th>
                    <th class="px-3 py-3 text-right font-semibold text-gray-900" id="grand-total">0</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Notes + payment --}}
<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="{{ $card }}">
        <div class="{{ $cardHead }}">Notes</div>
        <div class="p-5">
            <textarea name="notes" rows="5" class="{{ $in }}">{{ $val('notes') }}</textarea>
        </div>
    </div>
    <div class="{{ $card }}">
        <div class="{{ $cardHead }}">Payment Information</div>
        <div class="space-y-3 p-5"
             x-data="{
                open: false,
                search: '',
                selected: @js(old('bank_name', $r->bank_name ?? '')),
                banks: [
                    'BCA (Bank Central Asia)',
                    'Mandiri (Bank Mandiri)',
                    'BNI (Bank Negara Indonesia)',
                    'BRI (Bank Rakyat Indonesia)',
                    'CIMB Niaga',
                    'Bank Danamon',
                    'Permata Bank',
                    'BSI (Bank Syariah Indonesia)',
                    'Bank Jabar Banten (BJB)',
                    'Bank DKI',
                    'Bank Jateng',
                    'Bank Jatim',
                    'OCBC Indonesia',
                    'Maybank Indonesia',
                    'Bank Mega',
                    'Panin Bank',
                    'UOB Indonesia',
                    'SeaBank Indonesia',
                    'Bank Jago',
                    'Blu by BCA Digital',
                    'Allo Bank'
                ],
                get filteredBanks() {
                    if (this.search === '') return this.banks;
                    return this.banks.filter(b => b.toLowerCase().includes(this.search.toLowerCase()));
                }
             }">
            <div>
                <label class="{{ $lb }}">Bank Name</label>
                <input type="hidden" name="bank_name" x-model="selected">
                <div class="relative mt-1">
                    <button type="button" @click="open = !open"
                            class="w-full flex items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <span x-text="selected || 'Select or search bank...'"
                              :class="{'text-gray-400': !selected, 'text-gray-900': selected}"></span>
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" x-cloak @click.away="open = false"
                         class="absolute z-10 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
                        <div class="p-2 border-b border-gray-100">
                            <input type="text" x-model="search" placeholder="Search bank name..."
                                   class="w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                        </div>
                        <ul class="max-h-60 overflow-y-auto py-1 text-sm">
                            <template x-for="bank in filteredBanks" :key="bank">
                                <li @click="selected = bank; open = false; search = ''"
                                    class="cursor-pointer px-3 py-2 hover:bg-blue-50 hover:text-blue-700"
                                    x-text="bank"></li>
                            </template>
                            <li x-show="filteredBanks.length === 0"
                                class="px-3 py-2 text-gray-400 text-center">No bank found</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <label class="{{ $lb }}">Account Holder Name</label>
                <input name="account_name" class="{{ $in }}" value="{{ $val('account_name') }}">
            </div>
            <div>
                <label class="{{ $lb }}">Account Number</label>
                <input name="account_number" class="{{ $in }}" value="{{ $val('account_number') }}">
            </div>
        </div>
    </div>
</div>

{{-- Signers (optional) --}}
<div class="{{ $card }}">
    <div class="{{ $cardHead }}">Signers <span class="font-normal text-gray-500">(optional)</span></div>
    <div class="p-5">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach(['requested' => 'Requested by', 'checked' => 'Checked', 'acknowledge' => 'Acknowledge', 'verified' => 'Verified', 'approved' => 'Approved'] as $key => $label)
                <div class="space-y-1.5">
                    <label class="{{ $lb }}">{{ $label }}</label>

                    @if(isset($signerOptions[$key]) && $signerOptions[$key]->isNotEmpty())
                        <select data-signer-select="{{ $key }}" class="{{ $in }}">
                            <option value="">— Clear —</option>
                            @foreach($signerOptions[$key] as $opt)
                                <option value="{{ $opt->id }}"
                                        data-name="{{ $opt->name }}"
                                        data-title="{{ $opt->title }}"
                                        @selected($sg($key, 'name') === $opt->name)>
                                    {{ $opt->name }} — {{ $opt->title }}
                                </option>
                            @endforeach
                        </select>
                    @endif

                    <input name="signers[{{ $key }}][name]" class="{{ $in }}" placeholder="Name (optional)" value="{{ $sg($key, 'name') }}">
                    <input name="signers[{{ $key }}][title]" class="{{ $in }}" placeholder="Title (optional)" value="{{ $sg($key, 'title') }}">
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-xs text-gray-500">
            Pick from the master list, type manually, or leave blank if not required.
            For "Requested by": if left blank, the name is filled from the requester name and the title from the Position field.
            The master list is managed in Master Data &rarr; Signers.
        </p>
    </div>
</div>

<div class="flex gap-2">
    <button type="submit" class="rounded-lg bg-blue-700 px-5 py-2 text-sm font-medium text-white hover:bg-blue-800">Save</button>
    <a href="{{ route('reimbursement.index') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancel</a>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
(function () {
    const body = document.getElementById('items-body');
    const inSm = @json($inSm);
    const fmt = n => new Intl.NumberFormat('id-ID').format(n);

    function nextIndex() {
        let max = -1;
        body.querySelectorAll('input[name^="items["]').forEach(el => {
            const m = el.name.match(/^items\[(\d+)\]/);
            if (m) max = Math.max(max, parseInt(m[1], 10));
        });
        return max + 1;
    }

    function recalc() {
        let sum = 0;
        body.querySelectorAll('tr').forEach(tr => {
            const qty   = parseFloat(tr.querySelector('.js-qty')?.value)   || 0;
            const price = parseFloat(tr.querySelector('.js-price')?.value) || 0;
            const t = qty * price;
            const cell = tr.querySelector('.js-total');
            if (cell) cell.textContent = fmt(t);
            sum += t;
        });
        document.getElementById('grand-total').textContent = 'Rp' + fmt(sum);
    }

    document.getElementById('add-row').addEventListener('click', () => {
        const i = nextIndex();
        const td = 'class="px-3 py-2.5"';
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td ${td}><input type="date" name="items[${i}][date_from]" class="${inSm}"></td>
            <td ${td}><input type="date" name="items[${i}][date_to]" class="${inSm}"></td>
            <td ${td}><input name="items[${i}][description]" class="${inSm}" required></td>
            <td ${td}><input name="items[${i}][receipt_no]" class="${inSm}"></td>
            <td ${td}><input name="items[${i}][purpose]" class="${inSm}"></td>
            <td ${td}><input type="number" min="1" name="items[${i}][qty]" class="${inSm} js-qty text-center" value="1" required></td>
            <td ${td}><input name="items[${i}][unit]" class="${inSm}" value="Day" required></td>
            <td ${td}><input type="number" min="0" name="items[${i}][price]" class="${inSm} js-price text-right" value="0" required></td>
            <td class="px-3 py-2.5 text-right text-gray-800 js-total">0</td>
            <td class="px-3 py-2.5 text-center"><button type="button" class="js-remove rounded-md border border-red-300 px-2 py-1.5 text-red-600 hover:bg-red-50" aria-label="Remove row">&times;</button></td>`;
        body.appendChild(tr);
        recalc();
    });

    body.addEventListener('input', e => {
        if (e.target.matches('.js-qty, .js-price')) recalc();
    });

    body.addEventListener('click', e => {
        const btn = e.target.closest('.js-remove');
        if (!btn) return;
        const rows = body.querySelectorAll('tr');
        if (rows.length > 1) {
            btn.closest('tr').remove();
            recalc();
        }
    });

    recalc();

    // Signer master -> fill name & title
    document.querySelectorAll('[data-signer-select]').forEach(sel => {
        sel.addEventListener('change', () => {
            const opt = sel.selectedOptions[0];
            const k = sel.dataset.signerSelect;
            const nameEl  = document.querySelector(`[name="signers[${k}][name]"]`);
            const titleEl = document.querySelector(`[name="signers[${k}][title]"]`);
            if (nameEl)  nameEl.value  = opt && opt.value ? opt.dataset.name  : '';
            if (titleEl) titleEl.value = opt && opt.value ? opt.dataset.title : '';
        });
    });
})();
</script>