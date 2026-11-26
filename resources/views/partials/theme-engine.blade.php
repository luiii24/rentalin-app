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

    function toggleThemeMode() {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('rentalin_theme', isDark ? 'dark' : 'light');
    }
</script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
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
                        canvas: '#E5E7EF',
                        ink: '#1E1E1E',
                        darkBg: '#111216',
                        darkCard: '#1D1F27',
                        darkItem: '#2A2E3B',
                    }
                }
            }
        }
    }
</script>
<style>
    /* =====================================================================
       1. HAPUS SEMUA BORDER STRUKTURAL (TETAP PERTAHANKAN SPINNER & AKTIF)
       ===================================================================== */
    *:not(.animate-spin):not(.keep-border),
    .divide-y > :not([hidden]) ~ :not([hidden]),
    .divide-x > :not([hidden]) ~ :not([hidden]) {
        border-color: transparent !important;
    }
    .border-brand-pink {
        border-color: #FF6B8B !important;
    }

    /* =====================================================================
       2. MODE SIANG (LIGHT MODE) - HIERARKI ABU-ABU TANPA BORDER
       ===================================================================== */
    body {
        background-color: #EFF1F5 !important;
        color: #1E1E1E;
    }
    .bg-white {
        background-color: #FFFFFF !important;
        box-shadow: 0 2px 12px -2px rgba(15, 23, 42, 0.045);
    }
    .bg-white\/90,
    .bg-white\/95 {
        background-color: rgba(255, 255, 255, 0.92) !important;
    }
    .bg-brand-canvas,
    .bg-gray-50,
    .bg-slate-50 {
        background-color: #E5E7EF !important;
    }
    .bg-gray-100,
    .bg-slate-100 {
        background-color: #DCE0EA !important;
    }
    .hover\:bg-gray-50:hover,
    .hover\:bg-gray-100:hover,
    .hover\:bg-brand-canvas:hover,
    tbody tr:hover {
        background-color: #DCE0EA !important;
    }
    input[type="text"],
    input[type="number"],
    input[type="tel"],
    input[type="email"],
    input[type="password"],
    input[type="date"],
    input[type="search"],
    select,
    textarea {
        background-color: #E5E7EF !important;
        color: #1E1E1E !important;
        transition: background-color 0.2s ease, box-shadow 0.2s ease;
    }
    input:focus,
    select:focus,
    textarea:focus {
        outline: none !important;
        box-shadow: 0 0 0 3px rgba(255, 107, 139, 0.22) !important;
    }

    /* =====================================================================
       3. MODE MALAM (DARK MODE) - HIERARKI ABU-ABU SLATE TANPA BORDER
       ===================================================================== */
    html.dark body {
        background-color: #111216 !important;
        color: #F3F4F6 !important;
    }

    /* Lapisan 1: Kartu Utama, Navbar, Sidebar, Modal */
    html.dark .bg-white {
        background-color: #1D1F27 !important;
        box-shadow: 0 4px 18px -2px rgba(0, 0, 0, 0.38) !important;
    }
    html.dark .bg-white\/90,
    html.dark .bg-white\/95 {
        background-color: rgba(29, 31, 39, 0.92) !important;
    }

    /* Lapisan 2: Item di Dalam Kartu, Input, Header Tabel, Sub-panel */
    html.dark .bg-brand-canvas,
    html.dark .bg-gray-50,
    html.dark .bg-slate-50,
    html.dark thead,
    html.dark thead tr {
        background-color: #2A2E3B !important;
    }
    html.dark .bg-gray-100,
    html.dark .bg-slate-100,
    html.dark .bg-gray-200 {
        background-color: #323746 !important;
    }

    /* Elemen putih bersarang di dalam item abu-abu (contoh: ikon bulat search bar) */
    html.dark .bg-brand-canvas .bg-white,
    html.dark .bg-gray-100 .bg-white,
    html.dark .bg-gray-50 .bg-white {
        background-color: #363B4B !important;
        box-shadow: none !important;
    }

    /* Lapisan 3: Efek Hover & Baris Aktif */
    html.dark .hover\:bg-gray-50:hover,
    html.dark .hover\:bg-gray-100:hover,
    html.dark .hover\:bg-brand-canvas:hover,
    html.dark .hover\:bg-white:hover,
    html.dark tbody tr:hover {
        background-color: #353A4A !important;
    }

    /* Form Input, Select, Textarea di Mode Malam */
    html.dark input[type="text"],
    html.dark input[type="number"],
    html.dark input[type="tel"],
    html.dark input[type="email"],
    html.dark input[type="password"],
    html.dark input[type="date"],
    html.dark input[type="search"],
    html.dark select,
    html.dark textarea {
        background-color: #2A2E3B !important;
        color: #F8FAFC !important;
    }
    html.dark input::placeholder,
    html.dark textarea::placeholder {
        color: #94A3B8 !important;
    }

    /* Hierarki Teks di Mode Malam */
    html.dark .text-brand-ink,
    html.dark .text-gray-900,
    html.dark .text-gray-800,
    html.dark .text-slate-900,
    html.dark .text-slate-800 {
        color: #F8FAFC !important;
    }
    html.dark .text-gray-700,
    html.dark .text-gray-600,
    html.dark .text-slate-700,
    html.dark .text-slate-600 {
        color: #CBD5E1 !important;
    }
    html.dark .text-gray-500,
    html.dark .text-gray-400,
    html.dark .text-slate-500,
    html.dark .text-slate-400 {
        color: #94A3B8 !important;
    }

    /* Badge & Aksen Pastel di Mode Malam (Agar Tidak Silau) */
    html.dark .bg-brand-yellowSoft,
    html.dark .bg-yellow-50,
    html.dark .bg-amber-50,
    html.dark .bg-amber-100 {
        background-color: rgba(243, 198, 79, 0.16) !important;
    }
    html.dark .bg-brand-pinkSoft,
    html.dark .bg-pink-50,
    html.dark .bg-rose-50 {
        background-color: rgba(255, 107, 139, 0.16) !important;
    }
    html.dark .bg-emerald-50,
    html.dark .bg-emerald-100,
    html.dark .bg-green-50,
    html.dark .bg-green-100 {
        background-color: rgba(16, 185, 129, 0.16) !important;
    }
    html.dark .bg-blue-50,
    html.dark .bg-blue-100,
    html.dark .bg-indigo-50 {
        background-color: rgba(59, 130, 246, 0.16) !important;
    }
    html.dark .bg-red-50,
    html.dark .bg-red-100 {
        background-color: rgba(239, 68, 68, 0.16) !important;
    }

    /* Animasi Transisi Halaman SPA-Lite */
    #public-spa-main {
        transition: opacity 0.22s cubic-bezier(0.16, 1, 0.3, 1), transform 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .page-leaving { opacity: 0; transform: translateY(8px); }
    .page-entering { opacity: 0; transform: translateY(-6px); }
    #public-progress-bar { transition: width 0.3s ease, opacity 0.2s ease; }
</style>