import test from 'node:test';
import assert from 'node:assert/strict';
import {
    normalizePersonelRow,
    validatePersonelRows,
    isDraftSubmitter,
    resolveFocusAfterRemove,
} from '../../public/js/personel-list.js';

test('normalizePersonelRow memangkas spasi berlebih pada nama, peran, dan instansi', () => {
    const result = normalizePersonelRow({
        nama: '  Dr. Budi Santoso  ',
        peran: '  Ketua Tim ',
        instansi: '  FTI YARSI  ',
    });

    assert.deepEqual(result, {
        nama: 'Dr. Budi Santoso',
        peran: 'Ketua Tim',
        instansi: 'FTI YARSI',
    });
});

test('validatePersonelRows menolak daftar kosong saat minRows >= 1', () => {
    const result = validatePersonelRows([], { minRows: 1 });

    assert.equal(result.valid, false);
    assert.equal(result.reason, 'min-rows');
});

test('validatePersonelRows menolak baris dengan kolom nama kosong atau spasi saja', () => {
    const result = validatePersonelRows([
        { nama: 'Andi Wijaya', peran: 'Ketua', instansi: 'FTI' },
        { nama: '   ', peran: 'Anggota', instansi: '221001' },
    ]);

    assert.equal(result.valid, false);
    assert.equal(result.reason, 'empty-fields');
    assert.equal(result.rowErrors[0].namaError, '');
    assert.equal(result.rowErrors[0].peranError, '');
    assert.notEqual(result.rowErrors[1].namaError, '');
    assert.equal(result.rowErrors[1].peranError, '');
});

test('validatePersonelRows menolak baris dengan kolom peran kosong', () => {
    const result = validatePersonelRows([
        { nama: 'Siti Aminah', peran: '', instansi: 'Universitas YARSI' },
    ]);

    assert.equal(result.valid, false);
    assert.equal(result.reason, 'empty-fields');
    assert.equal(result.rowErrors[0].namaError, '');
    assert.notEqual(result.rowErrors[0].peranError, '');
});

test('validatePersonelRows menerima seluruh baris jika nama dan peran terisi walau instansi kosong', () => {
    const result = validatePersonelRows([
        { nama: 'Andi Wijaya', peran: 'Ketua', instansi: '' },
        { nama: 'Rina Putri', peran: 'Anggota', instansi: '2210002' },
    ]);

    assert.equal(result.valid, true);
    assert.equal(result.reason, 'ok');
    assert.equal(result.message, '');
});

test('isDraftSubmitter mengenali tombol Simpan Draf agar draf parsial tidak diblokir', () => {
    assert.equal(isDraftSubmitter({ value: 'draft', name: 'action' }), true);
    assert.equal(isDraftSubmitter({ value: 'submit', name: 'simpan_draf' }), true);
    assert.equal(isDraftSubmitter({ value: 'submit', name: 'action', formnovalidate: true }), true);
    assert.equal(isDraftSubmitter({ value: 'submit', name: 'action', formnovalidate: false }), false);
    assert.equal(isDraftSubmitter(null), false);
});

test('resolveFocusAfterRemove menjaga fokus keyboard ke baris berikutnya, sebelumnya, atau tombol tambah', () => {
    assert.deepEqual(resolveFocusAfterRemove(0, 2), { target: 'row', index: 0 });
    assert.deepEqual(resolveFocusAfterRemove(2, 2), { target: 'row', index: 1 });
    assert.deepEqual(resolveFocusAfterRemove(0, 0), { target: 'add-button', index: -1 });
});
