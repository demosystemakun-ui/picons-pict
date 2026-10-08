

<?php $__env->startSection('title', 'Inventory Stock per Departemen'); ?>
<?php $__env->startSection('page-title', 'Department Stock Items'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 pb-12">

    
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Department Stock Items
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                <?php if(auth()->user()->hasRole('Super Admin')): ?>
                    Monitor physical stock availability across all departments.
                <?php else: ?>
                    List of stock items for department
                    <span class="font-medium text-primary-600 dark:text-primary-400"><?php echo e(auth()->user()->department->name ?? 'My Department'); ?></span>.
                <?php endif; ?>
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 self-start">

            
            <button type="button"
                    onclick="openExportModal()"
                    class="inline-flex items-center gap-2 rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 px-4 py-2 text-sm font-semibold text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>

            <?php if(auth()->user()->hasRole('Super Admin')): ?>
                <a href="<?php echo e(route('inventory.stock.create')); ?>"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Stok
                </a>
            <?php endif; ?>
        </div>
    </div>

    
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

        
        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        <?php if(auth()->user()->hasRole('Super Admin')): ?>
                            Physical Stock List
                        <?php else: ?>
                            Stok Departemen
                        <?php endif; ?>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total <?php echo e($stocks->total() ?? $stocks->count()); ?> entri stok tercatat
                    </p>
                </div>
            </div>

            
            <?php if(auth()->user()->hasRole('Super Admin')): ?>
                <form method="GET" action="<?php echo e(route('inventory.stock.index')); ?>" class="flex items-center gap-2">
                    <label for="department_id" class="sr-only">Filter Departemen</label>
                    <div class="relative">
                        <select name="department_id" id="department_id" onchange="this.form.submit()"
                                class="appearance-none rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 pl-3 pr-9 py-2 text-xs font-medium text-gray-700 dark:text-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition cursor-pointer">
                            <option value="">All Departments</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>" <?php echo e(request('department_id') == $dept->id ? 'selected' : ''); ?>>
                                    <?php echo e($dept->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <svg class="pointer-events-none absolute right-2.5 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <?php if(request('department_id')): ?>
                        <a href="<?php echo e(route('inventory.stock.index')); ?>"
                           class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition"
                           title="Reset filter">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </header>

        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Departemen</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Nama Barang / Item</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Sisa Stok</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Terakhir Diperbarui</th>
                        <?php if(!auth()->user()->hasRole('Super Admin')): ?>
                            <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-center">Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    <?php $__empty_1 = true; $__currentLoopData = $stocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $stockValue = (int) $stock->stock;
                            $isLow = $stockValue <= 5;
                            $isCritical = $stockValue <= 2;
                        ?>
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 shrink-0">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="font-medium text-gray-900 dark:text-white"><?php echo e($stock->department->name ?? '-'); ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs">
                                    <?php echo e($stock->item->item_name ?? '-'); ?>

                                </div>
                                <?php if($stock->item->item_code ?? false): ?>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5">
                                        <?php echo e($stock->item->item_code); ?>

                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <?php if($isCritical): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-200 dark:ring-red-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                        <?php echo e($stockValue); ?> Unit
                                    </span>
                                <?php elseif($isLow): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-200 dark:ring-amber-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        <?php echo e($stockValue); ?> Unit
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        <?php echo e($stockValue); ?> Unit
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-xs font-medium text-gray-600 dark:text-gray-300">
                                    <?php echo e($stock->updated_at->format('d M Y')); ?>

                                </div>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                                    <?php echo e($stock->updated_at->format('H:i')); ?> WIB
                                </div>
                            </td>
                            <?php if(!auth()->user()->hasRole('Super Admin')): ?>
                                <td class="px-6 py-4 text-center">
                                    <a href="<?php echo e(route('inventory.stock.use.item', $stock->id)); ?>"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 dark:border-red-900/60 bg-white dark:bg-gray-800 px-3 py-1.5 text-xs font-semibold text-red-600 dark:text-red-400 shadow-sm hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                                        </svg>
                                        Pakai
                                    </a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="<?php echo e(auth()->user()->hasRole('Super Admin') ? '4' : '5'); ?>" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700/50 mb-4">
                                        <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada data stok</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                                        Data stok untuk departemen ini belum tercatat dalam sistem.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if($stocks->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                <?php echo e($stocks->links()); ?>

            </div>
        <?php endif; ?>
    </section>
</div>


<div id="exportModal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true">

    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
         onclick="closeExportModal()"></div>

    <div class="relative z-10 flex items-center justify-center min-h-full p-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700">

            
            <div class="flex items-start gap-3 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                        Export Stock Usage
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Pilih rentang tanggal untuk export laporan pemakaian.
                    </p>
                </div>
            </div>

            
            <form action="<?php echo e(route('inventory.stock.export-usage')); ?>" method="GET" class="space-y-4">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                            Dari Tanggal
                        </label>
                        <input type="date" name="start_date"
                               value="<?php echo e(now()->startOfMonth()->format('Y-m-d')); ?>"
                               required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                            Sampai Tanggal
                        </label>
                        <input type="date" name="end_date"
                               value="<?php echo e(now()->format('Y-m-d')); ?>"
                               required
                               class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                </div>

                <?php if(auth()->user()->hasRole('Super Admin')): ?>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-1.5">
                            Departemen
                        </label>
                        <select name="department_id"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">Semua Departemen</option>
                            <?php $__currentLoopData = $departments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($dept->id); ?>"><?php echo e($dept->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                <?php endif; ?>

                
                <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/60 px-4 py-3">
                    <div class="flex gap-2.5">
                        <svg class="h-4 w-4 flex-shrink-0 text-blue-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-xs text-blue-700 dark:text-blue-300 leading-relaxed">
                            File Excel akan berisi daftar lengkap pemakaian stok harian dengan tanggal, jam, nama barang, jumlah, dan waktu update terakhir.
                        </p>
                    </div>
                </div>

                
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeExportModal()"
                            class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Excel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function openExportModal() {
        document.getElementById('exportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeExportModal() {
        document.getElementById('exportModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeExportModal();
    });
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/inventory/stock/index.blade.php ENDPATH**/ ?>