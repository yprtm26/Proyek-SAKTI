<div id="modalRevisiTu"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalRevisiTuTitle"
     aria-hidden="true"
     class="sakti-modal-backdrop hidden">
    <div class="sakti-modal-panel max-w-lg p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h3 id="modalRevisiTuTitle" class="text-lg font-bold text-gray-900">
                    Kembalikan Surat untuk Revisi
                </h3>
                <p id="revisiTuNamaSurat" class="text-sm text-gray-700 mt-1">
                    Pengajuan Surat
                </p>
            </div>
            <button type="button"
                    data-modal-close
                    aria-label="Tutup dialog catatan revisi"
                    class="sakti-touch-target rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 px-3 text-base font-bold">
                ×
            </button>
        </div>

        <form id="formRevisiTu" method="POST" action="" novalidate>
            @csrf
            <div class="mb-4">
                <label for="revisiTuCatatanInput" class="block text-sm font-semibold text-gray-900 mb-2">
                    Catatan Perbaikan (Wajib Diisi) <span class="text-red-700">*</span>
                </label>
                <textarea id="revisiTuCatatanInput"
                          name="catatan_revisi"
                          rows="4"
                          required
                          placeholder="Tuliskan bagian data atau lampiran yang perlu diperbaiki pengusul..."
                          class="w-full rounded-lg border border-gray-400 px-3 py-2 text-sm text-gray-900 bg-white focus:border-orange-700"></textarea>
                <p id="revisiTuCatatanError"
                   role="alert"
                   class="hidden mt-2 text-sm font-medium text-red-700"></p>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button"
                        data-modal-close
                        class="sakti-touch-target px-4 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 active:bg-gray-200 text-sm font-semibold">
                    Batal
                </button>
                <button type="submit"
                        class="sakti-touch-target px-4 py-2 rounded-lg bg-amber-700 hover:bg-amber-800 active:bg-amber-900 text-white text-sm font-semibold">
                    Kirim Catatan Revisi
                </button>
            </div>
        </form>
    </div>
</div>
