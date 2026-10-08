<div x-data="{ open: <?php echo e($active ? 'true' : 'false'); ?> }">
    <button @click="open = !open"
        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-800 hover:text-white transition-colors <?php echo e($active ? 'text-white' : 'text-gray-300'); ?>">
        <span class="flex items-center gap-3">
            <i data-lucide="<?php echo e($icon); ?>" class="w-4 h-4 shrink-0"></i>
            <span><?php echo e($label); ?></span>
        </span>
        
        <svg class="w-3.5 h-3.5 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" x-collapse class="pl-9 mt-1 space-y-0.5">
        <?php $__currentLoopData = $links; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e($link['route'] ? route($link['route']) : '#'); ?>"
               <?php if(!$link['route']): ?> onclick="return false;" <?php endif; ?>
               class="block px-3 py-2 rounded-lg text-sm <?php echo e($link['route'] && request()->routeIs($link['route']) ? 'bg-primary-600 text-white' : ($link['route'] ? 'hover:bg-gray-800 hover:text-white text-gray-400' : 'text-gray-600 cursor-not-allowed')); ?>">
                <?php echo e($link['label']); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/layouts/partials/sidebar-group.blade.php ENDPATH**/ ?>