import test from 'node:test';
import assert from 'node:assert/strict';

import {
    RANGE_MESSAGE,
    addDays,
    addMonths,
    buildMonthMatrix,
    compareIsoDates,
    daysInMonth,
    endOfWeek,
    formatLongDate,
    isRangeValid,
    monthLabel,
    parseIsoDate,
    resolveDateKey,
    startOfWeek,
    toIsoDate,
    todayIsoDate,
    validateDateRange,
} from '../../public/js/datepicker.js';

test('parseIsoDate hanya menerima teks YYYY-MM-DD yang benar', () => {
    assert.equal(toIsoDate(parseIsoDate('2026-11-18')), '2026-11-18');
    assert.equal(toIsoDate(parseIsoDate(' 2026-01-05 ')), '2026-01-05');
    assert.equal(toIsoDate(parseIsoDate('2024-02-29')), '2024-02-29');

    assert.equal(parseIsoDate('18-11-2026'), null);
    assert.equal(parseIsoDate('2026/11/18'), null);
    assert.equal(parseIsoDate('2026-13-01'), null);
    assert.equal(parseIsoDate('2026-02-30'), null);
    assert.equal(parseIsoDate(''), null);
    assert.equal(parseIsoDate(null), null);
});

test('toIsoDate dan todayIsoDate menghasilkan format dua digit', () => {
    assert.equal(toIsoDate(new Date(Date.UTC(2026, 0, 5))), '2026-01-05');
    assert.equal(todayIsoDate(new Date(2026, 10, 8)), '2026-11-08');
});

test('compareIsoDates membandingkan tanggal dan menolak format salah', () => {
    assert.equal(compareIsoDates('2026-01-05', '2026-01-05'), 0);
    assert.equal(compareIsoDates('2026-01-05', '2026-01-06'), -1);
    assert.equal(compareIsoDates('2026-01-07', '2026-01-06'), 1);
    assert.equal(compareIsoDates('2026-01-05', '05/01/2026'), null);
});

test('validateDateRange menolak tanggal selesai sebelum tanggal mulai', () => {
    const result = validateDateRange('2026-05-10', '2026-05-09');

    assert.equal(result.valid, false);
    assert.equal(result.code, 'range');
    assert.equal(result.message, RANGE_MESSAGE);
    assert.equal(isRangeValid('2026-05-10', '2026-05-09'), false);
});

test('validateDateRange menerima tanggal selesai sama dengan tanggal mulai', () => {
    const result = validateDateRange('2026-05-10', '2026-05-10');

    assert.equal(result.valid, true);
    assert.equal(result.code, 'ok');
    assert.equal(isRangeValid('2026-05-10', '2026-05-10'), true);
});

test('validateDateRange membedakan input kosong dan format salah', () => {
    assert.equal(validateDateRange('', '2026-05-10').code, 'required');
    assert.equal(validateDateRange('2026-05-10', '').code, 'required');
    assert.equal(validateDateRange('10-05-2026', '2026-05-11').code, 'format');
});

test('daysInMonth dan addMonths menangani akhir bulan serta tahun kabisat', () => {
    assert.equal(daysInMonth(2024, 1), 29);
    assert.equal(daysInMonth(2026, 1), 28);

    assert.equal(toIsoDate(addMonths(new Date(Date.UTC(2026, 0, 31)), 1)), '2026-02-28');
    assert.equal(toIsoDate(addMonths(new Date(Date.UTC(2024, 0, 31)), 1)), '2024-02-29');
    assert.equal(toIsoDate(addMonths(new Date(Date.UTC(2026, 0, 31)), 12)), '2027-01-31');
    assert.equal(toIsoDate(addMonths(new Date(Date.UTC(2026, 2, 15)), -1)), '2026-02-15');
});

test('navigasi minggu dimulai hari Senin', () => {
    const rabu = parseIsoDate('2026-11-18');

    assert.equal(rabu.getUTCDay(), 3);
    assert.equal(toIsoDate(startOfWeek(rabu)), '2026-11-16');
    assert.equal(toIsoDate(endOfWeek(rabu)), '2026-11-22');
    assert.equal(toIsoDate(addDays(rabu, 3)), '2026-11-21');
});

test('buildMonthMatrix membentuk 6 minggu dengan Senin sebagai kolom pertama', () => {
    const november = buildMonthMatrix(2026, 10);

    assert.equal(november.length, 42);
    assert.equal(toIsoDate(november[6]), '2026-11-01');
    assert.equal(toIsoDate(november[35]), '2026-11-30');
    assert.equal(november[36], null);

    const februari = buildMonthMatrix(2026, 1);

    assert.equal(toIsoDate(februari[6]), '2026-02-01');
    assert.equal(toIsoDate(februari[33]), '2026-02-28');
    assert.equal(februari[34], null);

    const kabisat = buildMonthMatrix(2024, 1);

    assert.equal(toIsoDate(kabisat[3]), '2024-02-01');
    assert.equal(toIsoDate(kabisat[31]), '2024-02-29');
});

test('label bulan dan tanggal panjang memakai bahasa Indonesia', () => {
    assert.equal(monthLabel(2026, 10), 'November 2026');
    assert.match(formatLongDate(parseIsoDate('2026-11-18')), /18 November 2026/);
});

test('tombol panah memindahkan fokus harian dan mingguan', () => {
    assert.deepEqual(resolveDateKey('ArrowLeft'), { type: 'move', days: -1 });
    assert.deepEqual(resolveDateKey('ArrowRight'), { type: 'move', days: 1 });
    assert.deepEqual(resolveDateKey('ArrowUp'), { type: 'move', days: -7 });
    assert.deepEqual(resolveDateKey('ArrowDown'), { type: 'move', days: 7 });
    assert.deepEqual(resolveDateKey('Home'), { type: 'weekStart' });
    assert.deepEqual(resolveDateKey('End'), { type: 'weekEnd' });
});

test('PageUp dan PageDown berpindah bulan, Shift berpindah tahun', () => {
    assert.deepEqual(resolveDateKey('PageUp'), { type: 'moveMonth', months: -1 });
    assert.deepEqual(resolveDateKey('PageDown'), { type: 'moveMonth', months: 1 });
    assert.deepEqual(resolveDateKey('PageUp', { shiftKey: true }), { type: 'moveMonth', months: -12 });
    assert.deepEqual(resolveDateKey('PageDown', { shiftKey: true }), { type: 'moveMonth', months: 12 });
});

test('Enter dan Space memilih, Escape menutup tanpa kehilangan fokus', () => {
    assert.deepEqual(resolveDateKey('Enter'), { type: 'select' });
    assert.deepEqual(resolveDateKey(' '), { type: 'select' });

    assert.deepEqual(resolveDateKey('Escape'), { type: 'close', restoreFocus: true });
});

test('Tab menutup kalender dan membiarkan fokus berpindah', () => {
    assert.deepEqual(resolveDateKey('Tab'), {
        type: 'close',
        allowTab: true,
        restoreFocus: false,
    });
});
