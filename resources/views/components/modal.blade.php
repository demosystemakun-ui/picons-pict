{{--
    Generic modal shell. Usage:
    <x-modal name="create-category" title="Tambah Kategori">
        ... form fields ...
    </x-modal>
    Toggle via Alpine: x-data="{ open: false }" and dispatch/trigger `open = true`
--}}
@props(['show' => 'modalOpen', 'title' => ''])

<div x-show="{{ $show }}" x-cloak
     class="fixed inset-0 z-40 flex items-center justify-center p-4 bg-black/40"
     x-transition.opacity>
    <div @click.outside="{{ $show }} = false"
         class="bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto"
         x-transition>
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="font-semibold text-sm">{{ $title }}</h3>
            <button @click="{{ $show }} = false" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
        <div class="p-5">
            {{ $slot }}
        </div>
    </div>
</div>
