/**
 * Logika murni dan pengelola DOM daftar anggota kegiatan (B-10 B).
 *
 * Fungsi murni diekspor agar dapat diuji langsung dengan `node --test`,
 * sementara inisialisasi DOM berjalan otomatis di browser.
 */

/**
 * Normalisasi nilai baris anggota.
 *
 * @param {{ nama?: string, peran?: string, instansi?: string }} row
 * @returns {{ nama: string, peran: string, instansi: string }}
 */
export function normalizePersonelRow(row = {}) {
    return {
        nama: typeof row.nama === 'string' ? row.nama.trim() : '',
        peran: typeof row.peran === 'string' ? row.peran.trim() : '',
        instansi: typeof row.instansi === 'string' ? row.instansi.trim() : '',
    };
}

/**
 * Validasi daftar baris anggota kegiatan sebelum form dikirim.
 *
 * @param {Array<{ nama?: string, peran?: string, instansi?: string }>} rows
 * @param {{ minRows?: number }} options
 * @returns {{
 *   valid: boolean,
 *   reason: 'ok' | 'min-rows' | 'empty-fields',
 *   message: string,
 *   rowErrors: Array<{ index: number, namaError: string, peranError: string }>
 * }}
 */
export function validatePersonelRows(rows = [], options = {}) {
    const minRows = Number.isInteger(options.minRows) ? options.minRows : 1;

    if (!Array.isArray(rows) || rows.length < minRows) {
        return {
            valid: false,
            reason: 'min-rows',
            message: `Minimal ${minRows} anggota kegiatan harus diisi sebelum mengirim pengajuan.`,
            rowErrors: [],
        };
    }

    const rowErrors = [];
    let hasError = false;

    rows.forEach((rawRow, index) => {
        const row = normalizePersonelRow(rawRow);
        const namaError = row.nama === '' ? 'Nama anggota wajib diisi.' : '';
        const peranError = row.peran === '' ? 'Peran kegiatan wajib diisi.' : '';

        if (namaError !== '' || peranError !== '') {
            hasError = true;
        }

        rowErrors.push({ index, namaError, peranError });
    });

    if (hasError) {
        return {
            valid: false,
            reason: 'empty-fields',
            message: 'Harap lengkapi kolom Nama dan Peran pada seluruh baris anggota sebelum mengajukan surat.',
            rowErrors,
        };
    }

    return {
        valid: true,
        reason: 'ok',
        message: '',
        rowErrors,
    };
}

/**
 * Tentukan apakah aksi submit merupakan penyimpanan draf (boleh parsial).
 *
 * @param {HTMLElement | null} submitter
 * @returns {boolean}
 */
export function isDraftSubmitter(submitter) {
    if (!submitter) {
        return false;
    }

    const value = typeof submitter.getAttribute === 'function'
        ? submitter.getAttribute('value')
        : submitter.value;
    const name = typeof submitter.getAttribute === 'function'
        ? submitter.getAttribute('name')
        : submitter.name;
    const hasFormNoValidate = typeof submitter.hasAttribute === 'function'
        ? submitter.hasAttribute('formnovalidate')
        : Boolean(submitter.formnovalidate);

    return value === 'draft' || name === 'simpan_draf' || hasFormNoValidate;
}

/**
 * Hitung target indeks fokus setelah baris pada `removedIndex` dihapus.
 *
 * @param {number} removedIndex
 * @param {number} remainingCount
 * @returns {{ target: 'row' | 'add-button', index: number }}
 */
export function resolveFocusAfterRemove(removedIndex, remainingCount) {
    if (remainingCount <= 0) {
        return { target: 'add-button', index: -1 };
    }

    const nextIndex = removedIndex < remainingCount ? removedIndex : remainingCount - 1;
    return { target: 'row', index: Math.max(0, nextIndex) };
}

function escapeHtml(text) {
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function buildRowMarkup(fieldName, index, displayNumber, row = {}, minRows = 1, totalRows = 1) {
    const namaId = `${fieldName}-${index}-nama`;
    const peranId = `${fieldName}-${index}-peran`;
    const instansiId = `${fieldName}-${index}-instansi`;
    const cannotRemove = totalRows <= minRows;

    return `
        <div class="sakti-personel-row p-3 md:px-4 md:py-3 grid grid-cols-1 md:grid-cols-12 gap-3 items-start"
             data-personel-row
             data-row-index="${index}">
            <div class="md:col-span-1 flex items-center justify-between md:justify-center md:pt-2.5">
                <span class="text-xs font-semibold text-gray-600 md:hidden">Anggota</span>
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-800 text-xs font-semibold"
                      data-personel-number>${displayNumber}</span>
            </div>

            <div class="md:col-span-4">
                <label for="${namaId}" class="block md:sr-only text-xs font-medium text-gray-800 mb-1">
                    Nama Lengkap Anggota ${displayNumber} <span class="text-red-700" aria-hidden="true">*</span>
                </label>
                <input type="text"
                       id="${namaId}"
                       name="${fieldName}[${index}][nama]"
                       value="${escapeHtml(row.nama ?? '')}"
                       data-personel-nama
                       aria-required="true"
                       aria-invalid="false"
                       placeholder="Nama lengkap anggota"
                       class="sakti-personel-input w-full rounded-lg px-3 py-2 text-sm">
                <p data-personel-field-error="nama"
                   class="hidden text-xs font-medium text-red-700 mt-1"
                   role="alert"></p>
            </div>

            <div class="md:col-span-3">
                <label for="${peranId}" class="block md:sr-only text-xs font-medium text-gray-800 mb-1">
                    Peran dalam Kegiatan Anggota ${displayNumber} <span class="text-red-700" aria-hidden="true">*</span>
                </label>
                <input type="text"
                       id="${peranId}"
                       name="${fieldName}[${index}][peran]"
                       value="${escapeHtml(row.peran ?? '')}"
                       data-personel-peran
                       aria-required="true"
                       aria-invalid="false"
                       placeholder="Contoh: Ketua / Anggota / Narasumber"
                       class="sakti-personel-input w-full rounded-lg px-3 py-2 text-sm">
                <p data-personel-field-error="peran"
                   class="hidden text-xs font-medium text-red-700 mt-1"
                   role="alert"></p>
            </div>

            <div class="md:col-span-3">
                <label for="${instansiId}" class="block md:sr-only text-xs font-medium text-gray-800 mb-1">
                    Instansi, NIM, atau NIDN Anggota ${displayNumber}
                </label>
                <input type="text"
                       id="${instansiId}"
                       name="${fieldName}[${index}][instansi]"
                       value="${escapeHtml(row.instansi ?? '')}"
                       data-personel-instansi
                       placeholder="Instansi / NIM / NIDN"
                       class="sakti-personel-input w-full rounded-lg px-3 py-2 text-sm">
            </div>

            <div class="md:col-span-1 flex justify-end md:justify-center">
                <button type="button"
                        data-personel-remove
                        aria-label="Hapus anggota baris ${displayNumber}"
                        ${cannotRemove ? 'disabled aria-disabled="true" title="Minimal satu anggota kegiatan diperlukan"' : 'title="Hapus baris anggota ini"'}
                        class="sakti-personel-btn-remove inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="md:sr-only">Hapus</span>
                </button>
            </div>
        </div>
    `;
}

export function initPersonelList(wrapper) {
    if (!wrapper || wrapper.dataset.personelReady === '1') {
        return;
    }
    wrapper.dataset.personelReady = '1';

    const fieldName = wrapper.dataset.fieldName || 'personel';
    const minRows = Number.parseInt(wrapper.dataset.minRows || '1', 10);
    const listEl = wrapper.querySelector('[data-personel-rows]');
    const addBtn = wrapper.querySelector('[data-personel-add]');
    const emptyStateEl = wrapper.querySelector('[data-personel-empty]');
    const errorBoxEl = wrapper.querySelector('[data-personel-error]');
    const errorTextEl = wrapper.querySelector('[data-personel-error-text]');
    const statusEl = wrapper.querySelector('[data-personel-status]');
    const form = wrapper.closest('form');

    let nextIndex = Number.parseInt(wrapper.dataset.nextIndex || String(listEl?.children.length || 0), 10);

    function getRows() {
        return Array.from(listEl.querySelectorAll('[data-personel-row]'));
    }

    function readRowsData() {
        return getRows().map((rowEl) => ({
            nama: rowEl.querySelector('[data-personel-nama]')?.value || '',
            peran: rowEl.querySelector('[data-personel-peran]')?.value || '',
            instansi: rowEl.querySelector('[data-personel-instansi]')?.value || '',
        }));
    }

    function announce(message) {
        if (statusEl) {
            statusEl.textContent = message;
        }
    }

    function clearErrors() {
        if (errorBoxEl) {
            errorBoxEl.classList.add('hidden');
        }
        getRows().forEach((rowEl) => {
            ['nama', 'peran'].forEach((key) => {
                const input = rowEl.querySelector(`[data-personel-${key}]`);
                const err = rowEl.querySelector(`[data-personel-field-error="${key}"]`);
                if (input) {
                    input.setAttribute('aria-invalid', 'false');
                }
                if (err) {
                    err.textContent = '';
                    err.classList.add('hidden');
                }
            });
        });
    }

    function syncState() {
        const rows = getRows();
        const total = rows.length;

        if (emptyStateEl) {
            emptyStateEl.classList.toggle('hidden', total > 0);
        }

        rows.forEach((rowEl, idx) => {
            const no = idx + 1;
            const numEl = rowEl.querySelector('[data-personel-number]');
            if (numEl) {
                numEl.textContent = String(no);
            }

            const removeBtn = rowEl.querySelector('[data-personel-remove]');
            if (removeBtn) {
                const disableRemove = total <= minRows;
                removeBtn.disabled = disableRemove;
                removeBtn.setAttribute('aria-disabled', disableRemove ? 'true' : 'false');
                removeBtn.setAttribute('aria-label', `Hapus anggota baris ${no}`);
                removeBtn.setAttribute(
                    'title',
                    disableRemove ? 'Minimal satu anggota kegiatan diperlukan' : `Hapus anggota baris ${no}`
                );
            }
        });
    }

    function addRow(initial = {}) {
        clearErrors();
        const currentRows = getRows();
        const displayNo = currentRows.length + 1;
        const index = nextIndex++;

        const temp = document.createElement('div');
        temp.innerHTML = buildRowMarkup(fieldName, index, displayNo, initial, minRows, displayNo).trim();
        const rowEl = temp.firstElementChild;
        listEl.appendChild(rowEl);

        syncState();
        announce(`Baris anggota ${displayNo} ditambahkan.`);

        const namaInput = rowEl.querySelector('[data-personel-nama]');
        if (namaInput) {
            namaInput.focus();
        }
    }

    function removeRow(rowEl) {
        const rows = getRows();
        const removedIndex = rows.indexOf(rowEl);
        if (removedIndex === -1 || rows.length <= minRows) {
            return;
        }

        rowEl.remove();
        clearErrors();
        syncState();

        const updatedRows = getRows();
        announce(`Baris anggota ${removedIndex + 1} dihapus. Sisa ${updatedRows.length} anggota.`);

        const focusTarget = resolveFocusAfterRemove(removedIndex, updatedRows.length);
        if (focusTarget.target === 'row' && updatedRows[focusTarget.index]) {
            const targetBtn = updatedRows[focusTarget.index].querySelector('[data-personel-remove]');
            const targetInput = updatedRows[focusTarget.index].querySelector('[data-personel-nama]');
            if (targetBtn && !targetBtn.disabled) {
                targetBtn.focus();
            } else if (targetInput) {
                targetInput.focus();
            }
        } else if (addBtn) {
            addBtn.focus();
        }
    }

    function validateAndDisplayErrors() {
        clearErrors();
        const rows = getRows();
        const result = validatePersonelRows(readRowsData(), { minRows });

        if (result.valid) {
            return true;
        }

        if (errorBoxEl && errorTextEl) {
            errorTextEl.textContent = result.message;
            errorBoxEl.classList.remove('hidden');
        }

        let firstInvalid = null;

        result.rowErrors.forEach(({ index, namaError, peranError }) => {
            const rowEl = rows[index];
            if (!rowEl) {
                return;
            }

            if (namaError) {
                const input = rowEl.querySelector('[data-personel-nama]');
                const err = rowEl.querySelector('[data-personel-field-error="nama"]');
                if (input) {
                    input.setAttribute('aria-invalid', 'true');
                    if (!firstInvalid) firstInvalid = input;
                }
                if (err) {
                    err.textContent = namaError;
                    err.classList.remove('hidden');
                }
            }

            if (peranError) {
                const input = rowEl.querySelector('[data-personel-peran]');
                const err = rowEl.querySelector('[data-personel-field-error="peran"]');
                if (input) {
                    input.setAttribute('aria-invalid', 'true');
                    if (!firstInvalid) firstInvalid = input;
                }
                if (err) {
                    err.textContent = peranError;
                    err.classList.remove('hidden');
                }
            }
        });

        if (firstInvalid) {
            firstInvalid.focus();
        } else if (addBtn) {
            addBtn.focus();
        }

        return false;
    }

    if (addBtn) {
        addBtn.addEventListener('click', () => addRow());
    }

    wrapper.addEventListener('click', (event) => {
        const emptyAddBtn = event.target.closest('[data-personel-empty-add]');
        if (emptyAddBtn) {
            addRow();
            return;
        }

        const removeBtn = event.target.closest('[data-personel-remove]');
        if (removeBtn && !removeBtn.disabled) {
            const rowEl = removeBtn.closest('[data-personel-row]');
            if (rowEl) {
                removeRow(rowEl);
            }
        }
    });

    wrapper.addEventListener('input', (event) => {
        const input = event.target.closest('[data-personel-nama], [data-personel-peran]');
        if (!input) {
            return;
        }

        if (input.value.trim() !== '') {
            input.setAttribute('aria-invalid', 'false');
            const key = input.hasAttribute('data-personel-nama') ? 'nama' : 'peran';
            const rowEl = input.closest('[data-personel-row]');
            const err = rowEl?.querySelector(`[data-personel-field-error="${key}"]`);
            if (err) {
                err.textContent = '';
                err.classList.add('hidden');
            }
        }
    });

    if (form) {
        form.addEventListener('submit', (event) => {
            if (isDraftSubmitter(event.submitter)) {
                clearErrors();
                return;
            }

            if (!validateAndDisplayErrors()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    syncState();
}

export function initAllPersonelLists(root = typeof document !== 'undefined' ? document : null) {
    if (!root) {
        return;
    }

    root.querySelectorAll('[data-sakti-personel]').forEach((wrapper) => {
        initPersonelList(wrapper);
    });
}

if (typeof document !== 'undefined') {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initAllPersonelLists(document));
    } else {
        initAllPersonelLists(document);
    }
}
