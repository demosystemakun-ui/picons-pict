<div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)">
    @if (session('success'))
        <div x-show="show" x-cloak x-transition class="mx-4 lg:mx-6 mt-4 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 text-sm flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div x-show="show" x-cloak x-transition class="mx-4 lg:mx-6 mt-4 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 text-sm flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4"></i> {{ session('error') }}
        </div>
    @endif
</div>