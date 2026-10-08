@php $item = $item ?? null; @endphp

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Item Code</label>
        <input type="text" name="item_code" value="{{ old('item_code', $item->item_code ?? '') }}" required
               class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
        @error('item_code') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
            <option value="active" @selected(old('status', $item->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $item->status ?? '') === 'inactive')>Inactive</option>
        </select>
    </div>
</div>

<div>
    <label class="block text-sm font-medium mb-1">Item Name</label>
    <input type="text" name="item_name" value="{{ old('item_name', $item->item_name ?? '') }}" required
           class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
    @error('item_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Category</label>
        <select name="category_id" required class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
            <option value="">-- Pilih Kategori --</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id ?? '') == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Unit</label>
        <select name="unit_id" required class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
            <option value="">-- Pilih Satuan --</option>
            @foreach ($units as $unit)
                <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id ?? '') == $unit->id)>{{ $unit->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Minimum Stock</label>
        <input type="number" min="0" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock ?? 0) }}" required
               class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Current Stock</label>
        <input type="number" min="0" name="current_stock" value="{{ old('current_stock', $item->current_stock ?? 0) }}" required
               class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">
    </div>
</div>

<div>
    <label class="block text-sm font-medium mb-1">Description</label>
    <textarea name="description" rows="3" class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-900 px-3 py-2">{{ old('description', $item->description ?? '') }}</textarea>
</div>
