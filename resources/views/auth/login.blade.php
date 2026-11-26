<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Rentalin</title>
    <script>
        (function() {
            const savedTheme = localStorage.getItem('rentalin_theme');
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['"Plus Jakarta Sans"', 'sans-serif'] },
                    colors: {
                        brand: {
                            pink: '#FF6B8B',
                            pinkHover: '#F25577',
                            yellow: '#F3C64F',
                            yellowSoft: '#FDF5DF',
                            pinkSoft: '#FFE8ED',
                            canvas: '#F6F6F6',
                            ink: '#1E1E1E',
                            darkBg: '#121316',
                            darkCard: '#1B1D22',
                            darkBorder: '#2A2D35',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        html.dark body { background-color: #121316; color: #F3F4F6; }
        html.dark .bg-white { background-color: #1B1D22 !important; }
        html.dark .bg-brand-canvas { background-color: #16181D !important; }
        html.dark .text-brand-ink { color: #F9FAFB !important; }
        html.dark .text-gray-500 { color: #9CA3AF !important; }
        html.dark .border-gray-200 { border-color: #2A2D35 !important; }
        html.dark .border-gray-100 { border-color: transparent !important; }
        html.dark input { background-color: #121316 !important; color: #F9FAFB !important; border-color: #2A2D35 !important; }
    </style>
</head>
<body class="bg-brand-canvas text-brand-ink font-sans antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden transition-colors duration-300">

    <!-- Dekorasi Lingkaran Latar Belakang -->
    <div class="absolute -left-20 -top-20 w-72 h-72 sm:w-96 sm:h-96 rounded-full bg-brand-yellow/80 pointer-events-none"></div>
    <div class="absolute -right-20 -bottom-24 w-80 h-80 sm:w-[26rem] sm:h-[26rem] rounded-full bg-brand-pink/80 pointer-events-none"></div>

    <!-- Tombol Toggle Tema di Pojok Kanan Atas -->
    <button type="button" onclick="toggleThemeMode()" title="Mode Siang / Malam"
        class="fixed top-5 right-5 z-20 w-10 h-10 rounded-full bg-white/90 backdrop-blur-xs border border-gray-200 shadow-xs flex items-center justify-center text-brand-ink transition">
        <i class="bi bi-moon-fill dark:hidden text-xs"></i>
        <i class="bi bi-sun-fill hidden dark:inline text-brand-yellow text-xs"></i>
    </button>

    <!-- Kartu Login Utama -->
    <div class="relative z-10 w-full max-w-md bg-white rounded-[2rem] shadow-2xl border border-gray-100 p-7 sm:p-9">
        <!-- Logo & Header -->
        <div class="text-center mb-7">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-brand-yellow text-[#1E1E1E] text-2xl mb-4 shadow-xs hover:scale-105 transition-transform">
                <i class="bi bi-car-front-fill"></i>
            </a>
            <h1 class="text-2xl font-extrabold text-brand-ink tracking-tight">Admin Rentalin</h1>
            <p class="text-xs text-gray-500 mt-1">Masuk untuk mengelola armada, jadwal sewa, dan konfirmasi nota</p>
        </div>

        <!-- Notifikasi Error Login -->
        @if($errors->any())
            <div class="mb-5 bg-brand-pinkSoft border border-brand-pink/30 text-brand-pink px-4 py-3 rounded-2xl text-xs font-bold flex items-center gap-2">
                <i class="bi bi-exclamation-circle-fill text-sm flex-shrink-0"></i>
                <span>{{ $errors->first() }}</span>
            </div>
        @endif

        <!-- Form Login -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-xs font-extrabold text-brand-ink mb-1.5">Email Administrator</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 text-sm">
                        <i class="bi bi-envelope"></i>
                    </span>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="admin@rentalin.id"
                        class="w-full border border-gray-200 rounded-2xl pl-11 pr-4 py-3 text-xs font-semibold text-brand-ink focus:outline-none focus:border-brand-pink focus:ring-4 focus:ring-brand-pink/15 transition">
                </div>
            </div>

            <div>
                <label for="password" class="block text-xs font-extrabold text-brand-ink mb-1.5">Kata Sandi</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 text-sm">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" required
                        placeholder="••••••••"
                        class="w-full border border-gray-200 rounded-2xl pl-11 pr-11 py-3 text-xs font-semibold text-brand-ink focus:outline-none focus:border-brand-pink focus:ring-4 focus:ring-brand-pink/15 transition">
                    <button type="button" onclick="togglePasswordVisibility()" aria-label="Lihat Kata Sandi"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-brand-ink text-sm transition">
                        <i id="eye-icon" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-gray-300 accent-brand-pink">
                    <span class="text-xs font-semibold text-gray-500">Ingat sesi saya</span>
                </label>

                <a href="{{ route('home') }}" class="text-xs font-bold text-gray-400 hover:text-brand-pink transition">
                    &larr; Beranda Publik
                </a>
            </div>

            <button type="submit"
                class="w-full mt-2 py-3.5 rounded-full bg-brand-pink hover:bg-brand-pinkHover text-white text-xs font-extrabold shadow-sm transition flex items-center justify-center gap-2">
                <span>Masuk ke Dashboard</span>
                <i class="bi bi-arrow-right"></i>
            </button>
        </form>
    </div>

    <script>
        function toggleThemeMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('rentalin_theme', isDark ? 'dark' : 'light');
        }

        function togglePasswordVisibility() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</body>
</html>