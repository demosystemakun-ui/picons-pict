{{-- Item request --}}
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 overflow-hidden shadow-sm">
    <div class="h-1 bg-purple-500"></div>
    <div class="p-5 space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm" id="itemsTable">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 pr-2 text-xs font-medium text-gray-500 w-1/3">Nama Barang</th>
                        <th class="py-2 px-2 text-xs font-medium text-gray-500 w-1/3">Catatan Kebutuhan</th>
                        <th class="py-2 px-2 text-xs font-medium text-gray-500 w-24">Qty</th>
                        <th class="py-2 pl-2 w-10"></th>
                    </tr>
                </thead>
                <tbody id="itemsBody">
                    @foreach($consumableRequest->items as $index => $item)
                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                        <td class="py-2 pr-2">
                            <input type="text" name="items[{{ $index }}][item_name]" value="{{ old('items.'.$index.'.item_name', $item->item_name) }}" required
                                   class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </td>
                        <td class="py-2 px-2">
                            <input type="text" name="items[{{ $index }}][item_requirement_note]" value="{{ old('items.'.$index.'.item_requirement_note', $item->item_requirement_note) }}"
                                   class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" name="items[{{ $index }}][quantity]" value="{{ old('items.'.$index.'.quantity', $item->quantity_requested) }}" min="0.01" step="0.01" required
                                   class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">
                        </td>
                        <td class="py-2 pl-2 text-right">
                            <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" onclick="addItemRow()" 
                class="inline-flex items-center gap-2 rounded-lg border border-dashed border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:border-primary-400 hover:text-primary-600 dark:hover:text-primary-400 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Item
        </button>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Target to Purchase</label>
            <textarea name="target_purchase" rows="2"
                      class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">{{ old('target_purchase', $consumableRequest->target_purchase) }}</textarea>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let itemIndex = {{ $consumableRequest->items->count() }};

    function addItemRow(data = null) {
        const tbody = document.getElementById('itemsBody');
        const tr = document.createElement('tr');
        tr.className = 'border-b border-gray-100 dark:border-gray-700/50';
        
        const itemName = data ? data.item_name : '';
        const reqNote = data ? data.item_requirement_note : '';
        const qty = data ? data.quantity_requested : 1;

        tr.innerHTML = `
            <td class="py-2 pr-2">
                <input type="text" name="items[${itemIndex}][item_name]" value="${itemName}" required
                       class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">
            </td>
            <td class="py-2 px-2">
                <input type="text" name="items[${itemIndex}][item_requirement_note]" value="${reqNote}"
                       class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">
            </td>
            <td class="py-2 px-2">
                <input type="number" name="items[${itemIndex}][quantity]" value="${qty}" min="0.01" step="0.01" required
                       class="w-full rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-sm focus:border-primary-500 focus:ring-primary-500">
            </td>
            <td class="py-2 pl-2 text-right">
                <button type="button" onclick="this.closest('tr').remove()" class="text-red-500 hover:text-red-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        itemIndex++;
    }
</script>
@endpush