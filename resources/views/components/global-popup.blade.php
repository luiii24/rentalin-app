<!-- GLOBAL POP-UP NOTIFICATION & CONFIRMATION -->
<div id="rentalin-global-popup" class="fixed inset-0 bg-black/60 backdrop-blur-xs z-[90] hidden flex items-center justify-center p-4 transition-opacity duration-200">
    <div id="rentalin-popup-card" class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl border border-gray-100 transform scale-95 opacity-0 transition-all duration-200">
        <!-- ANIMASI LOADING TIMER 4 DETIK -->
        <div id="rentalin-popup-progress-wrap" class="w-full h-1.5 bg-brand-canvas overflow-hidden hidden">
            <div id="rentalin-popup-progress-bar" class="h-full bg-brand-pink w-full origin-left"></div>
        </div>

        <div class="p-6 text-center">
            <div id="rentalin-popup-icon-box" class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl bg-emerald-500/15 text-emerald-600">
                <i id="rentalin-popup-icon" class="bi bi-check-circle-fill"></i>
            </div>

            <h3 id="rentalin-popup-title" class="text-base font-extrabold text-brand-ink">Berhasil</h3>
            <p id="rentalin-popup-message" class="text-xs text-gray-500 mt-1.5 leading-relaxed"></p>

            <!-- AREA CUSTOM BUTTON (MAKSIMAL 2 TOMBOL) -->
            <div id="rentalin-popup-buttons" class="mt-5 flex items-center justify-center gap-2.5"></div>
        </div>
    </div>
</div>

<script>
    window.RentalinPopup = (function () {
        let autoCloseTimer = null;

        const TYPE_STYLES = {
            success: {
                boxClass: 'w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl bg-emerald-500/15 text-emerald-600 dark:text-emerald-400',
                iconClass: 'bi bi-check-circle-fill',
                barClass: 'h-full bg-emerald-500 w-full',
                defaultTitle: 'Berhasil',
            },
            failed: {
                boxClass: 'w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl bg-brand-pinkSoft text-brand-pink',
                iconClass: 'bi bi-x-circle-fill',
                barClass: 'h-full bg-brand-pink w-full',
                defaultTitle: 'Gagal',
            },
            error: {
                boxClass: 'w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl bg-brand-pinkSoft text-brand-pink',
                iconClass: 'bi bi-x-circle-fill',
                barClass: 'h-full bg-brand-pink w-full',
                defaultTitle: 'Gagal',
            },
            warning: {
                boxClass: 'w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl bg-amber-500/15 text-amber-600 dark:text-amber-400',
                iconClass: 'bi bi-exclamation-triangle-fill',
                barClass: 'h-full bg-amber-500 w-full',
                defaultTitle: 'Peringatan',
            },
            info: {
                boxClass: 'w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center text-2xl bg-blue-500/15 text-blue-600 dark:text-blue-400',
                iconClass: 'bi bi-info-circle-fill',
                barClass: 'h-full bg-blue-500 w-full',
                defaultTitle: 'Informasi',
            },
        };

        const BUTTON_VARIANTS = {
            primary: 'flex-1 py-2.5 px-4 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition cursor-pointer',
            yellow: 'flex-1 py-2.5 px-4 rounded-full bg-brand-yellow hover:opacity-90 active:scale-95 text-[#1E1E1E] text-xs font-extrabold transition cursor-pointer',
            success: 'flex-1 py-2.5 px-4 rounded-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white text-xs font-extrabold transition cursor-pointer',
            danger: 'flex-1 py-2.5 px-4 rounded-full bg-brand-pink hover:bg-brand-pinkHover active:scale-95 text-white text-xs font-extrabold transition cursor-pointer',
            secondary: 'flex-1 py-2.5 px-4 rounded-full bg-brand-canvas hover:opacity-85 text-brand-ink text-xs font-extrabold transition cursor-pointer',
        };

        function close() {
            if (autoCloseTimer) {
                clearTimeout(autoCloseTimer);
                autoCloseTimer = null;
            }
            const overlay = document.getElementById('rentalin-global-popup');
            const card    = document.getElementById('rentalin-popup-card');
            if (!overlay || !card) return;

            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 180);
        }

        function show(options = {}) {
            if (autoCloseTimer) {
                clearTimeout(autoCloseTimer);
                autoCloseTimer = null;
            }

            const overlay      = document.getElementById('rentalin-global-popup');
            const card         = document.getElementById('rentalin-popup-card');
            const iconBox      = document.getElementById('rentalin-popup-icon-box');
            const iconEl       = document.getElementById('rentalin-popup-icon');
            const titleEl      = document.getElementById('rentalin-popup-title');
            const msgEl        = document.getElementById('rentalin-popup-message');
            const btnWrap      = document.getElementById('rentalin-popup-buttons');
            const progressWrap = document.getElementById('rentalin-popup-progress-wrap');
            const progressBar  = document.getElementById('rentalin-popup-progress-bar');

            if (!overlay || !card) return;

            const type     = TYPE_STYLES[options.type] ? options.type : 'success';
            const styleCfg = TYPE_STYLES[type];
            const duration = Number(options.duration ?? 4000);
            const autoClose = options.autoClose !== false; // default true (4 detik)

            iconBox.className = styleCfg.boxClass;
            iconEl.className  = options.icon || styleCfg.iconClass;
            titleEl.innerText = options.title || styleCfg.defaultTitle;
            msgEl.innerText   = options.message || '';

            // Render maksimal 2 tombol kustom
            const buttons = Array.isArray(options.buttons) ? options.buttons.slice(0, 2) : [];
            btnWrap.innerHTML = '';

            if (buttons.length === 0) {
                btnWrap.classList.add('hidden');
            } else {
                btnWrap.classList.remove('hidden');
                buttons.forEach((btnCfg) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.innerText = btnCfg.text || 'Oke';
                    btn.className = BUTTON_VARIANTS[btnCfg.variant] || BUTTON_VARIANTS.primary;
                    btn.onclick = function () {
                        close();
                        if (typeof btnCfg.onClick === 'function') {
                            btnCfg.onClick();
                        }
                    };
                    btnWrap.appendChild(btn);
                });
            }

            // Tampilkan modal
            overlay.classList.remove('hidden');
            requestAnimationFrame(() => {
                card.classList.remove('scale-95', 'opacity-0');
                card.classList.add('scale-100', 'opacity-100');
            });

            // Animasi progress bar timer 4 detik
            if (autoClose && duration > 0) {
                progressWrap.classList.remove('hidden');
                progressBar.className = styleCfg.barClass;
                progressBar.style.transition = 'none';
                progressBar.style.width = '100%';

                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        progressBar.style.transition = `width ${duration}ms linear`;
                        progressBar.style.width = '0%';
                    });
                });

                autoCloseTimer = setTimeout(() => {
                    close();
                    if (typeof options.onTimeout === 'function') {
                        options.onTimeout();
                    }
                }, duration);
            } else {
                progressWrap.classList.add('hidden');
            }
        }

        function confirm(options = {}) {
            show({
                type: options.type || 'warning',
                title: options.title || 'Konfirmasi Tindakan',
                message: options.message || 'Apakah Anda yakin ingin melanjutkan?',
                autoClose: false,
                buttons: [
                    {
                        text: options.cancelText || 'Tidak',
                        variant: 'secondary',
                        onClick: options.onCancel,
                    },
                    {
                        text: options.confirmText || 'Ya, Lanjutkan',
                        variant: options.confirmVariant || 'danger',
                        onClick: options.onConfirm,
                    },
                ],
            });
        }

        return { show, confirm, close };
    })();
</script>