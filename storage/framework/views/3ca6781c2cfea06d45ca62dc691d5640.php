<a href="<?php echo e($route ? route($route) : '#'); ?>"
   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-colors <?php echo e($route && request()->routeIs($route.'*') ? 'bg-primary-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'); ?>">
    <i data-lucide="<?php echo e($icon); ?>" class="w-4 h-4 shrink-0"></i>
    <span><?php echo e($label); ?></span>
</a><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/layouts/partials/sidebar-link.blade.php ENDPATH**/ ?>