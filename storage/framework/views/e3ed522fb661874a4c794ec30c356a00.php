

<?php $__env->startSection('title', 'Edit User'); ?>
<?php $__env->startSection('page-title', 'Master Data - Edit User'); ?>

<?php $__env->startSection('content'); ?>
<div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 max-w-3xl">
    <form action="<?php echo e(route('users.update', $user)); ?>" method="POST">
        <?php echo $__env->make('users._form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </form>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/users/edit.blade.php ENDPATH**/ ?>