/**
 * Date picker aksesibel SAKTI (B-09).
 *
 * Bagian logika murni (parse, format, rentang, navigasi keyboard) dipisahkan
 * dari DOM agar dapat diuji dengan `node --test`.
 *
 * Nilai yang dipertukarkan selalu memakai format YYYY-MM-DD, baik dari
 * ketikan pengguna maupun dari kalender visual.
 */

export const RANGE_MESSAGE =
    'Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.';

export const FORMAT_MESSAGE =
    'Format tanggal harus YYYY-MM-DD (contoh: 2026-11-18).';

export const REQUIRED_MESSAGE =
    'Tanggal mulai dan tanggal selesai wajib diisi.';

const WEEKDAY_LABELS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

export const pad2 = (value) => String(value).padStart(2, '0');

export function daysInMonth(year, monthIndex) {
    return new Date(Date.UTC(year, monthIndex + 1, 0)).getUTCDate();
}

export function toIsoDate(date) {
    return [
        date.getUTCFullYear(),
        pad2(date.getUTCMonth() + 1),
        pad2(date.getUTCDate()),
    ].join('-');
}

/**
 * Parse teks tanggal YYYY-MM-DD menjadi Date (UTC).
 * Mengembalikan null untuk format salah atau tanggal yang tidak ada
 * (contoh: 2026-02-30).
 */
export function parseIsoDate(value) {
    if (typeof value !== 'string') {
        return null;
    }

    const match = /^(\d{4})-(\d{2})-(\d{2})$/.exec(value.trim());

    if (!match) {
        return null;
    }

    const year = Number(match[1]);
    const month = Number(match[2]);
    const day = Number(match[3]);

    if (month < 1 || month > 12) {
        return null;
    }

    if (day < 1 || day > daysInMonth(year, month - 1)) {
        return null;
    }

    return new Date(Date.UTC(year, month - 1, day));
}

export function compareIsoDates(first, second) {
    const a = parseIsoDate(first);
    const b = parseIsoDate(second);

    if (!a || !b) {
        return null;
    }

    return Math.sign(a.getTime() - b.getTime());
}

export function isRangeValid(startValue, endValue) {
    return validateDateRange(startValue, endValue).valid;
}

/**
 * Validasi lengkap rentang tanggal untuk dipakai UI dan pengujian.
 *
 * Kode hasil: required | format | range | ok
 */
export function validateDateRange(startValue, endValue) {
    const startText = String(startValue ?? '').trim();
    const endText = String(endValue ?? '').trim();

    if (startText === '' || endText === '') {
        return { valid: false, code: 'required', message: REQUIRED_MESSAGE };
    }

    const start = parseIsoDate(startText);
    const end = parseIsoDate(endText);

    if (!start || !end) {
        return { valid: false, code: 'format', message: FORMAT_MESSAGE };
    }

    if (end.getTime() < start.getTime()) {
        return { valid: false, code: 'range', message: RANGE_MESSAGE };
    }

    return { valid: true, code: 'ok', message: '' };
}

export function addDays(date, days) {
    return new Date(Date.UTC(
        date.getUTCFullYear(),
        date.getUTCMonth(),
        date.getUTCDate() + days
    ));
}

export function addMonths(date, months) {
    const target = new Date(Date.UTC(
        date.getUTCFullYear(),
        date.getUTCMonth() + months,
        1
    ));

    const day = Math.min(
        date.getUTCDate(),
        daysInMonth(target.getUTCFullYear(), target.getUTCMonth())
    );

    return new Date(Date.UTC(
        target.getUTCFullYear(),
        target.getUTCMonth(),
        day
    ));
}

/**
 * Awal minggu (Senin) pada pukul 00:00 UTC.
 */
export function startOfWeek(date) {
    const offset = (date.getUTCDay() + 6) % 7;
    return addDays(date, -offset);
}

export function endOfWeek(date) {
    return addDays(startOfWeek(date), 6);
}

/**
 * Matriks 6 x 7 tanggal untuk satu bulan (Senin sebagai hari pertama).
 * Sel di luar bulan bernilai null.
 *
 * @returns {Array<Date|null>}
 */
export function buildMonthMatrix(year, monthIndex) {
    const firstDay = new Date(Date.UTC(year, monthIndex, 1));
    const offset = (firstDay.getUTCDay() + 6) % 7;
    const total = daysInMonth(year, monthIndex);
    const cells = [];

    for (let index = 0; index < 42; index++) {
        const day = index - offset + 1;

        cells.push(day >= 1 && day <= total
            ? new Date(Date.UTC(year, monthIndex, day))
            : null);
    }

    return cells;
}

export function monthLabel(year, monthIndex) {
    const label = new Intl.DateTimeFormat('id-ID', {
        month: 'long',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(new Date(Date.UTC(year, monthIndex, 1)));

    return label.charAt(0).toUpperCase() + label.slice(1);
}

export function formatLongDate(date) {
    return new Intl.DateTimeFormat('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        timeZone: 'UTC',
    }).format(date);
}

export function todayIsoDate(now = new Date()) {
    return [
        now.getFullYear(),
        pad2(now.getMonth() + 1),
        pad2(now.getDate()),
    ].join('-');
}

/**
 * Terjemahkan tombol keyboard pada kalender menjadi aksi.
 */
export function resolveDateKey(key, state = {}) {
    const { shiftKey = false } = state;

    switch (key) {
        case 'ArrowLeft':
            return { type: 'move', days: -1 };
        case 'ArrowRight':
            return { type: 'move', days: 1 };
        case 'ArrowUp':
            return { type: 'move', days: -7 };
        case 'ArrowDown':
            return { type: 'move', days: 7 };
        case 'Home':
            return { type: 'weekStart' };
        case 'End':
            return { type: 'weekEnd' };
        case 'PageUp':
            return { type: 'moveMonth', months: shiftKey ? -12 : -1 };
        case 'PageDown':
            return { type: 'moveMonth', months: shiftKey ? 12 : 1 };
        case 'Enter':
        case ' ':
            return { type: 'select' };
        case 'Escape':
            return { type: 'close', restoreFocus: true };
        case 'Tab':
            return { type: 'close', allowTab: true, restoreFocus: false };
        default:
            return { type: 'none' };
    }
}

const readyAttribute = 'data-datepicker-ready';
let uniqueId = 0;

function createTrigger() {
    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.setAttribute('data-datepicker-trigger', '');
    trigger.setAttribute('aria-haspopup', 'dialog');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.className = 'sakti-datepicker-trigger';

    trigger.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M8 7V3m8 4V3M3.5 9.5h17M5 5h14a1.5 1.5 0 011.5 1.5v13A1.5 1.5 0 0119 21H5a1.5 1.5 0 01-1.5-1.5v-13A1.5 1.5 0 015 5z" />
        </svg>`;

    return trigger;
}

function createPopover(input, calendarId) {
    const popover = document.createElement('div');
    const wrapperLabel = input.closest('[data-sakti-datepicker]')?.dataset.datepickerLabel;

    popover.className = 'sakti-datepicker-popover';
    popover.id = calendarId;
    popover.setAttribute('role', 'dialog');
    popover.setAttribute('aria-modal', 'false');
    popover.setAttribute('aria-label', `Kalender ${wrapperLabel || inputLabel(input)}`);
    popover.hidden = true;

    popover.innerHTML = `
        <div class="sakti-datepicker-header">
            <button type="button" data-datepicker-prev aria-label="Bulan sebelumnya">&lsaquo;</button>
            <p class="sakti-datepicker-caption" data-datepicker-caption aria-live="polite"></p>
            <button type="button" data-datepicker-next aria-label="Bulan berikutnya">&rsaquo;</button>
        </div>
        <table class="sakti-datepicker-grid" role="grid" aria-label="Tanggal">
            <thead>
                <tr role="row">${WEEKDAY_LABELS.map((label) => `<th scope="col" role="columnheader" abbr="${label}">${label}</th>`).join('')}</tr>
            </thead>
            <tbody data-datepicker-days></tbody>
        </table>
        <div class="sakti-datepicker-footer">
            <button type="button" data-datepicker-today>Hari ini</button>
            <button type="button" data-datepicker-clear>Kosongkan</button>
        </div>`;

    return popover;
}

function namedInput(input, name) {
    if (!name) {
        return null;
    }

    if (input.form) {
        const element = input.form.elements.namedItem(name);
        if (element instanceof HTMLInputElement) {
            return element;
        }
    }

    return document.querySelector(`input[name="${name.replace(/"/g, '\\"')}"]`);
}

function inputLabel(input) {
    if (input.id) {
        const label = document.querySelector(`label[for="${input.id}"]`);

        if (label && label.textContent.trim() !== '') {
            return label.textContent.replace(/\s+/g, ' ').trim();
        }
    }

    return input.dataset.datepickerLabel || input.getAttribute('aria-label') || input.name || 'tanggal';
}

function dateLimits(input) {
    return {
        min: input.min || null,
        max: input.max || null,
    };
}

function isDateDisabled(iso, input) {
    const { min, max } = dateLimits(input);

    return Boolean((min && iso < min) || (max && iso > max));
}

function initDateInput(input) {
    if (input.getAttribute(readyAttribute) === '1' || input.disabled) {
        return;
    }

    input.setAttribute(readyAttribute, '1');

    let wrapper = input.closest('[data-sakti-datepicker]');

    if (!wrapper) {
        wrapper = document.createElement('div');
        wrapper.className = 'sakti-datepicker sakti-datepicker-native relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
    }

    let trigger = wrapper.querySelector('[data-datepicker-trigger]');

    if (!trigger) {
        trigger = createTrigger();
        input.insertAdjacentElement('afterend', trigger);
    }

    if (!trigger.hasAttribute('aria-label')) {
        trigger.setAttribute('aria-label', `Buka kalender ${inputLabel(input)}`);
    }

    uniqueId++;
    const calendarId = `${input.id || `sakti-date-${uniqueId}`}-kalender`;

    if (!input.id) {
        input.id = `sakti-date-${uniqueId}`;
    }

    const popover = createPopover(input, calendarId);
    (trigger.parentElement ?? wrapper).appendChild(popover);

    trigger.setAttribute('aria-controls', calendarId);

    const caption = popover.querySelector('[data-datepicker-caption]');
    const daysBody = popover.querySelector('[data-datepicker-days]');
    const prevButton = popover.querySelector('[data-datepicker-prev]');
    const nextButton = popover.querySelector('[data-datepicker-next]');
    const todayButton = popover.querySelector('[data-datepicker-today]');
    const clearButton = popover.querySelector('[data-datepicker-clear]');
    const rangeError = wrapper.querySelector('[data-date-range-error]');

    const today = () => parseIsoDate(todayIsoDate()) ?? new Date();
    let focusDate = parseIsoDate(input.value) ?? today();
    let viewDate = focusDate;

    function focusDay(iso = toIsoDate(focusDate)) {
        const button = daysBody.querySelector(`button[data-date="${iso}"]:not(:disabled)`);

        if (button) {
            button.focus();
            focusDate = parseIsoDate(iso) ?? focusDate;
            return true;
        }

        return false;
    }

    function render() {
        const year = viewDate.getUTCFullYear();
        const monthIndex = viewDate.getUTCMonth();
        const selectedIso = input.value;

        caption.textContent = monthLabel(year, monthIndex);
        daysBody.innerHTML = '';

        const cells = buildMonthMatrix(year, monthIndex);

        for (let week = 0; week < 6; week++) {
            const row = document.createElement('tr');
            row.setAttribute('role', 'row');

            for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
                const date = cells[week * 7 + dayIndex];
                const cell = document.createElement('td');
                cell.setAttribute('role', 'gridcell');

                if (!date) {
                    cell.className = 'sakti-datepicker-empty';
                    cell.setAttribute('aria-hidden', 'true');
                    row.appendChild(cell);
                    continue;
                }

                const iso = toIsoDate(date);
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'sakti-datepicker-day';
                button.dataset.date = iso;
                button.textContent = String(date.getUTCDate());
                button.setAttribute('aria-label', formatLongDate(date));
                button.setAttribute('aria-selected', iso === selectedIso ? 'true' : 'false');
                button.tabIndex = iso === toIsoDate(focusDate) ? 0 : -1;

                if (iso === selectedIso) {
                    button.classList.add('is-selected');
                }

                if (iso === todayIsoDate()) {
                    button.setAttribute('aria-current', 'date');
                }

                if (isDateDisabled(iso, input)) {
                    button.disabled = true;
                    button.setAttribute('aria-disabled', 'true');
                }

                cell.appendChild(button);
                row.appendChild(cell);
            }

            daysBody.appendChild(row);
        }
    }

    function ensureFocusableDay() {
        if (daysBody.querySelector('button[data-date]:not(:disabled)')) {
            return;
        }

        // Jika seluruh bulan dinonaktifkan, tetap beri satu tombol fokus.
        const first = daysBody.querySelector('button[data-date]');
        if (first) {
            first.tabIndex = 0;
        }
    }

    function open() {
        if (input.disabled) {
            return;
        }

        focusDate = parseIsoDate(input.value) ?? today();
        viewDate = focusDate;
        render();
        ensureFocusableDay();
        popover.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');

        if (!focusDay()) {
            prevButton.focus();
        }
    }

    function close({ restoreFocus = true } = {}) {
        if (popover.hidden) {
            return;
        }

        popover.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');

        if (restoreFocus) {
            trigger.focus();
        }
    }

    function select(iso) {
        input.value = iso;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
        close();
        input.focus();
    }

    function moveFocus(days) {
        focusDate = addDays(focusDate, days);
        viewDate = focusDate;
        render();
        ensureFocusableDay();
        focusDay();
    }

    function moveMonth(months) {
        focusDate = addMonths(focusDate, months);
        viewDate = focusDate;
        render();
        ensureFocusableDay();
        focusDay();
    }

    trigger.addEventListener('click', () => {
        if (popover.hidden) {
            open();
        } else {
            close();
        }
    });

    trigger.addEventListener('keydown', (event) => {
        if (['Enter', ' ', 'ArrowDown', 'ArrowUp'].includes(event.key)) {
            event.preventDefault();
            open();
        }

        if (event.key === 'Escape') {
            close({ restoreFocus: false });
        }
    });

    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            open();
        }

        if (event.key === 'Escape') {
            close({ restoreFocus: false });
        }
    });

    popover.addEventListener('keydown', (event) => {
        const action = resolveDateKey(event.key, { shiftKey: event.shiftKey });

        switch (action.type) {
            case 'move':
                event.preventDefault();
                moveFocus(action.days);
                break;
            case 'weekStart':
                event.preventDefault();
                focusDate = startOfWeek(focusDate);
                viewDate = focusDate;
                render();
                focusDay();
                break;
            case 'weekEnd':
                event.preventDefault();
                focusDate = endOfWeek(focusDate);
                viewDate = focusDate;
                render();
                focusDay();
                break;
            case 'moveMonth':
                event.preventDefault();
                moveMonth(action.months);
                break;
            case 'select': {
                const target = event.target.closest('button[data-date]');
                if (target && !target.disabled) {
                    event.preventDefault();
                    select(target.dataset.date);
                }
                break;
            }
            case 'close':
                if (event.key === 'Tab') {
                    close({ restoreFocus: false });
                } else {
                    event.preventDefault();
                    close({ restoreFocus: action.restoreFocus !== false });
                }
                break;
            default:
                break;
        }
    });

    popover.addEventListener('click', (event) => {
        const day = event.target.closest('button[data-date]');

        if (day && !day.disabled) {
            select(day.dataset.date);
            return;
        }

        if (event.target.closest('[data-datepicker-prev]')) {
            viewDate = addMonths(viewDate, -1);
            focusDate = viewDate;
            render();
            ensureFocusableDay();
            focusDay();
        }

        if (event.target.closest('[data-datepicker-next]')) {
            viewDate = addMonths(viewDate, 1);
            focusDate = viewDate;
            render();
            ensureFocusableDay();
            focusDay();
        }

        if (event.target.closest('[data-datepicker-today]')) {
            focusDate = today();
            viewDate = focusDate;
            render();
            focusDay();
        }

        if (event.target.closest('[data-datepicker-clear]')) {
            input.value = '';
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            input.focus();
        }
    });

    document.addEventListener('mousedown', (event) => {
        if (!popover.hidden && !wrapper.contains(event.target)) {
            close({ restoreFocus: false });
        }
    });

    function clearRangeError() {
        input.setCustomValidity('');
        input.setAttribute('aria-invalid', 'false');

        if (rangeError) {
            rangeError.textContent = '';
            rangeError.classList.add('hidden');
        }
    }

    function showRangeError(message) {
        input.setAttribute('aria-invalid', 'true');

        if (rangeError) {
            rangeError.textContent = message;
            rangeError.classList.remove('hidden');
        }
    }

    function bindRangeValidation() {
        const startName = input.dataset.minField;
        const startInput = namedInput(input, startName);

        if (!startInput) {
            return;
        }

        const validate = () => {
            if (!startInput.value || !input.value) {
                clearRangeError();
                return;
            }

            const result = validateDateRange(startInput.value, input.value);

            if (result.valid) {
                clearRangeError();
                return;
            }

            showRangeError(result.message);

            // Hanya rentang yang salah yang perlu memblokir submit.
            // Format salah pada input date tidak mungkin tersimpan.
            input.setCustomValidity(result.code === 'range' ? result.message : '');
        };

        const syncMin = () => {
            if (startInput.value) {
                input.min = startInput.value;
            } else {
                input.removeAttribute('min');
            }

            validate();
        };

        startInput.addEventListener('input', syncMin);
        startInput.addEventListener('change', syncMin);
        input.addEventListener('input', validate);
        input.addEventListener('change', validate);

        syncMin();
    }

    bindRangeValidation();
}

export function initAll(root = document) {
    root.querySelectorAll('input[type="date"]').forEach(initDateInput);
}

if (typeof document !== 'undefined') {
    const start = () => initAll();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
}

if (typeof window !== 'undefined') {
    window.SaktiDatepicker = {
        initAll,
        parseIsoDate,
        validateDateRange,
    };
}
