/**
 * Pengendali modal aksesibel SAKTI (B-14, B-20, B-28).
 *
 * Fitur:
 *  - Focus trap (Tab / Shift+Tab tetap berada di dalam dialog aktif)
 *  - Tutup dengan tombol Escape atau klik backdrop
 *  - Kembalikan fokus ke tombol pemicu setelah dialog ditutup
 *  - Validasi client-side catatan revisi tidak boleh kosong/spasi saja (B-20)
 *  - Pratinjau dokumen surat asinkron dengan loading, error 404/jaringan, dan konten terformat (B-28)
 */

const FOCUSABLE_SELECTOR = [
    'a[href]',
    'button:not([disabled])',
    'textarea:not([disabled])',
    'input:not([disabled]):not([type="hidden"])',
    'select:not([disabled])',
    '[tabindex]:not([tabindex="-1"])',
].join(',');

/**
 * Ambil daftar elemen yang bisa difokus secara berurutan di dalam wadah dialog.
 */
export function getFocusableElements(container) {
    if (!container || typeof container.querySelectorAll !== 'function') {
        return [];
    }

    return Array.from(container.querySelectorAll(FOCUSABLE_SELECTOR)).filter((el) => {
        if (el.hasAttribute('disabled') || el.getAttribute('aria-hidden') === 'true') {
            return false;
        }
        if (typeof el.offsetParent !== 'undefined' && el.offsetParent === null && el.getClientRects?.().length === 0) {
            return false;
        }
        return true;
    });
}

/**
 * Tangani penekanan tombol Tab / Shift+Tab agar fokus tetap terjebak di dalam dialog.
 */
export function trapFocusKeydown(event, container, activeElement = null) {
    if (!event || event.key !== 'Tab') {
        return false;
    }

    const focusables = getFocusableElements(container);
    if (focusables.length === 0) {
        event.preventDefault?.();
        return true;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    const current = activeElement ?? (typeof document !== 'undefined' ? document.activeElement : null);

    if (event.shiftKey) {
        if (current === first || !focusables.includes(current)) {
            event.preventDefault?.();
            last.focus?.();
            return true;
        }
        return false;
    }

    if (current === last || !focusables.includes(current)) {
        event.preventDefault?.();
        first.focus?.();
        return true;
    }

    return false;
}

/**
 * Validasi catatan revisi Admin TU (B-20 AC-3: wajib diisi, tidak boleh kosong/spasi saja).
 */
export function validateRevisionNote(rawNote) {
    const trimmed = typeof rawNote === 'string' ? rawNote.trim() : '';

    if (trimmed.length === 0) {
        return {
            valid: false,
            value: '',
            message: 'Catatan revisi wajib diisi sebelum mengembalikan surat kepada pengusul.',
        };
    }

    if (trimmed.length < 3) {
        return {
            valid: false,
            value: trimmed,
            message: 'Catatan revisi minimal 3 karakter agar pengusul memahami perbaikan yang diperlukan.',
        };
    }

    if (/[<>]/.test(trimmed)) {
        return {
            valid: false,
            value: trimmed,
            message: 'Catatan revisi tidak boleh mengandung karakter tag HTML (< atau >).',
        };
    }

    return {
        valid: true,
        value: trimmed,
        message: '',
    };
}

/**
 * Tentukan pesan galat yang aman dan jelas untuk pratinjau dokumen (B-28 AC-3).
 */
export function extractPreviewErrorMessage(statusCode) {
    if (statusCode === 404) {
        return 'Dokumen surat tidak ditemukan atau belum tersedia di sistem (404).';
    }
    if (statusCode === 403) {
        return 'Anda tidak memiliki hak akses untuk membuka pratinjau dokumen ini (403).';
    }
    return 'Gagal memuat pratinjau dokumen surat. Silakan coba lagi beberapa saat lagi.';
}

export function initSaktiModals(root = typeof document !== 'undefined' ? document : null) {
    if (!root) {
        return;
    }

    let activeModal = null;
    let lastTrigger = null;

    function openModal(modal, trigger = null, initialFocusEl = null) {
        if (!modal) {
            return;
        }
        if (activeModal && activeModal !== modal) {
            closeModal(activeModal, false);
        }

        lastTrigger = trigger || root.activeElement || null;
        activeModal = modal;

        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        root.body?.classList?.add('overflow-hidden');

        const focusables = getFocusableElements(modal);
        const targetFocus = initialFocusEl || focusables[0];
        if (targetFocus && typeof targetFocus.focus === 'function') {
            targetFocus.focus();
        }
    }

    function closeModal(modal = activeModal, restoreFocus = true) {
        if (!modal) {
            return;
        }

        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        root.body?.classList?.remove('overflow-hidden');

        if (activeModal === modal) {
            activeModal = null;
        }

        if (restoreFocus && lastTrigger && typeof lastTrigger.focus === 'function') {
            lastTrigger.focus();
        }
        lastTrigger = null;
    }

    // Global Escape & Focus Trap handler
    root.addEventListener('keydown', (event) => {
        if (!activeModal) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeModal(activeModal, true);
            return;
        }

        if (event.key === 'Tab') {
            trapFocusKeydown(event, activeModal, root.activeElement);
        }
    });

    // Backdrop & [data-modal-close] click handler
    root.addEventListener('click', (event) => {
        const closeTrigger = event.target?.closest?.('[data-modal-close]');
        if (closeTrigger && activeModal && activeModal.contains(closeTrigger)) {
            event.preventDefault();
            closeModal(activeModal, true);
            return;
        }

        if (activeModal && event.target === activeModal) {
            closeModal(activeModal, true);
        }
    });

    // ================= B-14: Modal Konfirmasi Hapus Draf =================
    const deleteModal = root.getElementById('modalHapusDraf');
    const deleteForm = root.getElementById('formHapusDraf');
    const deleteDocName = root.getElementById('hapusDrafNamaSurat');
    const deleteCancelBtn = root.getElementById('btnBatalHapusDraf');

    if (deleteModal && deleteForm) {
        root.addEventListener('click', (event) => {
            const btn = event.target?.closest?.('[data-delete-draft-trigger]');
            if (!btn || btn.disabled) {
                return;
            }

            const actionUrl = btn.getAttribute('data-delete-url');
            const namaSurat = btn.getAttribute('data-surat-name') || 'Draf pengajuan surat';
            if (!actionUrl) {
                return;
            }

            deleteForm.setAttribute('action', actionUrl);
            if (deleteDocName) {
                deleteDocName.textContent = namaSurat;
            }

            openModal(deleteModal, btn, deleteCancelBtn);
        });
    }

    // ================= B-20: Modal Catatan Revisi TU =================
    const revisiModal = root.getElementById('modalRevisiTu');
    const revisiForm = root.getElementById('formRevisiTu');
    const revisiTargetLabel = root.getElementById('revisiTuNamaSurat');
    const revisiTextarea = root.getElementById('revisiTuCatatanInput');
    const revisiError = root.getElementById('revisiTuCatatanError');

    if (revisiModal && revisiForm && revisiTextarea) {
        const clearRevisiError = () => {
            revisiTextarea.removeAttribute('aria-invalid');
            if (revisiError) {
                revisiError.textContent = '';
                revisiError.classList.add('hidden');
            }
        };

        root.addEventListener('click', (event) => {
            const btn = event.target?.closest?.('[data-revisi-trigger]');
            if (!btn || btn.disabled) {
                return;
            }

            const actionUrl = btn.getAttribute('data-revisi-url');
            const label = btn.getAttribute('data-surat-name') || 'Pengajuan Surat';
            if (!actionUrl) {
                return;
            }

            revisiForm.setAttribute('action', actionUrl);
            if (revisiTargetLabel) {
                revisiTargetLabel.textContent = label;
            }
            revisiTextarea.value = '';
            clearRevisiError();

            openModal(revisiModal, btn, revisiTextarea);
        });

        revisiTextarea.addEventListener('input', clearRevisiError);

        revisiForm.addEventListener('submit', (event) => {
            const result = validateRevisionNote(revisiTextarea.value);
            if (!result.valid) {
                event.preventDefault();
                revisiTextarea.setAttribute('aria-invalid', 'true');
                if (revisiError) {
                    revisiError.textContent = result.message;
                    revisiError.classList.remove('hidden');
                }
                revisiTextarea.focus();
                return;
            }
            revisiTextarea.value = result.value;
        });
    }

    // ================= B-28: Modal Pratinjau Surat =================
    const previewModal = root.getElementById('modalPratinjauSurat');
    const previewTitle = root.getElementById('pratinjauModalSubtitle');
    const previewLoading = root.getElementById('pratinjauLoadingState');
    const previewErrorBox = root.getElementById('pratinjauErrorState');
    const previewErrorText = root.getElementById('pratinjauErrorMessage');
    const previewBody = root.getElementById('pratinjauHtmlContainer');
    const previewFullTabLink = root.getElementById('pratinjauBukaTabLink');
    const previewCloseBtn = root.getElementById('btnTutupPratinjau');

    if (previewModal && previewBody) {
        let activeController = null;

        root.addEventListener('click', async (event) => {
            const btn = event.target?.closest?.('[data-preview-trigger]');
            if (!btn || btn.disabled) {
                return;
            }

            const previewUrl = btn.getAttribute('data-preview-url');
            const suratName = btn.getAttribute('data-surat-name') || 'Dokumen Surat';
            if (!previewUrl) {
                return;
            }

            if (activeController) {
                activeController.abort();
            }
            activeController = new AbortController();

            if (previewTitle) {
                previewTitle.textContent = suratName;
            }
            if (previewFullTabLink) {
                previewFullTabLink.setAttribute('href', previewUrl);
            }

            previewBody.innerHTML = '';
            previewBody.classList.add('hidden');
            previewErrorBox?.classList.add('hidden');
            previewLoading?.classList.remove('hidden');

            openModal(previewModal, btn, previewCloseBtn);

            try {
                const separator = previewUrl.includes('?') ? '&' : '?';
                const response = await fetch(`${previewUrl}${separator}fragment=1`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    signal: activeController.signal,
                });

                if (!response.ok) {
                    throw new Error(extractPreviewErrorMessage(response.status));
                }

                const html = await response.text();
                previewLoading?.classList.add('hidden');
                previewBody.innerHTML = html;
                previewBody.classList.remove('hidden');
            } catch (err) {
                if (err?.name === 'AbortError') {
                    return;
                }
                previewLoading?.classList.add('hidden');
                if (previewErrorText) {
                    previewErrorText.textContent = err?.message || extractPreviewErrorMessage(500);
                }
                previewErrorBox?.classList.remove('hidden');
            }
        });
    }
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initSaktiModals(document));
    } else {
        initSaktiModals(document);
    }
}
