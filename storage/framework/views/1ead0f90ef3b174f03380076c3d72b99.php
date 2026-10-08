<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo $__env->yieldContent('title', 'PICONS — PICT Consumables'); ?></title>

    <link rel="icon" type="image/png" href="<?php echo e(asset('logo/pict.png')); ?>">
    <link rel="apple-touch-icon" href="<?php echo e(asset('logo/pict.png')); ?>">

    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#1e40af',  // ← warna utama tombol & sidebar active
                            700: '#1e3a8a',
                            800: '#172554',
                            900: '#0f172a',
                        },
                    },
                },
            },
        };
    </script>

    
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    
    <script src="https://unpkg.com/lucide@latest"></script>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">

        
        <?php echo $__env->make('layouts.partials.sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        
        <div x-show="sidebarOpen"
             x-cloak
             x-transition:enter="transition-opacity ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false"
             class="fixed inset-0 z-20 bg-black/40 lg:hidden"
             aria-hidden="true"></div>

        
        <div class="lg:ml-64 flex flex-col min-h-screen">

            <?php echo $__env->make('layouts.partials.navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <main class="flex-1 p-4 sm:p-6">
                <?php echo $__env->yieldContent('content'); ?>
            </main>

            <?php if (! empty(trim($__env->yieldContent('footer')))): ?>
                <footer class="border-t border-slate-200 bg-white px-6 py-4 text-xs text-slate-500">
                    <?php echo $__env->yieldContent('footer'); ?>
                </footer>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) lucide.createIcons();
        });
    </script>

    
    <?php echo $__env->make('components.flash-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.confirm-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php echo $__env->make('components.pr-number-modal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/layouts/app.blade.php ENDPATH**/ ?>