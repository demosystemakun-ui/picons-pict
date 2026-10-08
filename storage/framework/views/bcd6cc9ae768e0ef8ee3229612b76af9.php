<?php $__env->startSection('content'); ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Reimbursement</h1>
            <p class="text-sm text-gray-500">Daftar form klaim reimbursement.</p>
        </div>
        <a href="<?php echo e(route('reimbursement.create')); ?>"
           class="inline-flex items-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">
            Buat form baru
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">No. form</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th>
                        <th class="px-4 py-3 font-medium">Pemohon</th>
                        <th class="px-4 py-3 font-medium text-right">Item</th>
                        <th class="px-4 py-3 font-medium text-right">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $reimbursements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($r->number); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($r->request_date->format('d M Y')); ?></td>
                        <td class="px-4 py-3 text-gray-600"><?php echo e($r->requested_by); ?></td>
                        <td class="px-4 py-3 text-right text-gray-600"><?php echo e($r->items_count); ?></td>
                        <td class="px-4 py-3 text-right text-gray-900">Rp<?php echo e(number_format($r->items()->selectRaw('SUM(qty*price) t')->value('t'), 0, ',', '.')); ?></td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-1.5">
                                <a href="<?php echo e(route('reimbursement.show', $r)); ?>" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Lihat</a>
                                <a href="<?php echo e(route('reimbursement.print', $r)); ?>" target="_blank" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Cetak</a>
                                <a href="<?php echo e(route('reimbursement.pdf', $r)); ?>" target="_blank" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">PDF</a>
                                <a href="<?php echo e(route('reimbursement.edit', $r)); ?>" class="rounded-md bg-blue-700 px-2.5 py-1 text-xs text-white hover:bg-blue-800">Ubah</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-10 text-center text-gray-500">Belum ada form reimbursement. Klik "Buat form baru" untuk memulai.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div><?php echo e($reimbursements->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/procurement/reimbursement/index.blade.php ENDPATH**/ ?>