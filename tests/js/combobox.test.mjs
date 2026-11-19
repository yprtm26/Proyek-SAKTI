import test from 'node:test';
import assert from 'node:assert/strict';

import {
    clampIndex,
    filterOptions,
    moveIndex,
    normalizeText,
    resolveKey,
} from '../../public/js/combobox.js';

const prodi = [
    { id: 1, label: 'Teknik Informatika', kode: 'TI', description: 'S1' },
    { id: 2, label: 'Perpustakaan dan Sains Informasi', kode: 'PdSI', description: 'S1' },
];

const semester = [
    { id: 1, label: 'Ganjil', kode: 'ganjil', description: 'Semester paruh' },
    { id: 2, label: 'Genap', kode: 'genap', description: 'Semester paruh' },
    { id: 3, label: 'Semester 1', kode: '1', description: 'Semester tingkat' },
];

test('normalizeText menghapus spasi tepi, huruf besar, dan aksen', () => {
    assert.equal(normalizeText('  Teknik  '), 'teknik');
    assert.equal(normalizeText('Perpustakaan'), 'perpustakaan');
    assert.equal(normalizeText('Ékonomi'), 'ekonomi');
});

test('filterOptions mencari berdasarkan label dan kode tanpa membedakan huruf besar', () => {
    assert.deepEqual(filterOptions(prodi, 'teknik').map((item) => item.kode), ['TI']);
    assert.deepEqual(filterOptions(prodi, 'PDSI').map((item) => item.kode), ['PdSI']);
    assert.deepEqual(filterOptions(prodi, 'sains informasi').map((item) => item.kode), ['PdSI']);
    assert.equal(filterOptions(prodi, '').length, 2);
});

test('filterOptions mencocokkan deskripsi dan mengembalikan kosong bila tidak ada', () => {
    assert.deepEqual(filterOptions(semester, 'paruh').map((item) => item.label), ['Ganjil', 'Genap']);
    assert.deepEqual(filterOptions(semester, 'tingkat').map((item) => item.label), ['Semester 1']);
    assert.deepEqual(filterOptions(prodi, 'kedokteran'), []);
});

test('clampIndex dan moveIndex menjaga indeks tetap di dalam rentang', () => {
    assert.equal(clampIndex(5, 3), 2);
    assert.equal(clampIndex(-2, 3), 0);
    assert.equal(clampIndex(0, 0), -1);

    assert.equal(moveIndex(0, 3, 1), 1);
    assert.equal(moveIndex(2, 3, 1), 0);
    assert.equal(moveIndex(0, 3, -1), 2);
    assert.equal(moveIndex(-1, 3, 1), 0);
    assert.equal(moveIndex(-1, 3, -1), 2);
});

test('Enter membuka dropdown saat tertutup dan memilih opsi saat terbuka', () => {
    assert.deepEqual(resolveKey('Enter', { open: false, isTrigger: true }), { type: 'open' });

    const action = resolveKey('Enter', { open: true, activeIndex: 1, count: 3 });
    assert.equal(action.type, 'select');
    assert.equal(action.index, 1);

    assert.equal(resolveKey('Enter', { open: true, count: 0 }).type, 'none');
});

test('Escape menutup dropdown dan mengembalikan fokus ke pemicu', () => {
    const action = resolveKey('Escape', { open: true });

    assert.equal(action.type, 'close');
    assert.equal(action.restoreFocus, true);
    assert.equal(resolveKey('Escape', { open: false }).type, 'none');
});

test('Tab membuka dropdown saat tertutup dan menutupnya saat terbuka', () => {
    assert.equal(resolveKey('Tab', { open: false, isTrigger: true }).type, 'open');

    const action = resolveKey('Tab', { open: true });
    assert.equal(action.type, 'close');
    assert.equal(action.allowTab, true);
    assert.equal(Boolean(action.restoreFocus), false);
});

test('tombol panah membuka dropdown atau memindahkan opsi aktif', () => {
    assert.equal(resolveKey('ArrowDown', { open: false }).type, 'open');
    assert.equal(resolveKey('ArrowUp', { open: false }).type, 'open');

    assert.deepEqual(resolveKey('ArrowDown', { open: true }), { type: 'move', delta: 1 });
    assert.deepEqual(resolveKey('ArrowUp', { open: true }), { type: 'move', delta: -1 });
    assert.equal(resolveKey('Home', { open: true }).type, 'first');
    assert.equal(resolveKey('End', { open: true }).type, 'last');
});

test('spasi hanya membuka dropdown dari tombol, bukan saat mengetik pencarian', () => {
    assert.equal(resolveKey(' ', { open: false, isTrigger: true }).type, 'open');
    assert.equal(resolveKey(' ', { open: false, isTrigger: false }).type, 'none');
    assert.equal(resolveKey(' ', { open: true, isTrigger: false }).type, 'none');
});
