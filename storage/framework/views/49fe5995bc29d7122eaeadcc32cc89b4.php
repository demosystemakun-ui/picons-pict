

<?php $__env->startSection('title', 'Riwayat Consumable Request'); ?>
<?php $__env->startSection('page-title', 'Riwayat Pengajuan Saya'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6 pb-12">

    
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white">
                Riwayat Pengajuan
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Pantau status dan riwayat consumable request yang Anda ajukan.
            </p>
        </div>

        <button type="button" onclick="openCreateModal()"
                class="inline-flex items-center gap-2 self-start rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 transition">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Request Baru
        </button>
    </div>

    
    <?php if(session('success')): ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/60 px-5 py-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300"><?php echo e(session('success')); ?></p>
            </div>
        </div>
    <?php endif; ?>

    
    <section class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

        <header class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary-50 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Pengajuan Saya</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Total <?php echo e($requests->total() ?? $requests->count()); ?> pengajuan
                    </p>
                </div>
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-900/40">
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">No. Referensi</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Departemen</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Barang</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-6 py-3.5 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/70">
                    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="group hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700/60 px-2 py-1 text-xs font-mono font-semibold text-gray-700 dark:text-gray-300">
                                    <?php echo e($req->no ?? '#' . $req->id); ?>

                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-700/60 text-slate-600 dark:text-slate-300 shrink-0">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <span class="text-sm text-gray-600 dark:text-gray-300 truncate">
                                        <?php echo e($req->department->name ?? $req->user->department->name ?? '-'); ?>

                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white truncate max-w-xs">
                                    <?php echo e($req->items->first()->item_name ?? $req->item_name ?? '-'); ?>

                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Qty: <span class="font-semibold text-gray-700 dark:text-gray-300">
                                        <?php echo e($req->items->sum('quantity') ?? $req->quantity); ?>

                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <?php if($req->status == 'approved'): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:text-emerald-400 ring-1 ring-inset ring-emerald-200 dark:ring-emerald-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Approved
                                    </span>
                                <?php elseif($req->status == 'rejected'): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 dark:bg-red-500/10 px-2.5 py-1 text-xs font-semibold text-red-700 dark:text-red-400 ring-1 ring-inset ring-red-200 dark:ring-red-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
                                        Rejected
                                    </span>
                                <?php elseif($req->status == 'revision'): ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-orange-50 dark:bg-orange-500/10 px-2.5 py-1 text-xs font-semibold text-orange-700 dark:text-orange-400 ring-1 ring-inset ring-orange-200 dark:ring-orange-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                        Revision
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-200 dark:ring-amber-900/60">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Pending
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <a href="<?php echo e(route('consumable-requests.show', $req->id)); ?>"
                                       class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                        Detail
                                    </a>

                                    <?php if($req->status == 'pending'): ?>
                                        <a href="<?php echo e(route('consumable-requests.edit', $req->id)); ?>"
                                           class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-medium text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Edit
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700/50 mb-4">
                                        <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Belum ada pengajuan</p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-sm">
                                        Anda belum pernah membuat consumable request. Klik tombol
                                        <button type="button" onclick="openCreateModal()" class="font-medium text-primary-600 dark:text-primary-400 hover:underline">Buat Request Baru</button>
                                        untuk mulai mengajukan.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($requests->hasPages()): ?>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                <?php echo e($requests->links()); ?>

            </div>
        <?php endif; ?>
    </section>
</div>


<?php echo $__env->make('consumable-requests._modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<?php echo $__env->make('consumable-requests._scripts', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/consumable-requests/my-index.blade.php ENDPATH**/ ?>