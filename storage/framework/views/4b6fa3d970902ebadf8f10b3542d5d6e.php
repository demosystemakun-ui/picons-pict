<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Consumable Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        
        body {
            background: #1e3a8a;
            background-image: 
                radial-gradient(at 0% 0%, rgba(96, 165, 250, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(147, 51, 234, 0.3) 0px, transparent 50%),
                radial-gradient(at 50% 50%, rgba(30, 58, 138, 0.8) 0px, transparent 100%);
            min-height: 100vh;
        }

        /* Grid pattern overlay */
        .grid-pattern {
            background-image: 
                linear-gradient(rgba(255, 255, 255, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }

        .glass-card {
            background: rgba(30, 58, 138, 0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .input-field {
            transition: all 0.2s ease;
        }
        
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(96, 165, 250, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-20px) scale(1.05); }
        }

        .glow-float {
            animation: float 8s ease-in-out infinite;
        }

        .glow-float-delayed {
            animation: float 8s ease-in-out infinite;
            animation-delay: -4s;
        }
    </style>
</head>
<body class="grid-pattern flex flex-col items-center justify-center py-6 px-4 relative min-h-screen overflow-x-hidden">

    <!-- Ambient glow decorations (warna lebih cerah/terang) -->
    <div class="absolute top-[-10%] left-[-5%] w-[500px] h-[500px] bg-blue-400/20 rounded-full blur-[120px] glow-float pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-5%] w-[500px] h-[500px] bg-indigo-400/20 rounded-full blur-[120px] glow-float-delayed pointer-events-none"></div>

    <div class="relative w-full max-w-[420px] z-10 my-auto">
        
        <!-- Brand Header -->
        <div class="flex flex-col items-center mb-6">
            <div class="relative">
                <div class="absolute inset-0 bg-blue-400 rounded-2xl blur-xl opacity-50"></div>
                <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white font-bold text-xl shadow-xl shadow-blue-500/30 border border-blue-300/30">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
            <h1 class="mt-4 text-xl font-bold text-white tracking-tight">Consumable Management</h1>
            <p class="text-sm text-blue-200 mt-0.5">Masuk ke akun Anda untuk melanjutkan</p>
        </div>

        <!-- Login Card -->
        <div class="glass-card rounded-2xl shadow-2xl p-6 sm:p-8 relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-blue-400/60 to-transparent"></div>

            <?php if($errors->any()): ?>
                <div class="mb-5 rounded-xl bg-red-500/15 border border-red-500/30 text-red-200 text-sm px-4 py-3 flex items-start gap-2.5">
                    <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span><?php echo e($errors->first()); ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo e(route('login')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-blue-100 mb-1.5">Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-blue-300 group-focus-within:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input 
                            id="email"
                            type="email" 
                            name="email" 
                            value="<?php echo e(old('email')); ?>" 
                            required 
                            autofocus
                            autocomplete="email"
                            placeholder="admin@company.com"
                            class="input-field w-full rounded-xl bg-blue-950/40 border border-blue-400/30 focus:border-blue-300 focus:ring-0 text-sm text-white placeholder-blue-300/50 pl-11 pr-4 py-2.5 outline-none"
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-sm font-medium text-blue-100 mb-1.5">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-blue-300 group-focus-within:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            id="password"
                            type="password" 
                            name="password" 
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                            class="input-field w-full rounded-xl bg-blue-950/40 border border-blue-400/30 focus:border-blue-300 focus:ring-0 text-sm text-white placeholder-blue-300/50 pl-11 pr-12 py-2.5 outline-none"
                        >
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-blue-300 hover:text-white transition-colors focus:outline-none">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between pt-0.5">
                    <label class="flex items-center gap-2 text-sm text-blue-200 cursor-pointer hover:text-white transition-colors">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-blue-400 bg-blue-950 text-blue-500 focus:ring-blue-400/30 focus:ring-offset-0 cursor-pointer"
                        >
                        <span>Ingat saya</span>
                    </label>
                    <a href="#" class="text-sm text-blue-300 hover:text-white font-medium transition-colors">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit -->
                <button 
                    type="submit" 
                    class="btn-primary w-full text-white font-semibold py-2.5 rounded-xl text-sm shadow-lg shadow-blue-500/30 mt-1"
                >
                    Login
                </button>
            </form>


        <!-- Footer -->
        <p class="text-center text-xs text-blue-300/70 mt-5">
            &copy; 2026 - PICONS System
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.242M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }
    </script>
</body>
</html>
<?php /**PATH D:\laravel-13 - Copy - Copy\resources\views/auth/login.blade.php ENDPATH**/ ?>