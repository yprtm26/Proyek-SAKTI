@php
    $isFifo = ($sort ?? 'asc') === 'asc';
@endphp

@if($isFifo)
    <div class="mb-4 rounded-lg border border-orange-300 bg-orange-50 px-4 py-2.5 text-sm text-gray-900">
        <strong>Urutan Antrean Aktif:</strong> Pengajuan dengan waktu masuk paling awal ditampilkan di posisi teratas untuk diproses terlebih dahulu.
    </div>
@endif

@if(session('error'))
    <div role="alert" class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
        {{ session('error') }}
    </div>
@endif

@if($errors->has('catatan_revisi'))
    <div role="alert" class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
        {{ $errors->first('catatan_revisi') }}
    </div>
@endif

@if($suratPengajuan->isEmpty())
    <div class="bg-white border border-gray-300 rounded-xl p-8 text-center">
        <p class="text-base font-bold text-gray-900">
            {{ $emptyMessage }}
        </p>
        <p class="text-sm text-gray-700 mt-1">
            Surat berstatus Draf, Perlu Revisi, atau sudah Selesai tidak masuk ke dalam antrean aktif verifikasi TU.
        </p>
    </div>
@else
    {{-- Desktop Table --}}
    <div class="hidden md:block bg-white border border-gray-300 rounded-xl shadow-sm overflow-x-auto mb-10">
        <table class="w-full border-collapse text-sm">
            <thead class="bg-gray-100 text-gray-900 border-b border-gray-300">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left font-bold">Antrean</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold">Nama</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold">{{ $identityLabel }}</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold">Jenis Surat</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold">Waktu Masuk</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold">Status</th>
                    <th scope="col" class="px-4 py-3 text-left font-bold">Aksi Verifikasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($suratPengajuan as $i => $surat)
                    @php
                        $badge = statusBadgeMeta((string) $surat->status);
                        $namaPengusul = $roleTarget === 'dosen'
                            ? ($surat->data['nama_dosen'] ?? $surat->user->name)
                            : $surat->user->name;
                        $identitas = $roleTarget === 'dosen'
                            ? ($surat->data['nidn'] ?? $surat->user->nidn ?? $surat->user->serial_number ?? '-')
                            : ($surat->user->npm ?? $surat->user->nidn ?? $surat->user->serial_number ?? '-');
                        $statusText = $roleTarget === 'dosen'
                            ? statusDosen($surat->status)
                            : statusMahasiswa($surat->status);
                    @endphp
                    <tr class="hover:bg-gray-50 align-top">
                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-900">#{{ $i + 1 }}</span>
                            @if($isFifo && $i === 0)
                                <span class="sakti-fifo-priority ml-1.5">Prioritas Utama</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $namaPengusul }}</td>
                        <td class="px-4 py-3 text-gray-800">
                            {{ $identitas }}
                            <span class="block text-xs text-gray-700">{{ ucfirst($surat->user->role) }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-900">{{ $surat->jenisSurat->nama }}</td>
                        <td class="px-4 py-3 text-gray-800 whitespace-nowrap font-medium">
                            {{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="{{ $badge['class'] }}"
                                  style="background-color: {{ $badge['bg'] }}; color: {{ $badge['fg'] }};">
                                {{ $statusText }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                @if($surat->status === 'menunggu')
                                    <form method="POST" action="/admin/surat/{{ $surat->id }}/verifikasi" class="inline">
                                        @csrf
                                        <button type="submit"
                                                class="sakti-touch-target px-3 py-2 rounded-lg bg-green-700 hover:bg-green-800 text-white text-xs font-semibold">
                                            Setujui Verifikasi
                                        </button>
                                    </form>
                                    <button type="button"
                                            data-revisi-trigger
                                            data-revisi-url="/admin/surat/{{ $surat->id }}/revisi"
                                            data-surat-name="{{ $surat->jenisSurat->nama }} - {{ $namaPengusul }}"
                                            class="sakti-touch-target px-3 py-2 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-xs font-semibold">
                                        Kembalikan Revisi
                                    </button>
                                @endif

                                <button type="button"
                                        data-preview-trigger
                                        data-preview-url="/surat/{{ $surat->id }}/pratinjau"
                                        data-surat-name="{{ $surat->jenisSurat->nama }}"
                                        class="sakti-touch-target px-3 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-xs font-semibold">
                                    Pratinjau
                                </button>

                                <a href="/admin/surat/{{ $surat->id }}"
                                   class="sakti-touch-target px-3 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile Card Stack (R-03) --}}
    <div class="md:hidden space-y-3 mb-10">
        @foreach($suratPengajuan as $i => $surat)
            @php
                $badge = statusBadgeMeta((string) $surat->status);
                $namaPengusul = $roleTarget === 'dosen'
                    ? ($surat->data['nama_dosen'] ?? $surat->user->name)
                    : $surat->user->name;
                $identitas = $roleTarget === 'dosen'
                    ? ($surat->data['nidn'] ?? $surat->user->nidn ?? $surat->user->serial_number ?? '-')
                    : ($surat->user->npm ?? $surat->user->nidn ?? $surat->user->serial_number ?? '-');
                $statusText = $roleTarget === 'dosen'
                    ? statusDosen($surat->status)
                    : statusMahasiswa($surat->status);
            @endphp
            <article class="bg-white border border-gray-300 rounded-xl p-4 shadow-sm space-y-3">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-xs font-bold text-gray-900">Antrean #{{ $i + 1 }}</span>
                            @if($isFifo && $i === 0)
                                <span class="sakti-fifo-priority">Prioritas Utama</span>
                            @endif
                        </div>
                        <h3 class="text-base font-bold text-gray-900 mt-1 break-words">{{ $surat->jenisSurat->nama }}</h3>
                        <p class="text-sm text-gray-800">{{ $namaPengusul }} ({{ $identitas }})</p>
                    </div>
                    <span class="{{ $badge['class'] }} shrink-0"
                          style="background-color: {{ $badge['bg'] }}; color: {{ $badge['fg'] }};">
                        {{ $statusText }}
                    </span>
                </div>

                <p class="text-sm text-gray-700">
                    Waktu Masuk: <strong class="text-gray-900">{{ $surat->created_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </p>

                <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-200">
                    @if($surat->status === 'menunggu')
                        <form method="POST" action="/admin/surat/{{ $surat->id }}/verifikasi" class="flex-1">
                            @csrf
                            <button type="submit"
                                    class="sakti-touch-target w-full px-3 py-2 rounded-lg bg-green-700 hover:bg-green-800 text-white text-sm font-semibold">
                                Setujui Verifikasi
                            </button>
                        </form>
                        <button type="button"
                                data-revisi-trigger
                                data-revisi-url="/admin/surat/{{ $surat->id }}/revisi"
                                data-surat-name="{{ $surat->jenisSurat->nama }} - {{ $namaPengusul }}"
                                class="sakti-touch-target flex-1 px-3 py-2 rounded-lg bg-amber-700 hover:bg-amber-800 text-white text-sm font-semibold">
                            Kembalikan Revisi
                        </button>
                    @endif

                    <button type="button"
                            data-preview-trigger
                            data-preview-url="/surat/{{ $surat->id }}/pratinjau"
                            data-surat-name="{{ $surat->jenisSurat->nama }}"
                            class="sakti-touch-target flex-1 px-3 py-2 rounded-lg border border-gray-400 bg-white text-gray-900 hover:bg-gray-100 text-sm font-semibold">
                        Pratinjau
                    </button>

                    <a href="/admin/surat/{{ $surat->id }}"
                       class="sakti-touch-target flex-1 px-3 py-2 rounded-lg bg-blue-700 hover:bg-blue-800 text-white text-sm font-semibold">
                        Detail
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endif

<x-modal-revisi-tu />
<x-modal-pratinjau-surat />
