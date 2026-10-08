
<header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="flex items-center justify-between gap-3 h-16 px-4 sm:px-6">

        
        <div class="flex items-center gap-3 min-w-0">

            
            <button type="button"
                    x-show="!sidebarOpen"
                    x-cloak
                    @click="sidebarOpen = true"
                    class="lg:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg
                           text-gray-600 hover:text-gray-900 hover:bg-gray-100
                           transition shrink-0"
                    aria-label="Open sidebar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
            </button>

            
            <div class="min-w-0">
                <h1 class="text-base sm:text-lg font-semibold text-gray-900 truncate">
                    <?php echo $__env->yieldContent('page-title', 'Dashboard'); ?>
                </h1>

                
                <?php
                    $segments    = collect(request()->segments())->filter()->values();
                    $customTitle = trim($__env->yieldContent('page-title'));
                ?>

                <nav aria-label="Breadcrumb" class="mt-0.5">
                    <ol class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500 flex-wrap">
                        
                        <li>
                            <a href="<?php echo e(url('/')); ?>"
                               class="text-teal-600 hover:text-teal-700 hover:underline font-medium transition-colors">
                                Home
                            </a>
                        </li>

                        <?php $__empty_1 = true; $__currentLoopData = $segments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $segment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $url    = url(implode('/', $segments->slice(0, $index + 1)->toArray()));
                                $isLast = $index === $segments->count() - 1;
                                // Segment terakhir → pakai page-title kalau ada
                                if ($isLast && $customTitle && $customTitle !== 'Dashboard') {
                                    $label = $customTitle;
                                } else {
                                    $label = ucwords(str_replace(['-', '_'], ' ', $segment));
                                }
                            ?>

                            <li aria-hidden="true" class="text-gray-300 select-none">/</li>
                            <li>
                                <?php if($isLast): ?>
                                    <span class="text-gray-700 font-medium truncate max-w-[200px] inline-block align-bottom">
                                        <?php echo e($label); ?>

                                    </span>
                                <?php else: ?>
                                    <a href="<?php echo e($url); ?>"
                                       class="text-teal-600 hover:text-teal-700 hover:underline font-medium transition-colors">
                                        <?php echo e($label); ?>

                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <li aria-hidden="true" class="text-gray-300 select-none">/</li>
                            <li>
                                <span class="text-gray-700 font-medium"><?php echo e($customTitle ?: 'Dashboard'); ?></span>
                            </li>
                        <?php endif; ?>
                    </ol>
                </nav>
            </div>
        </div>

        
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">

            
            <button type="button"
                    class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-lg
                           text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                    aria-label="Toggle dark mode">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            
            <?php if(auth()->guard()->check()): ?>
                <div x-data="{ open: false }" class="relative">
                    <button type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-full pl-1 pr-3 py-1
                                   bg-gray-100 hover:bg-gray-200 transition"
                            :aria-expanded="open.toString()"
                            aria-label="User menu">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                                     bg-emerald-600 text-white text-xs font-bold shrink-0">
                            <?php echo e(strtoupper(substr(auth()->user()->name ?? 'U', 0, 1))); ?>

                        </span>
                        <span class="hidden sm:block text-xs font-medium text-gray-700 max-w-[140px] truncate">
                            <?php echo e(auth()->user()->name ?? 'User'); ?>

                        </span>
                        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open"
                         x-cloak
                         @click.outside="open = false"
                         x-transition
                         class="absolute right-0 mt-2 w-48 rounded-xl bg-white border border-gray-200 shadow-lg overflow-hidden z-30">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-xs text-gray-500">Signed in as</p>
                            <p class="text-sm font-semibold text-gray-900 truncate"><?php echo e(auth()->user()->email ?? ''); ?></p>
                        </div>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                        <form method="POST" action="<?php echo e(route('logout')); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header><?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/layouts/partials/navbar.blade.php ENDPATH**/ ?>