<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    <button @click="open = !open"
        class="w-full flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg hover:bg-gray-800 hover:text-white transition-colors {{ $active ? 'text-white' : 'text-gray-300' }}">
        <span class="flex items-center gap-3">
            <i data-lucide="{{ $icon }}" class="w-4 h-4 shrink-0"></i>
            <span>{{ $label }}</span>
        </span>
        {{-- Menggunakan SVG inline untuk panah agar transisi rotasinya mulus dan stabil --}}
        <svg class="w-3.5 h-3.5 transition-transform duration-200 shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </button>
    <div x-show="open" x-collapse class="pl-9 mt-1 space-y-0.5">
        @foreach ($links as $link)
            <a href="{{ $link['route'] ? route($link['route']) : '#' }}"
               @if(!$link['route']) onclick="return false;" @endif
               class="block px-3 py-2 rounded-lg text-sm {{ $link['route'] && request()->routeIs($link['route']) ? 'bg-primary-600 text-white' : ($link['route'] ? 'hover:bg-gray-800 hover:text-white text-gray-400' : 'text-gray-600 cursor-not-allowed') }}">
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</div>