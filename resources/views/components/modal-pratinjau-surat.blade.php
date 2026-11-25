<div id="modalPratinjauSurat"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalPratinjauTitle"
     aria-hidden="true"
     class="sakti-modal-backdrop hidden">
    <div class="sakti-modal-panel max-w-4xl">
        <div class="px-5 py-4 border-b border-gray-300 bg-white flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 id="modalPratinjauTitle" class="text-lg font-bold text-gray-900">
                    Pratinjau Dokumen Surat
                </h3>
                <p id="pratinjauModalSubtitle" class="text-sm text-gray-700">
                    Dokumen Surat
                </p>
            </div>

            <div class="flex items-center gap-2">
                <a id="pratinjauBukaTabLink"
                   href="#"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="sakti-touch-target px-3 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-sm font-semibold">
                    Buka Halaman Penuh
                </a>
                <button type="button"
                        id="btnTutupPratinjau"
                        data-modal-close
                        aria-label="Tutup pratinjau dokumen surat"
                        class="sakti-touch-target px-4 py-2 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold">
                    Tutup (Esc)
                </button>
            </div>
        </div>

        <div class="sakti-preview-viewport flex-1">
            {{-- Loading state (R-27) --}}
            <div id="pratinjauLoadingState"
                 role="status"
                 aria-live="polite"
                 class="bg-white border border-gray-300 rounded-lg p-8 text-center text-gray-900">
                <p class="text-base font-semibold">Memuat pratinjau dokumen surat...</p>
                <p class="text-sm text-gray-700 mt-1">Mohon tunggu sebentar selagi sistem menyiapkan tata letak resmi.</p>
            </div>

            {{-- Error state (R-27 + B-28 AC-3) --}}
            <div id="pratinjauErrorState"
                 role="alert"
                 class="hidden bg-red-50 border border-red-300 rounded-lg p-6 text-center">
                <p id="pratinjauErrorMessage" class="text-base font-semibold text-red-900">
                    Dokumen pratinjau tidak ditemukan.
                </p>
                <p class="text-sm text-red-800 mt-2">
                    Periksa kembali daftar riwayat pengajuan Anda atau tutup jendela ini dengan tombol Escape.
                </p>
            </div>

            {{-- Formatted preview content --}}
            <div id="pratinjauHtmlContainer" class="hidden"></div>
        </div>
    </div>
</div>
