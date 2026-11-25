<div id="modalHapusDraf"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalHapusDrafTitle"
     aria-describedby="modalHapusDrafDesc"
     aria-hidden="true"
     class="sakti-modal-backdrop hidden">
    <div class="sakti-modal-panel max-w-md p-6">
        <div class="flex items-start justify-between gap-4 mb-4">
            <h3 id="modalHapusDrafTitle" class="text-lg font-bold text-gray-900">
                Hapus Draf Pengajuan Surat
            </h3>
            <button type="button"
                    data-modal-close
                    aria-label="Tutup dialog hapus draf"
                    class="sakti-touch-target rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-100 px-3 text-base font-bold">
                ×
            </button>
        </div>

        <p id="modalHapusDrafDesc" class="text-sm text-gray-800 leading-relaxed mb-3">
            Apakah Anda yakin ingin menghapus permanen draf <strong id="hapusDrafNamaSurat" class="text-gray-900">pengajuan surat</strong>?
            Tindakan ini tidak dapat dibatalkan dan seluruh data isian draf akan terhapus dari sistem.
        </p>

        <form id="formHapusDraf" method="POST" action="" class="mt-5">
            @csrf
            @method('DELETE')
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
                <button type="button"
                        id="btnBatalHapusDraf"
                        data-modal-close
                        class="sakti-touch-target px-4 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 active:bg-gray-200 text-sm font-semibold">
                    Batal
                </button>
                <button type="submit"
                        id="btnKonfirmasiHapusDraf"
                        class="sakti-touch-target px-4 py-2 rounded-lg bg-red-700 hover:bg-red-800 active:bg-red-900 text-white text-sm font-semibold">
                    Ya, Hapus Permanen
                </button>
            </div>
        </form>
    </div>
</div>
