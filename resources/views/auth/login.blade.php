<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Consumable Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="{{ asset('logo/pict.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo/pict.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Tentukan sedini mungkin apakah intro ditampilkan (sekali per sesi, hormati reduce-motion) --}}
    <script>
        try {
            if (!sessionStorage.getItem('picons-intro') && !matchMedia('(prefers-reduced-motion: reduce)').matches) {
                document.documentElement.classList.add('intro-on');
            }
        } catch (e) {}
    </script>

    <style>
        * { font-family: 'Inter', sans-serif; }

        body {
            background:
                radial-gradient(circle at 12% 18%, rgba(251, 113, 133, 0.14), transparent 40%),
                radial-gradient(circle at 88% 82%, rgba(99, 102, 241, 0.16), transparent 42%),
                linear-gradient(135deg, #f8fafc 0%, #eef2ff 52%, #fff1f2 100%);
            min-height: 100vh;
        }

        .split-shell {
            box-shadow: 0 28px 65px -18px rgba(15, 23, 42, 0.14), 0 0 0 1px rgba(255, 255, 255, 0.9);
        }

        .btn-coral {
            background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-coral:hover:not(:disabled) {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            transform: translateY(-1px);
            box-shadow: 0 12px 22px -6px rgba(244, 63, 94, 0.42);
        }
        .btn-coral:active:not(:disabled) { transform: translateY(0); }

        .input-modern { transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease; }
        .input-modern:focus { background-color: #fff; box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.12); }

        /* ===== Intro ===== */
        .intro { display: none; }
        .intro-on .intro { display: block; position: fixed; inset: 0; z-index: 100; pointer-events: all; }
        .intro-on body { overflow: hidden; }

        /* dua panel yang membuka seperti tirai */
        .intro__half {
            position: absolute; top: 0; bottom: 0; width: 50.2%;
            background: linear-gradient(160deg, #1e1b4b 0%, #312e81 55%, #4c1d4f 100%);
            transition: transform .95s cubic-bezier(.76, 0, .24, 1);
        }
        .intro__half--l { left: 0; }
        .intro__half--r { right: 0; }
        .intro.is-leaving .intro__half--l { transform: translateX(-101%); }
        .intro.is-leaving .intro__half--r { transform: translateX(101%); }

        .intro__center {
            position: absolute; inset: 0; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 1rem; color: #fff;
            transition: opacity .35s ease, transform .5s ease;
        }
        .intro.is-leaving .intro__center { opacity: 0; transform: scale(.94); }

        .intro__badge {
            width: 5rem; height: 5rem; border-radius: 1.25rem; background: #fff; padding: .9rem;
            display: grid; place-items: center; position: relative;
            box-shadow: 0 20px 50px -10px rgba(244, 63, 94, .55);
            animation: pop .8s cubic-bezier(.2, .9, .3, 1.25) both;
        }
        .intro__badge::after {
            content: ""; position: absolute; inset: -10px; border-radius: 1.75rem;
            border: 2px solid rgba(251, 113, 133, .7);
            animation: ring 1.6s ease-out .5s infinite;
        }
        .intro__badge img { width: 100%; height: 100%; object-fit: contain; }

        .intro__word { display: flex; font-size: 1.9rem; font-weight: 800; letter-spacing: .02em; }
        .intro__word span {
            display: inline-block; opacity: 0; transform: translateY(.7em);
            animation: letter .5s cubic-bezier(.2, .8, .2, 1) forwards;
            animation-delay: calc(.5s + var(--i) * 60ms);
        }
        .intro__tag { font-size: .8rem; color: rgba(255,255,255,.7); opacity: 0; animation: fade .6s ease 1.1s forwards; }
        .intro__bar {
            width: 11rem; height: 3px; border-radius: 9999px; background: rgba(255,255,255,.18);
            overflow: hidden; margin-top: .5rem; opacity: 0; animation: fade .4s ease 1s forwards;
        }
        .intro__bar span {
            display: block; height: 100%; border-radius: inherit; transform: scaleX(0); transform-origin: left;
            background: linear-gradient(90deg, #fb7185, #fff);
            animation: fill 1.2s cubic-bezier(.65, 0, .35, 1) 1.05s forwards;
        }

        @keyframes pop    { from { opacity: 0; transform: scale(.4) rotate(-10deg); } to { opacity: 1; transform: none; } }
        @keyframes ring   { from { opacity: .9; transform: scale(.9); } to { opacity: 0; transform: scale(1.35); } }
        @keyframes letter { to { opacity: 1; transform: none; } }
        @keyframes fade   { to { opacity: 1; } }
        @keyframes fill   { to { transform: scaleX(1); } }

        /* ===== Masuknya halaman (menunggu intro selesai) ===== */
        .reveal { animation: rise .7s cubic-bezier(.2, .8, .2, 1) both; animation-delay: calc(var(--d, 0) * 90ms); }
        .intro-on .reveal, .intro-on .shell-in { opacity: 0; animation-play-state: paused; }
        .shell-in { animation: shell .8s cubic-bezier(.2, .8, .2, 1) both; }
        @keyframes rise  { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
        @keyframes shell { from { opacity: 0; transform: translateY(18px) scale(.985); } to { opacity: 1; transform: none; } }

        /* ===== Panel kiri hidup ===== */
        .float-a { animation: floaty 6s ease-in-out infinite; }
        .float-b { animation: floaty 7s ease-in-out -2s infinite reverse; }
        @keyframes floaty { 0%,100% { transform: translateY(0) rotate(var(--r, 0deg)); } 50% { transform: translateY(-7px) rotate(var(--r, 0deg)); } }

        .bar-grow { transform-origin: bottom; animation: grow .8s cubic-bezier(.2, .8, .2, 1) both; animation-delay: calc(var(--b) * 90ms); }
        .intro-on .bar-grow { animation-play-state: paused; transform: scaleY(0); }
        @keyframes grow { from { transform: scaleY(0); } to { transform: scaleY(1); } }

        .pulse-dot { animation: pdot 2s ease-in-out infinite; }
        @keyframes pdot { 0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,.5); } 50% { box-shadow: 0 0 0 5px rgba(16,185,129,0); } }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
            .reveal, .shell-in { opacity: 1 !important; }
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 sm:p-6 md:p-8 text-slate-800 antialiased">

    <!-- Intro -->
    <div id="intro" class="intro" aria-hidden="true">
        <div class="intro__half intro__half--l"></div>
        <div class="intro__half intro__half--r"></div>
        <div class="intro__center">
            <div class="intro__badge"><img src="{{ asset('logo/pict.png') }}" alt=""></div>
            <div class="intro__word">
                @foreach (str_split('PICONS') as $i => $ch)
                    <span style="--i: {{ $i }}">{{ $ch }}</span>
                @endforeach
            </div>
            <p class="intro__tag">Consumable Management System</p>
            <div class="intro__bar"><span></span></div>
        </div>
    </div>

    <main class="shell-in split-shell w-full max-w-4xl bg-white/95 backdrop-blur-xl rounded-3xl p-3 sm:p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-0 items-stretch">

            <!-- Left Panel -->
            <div class="md:col-span-6 bg-gradient-to-br from-rose-50/90 via-indigo-50/75 to-blue-50/80 rounded-2xl p-6 sm:p-7 relative overflow-hidden flex flex-col justify-between min-h-[280px] md:min-h-[540px] border border-rose-100/60">

                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-slate-200/70 flex items-center justify-center overflow-hidden p-1.5">
                            <img src="{{ asset('logo/pict.png') }}" alt="PICONS Logo" class="w-full h-full object-contain">
                        </div>
                        <div class="leading-tight">
                            <span class="block text-sm font-bold text-slate-900 tracking-tight">PICONS</span>
                            <span class="block text-[11px] font-medium text-rose-500">Consumable Management</span>
                        </div>
                    </div>
                    <span class="hidden sm:inline-flex items-center gap-1.5 text-[11px] font-medium text-slate-600 bg-white/80 px-2.5 py-1 rounded-full border border-white">
                        <span class="pulse-dot w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        System Ready
                    </span>
                </div>

                <div class="relative z-10 my-auto py-5 space-y-3">
                    <div class="float-a ml-auto mr-1 sm:mr-4 w-48 sm:w-52 bg-white/95 rounded-2xl p-3.5 shadow-lg shadow-slate-900/5 border border-white" style="--r: 2deg; transform: rotate(2deg)">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-semibold text-slate-700">Stock Movement</span>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">+<span id="countup" data-to="14.2">14.2</span>%</span>
                        </div>
                        <div class="flex items-end justify-between gap-2 h-16 px-1 pt-1">
                            <div class="bar-grow flex-1 bg-rose-100 rounded-t-md h-7" style="--b:0"></div>
                            <div class="bar-grow flex-1 bg-rose-200 rounded-t-md h-11" style="--b:1"></div>
                            <div class="bar-grow flex-1 bg-indigo-200 rounded-t-md h-9" style="--b:2"></div>
                            <div class="bar-grow flex-1 bg-rose-400 rounded-t-md h-14" style="--b:3"></div>
                            <div class="bar-grow flex-1 bg-rose-500 rounded-t-md h-12" style="--b:4"></div>
                        </div>
                    </div>

                    <div class="float-b mr-auto ml-1 sm:ml-3 w-44 bg-white/90 rounded-xl px-3 py-2.5 shadow-md shadow-slate-900/5 border border-white flex items-center gap-2.5" style="--r: -2deg; transform: rotate(-2deg)">
                        <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-500 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div class="leading-tight">
                            <p class="text-[11px] font-semibold text-slate-800">Inventory Sync</p>
                            <p class="text-[10px] text-slate-400">Real-time tracking</p>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 bg-white/85 backdrop-blur-md rounded-2xl px-4 py-3.5 border border-white shadow-sm">
                    <p class="text-xs font-semibold text-slate-900">Streamline Consumable Operations</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Monitor stock levels, track requests, and keep terminal supplies organized in one place.
                    </p>
                </div>

                <div class="w-52 h-52 rounded-full bg-rose-200/35 blur-3xl absolute -bottom-14 -left-14 pointer-events-none"></div>
                <div class="w-44 h-44 rounded-full bg-indigo-200/40 blur-3xl absolute top-12 -right-12 pointer-events-none"></div>
            </div>

            <!-- Right Panel -->
            <div class="md:col-span-6 flex flex-col justify-center px-5 sm:px-9 md:px-11 py-7 md:py-9">
                <div class="w-full max-w-sm mx-auto">
                    <div class="reveal mb-6" style="--d: 3">
                        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">Welcome back</h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">Sign in to access your consumable dashboard.</p>
                    </div>

                    @if ($errors->any())
                        <div role="alert" class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm px-3.5 py-2.5 flex items-start gap-2.5">
                            <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <div class="reveal" style="--d: 4">
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="name@company.com"
                                       class="input-modern w-full rounded-xl bg-slate-50/70 border border-slate-200/90 focus:border-rose-400 focus:ring-0 text-sm text-slate-900 placeholder-slate-400 pl-10 pr-3.5 py-2.5 outline-none">
                            </div>
                        </div>

                        <div class="reveal" style="--d: 5">
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Enter your password"
                                       class="input-modern w-full rounded-xl bg-slate-50/70 border border-slate-200/90 focus:border-rose-400 focus:ring-0 text-sm text-slate-900 placeholder-slate-400 pl-10 pr-10 py-2.5 outline-none">
                                <button type="button" id="togglePasswordBtn" onclick="togglePassword()" aria-label="Toggle password visibility" aria-pressed="false"
                                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer transition-colors">
                                    <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <p id="capsWarn" class="hidden text-[11px] text-amber-600 mt-1.5">Caps Lock is on.</p>
                        </div>

                        <div class="reveal flex items-center justify-between pt-0.5" style="--d: 6">
                            <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}
                                       class="w-3.5 h-3.5 rounded border-slate-300 text-rose-500 focus:ring-rose-500/20 cursor-pointer">
                                <span>Remember me</span>
                            </label>
                            <a href="{{ Route::has('password.request') ? route('password.request') : '#' }}" class="text-xs font-medium text-rose-500 hover:text-rose-600 transition-colors">Forgot password?</a>
                        </div>

                        <button id="submitBtn" type="submit"
                                class="reveal btn-coral w-full text-white font-semibold py-2.5 rounded-xl text-sm shadow-md shadow-rose-500/25 mt-2 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                style="--d: 7">
                            <svg id="spinner" class="hidden w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            <span id="submitText">Log In</span>
                        </button>
                    </form>

                    <p class="reveal text-center text-[11px] text-slate-400 mt-7" style="--d: 8">
                        &copy; {{ date('Y') }} PICONS System &middot; Consumable Management
                    </p>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Intro: tirai terbuka setelah progres selesai
        (function () {
            var el = document.getElementById('intro'), root = document.documentElement;
            if (!root.classList.contains('intro-on')) { el.remove(); return; }
            try { sessionStorage.setItem('picons-intro', '1'); } catch (e) {}
            setTimeout(function () { el.classList.add('is-leaving'); }, 2400);
            setTimeout(function () {
                root.classList.remove('intro-on'); // memicu animasi halaman
                el.remove();
                var f = document.getElementById('email'); if (f) f.focus();
            }, 3300);
        })();

        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            const btn = document.getElementById('togglePasswordBtn');
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.setAttribute('aria-pressed', String(isHidden));
            icon.innerHTML = isHidden
                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.242M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>'
                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
        }

        document.getElementById('password').addEventListener('keyup', function (e) {
            var on = e.getModifierState && e.getModifierState('CapsLock');
            document.getElementById('capsWarn').classList.toggle('hidden', !on);
        });

        document.getElementById('loginForm').addEventListener('submit', function () {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('spinner').classList.remove('hidden');
            document.getElementById('submitText').textContent = 'Signing in...';
        });
    </script>
</body>
</html>