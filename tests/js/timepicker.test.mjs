import test from 'node:test';
import assert from 'node:assert/strict';
import {
    parseTime,
    formatTime,
    compareTimes,
    applyTimeMask,
    validateTimeRange,
    RANGE_MESSAGE,
    FORMAT_MESSAGE,
    REQUIRED_MESSAGE,
} from '../../public/js/timepicker.js';

test('parseTime menerima format 24 jam HH:mm yang valid', () => {
    assert.deepEqual(parseTime('00:00'), { hour: 0, minute: 0 });
    assert.deepEqual(parseTime('08:30'), { hour: 8, minute: 30 });
    assert.deepEqual(parseTime('14:45'), { hour: 14, minute: 45 });
    assert.deepEqual(parseTime('23:59'), { hour: 23, minute: 59 });
    assert.deepEqual(parseTime(' 09:15 '), { hour: 9, minute: 15 });
});

test('parseTime menolak nilai dan format waktu yang tidak valid', () => {
    assert.equal(parseTime('24:00'), null);
    assert.equal(parseTime('25:10'), null);
    assert.equal(parseTime('12:60'), null);
    assert.equal(parseTime('12:99'), null);
    assert.equal(parseTime('8:30'), null); // Wajib 2 digit jam
    assert.equal(parseTime('14:5'), null);
    assert.equal(parseTime('pagi'), null);
    assert.equal(parseTime(''), null);
    assert.equal(parseTime(null), null);
    assert.equal(parseTime(undefined), null);
});

test('formatTime menghasilkan string 24 jam dua digit berpaduan HH:mm', () => {
    assert.equal(formatTime(0, 0), '00:00');
    assert.equal(formatTime(8, 5), '08:05');
    assert.equal(formatTime(14, 30), '14:30');
    assert.equal(formatTime(23, 59), '23:59');
    assert.equal(formatTime(24, 0), '');
    assert.equal(formatTime(12, 60), '');
});

test('compareTimes membandingkan urutan kronologis waktu', () => {
    assert.equal(compareTimes('08:00', '09:00'), -1);
    assert.equal(compareTimes('14:30', '14:30'), 0);
    assert.equal(compareTimes('15:00', '14:59'), 1);
    assert.equal(compareTimes('00:00', '23:59'), -1);
    assert.equal(compareTimes('invalid', '10:00'), null);
});

test('applyTimeMask auto-format titik dua saat mengetik angka', () => {
    // Digit pertama > 2 otomatis jadi 0X:
    assert.equal(applyTimeMask('8'), '08:');
    assert.equal(applyTimeMask('9'), '09:');

    // Jam 0 atau 1 atau 2 belum ditambahkan : sampai digit kedua
    assert.equal(applyTimeMask('1'), '1');
    assert.equal(applyTimeMask('14'), '14:');

    // Mengetik 4 digit berurutan
    assert.equal(applyTimeMask('1430'), '14:30');
    assert.equal(applyTimeMask('0805'), '08:05');

    // Jam melebihi 23 dikoreksi menjadi 23:
    assert.equal(applyTimeMask('28'), '23:');

    // Menit pertama > 5 dikoreksi menjadi 5
    assert.equal(applyTimeMask('147'), '14:5');
});

test('applyTimeMask mendukung penghapusan backspace tanpa stuck pada titik dua', () => {
    assert.equal(applyTimeMask('14', true), '14');
    assert.equal(applyTimeMask('', true), '');
});

test('validateTimeRange menolak input waktu kosong atau tidak lengkap', () => {
    const res = validateTimeRange('', '10:00', true);
    assert.equal(res.valid, false);
    assert.equal(res.code, 'required');
    assert.equal(res.message, REQUIRED_MESSAGE);
});

test('validateTimeRange menolak format waktu salah', () => {
    const res = validateTimeRange('25:00', '10:00', true);
    assert.equal(res.valid, false);
    assert.equal(res.code, 'format');
    assert.equal(res.message, FORMAT_MESSAGE);
});

test('validateTimeRange menolak waktu selesai <= waktu mulai pada hari yang sama', () => {
    // Waktu selesai lebih awal
    const resEarlier = validateTimeRange('14:00', '13:00', true);
    assert.equal(resEarlier.valid, false);
    assert.equal(resEarlier.code, 'range');
    assert.equal(resEarlier.message, RANGE_MESSAGE);

    // Waktu selesai sama persis
    const resEqual = validateTimeRange('14:00', '14:00', true);
    assert.equal(resEqual.valid, false);
    assert.equal(resEqual.code, 'range');
    assert.equal(resEqual.message, RANGE_MESSAGE);
});

test('validateTimeRange menerima waktu selesai > waktu mulai pada hari yang sama', () => {
    const res = validateTimeRange('08:30', '11:00', true);
    assert.equal(res.valid, true);
    assert.equal(res.code, 'ok');
});

test('validateTimeRange mengizinkan waktu lintas hari jika tanggal berbeda', () => {
    // Hari berbeda (kegiatan menginap/shift malam), misal 22:00 -> 02:00
    const res = validateTimeRange('22:00', '02:00', false);
    assert.equal(res.valid, true);
    assert.equal(res.code, 'ok');
});
