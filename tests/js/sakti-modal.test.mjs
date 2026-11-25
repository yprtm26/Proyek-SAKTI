import test from 'node:test';
import assert from 'node:assert/strict';

import {
    getFocusableElements,
    trapFocusKeydown,
    validateRevisionNote,
    extractPreviewErrorMessage,
} from '../../public/js/sakti-modal.js';

function createFakeElement(id, disabled = false) {
    let focused = false;
    return {
        id,
        disabled,
        hasAttribute(attr) {
            return attr === 'disabled' && this.disabled;
        },
        getAttribute() {
            return null;
        },
        focus() {
            focused = true;
        },
        isFocused() {
            return focused;
        },
    };
}

test('getFocusableElements menyaring elemen disabled dan tersembunyi', () => {
    const activeBtn = createFakeElement('btn-batal', false);
    const disabledBtn = createFakeElement('btn-disabled', true);
    const submitBtn = createFakeElement('btn-hapus', false);

    const fakeContainer = {
        querySelectorAll() {
            return [activeBtn, disabledBtn, submitBtn];
        },
    };

    const result = getFocusableElements(fakeContainer);
    assert.deepEqual(result.map((el) => el.id), ['btn-batal', 'btn-hapus']);
});

test('trapFocusKeydown memutar fokus Tab dari elemen terakhir ke pertama dan Shift+Tab sebaliknya', () => {
    const first = createFakeElement('btn-close');
    const second = createFakeElement('btn-cancel');
    const last = createFakeElement('btn-confirm');

    const container = {
        querySelectorAll() {
            return [first, second, last];
        },
    };

    let preventedForward = false;
    const forwardHandled = trapFocusKeydown(
        { key: 'Tab', shiftKey: false, preventDefault: () => { preventedForward = true; } },
        container,
        last
    );
    assert.equal(forwardHandled, true);
    assert.equal(preventedForward, true);
    assert.equal(first.isFocused(), true);

    let preventedBackward = false;
    const backwardHandled = trapFocusKeydown(
        { key: 'Tab', shiftKey: true, preventDefault: () => { preventedBackward = true; } },
        container,
        first
    );
    assert.equal(backwardHandled, true);
    assert.equal(preventedBackward, true);
    assert.equal(last.isFocused(), true);
});

test('validateRevisionNote menolak catatan kosong, spasi saja, atau mengandung tag HTML', () => {
    assert.equal(validateRevisionNote('').valid, false);
    assert.equal(validateRevisionNote('    ').valid, false);
    assert.equal(validateRevisionNote('ab').valid, false);
    assert.equal(validateRevisionNote('<script>alert(1)</script>').valid, false);

    const ok = validateRevisionNote('  Mohon perbaiki NPM dan tanggal kegiatan.  ');
    assert.equal(ok.valid, true);
    assert.equal(ok.value, 'Mohon perbaiki NPM dan tanggal kegiatan.');
});

test('extractPreviewErrorMessage mengembalikan pesan 404 yang aman dan informatif', () => {
    assert.match(extractPreviewErrorMessage(404), /404/);
    assert.match(extractPreviewErrorMessage(403), /403/);
    assert.match(extractPreviewErrorMessage(500), /Gagal memuat pratinjau/);
});
