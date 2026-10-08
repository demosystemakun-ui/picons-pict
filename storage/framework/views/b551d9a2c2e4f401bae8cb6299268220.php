

<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('page-title', 'Master Data - Users'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-4">

    <?php if(session('success')): ?>
        <div class="px-4 py-3 rounded-lg bg-green-50 text-green-700 text-sm"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="px-4 py-3 rounded-lg bg-red-50 text-red-700 text-sm"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / email..."
                   class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 w-56">
            <select name="role" class="text-sm rounded-lg border border-gray-200 dark:border-gray-700 dark:bg-gray-800 px-3 py-2">
                <option value="">Semua Role</option>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($r->name); ?>" <?php if(request('role') == $r->name): echo 'selected'; endif; ?>><?php echo e($r->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="text-sm px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900">Filter</button>
        </form>

        <a href="<?php echo e(route('users.create')); ?>"
           class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            <i data-lucide="plus" class="w-4 h-4"></i> Tambah User
        </a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3">Nama</th>
                        <th class="text-left px-5 py-3">Email</th>
                        <th class="text-left px-5 py-3">Departemen</th>
                        <th class="text-left px-5 py-3">Role</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                            <td class="px-5 py-3 font-medium"><?php echo e($u->name); ?></td>
                            <td class="px-5 py-3 text-gray-500"><?php echo e($u->email); ?></td>
                            <td class="px-5 py-3 text-gray-500"><?php echo e($u->department->name ?? '-'); ?></td>
                            <td class="px-5 py-3">
                                <?php $__currentLoopData = $u->roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-400"><?php echo e($role->name); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="<?php echo e(route('users.edit', $u)); ?>" class="p-1.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-500">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                    <?php if($u->id !== auth()->id()): ?>
                                        <form action="<?php echo e(route('users.destroy', $u)); ?>" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="p-1.5 rounded-md hover:bg-red-50 dark:hover:bg-red-900/30 text-red-500">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">Belum ada data user.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-700">
            <?php echo e($users->links()); ?>

        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/users/index.blade.php ENDPATH**/ ?>