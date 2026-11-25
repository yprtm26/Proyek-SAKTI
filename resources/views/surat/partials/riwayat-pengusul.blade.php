@php
    $statusAktif = request('status', '');
    $filterList = [
        '' => ['label' => 'Semua', 'activeClass' => 'bg-blue-700 text-white border-blue-700'],
        'draf' => ['label' => 'Draf', 'activeClass' => 'bg-gray-700 text-white border-gray-700'],
        'menunggu' => ['label' => 'Menunggu TU', 'activeClass' => 'bg-orange-800 text-white border-orange-800'],
        'diverifikasi_tu' => ['label' => 'Diverifikasi TU', 'activeClass' => 'bg-blue-700 text-white border-blue-700'],
        'perlu_revisi' => ['label' => 'Perlu Revisi', 'activeClass' => 'bg-amber-700 text-white border-amber-700'],
        'disetujui_final' => ['label' => 'Disetujui', 'activeClass' => 'bg-green-700 text-white border-green-700'],
        'ditolak' => ['label' => 'Ditolak', 'activeClass' => 'bg-red-700 text-white border-red-700'],
    ];
@endphp

<div class="max-w-7xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">
                Riwayat Pengajuan Surat
            </h2>
            <p class="text-sm text-gray-700">
                Daftar seluruh pengajuan surat milik akun Anda lengkap dengan nomor surat, tanggal pengajuan, dan status terkini.
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ $dashboardUrl }}"
               class="sakti-touch-target px-4 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-sm font-semibold">
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{-- Alert error dari server (misal penolakan hapus surat non-draf) --}}
    @if(session('error'))
        <div role="alert" class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->has('hapus_draf'))
        <div role="alert" class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
            {{ $errors->first('hapus_draf') }}
        </div>
    @endif

    {{-- Filter Status --}}
    <nav aria-label="Filter status riwayat pengajuan surat" class="mb-5 flex flex-wrap items-center gap-2 text-sm">
        <span class="font-semibold text-gray-800 mr-1">Status:</span>
        @foreach($filterList as $kode => $meta)
            <a href="{{ $kode === '' ? $riwayatUrl : $riwayatUrl . '?status=' . $kode }}"
               class="sakti-touch-target px-3.5 py-2 rounded-lg border font-semibold transition {{ $statusAktif === $kode ? $meta['activeClass'] : 'border-gray-300 bg-white text-gray-800 hover:bg-gray-100' }}">
                {{ $meta['label'] }}
            </a>
        @endforeach
    </nav>

    @if($suratList->isEmpty())
        {{-- Empty State (B-27 AC-3 & R-27) --}}
        <section class="bg-white border border-gray-300 rounded-xl p-8 text-center" aria-live="polite">
            @if(($totalSemuaSurat ?? 0) === 0)
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    Belum Ada Riwayat Pengajuan Surat
                </h3>
                <p class="text-sm text-gray-700 max-w-md mx-auto mb-5">
                    Akun Anda belum pernah membuat atau menyimpan draf pengajuan surat. Mulai pengajuan baru dari daftar template yang tersedia.
                </p>
                <a href="{{ $dashboardUrl }}"
                   class="sakti-touch-target px-5 py-2.5 rounded-lg bg-orange-700 hover:bg-orange-800 text-white text-sm font-semibold">
                    Ajukan Surat Pertama
                </a>
            @else
                <h3 class="text-lg font-bold text-gray-900 mb-2">
                    Tidak Ada Surat dengan Status Ini
                </h3>
                <p class="text-sm text-gray-700 max-w-md mx-auto mb-5">
                    Tidak ditemukan pengajuan surat pada filter status yang dipilih.
                </p>
                <a href="{{ $riwayatUrl }}"
                   class="sakti-touch-target px-5 py-2.5 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-sm font-semibold">
                    Tampilkan Semua Pengajuan
                </a>
            @endif
        </section>
    @else
        {{-- Desktop View: Tabel Terstruktur --}}
        <div class="hidden md:block bg-white border border-gray-300 rounded-xl overflow-x-auto shadow-sm">
            <table class="w-full border-collapse text-sm">
                <thead class="bg-gray-100 text-gray-900 border-b border-gray-300">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left font-bold">No</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Nomor Surat</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Jenis Surat</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Tanggal Pengajuan</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Status</th>
                        <th scope="col" class="px-4 py-3 text-left font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($suratList as $i => $surat)
                        @php
                            $badge = statusBadgeMeta((string) $surat->status);
                            $labelStatus = $role === 'dosen' ? statusDosen($surat->status) : statusMahasiswa($surat->status);
                            $namaSurat = $surat->jenisSurat?->nama ?? 'Pengajuan Surat';
                        @endphp
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-4 py-3 text-gray-800">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $surat->nomor_surat ?? 'Belum diterbitkan' }}
                            </td>
                            <td class="px-4 py-3 text-gray-900">
                                <div class="font-semibold">{{ $namaSurat }}</div>
                                @if($surat->status === 'perlu_revisi' && $surat->catatan_revisi)
                                    <p class="mt-1 text-xs font-medium text-amber-900 bg-amber-50 border border-amber-300 rounded px-2 py-1">
                                        Catatan Revisi: {{ $surat->catatan_revisi }}
                                    </p>
                                @elseif($surat->status === 'ditolak' && $surat->alasan_tolak)
                                    <p class="mt-1 text-xs font-medium text-red-900 bg-red-50 border border-red-300 rounded px-2 py-1">
                                        Alasan Penolakan: {{ $surat->alasan_tolak }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-800 whitespace-nowrap">
                                {{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="{{ $badge['class'] }}"
                                      style="background-color: {{ $badge['bg'] }}; color: {{ $badge['fg'] }};">
                                    {{ $labelStatus }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="button"
                                            data-preview-trigger
                                            data-preview-url="/surat/{{ $surat->id }}/pratinjau"
                                            data-surat-name="{{ $namaSurat }}"
                                            class="sakti-touch-target px-3 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-xs font-semibold">
                                        Pratinjau
                                    </button>

                                    @if($surat->status === 'draf')
                                        <a href="{{ $baseSuratUrl }}/{{ $surat->id }}/edit-draf"
                                           class="sakti-touch-target px-3 py-2 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-xs font-semibold">
                                            Edit Draf
                                        </a>
                                        <button type="button"
                                                data-delete-draft-trigger
                                                data-delete-url="/surat/{{ $surat->id }}/draf"
                                                data-surat-name="{{ $namaSurat }}"
                                                class="sakti-touch-target px-3 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-xs font-semibold">
                                            Hapus Draf
                                        </button>
                                    @else
                                        <a href="{{ $baseSuratUrl }}/{{ $surat->id }}"
                                           class="sakti-touch-target px-3 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold">
                                            Detail
                                        </a>
                                        <button type="button"
                                                disabled
                                                aria-disabled="true"
                                                title="Surat yang sudah diajukan tidak dapat dihapus"
                                                class="sakti-touch-target px-3 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-500 text-xs font-semibold opacity-70">
                                            Hapus Draf
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile View: Reflow Kartu Bertumpuk (R-03) --}}
        <div class="md:hidden space-y-3">
            @foreach($suratList as $i => $surat)
                @php
                    $badge = statusBadgeMeta((string) $surat->status);
                    $labelStatus = $role === 'dosen' ? statusDosen($surat->status) : statusMahasiswa($surat->status);
                    $namaSurat = $surat->jenisSurat?->nama ?? 'Pengajuan Surat';
                @endphp
                <article class="bg-white border border-gray-300 rounded-xl p-4 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-gray-700">
                                #{{ $i + 1 }} &bull; {{ $surat->nomor_surat ?? 'Nomor belum terbit' }}
                            </p>
                            <h3 class="text-base font-bold text-gray-900 mt-0.5 break-words">
                                {{ $namaSurat }}
                            </h3>
                        </div>
                        <span class="{{ $badge['class'] }} shrink-0"
                              style="background-color: {{ $badge['bg'] }}; color: {{ $badge['fg'] }};">
                            {{ $labelStatus }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-700">
                        Tanggal: <strong class="text-gray-900">{{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                    </p>

                    @if($surat->status === 'perlu_revisi' && $surat->catatan_revisi)
                        <p class="text-xs font-medium text-amber-900 bg-amber-50 border border-amber-300 rounded px-3 py-2">
                            Catatan Revisi: {{ $surat->catatan_revisi }}
                        </p>
                    @elseif($surat->status === 'ditolak' && $surat->alasan_tolak)
                        <p class="text-xs font-medium text-red-900 bg-red-50 border border-red-300 rounded px-3 py-2">
                            Alasan Penolakan: {{ $surat->alasan_tolak }}
                        </p>
                    @endif

                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200">
                        <button type="button"
                                data-preview-trigger
                                data-preview-url="/surat/{{ $surat->id }}/pratinjau"
                                data-surat-name="{{ $namaSurat }}"
                                class="sakti-touch-target flex-1 px-3 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-sm font-semibold">
                            Pratinjau
                        </button>

                        @if($surat->status === 'draf')
                            <a href="{{ $baseSuratUrl }}/{{ $surat->id }}/edit-draf"
                               class="sakti-touch-target flex-1 px-3 py-2 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-sm font-semibold">
                                Edit Draf
                            </a>
                            <button type="button"
                                    data-delete-draft-trigger
                                    data-delete-url="/surat/{{ $surat->id }}/draf"
                                    data-surat-name="{{ $namaSurat }}"
                                    class="sakti-touch-target w-full px-3 py-2 rounded-lg bg-red-700 hover:bg-red-800 text-white text-sm font-semibold">
                                Hapus Draf
                            </button>
                        @else
                            <a href="{{ $baseSuratUrl }}/{{ $surat->id }}"
                               class="sakti-touch-target flex-1 px-3 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold">
                                Detail
                            </a>
                            <button type="button"
                                    disabled
                                    aria-disabled="true"
                                    class="sakti-touch-target w-full px-3 py-2 rounded-lg border border-gray-300 bg-gray-100 text-gray-500 text-sm font-semibold opacity-70">
                                Hapus Draf (Nonaktif)
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

<x-modal-hapus-draf />
<x-modal-pratinjau-surat />
