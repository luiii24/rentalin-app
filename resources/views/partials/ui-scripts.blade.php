<script>
    window.toggleThemeMode = window.toggleThemeMode || function () {
        const isDark = document.documentElement.classList.toggle('dark');
        localStorage.setItem('rentalin_theme', isDark ? 'dark' : 'light');
    };

    window.RentalinUI = {
        monthShort: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
        monthFull: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
        dayFull: ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],

        formatDate(dateInput, mode = 'full') {
            if (!dateInput) return '-';
            const cleanIso = String(dateInput).trim().slice(0, 10);
            const d = new Date(cleanIso + 'T00:00:00');
            if (isNaN(d)) return String(dateInput);

            const dayNum = d.getDate();
            const mShort = this.monthShort[d.getMonth()];
            const mFull  = this.monthFull[d.getMonth()];
            const year   = d.getFullYear();
            const dayStr = this.dayFull[d.getDay()];

            if (mode === 'short') return `${dayNum} ${mShort} ${year}`;
            if (mode === 'long')  return `${dayNum} ${mFull} ${year}`;
            if (mode === 'datetime' && String(dateInput).length > 10) {
                const timePart = String(dateInput).trim().slice(11, 16);
                return `${dayNum} ${mShort} ${year} • ${timePart} WIB`;
            }
            return `${dayNum} ${mShort} ${year}, ${dayStr}`;
        },

        formatHumanDate(isoDate) {
            return this.formatDate(isoDate, 'full');
        },

        formatRupiah(amount, withPrefix = true) {
            const num = Number(String(amount || 0).replace(/[^0-9.-]+/g, '')) || 0;
            const formatted = num.toLocaleString('id-ID');
            return withPrefix ? `Rp ${formatted}` : formatted;
        },

        formatPhone(phone) {
            const digits = String(phone || '').replace(/\D/g, '');
            if (digits.length < 9) return phone || '-';
            return digits.replace(/(\d{4})(\d{4})(\d+)/, '$1-$2-$3');
        },

        filterItems({ inputId, clearBtnId, itemSelector, countId, emptyId, extraPredicate }) {
            const input = document.getElementById(inputId);
            const query = (input?.value || '').trim().toLowerCase();
            const clearBtn = clearBtnId ? document.getElementById(clearBtnId) : null;

            if (clearBtn) clearBtn.classList.toggle('hidden', query === '');

            const items = document.querySelectorAll(itemSelector);
            let visibleCount = 0;

            items.forEach(item => {
                const haystack = (item.dataset.search || item.innerText || '').toLowerCase();
                const matchQuery = query === '' || haystack.includes(query);
                const matchExtra = typeof extraPredicate === 'function' ? extraPredicate(item) : true;
                const isVisible = matchQuery && matchExtra;

                item.classList.toggle('hidden', !isVisible);
                if (isVisible) visibleCount++;
            });

            if (countId) {
                const countEl = document.getElementById(countId);
                if (countEl) countEl.innerText = visibleCount;
            }

            if (emptyId) {
                const emptyEl = document.getElementById(emptyId);
                if (emptyEl) emptyEl.classList.toggle('hidden', visibleCount > 0);
            }

            return visibleCount;
        },

        clearSearch(inputId, callbackFn) {
            const input = document.getElementById(inputId);
            if (input) {
                input.value = '';
                input.focus();
            }
            if (typeof callbackFn === 'function') callbackFn();
        },

        toggleAllTableRows(masterCheckbox, tableId) {
            const table = document.getElementById(tableId);
            if (!table) return;
            table.querySelectorAll('tbody tr:not(.hidden) input[type="checkbox"]').forEach(cb => {
                cb.checked = masterCheckbox.checked;
                const tr = cb.closest('tr');
                if (tr) tr.classList.toggle('bg-brand-pinkSoft/25', masterCheckbox.checked);
            });
        },

        sortTable(tableId, colIndex, headerBtn) {
            const table = document.getElementById(tableId);
            if (!table) return;
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => r.children.length > 1 && !r.dataset.emptyRow);
            const currentDir = headerBtn.dataset.dir === 'asc' ? 'desc' : 'asc';

            table.querySelectorAll('thead button').forEach(btn => {
                btn.dataset.dir = '';
                const ind = btn.querySelector('.sort-indicator');
                if (ind) ind.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
            });

            headerBtn.dataset.dir = currentDir;
            const indicator = headerBtn.querySelector('.sort-indicator');
            if (indicator) {
                indicator.innerHTML = currentDir === 'asc'
                    ? '<i class="bi bi-arrow-up text-brand-pink"></i>'
                    : '<i class="bi bi-arrow-down text-brand-pink"></i>';
            }

            rows.sort((a, b) => {
                const cellA = a.children[colIndex];
                const cellB = b.children[colIndex];
                const valA = (cellA?.dataset.sort || cellA?.innerText || '').trim();
                const valB = (cellB?.dataset.sort || cellB?.innerText || '').trim();

                const numA = parseFloat(valA.replace(/[^0-9.-]+/g, ''));
                const numB = parseFloat(valB.replace(/[^0-9.-]+/g, ''));

                if (!isNaN(numA) && !isNaN(numB) && /^\d/.test(valA.replace(/^Rp\s*/i, ''))) {
                    return currentDir === 'asc' ? numA - numB : numB - numA;
                }
                return currentDir === 'asc'
                    ? valA.localeCompare(valB, 'id', { sensitivity: 'base' })
                    : valB.localeCompare(valA, 'id', { sensitivity: 'base' });
            });

            rows.forEach(row => tbody.appendChild(row));
            const emptyRow = tbody.querySelector('tr[data-empty-row]');
            if (emptyRow) tbody.appendChild(emptyRow);
        },

        closeAllDropdowns() {
            document.querySelectorAll('.custom-select-group').forEach(group => {
                const trigger = group.querySelector('.custom-select-trigger');
                const menu = group.querySelector('.custom-select-menu');
                const chevron = trigger?.querySelector('.bi-chevron-down');
                if (menu) menu.classList.add('hidden');
                if (trigger) {
                    trigger.classList.remove('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
                    trigger.classList.add('border-gray-200');
                }
                if (chevron) chevron.classList.remove('rotate-180', 'text-brand-pink');
            });
        },

        toggleDropdown(e, triggerBtn) {
            if (e) e.stopPropagation();
            const group = triggerBtn.closest('.custom-select-group');
            const menu = group.querySelector('.custom-select-menu');
            const chevron = triggerBtn.querySelector('.bi-chevron-down');
            const isOpening = menu.classList.contains('hidden');

            this.closeAllDropdowns();
            this.closeAllDatePickers();

            if (isOpening) {
                menu.classList.remove('hidden');
                triggerBtn.classList.remove('border-gray-200');
                triggerBtn.classList.add('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
                if (chevron) chevron.classList.add('rotate-180', 'text-brand-pink');
            }
        },

        selectOption(optionBtn, value, label) {
            const group = optionBtn.closest('.custom-select-group');
            const targetInput = document.getElementById(group.dataset.target);
            const labelSpan = group.querySelector('.custom-select-label');

            if (targetInput) targetInput.value = value;
            if (labelSpan) labelSpan.innerText = label;

            group.querySelectorAll('.custom-option').forEach(opt => {
                opt.classList.remove('text-brand-pink', 'bg-brand-pinkSoft/40', 'font-bold');
                opt.classList.add('text-brand-ink');
                const check = opt.querySelector('.bi-check2');
                if (check) check.classList.add('hidden');
            });

            optionBtn.classList.remove('text-brand-ink');
            optionBtn.classList.add('text-brand-pink', 'bg-brand-pinkSoft/40', 'font-bold');
            const activeCheck = optionBtn.querySelector('.bi-check2');
            if (activeCheck) activeCheck.classList.remove('hidden');

            this.closeAllDropdowns();

            const callbackName = group.dataset.onchange;
            if (callbackName && typeof window[callbackName] === 'function') {
                window[callbackName](value);
            }
        },

        setSelectValue(inputId, value) {
            const input = document.getElementById(inputId);
            if (!input) return;
            const group = input.closest('.custom-select-group');
            const targetOpt = group?.querySelector(`.custom-option[data-value="${value}"]`);
            if (targetOpt) this.selectOption(targetOpt, value, targetOpt.dataset.label);
        },

        closeAllDatePickers() {
            document.querySelectorAll('.custom-datepicker-group').forEach(group => {
                const card = group.querySelector('.custom-calendar-card');
                const trigger = group.querySelector('.datepicker-trigger');
                const chevron = group.querySelector('.datepicker-chevron');
                if (card) card.classList.add('hidden');
                if (trigger) {
                    trigger.classList.remove('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
                    trigger.classList.add('border-gray-200');
                }
                if (chevron) chevron.classList.remove('rotate-180', 'text-brand-pink');
            });
        },

        toggleDatePicker(e, triggerBtn) {
            if (e) e.stopPropagation();
            const group = triggerBtn.closest('.custom-datepicker-group');
            const card = group.querySelector('.custom-calendar-card');
            const chevron = triggerBtn.querySelector('.datepicker-chevron');
            const isOpening = card.classList.contains('hidden');

            this.closeAllDropdowns();
            this.closeAllDatePickers();

            if (isOpening) {
                const input = document.getElementById(group.dataset.target);
                const currentVal = input?.value || new Date().toISOString().slice(0, 10);
                group.dataset.tempDate = currentVal;
                group.dataset.viewDate = currentVal;
                this.renderCalendar(group);

                card.classList.remove('hidden');
                triggerBtn.classList.remove('border-gray-200');
                triggerBtn.classList.add('border-brand-pink', 'ring-4', 'ring-brand-pink/15');
                if (chevron) chevron.classList.add('rotate-180', 'text-brand-pink');
            }
        },

        renderCalendar(group) {
            const viewIso = group.dataset.viewDate || new Date().toISOString().slice(0, 10);
            const tempIso = group.dataset.tempDate || viewIso;
            const minIso  = group.dataset.min || '';

            const viewObj = new Date(viewIso + 'T00:00:00');
            const year    = viewObj.getFullYear();
            const month   = viewObj.getMonth();

            group.querySelector('.calendar-month-label').innerText = `${this.monthFull[month]} ${year}`;
            const grid = group.querySelector('.calendar-days-grid');
            grid.innerHTML = '';

            const firstDayIndex = new Date(year, month, 1).getDay();
            const daysInMonth   = new Date(year, month + 1, 0).getDate();

            for (let i = 0; i < firstDayIndex; i++) grid.innerHTML += `<div></div>`;

            for (let day = 1; day <= daysInMonth; day++) {
                const mm = String(month + 1).padStart(2, '0');
                const dd = String(day).padStart(2, '0');
                const cellIso = `${year}-${mm}-${dd}`;
                const isDisabled = minIso !== '' && cellIso < minIso;
                const isSelected = cellIso === tempIso;
                const isSunday   = new Date(year, month, day).getDay() === 0;

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.innerText = day;

                if (isDisabled) {
                    btn.disabled = true;
                    btn.className = 'w-8 h-8 mx-auto rounded-full text-gray-300 dark:text-gray-700 cursor-not-allowed flex items-center justify-center';
                } else if (isSelected) {
                    btn.className = 'w-8 h-8 mx-auto rounded-full bg-brand-pink text-white font-extrabold shadow-sm flex items-center justify-center transition transform scale-105';
                } else {
                    btn.className = `w-8 h-8 mx-auto rounded-full hover:bg-brand-pinkSoft/70 flex items-center justify-center transition cursor-pointer ${isSunday ? 'text-brand-pink' : 'text-brand-ink'}`;
                    btn.onclick = () => {
                        group.dataset.tempDate = cellIso;
                        this.renderCalendar(group);
                    };
                }
                grid.appendChild(btn);
            }
        },

        changeMonth(navBtn, delta) {
            const group = navBtn.closest('.custom-datepicker-group');
            const currentView = new Date((group.dataset.viewDate || new Date().toISOString().slice(0, 10)) + 'T00:00:00');
            currentView.setMonth(currentView.getMonth() + delta);
            group.dataset.viewDate = `${currentView.getFullYear()}-${String(currentView.getMonth() + 1).padStart(2, '0')}-01`;
            this.renderCalendar(group);
        },

        confirmDate(doneBtn) {
            const group = doneBtn.closest('.custom-datepicker-group');
            const input = document.getElementById(group.dataset.target);
            const chosenIso = group.dataset.tempDate;

            if (input && chosenIso) {
                input.value = chosenIso;
                group.querySelector('.datepicker-display-text').innerText = this.formatDate(chosenIso, 'full');
            }
            this.closeAllDatePickers();

            const callbackName = group.dataset.onchange;
            if (callbackName && typeof window[callbackName] === 'function') window[callbackName](chosenIso);
        },

        setDateValue(inputId, isoDate) {
            const input = document.getElementById(inputId);
            if (!input || !isoDate) return;
            input.value = isoDate;
            const group = input.closest('.custom-datepicker-group');
            if (group) {
                group.dataset.tempDate = isoDate;
                group.dataset.viewDate = isoDate;
                const labelEl = group.querySelector('.datepicker-display-text');
                if (labelEl) labelEl.innerText = this.formatDate(isoDate, 'full');
            }
        },

        openModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.remove('hidden');
        },

        closeModal(id) {
            const el = document.getElementById(id);
            if (el) el.classList.add('hidden');
        },

        initAll() {
            document.querySelectorAll('[data-format-date]').forEach(el => {
                el.innerText = this.formatDate(el.dataset.formatDate, el.dataset.dateMode || 'short');
            });

            document.querySelectorAll('[data-format-rupiah]').forEach(el => {
                el.innerText = this.formatRupiah(el.dataset.formatRupiah);
            });

            document.querySelectorAll('[data-format-phone]').forEach(el => {
                el.innerText = this.formatPhone(el.dataset.formatPhone);
            });

            document.querySelectorAll('.custom-datepicker-group').forEach(group => {
                const input = document.getElementById(group.dataset.target);
                const labelEl = group.querySelector('.datepicker-display-text');
                if (input && labelEl && input.value) {
                    labelEl.innerText = this.formatDate(input.value, 'full');
                }
            });
        }
    };

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select-group')) RentalinUI.closeAllDropdowns();
        if (!e.target.closest('.custom-datepicker-group')) RentalinUI.closeAllDatePickers();
    });

    RentalinUI.initAll();
</script>