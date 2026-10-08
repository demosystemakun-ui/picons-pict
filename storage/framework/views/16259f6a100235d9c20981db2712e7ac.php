

<?php $__env->startSection('content'); ?>
<?php
    $in = 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200';
    $lb = 'mb-1 block text-sm font-medium text-gray-700';
?>
<div class="space-y-4">
    <div>
        <h1 class="text-xl font-semibold text-gray-900">Signers</h1>
        <p class="text-sm text-gray-500">Daftar penandatangan yang bisa dipilih di form reimbursement.</p>
    </div>

    <?php if(session('success')): ?>
        <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc space-y-0.5 pl-5"><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($e); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>

    
    <form method="POST" action="<?php echo e(route('signers.store')); ?>" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <?php echo csrf_field(); ?>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div>
                <label class="<?php echo e($lb); ?>">Nama</label>
                <input name="name" class="<?php echo e($in); ?>" value="<?php echo e(old('name')); ?>" required>
            </div>
            <div>
                <label class="<?php echo e($lb); ?>">Jabatan</label>
                <input name="title" class="<?php echo e($in); ?>" value="<?php echo e(old('title')); ?>" required>
            </div>
            <div>
                <label class="<?php echo e($lb); ?>">Posisi di form</label>
                <select name="slot" class="<?php echo e($in); ?>" required>
                    <?php $__currentLoopData = \App\Models\Signer::SLOTS; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($k); ?>" <?php if(old('slot') === $k): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div class="flex items-end gap-3">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300"> Aktif
                </label>
                <button class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Tambah</button>
            </div>
        </div>
    </form>

    
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Jabatan</th>
                    <th class="px-4 py-3 font-medium">Posisi di form</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
            <?php $__empty_1 = true; $__currentLoopData = $signers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900"><?php echo e($s->name); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e($s->title); ?></td>
                    <td class="px-4 py-3 text-gray-600"><?php echo e(\App\Models\Signer::SLOTS[$s->slot] ?? $s->slot); ?></td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-0.5 text-xs <?php echo e($s->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'); ?>">
                            <?php echo e($s->is_active ? 'Aktif' : 'Nonaktif'); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex justify-end gap-1.5">
                            <a href="<?php echo e(route('signers.edit', $s)); ?>" class="rounded-md border border-gray-300 px-2.5 py-1 text-xs text-gray-700 hover:bg-gray-50">Ubah</a>
                            <form method="POST" action="<?php echo e(route('signers.destroy', $s)); ?>" onsubmit="return confirm('Hapus penandatangan ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button class="rounded-md border border-red-300 px-2.5 py-1 text-xs text-red-700 hover:bg-red-50">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada penandatangan.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/signers/index.blade.php ENDPATH**/ ?>