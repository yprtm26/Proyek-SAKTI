/**
 * Time picker aksesibel SAKTI (B-10 A).
 *
 * Logika murni (parse, format, mask, validasi rentang) dipisahkan dari DOM
 * agar dapat diuji secara independen dengan node --test.
 *
 * Format standar yang digunakan adalah 24 jam HH:mm (00:00 - 23:59).
 */

export const FORMAT_MESSAGE = 'Format waktu harus HH:mm 24 jam (contoh: 08:30 atau 14:00).';
export const RANGE_MESSAGE = 'Waktu selesai tidak boleh lebih awal atau sama dengan waktu mulai pada tanggal yang sama.';
export const REQUIRED_MESSAGE = 'Waktu mulai dan waktu selesai wajib diisi.';

export const pad2 = (val) => String(val).padStart(2, '0');

/**
 * Parse string waktu 24 jam HH:mm menjadi objek { hour, minute }.
 * Mengembalikan null jika format atau nilai tidak valid.
 */
export function parseTime(value) {
    if (typeof value !== 'string') {
        return null;
    }

    const trimmed = value.trim();
    const match = /^(\d{2}):(\d{2})$/.exec(trimmed);

    if (!match) {
        return null;
    }

    const hour = Number(match[1]);
    const minute = Number(match[2]);

    if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
        return null;
    }

    return { hour, minute };
}

/**
 * Format jam dan menit menjadi string HH:mm 24 jam.
 */
export function formatTime(hour, minute) {
    if (hour == null || minute == null) {
        return '';
    }

    const h = Number(hour);
    const m = Number(minute);

    if (isNaN(h) || isNaN(m) || h < 0 || h > 23 || m < 0 || m > 59) {
        return '';
    }

    return `${pad2(h)}:${pad2(m)}`;
}

/**
 * Bandingkan dua string waktu HH:mm.
 * Mengembalikan -1 (a < b), 0 (a == b), 1 (a > b), atau null jika salah satu formatnya tidak valid.
 */
export function compareTimes(a, b) {
    const timeA = parseTime(a);
    const timeB = parseTime(b);

    if (!timeA || !timeB) {
        return null;
    }

    const totalMinutesA = timeA.hour * 60 + timeA.minute;
    const totalMinutesB = timeB.hour * 60 + timeB.minute;

    return Math.sign(totalMinutesA - totalMinutesB);
}

/**
 * Mask keyboard otomatis untuk format HH:mm 24 jam.
 * Membantu pengguna mengetik langsung angka tanpa mengetik titik dua secara manual.
 */
export function applyTimeMask(value, isBackspace = false) {
    if (typeof value !== 'string') {
        return '';
    }

    // Ambil hanya angka
    let raw = value.replace(/\D/g, '').slice(0, 4);

    if (raw.length === 0) {
        return '';
    }

    // Validasi digit pertama & kedua (jam 00-23)
    let h1 = raw[0];
    let h2 = raw[1];

    if (raw.length === 1) {
        // Jika digit pertama > 2, otomatis ubah jadi 0X (misal ketik 8 -> 08:)
        if (Number(h1) > 2) {
            return `0${h1}:`;
        }
        return h1;
    }

    let hourNum = Number(raw.slice(0, 2));
    if (hourNum > 23) {
        raw = '23' + raw.slice(2);
    }

    if (raw.length === 2) {
        if (isBackspace) {
            return raw;
        }
        return `${raw}:`;
    }

    // Ada digit menit (digit 3 dan/atau 4)
    let minutesPart = raw.slice(2);
    if (minutesPart.length >= 1) {
        let m1 = minutesPart[0];
        // Menit digit pertama maksimal 5 (00-59)
        if (Number(m1) > 5) {
            minutesPart = '5' + minutesPart.slice(1);
        }
    }

    return `${raw.slice(0, 2)}:${minutesPart.slice(0, 2)}`;
}

/**
 * Validasi rentang waktu mulai dan waktu selesai.
 */
export function validateTimeRange(startTime, endTime, isSameDate = true) {
    const startText = String(startTime ?? '').trim();
    const endText = String(endTime ?? '').trim();

    if (!startText || !endText) {
        return { valid: false, code: 'required', message: REQUIRED_MESSAGE };
    }

    const parsedStart = parseTime(startText);
    const parsedEnd = parseTime(endText);

    if (!parsedStart || !parsedEnd) {
        return { valid: false, code: 'format', message: FORMAT_MESSAGE };
    }

    if (isSameDate) {
        const cmp = compareTimes(startText, endText);
        if (cmp >= 0) {
            return { valid: false, code: 'range', message: RANGE_MESSAGE };
        }
    }

    return { valid: true, code: 'ok', message: '' };
}

// -------------------------------------------------------------
// DOM Inisialisasi & Interaksi
// -------------------------------------------------------------

export function initTimePickers(root = document) {
    const elements = root.querySelectorAll('[data-sakti-timepicker]');
    elements.forEach((container) => {
        if (container.__saktiTimePickerInitialized) {
            return;
        }
        container.__saktiTimePickerInitialized = true;
        attachTimePicker(container);
    });
}

function attachTimePicker(container) {
    const input = container.querySelector('[data-sakti-time-input]');
    const trigger = container.querySelector('[data-timepicker-trigger]');
    if (!input) return;

    let previousVal = input.value;

    // 1. Keyboard mask auto-format titik dua
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace') {
            input.__isBackspace = true;
        } else {
            input.__isBackspace = false;
        }
    });

    input.addEventListener('input', () => {
        const cursor = input.selectionStart;
        const oldLen = input.value.length;
        const masked = applyTimeMask(input.value, !!input.__isBackspace);

        input.value = masked;
        previousVal = masked;

        // Auto move cursor past colon
        if (!input.__isBackspace && masked.length === 3 && masked[2] === ':') {
            input.setSelectionRange(3, 3);
        }
    });

    // 2. Klik tombol trigger membuka time picker bawaan Chrome/browser
    if (!trigger) return;

    trigger.addEventListener('click', () => {
        if (typeof input.showPicker === 'function') {
            try {
                input.showPicker();
            } catch {
                input.focus();
            }
        } else {
            input.focus();
        }
    });
}

// Inisialisasi otomatis jika dijalankan di browser
if (typeof window !== 'undefined' && typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initTimePickers());
    } else {
        initTimePickers();
    }
}
