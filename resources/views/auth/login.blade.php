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

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background:
                radial-gradient(circle at 12% 18%, rgba(251, 113, 133, 0.14), transparent 40%),
                radial-gradient(circle at 88% 82%, rgba(99, 102, 241, 0.16), transparent 42%),
                linear-gradient(135deg, #f8fafc 0%, #eef2ff 52%, #fff1f2 100%);
            min-height: 100vh;
        }

        .split-shell {
            box-shadow:
                0 28px 65px -18px rgba(15, 23, 42, 0.14),
                0 0 0 1px rgba(255, 255, 255, 0.9);
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

        .btn-coral:active:not(:disabled) {
            transform: translateY(0);
        }

        .input-modern {
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
        }

        .input-modern:focus {
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(244, 63, 94, 0.12);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 sm:p-6 md:p-8 text-slate-800 antialiased">

    <main class="split-shell w-full max-w-4xl bg-white/95 backdrop-blur-xl rounded-3xl p-3 sm:p-4">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-0 items-stretch">

            <!-- Left Panel: Brand & Visual Preview -->
            <div class="md:col-span-6 bg-gradient-to-br from-rose-50/90 via-indigo-50/75 to-blue-50/80 rounded-2xl p-6 sm:p-7 relative overflow-hidden flex flex-col justify-between min-h-[280px] md:min-h-[540px] border border-rose-100/60">

                <!-- Top Brand Header -->
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
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        System Ready
                    </span>
                </div>

                <!-- Floating UI Cards -->
                <div class="relative z-10 my-auto py-5 space-y-3">
                    <!-- Mini Analytics Card -->
                    <div class="ml-auto mr-1 sm:mr-4 w-48 sm:w-52 bg-white/95 rounded-2xl p-3.5 shadow-lg shadow-slate-900/5 border border-white rotate-2">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-[11px] font-semibold text-slate-700">Stock Movement</span>
                            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-md">+14.2%</span>
                        </div>
                        <div class="flex items-end justify-between gap-2 h-16 px-1 pt-1">
                            <div class="flex-1 bg-rose-100 rounded-t-md h-7"></div>
                            <div class="flex-1 bg-rose-200 rounded-t-md h-11"></div>
                            <div class="flex-1 bg-indigo-200 rounded-t-md h-9"></div>
                            <div class="flex-1 bg-rose-400 rounded-t-md h-14"></div>
                            <div class="flex-1 bg-rose-500 rounded-t-md h-12"></div>
                        </div>
                    </div>

                    <!-- Mini Status Pill -->
                    <div class="mr-auto ml-1 sm:ml-3 w-44 bg-white/90 rounded-xl px-3 py-2.5 shadow-md shadow-slate-900/5 border border-white -rotate-2 flex items-center gap-2.5">
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

                <!-- Bottom Value Proposition -->
                <div class="relative z-10 bg-white/85 backdrop-blur-md rounded-2xl px-4 py-3.5 border border-white shadow-sm">
                    <p class="text-xs font-semibold text-slate-900">Streamline Consumable Operations</p>
                    <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">
                        Monitor stock levels, track requests, and keep terminal supplies organized in one place.
                    </p>
                </div>

                <!-- Soft Ambient Accents -->
                <div class="w-52 h-52 rounded-full bg-rose-200/35 blur-3xl absolute -bottom-14 -left-14 pointer-events-none"></div>
                <div class="w-44 h-44 rounded-full bg-indigo-200/40 blur-3xl absolute top-12 -right-12 pointer-events-none"></div>
            </div>

            <!-- Right Panel: Login Form -->
            <div class="md:col-span-6 flex flex-col justify-center px-5 sm:px-9 md:px-11 py-7 md:py-9">
                <div class="w-full max-w-sm mx-auto">
                    <div class="mb-6">
                        <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight">
                            Welcome back
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1">
                            Sign in to access your consumable dashboard.
                        </p>
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

                        <!-- Email Field -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email Address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    type="email"
                                    name="email"
                                    value="{{ old('email') }}"
                                    required
                                    autofocus
                                    autocomplete="email"
                                    placeholder="name@company.com"
                                    class="input-modern w-full rounded-xl bg-slate-50/70 border border-slate-200/90 focus:border-rose-400 focus:ring-0 text-sm text-slate-900 placeholder-slate-400 pl-10 pr-3.5 py-2.5 outline-none"
                                >
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Enter your password"
                                    class="input-modern w-full rounded-xl bg-slate-50/70 border border-slate-200/90 focus:border-rose-400 focus:ring-0 text-sm text-slate-900 placeholder-slate-400 pl-10 pr-10 py-2.5 outline-none"
                                >
                                <button
                                    type="button"
                                    id="togglePasswordBtn"
                                    onclick="togglePassword()"
                                    aria-label="Toggle password visibility"
                                    aria-pressed="false"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer transition-colors"
                                >
                                    <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="flex items-center justify-between pt-0.5">
                            <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                    class="w-3.5 h-3.5 rounded border-slate-300 text-rose-500 focus:ring-rose-500/20 cursor-pointer"
                                >
                                <span>Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-medium text-rose-500 hover:text-rose-600 transition-colors">
                                    Forgot password?
                                </a>
                            @else
                                <a href="#" class="text-xs font-medium text-rose-500 hover:text-rose-600 transition-colors">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <!-- Submit Button -->
                        <button
                            id="submitBtn"
                            type="submit"
                            class="btn-coral w-full text-white font-semibold py-2.5 rounded-xl text-sm shadow-md shadow-rose-500/25 mt-2 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <span id="submitText">Log In</span>
                        </button>
                    </form>

                    <!-- Footer -->
                    <p class="text-center text-[11px] text-slate-400 mt-7">
                        &copy; {{ date('Y') }} PICONS System &middot; Consumable Management
                    </p>
                </div>
            </div>

        </div>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            const btn = document.getElementById('togglePasswordBtn');
            const isHidden = input.type === 'password';

            input.type = isHidden ? 'text' : 'password';
            btn.setAttribute('aria-pressed', String(isHidden));

            if (isHidden) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.242M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('submitText');
            btn.disabled = true;
            text.textContent = 'Signing in...';
        });
    </script>
</body>
</html>