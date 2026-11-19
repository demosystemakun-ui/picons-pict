<!DOCTYPE html>
<html lang="id">
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
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 48%, #f1f5f9 100%);
            min-height: 100vh;
        }

        .split-shell {
            box-shadow: 0 25px 60px -15px rgba(30, 58, 138, 0.16);
        }

        .btn-coral {
            background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
            transition: all 0.2s ease;
        }

        .btn-coral:hover:not(:disabled) {
            background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 20px -6px rgba(244, 63, 94, 0.45);
        }

        .btn-coral:active:not(:disabled) {
            transform: translateY(0);
        }
    </style>
</head>
<body class="flex items-center justify-center p-4 sm:p-6 md:p-8 text-slate-800 antialiased">

    <main class="split-shell w-full max-w-4xl bg-white rounded-3xl p-3 sm:p-4 md:p-5 border border-white/80">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-0 items-stretch">

            <!-- Kolom Kiri: Panel Brand & Ilustrasi -->
            <div class="md:col-span-6 bg-gradient-to-br from-blue-50 via-indigo-50/70 to-blue-100/60 rounded-2xl p-6 sm:p-7 relative overflow-hidden flex flex-col justify-between min-h-[260px] md:min-h-[520px]">
                <!-- Brand di pojok kiri atas -->
                <div class="relative z-10 flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-white shadow-sm border border-blue-100 flex items-center justify-center overflow-hidden p-1">
                        <img src="{{ asset('logo/pict.png') }}" alt="Logo PICONS" class="w-full h-full object-contain">
                    </div>
                    <div class="leading-tight">
                        <span class="block text-sm font-bold text-slate-900 tracking-tight">PICONS</span>
                        <span class="block text-[11px] font-medium text-blue-600">Consumable Management</span>
                    </div>
                </div>

                <!-- Dekorasi Kartu Grafik & Elemen Visual -->
                <div class="relative z-10 my-auto py-4">
                    <!-- Kartu mini statistik -->
                    <div class="ml-auto mr-2 sm:mr-6 w-40 sm:w-44 bg-white/95 backdrop-blur rounded-xl p-3 shadow-md border border-blue-100/80 rotate-3">
                        <div class="flex items-end justify-between gap-1.5 h-16 px-1">
                            <span class="w-3 h-7 rounded-t bg-blue-200"></span>
                            <span class="w-3 h-12 rounded-t bg-blue-400"></span>
                            <span class="w-3 h-9 rounded-t bg-indigo-300"></span>
                            <span class="w-3 h-15 rounded-t bg-blue-600"></span>
                            <span class="w-3 h-11 rounded-t bg-blue-300"></span>
                        </div>
                        <div class="mt-2.5 space-y-1.5">
                            <div class="h-1.5 w-3/4 rounded bg-slate-200"></div>
                            <div class="h-1.5 w-1/2 rounded bg-slate-100"></div>
                        </div>
                    </div>

                    <!-- Aksen bentuk geometris -->
                    <div class="mt-3 flex items-center gap-3 pl-2">
                        <div class="flex flex-col gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-indigo-300 border border-indigo-400/40"></span>
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-300 border border-blue-400/40"></span>
                        </div>
                        <div class="flex flex-col gap-2">
                            <span class="w-12 h-2 rounded-full bg-indigo-200"></span>
                            <span class="w-16 h-2 rounded-full bg-blue-200"></span>
                        </div>
                    </div>
                </div>

                <!-- Caption bawah panel kiri -->
                <div class="relative z-10 bg-white/80 backdrop-blur-sm rounded-xl px-4 py-3 border border-white">
                    <p class="text-xs font-semibold text-slate-800">Pantau Stok &amp; Permintaan Barang</p>
                    <p class="text-[11px] text-slate-500 mt-0.5">Kelola consumable operasional lebih cepat dan terstruktur.</p>
                </div>

                <!-- Blob dekorasi latar -->
                <div class="w-48 h-48 rounded-full bg-blue-300/25 blur-2xl absolute -bottom-12 -left-12 pointer-events-none"></div>
                <div class="w-40 h-40 rounded-full bg-indigo-300/25 blur-2xl absolute top-10 -right-10 pointer-events-none"></div>
            </div>

            <!-- Kolom Kanan: Form Login -->
            <div class="md:col-span-6 flex flex-col justify-center px-4 sm:px-8 md:px-10 py-6 md:py-8 relative">
                <div class="w-full max-w-sm mx-auto">
                    <h1 class="text-2xl sm:text-[28px] font-bold text-slate-900 tracking-tight mb-6">
                        Login
                    </h1>

                    @if ($errors->any())
                        <div role="alert" class="mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs sm:text-sm px-3.5 py-2.5 flex items-start gap-2">
                            <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    <form id="loginForm" method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Email
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
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
                                    placeholder="nama@perusahaan.com"
                                    class="w-full rounded-lg border border-slate-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-500/15 text-sm text-slate-800 placeholder-slate-400 pl-9 pr-3.5 py-2.5 outline-none transition"
                                >
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Password
                            </label>
                            <div class="relative">
                                <button
                                    type="button"
                                    id="togglePasswordBtn"
                                    onclick="togglePassword()"
                                    aria-label="Tampilkan atau sembunyikan password"
                                    aria-pressed="false"
                                    class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer"
                                >
                                    <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="••••••••"
                                    class="w-full rounded-lg border border-slate-200 focus:border-rose-400 focus:ring-2 focus:ring-rose-500/15 text-sm text-slate-800 placeholder-slate-400 pl-9 pr-3.5 py-2.5 outline-none transition"
                                >
                            </div>
                        </div>

                        <!-- Remember & Forgot Password -->
                        <div class="flex items-center justify-between pt-0.5">
                            <label class="flex items-center gap-2 text-xs text-slate-600 cursor-pointer select-none">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                    class="w-3.5 h-3.5 rounded border-slate-300 text-rose-500 focus:ring-rose-500/20 cursor-pointer"
                                >
                                <span>Ingat saya</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline underline-offset-4 transition">
                                    Lupa password?
                                </a>
                            @else
                                <a href="#" class="text-xs font-medium text-slate-500 hover:text-slate-800 underline underline-offset-4 transition">
                                    Lupa password?
                                </a>
                            @endif
                        </div>

                        <!-- Tombol Login -->
                        <button
                            id="submitBtn"
                            type="submit"
                            class="btn-coral w-full text-white font-semibold py-2.5 rounded-lg text-sm shadow-md shadow-rose-500/25 mt-2 cursor-pointer disabled:opacity-70 disabled:cursor-not-allowed"
                        >
                            <span id="submitText">Log In</span>
                        </button>
                    </form>

                    <!-- Footer kecil -->
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
            text.textContent = 'Memproses...';
        });
    </script>
</body>
</html>