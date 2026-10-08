{{-- ═══════════════════════════════════════════════════════════════
     SIDEBAR — PICONS (Collapsible on Mobile, Fixed on Desktop)
══════════════════════════════════════════════════════════════ --}}
<aside
    x-show="sidebarOpen || window.innerWidth >= 1024"
    x-cloak
    class="fixed inset-y-0 left-0 z-30 w-64 h-screen bg-blue-900 text-white-300
           transform transition-transform duration-200 ease-in-out
           flex flex-col"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    aria-label="Sidebar navigation">

    {{-- ═══ BRAND / LOGO + CLOSE BUTTON ═══ --}}
    <div class="relative flex items-center justify-center h-20 px-5 bg-white border-b border-gray-200 shrink-0">
        <img src="{{ asset('logo/pict.png') }}"
             alt="PICONS Logo"
             class="w-36 h-16 object-contain rounded-lg shrink-0">

        {{-- Tombol close — HANYA muncul di mobile --}}
        <button type="button"
                @click="sidebarOpen = false"
            class="lg:hidden absolute right-5 p-1.5 rounded-lg text-gray-500 hover:text-gray-800 hover:bg-gray-100 transition"
                aria-label="Close sidebar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- ═══ NAVIGATION ═══ --}}
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto text-sm"
         aria-label="Main navigation"
         id="sidebarNav">

        {{-- ─── Search Box ─── --}}
        <div class="relative mb-3">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-white-500"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21 21l-4.35-4.35m1.35-5.65a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   id="sidebarSearch"
                   oninput="filterSidebarMenu(this.value)"
                   placeholder="Search..."
                   autocomplete="off"
                   class="w-full bg-white-800 text-white-200 placeholder-white-500 text-sm
                          rounded-lg pl-9 pr-8 py-2 border border-white-800
                          focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button type="button"
                    id="sidebarSearchClear"
                    onclick="document.getElementById('sidebarSearch').value=''; filterSidebarMenu('');"
                    class="hidden absolute right-2.5 top-1/2 -translate-y-1/2 text-white-500 hover:text-white">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Pesan jika tidak ada hasil --}}
        <p id="sidebarNoResult" class="hidden text-xs text-white-500 px-2 py-4 text-center">
            Menu tidak ditemukan.
        </p>

        {{-- ─── Dashboard ─── --}}
        @include('layouts.partials.sidebar-link', [
            'route' => 'dashboard',
            'icon'  => 'home',
            'label' => 'Dashboard',
        ])

        {{-- ─── Master Data (Super Admin only) ─── --}}
        @if(auth()->check() && auth()->user()->hasRole('Super Admin'))
            @include('layouts.partials.sidebar-group', [
                'label'  => 'Master Data',
                'icon'   => 'archive',
               'active' => request()->routeIs(['items.*', 'categories.*', 'units.*', 'vendors.*', 'departments.*', 'signers.*']),
'links'  => [
    ['route' => 'items.index',       'label' => 'Items'],
    ['route' => 'categories.index',  'label' => 'Categories'],
    ['route' => 'units.index',       'label' => 'Units'],
    ['route' => 'vendors.index',     'label' => 'Vendors'],
    ['route' => 'departments.index', 'label' => 'Departments'],
    ['route' => 'signers.index',     'label' => 'Signers'],
    ['route' => 'users.index',       'label' => 'Users'],
],
            ])
        @endif

        {{-- ─── Inventory ─── --}}
        @include('layouts.partials.sidebar-group', [
            'label'  => 'Inventory',
            'icon'   => 'box',
            'active' => request()->routeIs('inventory.*'),
            'links'  => [
                ['route' => 'inventory.stock.index', 'label' => 'Stock'],
                ['route' => null,                    'label' => 'Goods Receipt'],
                ['route' => null,                    'label' => 'Goods Issue'],
                ['route' => null,                    'label' => 'Stock Adjustment'],
            ],
        ])

        {{-- ─── Consumable Request ─── --}}
        @php
            $consumableLinks = [];
            if (auth()->check()) {
                $consumableLinks[] = auth()->user()->hasRole('Super Admin')
                    ? ['route' => 'consumable-requests.index',    'label' => 'Request List']
                    : ['route' => 'consumable-requests.my-index', 'label' => 'My Requests'];
            }
        @endphp

        @include('layouts.partials.sidebar-group', [
            'label'  => 'Consumable Request',
            'icon'   => 'clipboard-list',
            'active' => request()->routeIs('consumable-requests.*'),
            'links'  => $consumableLinks,
        ])

        {{-- ─── Procurement ─── --}}
        @php
            $procurementLinks = [];
            if (auth()->check() && auth()->user()->hasRole('Super Admin')) {
                $procurementLinks[] = ['route' => 'procurement.index',              'label' => 'Procurement Request'];
            }

            
            $procurementLinks[] = ['route' => null,                 'label' => 'Cash Advance'];
            $procurementLinks[] = ['route' => 'reimbursement.index', 'label' => 'Reimbursement'];
        @endphp

        @include('layouts.partials.sidebar-group', [
            'label'  => 'Procurement',
            'icon'   => 'shopping-cart',
            'active' => request()->routeIs(['procurement.*', 'reimbursement.*']),
            'links'  => $procurementLinks,
        ])

        {{-- ─── Reports ─── --}}
        @include('layouts.partials.sidebar-group', [
            'label'  => 'Reports',
            'icon'   => 'bar-chart-3',
            'active' => false,
            'links'  => [
                ['route' => null, 'label' => 'Stock Report'],
                ['route' => null, 'label' => 'Usage Report'],
                ['route' => null, 'label' => 'Purchase Report'],
                ['route' => null, 'label' => 'Vendor Report'],
                ['route' => null, 'label' => 'Cash Advance Report'],
            ],
        ])

        {{-- ─── Settings ─── --}}
        @include('layouts.partials.sidebar-group', [
            'label'  => 'Settings',
            'icon'   => 'settings',
            'active' => false,
            'links'  => [
                ['route' => null, 'label' => 'Users'],
                ['route' => null, 'label' => 'Roles & Permissions'],
            ],
        ])
    </nav>
</aside>

{{-- ═══ SIDEBAR SEARCH SCRIPT ═══ --}}
<script>
    function filterSidebarMenu(query) {
        query = query.trim().toLowerCase();

        const nav        = document.getElementById('sidebarNav');
        const clearBtn   = document.getElementById('sidebarSearchClear');
        const noResultEl = document.getElementById('sidebarNoResult');
        if (!nav) return;

        clearBtn.classList.toggle('hidden', query === '');

        // Ambil semua <a> menu di dalam nav
        const links = nav.querySelectorAll('a[href]');
        let anyVisible = false;

        links.forEach(link => {
            const text  = link.textContent.trim().toLowerCase();
            const match = query === '' || text.includes(query);

            const row = link.closest('li') || link;
            row.style.display = match ? '' : 'none';

            if (match) {
                anyVisible = true;

                if (query !== '') {
                    let parent = link.parentElement;
                    while (parent && parent !== nav) {
                        if (parent.hasAttribute('x-show')) {
                            parent.style.display = '';
                            parent.style.removeProperty('display');
                            parent.removeAttribute('x-cloak');
                        }
                        if (parent.__x && parent.__x.$data && ('open' in parent.__x.$data)) {
                            parent.__x.$data.open = true;
                        }
                        parent = parent.parentElement;
                    }
                }
            }
        });

        if (query === '') {
            nav.querySelectorAll('[x-show]').forEach(el => el.style.removeProperty('display'));
        }

        noResultEl.classList.toggle('hidden', anyVisible || query === '');
    }
</script>