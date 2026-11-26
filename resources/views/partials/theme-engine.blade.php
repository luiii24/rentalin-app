<!-- Fonts & Icons Global -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

<!-- Tailwind CSS Engine (Single Instance) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                fontFamily: {
                    sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                },
                colors: {
                    brand: {
                        pink: '#FF385C',
                        pinkHover: '#E02749',
                        pinkSoft: '#FFF0F3',
                        yellow: '#F3C64F',
                        yellowSoft: '#FEF9E7',
                        canvas: '#F4F5F7',
                        ink: '#1E1E1E',
                    }
                }
            }
        }
    };

    // Fungsi global toggle tema (tersedia di halaman publik maupun admin)
    window.toggleThemeMode = function () {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('rentalin_theme', isDark ? 'dark' : 'light');
    };
    window.toggleTheme = window.toggleThemeMode;
</script>

<style>
    /* =========================================================================
       GLOBAL INPUT & FORM STANDARDIZATION
       ========================================================================= */
    input[type="text"]:not(.pill-search-input),
    input[type="email"]:not(.pill-search-input),
    input[type="password"]:not(.pill-search-input),
    input[type="number"]:not(.pill-search-input),
    input[type="tel"]:not(.pill-search-input),
    input[type="url"]:not(.pill-search-input),
    textarea:not(.pill-search-input),
    select:not(.pill-search-input) {
        background-color: #F4F5F7;
        border: 1.5px solid #E5E7EB;
        color: #1E1E1E;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    input[type="text"]:not(.pill-search-input):focus,
    input[type="email"]:not(.pill-search-input):focus,
    input[type="password"]:not(.pill-search-input):focus,
    input[type="number"]:not(.pill-search-input):focus,
    input[type="tel"]:not(.pill-search-input):focus,
    input[type="url"]:not(.pill-search-input):focus,
    textarea:not(.pill-search-input):focus,
    select:not(.pill-search-input):focus {
        background-color: #FFFFFF;
        border-color: #FF385C;
        outline: none;
        box-shadow: 0 0 0 4px rgba(255, 56, 92, 0.14);
    }

    /* Reset total untuk input di dalam Single Pill Search Bar */
    input.pill-search-input,
    input.pill-search-input:focus,
    input.pill-search-input:hover,
    .rounded-full > input[type="text"],
    .rounded-full > input[type="text"]:focus,
    .rounded-full > input[type="search"],
    .rounded-full > input[type="search"]:focus,
    html.dark input.pill-search-input,
    html.dark input.pill-search-input:focus,
    html.dark .rounded-full > input[type="text"],
    html.dark .rounded-full > input[type="text"]:focus {
        background: transparent !important;
        background-color: transparent !important;
        border: 0 !important;
        outline: none !important;
        box-shadow: none !important;
        -webkit-appearance: none;
    }

    /* =========================================================================
       GLOBAL DARK MODE TOKENS
       ========================================================================= */
    html.dark body { background-color: #121316; color: #F3F4F6; }
    html.dark .bg-white { background-color: #1B1D22 !important; }
    html.dark .bg-white\/95 { background-color: rgba(27, 29, 34, 0.95) !important; }
    html.dark .bg-white\/90 { background-color: rgba(27, 29, 34, 0.9) !important; }
    html.dark .bg-white\/80 { background-color: rgba(27, 29, 34, 0.8) !important; }
    html.dark .bg-brand-canvas { background-color: #16181D !important; }
    html.dark .bg-brand-canvas\/50,
    html.dark .bg-brand-canvas\/40,
    html.dark .bg-brand-canvas\/70 { background-color: rgba(22, 24, 29, 0.7) !important; }
    html.dark .bg-brand-yellowSoft { background-color: rgba(243, 198, 79, 0.14) !important; }
    html.dark .bg-brand-pinkSoft { background-color: rgba(255, 56, 92, 0.15) !important; }
    html.dark .text-brand-ink { color: #F9FAFB !important; }
    html.dark .text-gray-600 { color: #D1D5DB !important; }
    html.dark .text-gray-500 { color: #9CA3AF !important; }
    html.dark .border-gray-200,
    html.dark .border-gray-200\/80 { border-color: #2A2D35 !important; }
    html.dark .border-gray-100,
    html.dark .border-gray-100\/80,
    html.dark .divide-gray-100 > :not([hidden]) ~ :not([hidden]) { border-color: #242730 !important; }

    html.dark input[type="text"]:not(.pill-search-input),
    html.dark input[type="email"]:not(.pill-search-input),
    html.dark input[type="password"]:not(.pill-search-input),
    html.dark input[type="number"]:not(.pill-search-input),
    html.dark input[type="tel"]:not(.pill-search-input),
    html.dark textarea:not(.pill-search-input),
    html.dark select:not(.pill-search-input) {
        background-color: #121316 !important;
        color: #F9FAFB !important;
        border-color: #2A2D35 !important;
    }

    html.dark input[type="text"]:not(.pill-search-input):focus,
    html.dark input[type="number"]:not(.pill-search-input):focus,
    html.dark input[type="tel"]:not(.pill-search-input):focus,
    html.dark textarea:not(.pill-search-input):focus {
        border-color: #FF385C !important;
        box-shadow: 0 0 0 4px rgba(255, 56, 92, 0.18) !important;
    }

    /* =========================================================================
       GLOBAL SCROLLBAR
       ========================================================================= */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.35); border-radius: 9999px; }
    ::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.55); }
</style>