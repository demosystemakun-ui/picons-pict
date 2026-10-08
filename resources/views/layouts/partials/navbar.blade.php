{{-- ═══════════════════════════════════════════════════════════════
     NAVBAR — PICONS (dengan breadcrumb otomatis)
══════════════════════════════════════════════════════════════ --}}
<header class="sticky top-0 z-10 bg-white/95 backdrop-blur border-b border-slate-200">
    <div class="flex items-center justify-between gap-3 h-16 px-4 sm:px-6">

        {{-- ═══ LEFT: BURGER + TITLE + BREADCRUMB ═══ --}}
        <div class="flex items-center gap-3 min-w-0">

            {{-- Burger toggle — HANYA mobile --}}
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

            {{-- Page title + Breadcrumb --}}
            <div class="min-w-0">
                <h1 class="text-base sm:text-lg font-semibold text-gray-900 truncate">
                    @yield('page-title', 'Dashboard')
                </h1>

                {{-- ═══ BREADCRUMB OTOMATIS ═══ --}}
                @php
                    $segments    = collect(request()->segments())->filter()->values();
                    $customTitle = trim($__env->yieldContent('page-title'));
                @endphp

                <nav aria-label="Breadcrumb" class="mt-0.5">
                    <ol class="flex items-center gap-1.5 text-xs sm:text-sm text-gray-500 flex-wrap">
                        {{-- Home --}}
                        <li>
                            <a href="{{ url('/') }}"
                               class="text-teal-600 hover:text-teal-700 hover:underline font-medium transition-colors">
                                Home
                            </a>
                        </li>

                        @forelse($segments as $index => $segment)
                            @php
                                $url    = url(implode('/', $segments->slice(0, $index + 1)->toArray()));
                                $isLast = $index === $segments->count() - 1;
                                // Segment terakhir → pakai page-title kalau ada
                                if ($isLast && $customTitle && $customTitle !== 'Dashboard') {
                                    $label = $customTitle;
                                } else {
                                    $label = ucwords(str_replace(['-', '_'], ' ', $segment));
                                }
                            @endphp

                            <li aria-hidden="true" class="text-gray-300 select-none">/</li>
                            <li>
                                @if($isLast)
                                    <span class="text-gray-700 font-medium truncate max-w-[200px] inline-block align-bottom">
                                        {{ $label }}
                                    </span>
                                @else
                                    <a href="{{ $url }}"
                                       class="text-teal-600 hover:text-teal-700 hover:underline font-medium transition-colors">
                                        {{ $label }}
                                    </a>
                                @endif
                            </li>
                        @empty
                            <li aria-hidden="true" class="text-gray-300 select-none">/</li>
                            <li>
                                <span class="text-gray-700 font-medium">{{ $customTitle ?: 'Dashboard' }}</span>
                            </li>
                        @endforelse
                    </ol>
                </nav>
            </div>
        </div>

        {{-- ═══ RIGHT: ACTIONS ═══ --}}
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">

            {{-- Dark mode toggle --}}
            <button type="button"
                    class="hidden sm:inline-flex items-center justify-center w-9 h-9 rounded-lg
                           text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                    aria-label="Toggle dark mode">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </button>

            {{-- User dropdown --}}
            @auth
                <div x-data="{ open: false }" class="relative">
                    <button type="button"
                            @click="open = !open"
                            class="inline-flex items-center gap-2 rounded-full pl-1 pr-3 py-1
                                   bg-gray-100 hover:bg-gray-200 transition"
                            :aria-expanded="open.toString()"
                            aria-label="User menu">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full
                                     bg-emerald-600 text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </span>
                        <span class="hidden sm:block text-xs font-medium text-gray-700 max-w-[140px] truncate">
                            {{ auth()->user()->name ?? 'User' }}
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
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</header>