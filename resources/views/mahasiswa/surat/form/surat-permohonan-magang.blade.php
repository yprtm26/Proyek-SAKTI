@extends('layouts.app')

@section('content')
@php
    $user = auth()->user();
    $prodi = $user->prodi; // TI / PdSI
@endphp

<main class="max-w-6xl mx-auto p-6">

    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">
            {{ $jenisSurat->nama }}
        </h2>
        <p class="text-sm text-gray-500">
            Isi permohonan magang dengan data yang benar
        </p>
    </div>

    <form method="POST" action="/mahasiswa/surat/ajukan/{{ $jenisSurat->id }}">
        @csrf

        <div class="bg-white rounded-xl shadow p-6 space-y-8">

            {{-- ================= JENIS MAGANG ================= --}}
            <section>
                <h3 class="font-semibold text-gray-700 mb-3">Jenis Magang</h3>

                <select name="tipe_magang" required
                        class="w-full border rounded-lg px-4 py-2">
                    <option value="">-- Pilih Jenis Magang --</option>

                    @if($prodi === 'TI')
                        <option value="reguler-ti">Magang Reguler (TI)</option>
                    @endif

                    @if($prodi === 'PdSI')
                        <option value="reguler-pdsi">Magang Reguler (PdSI)</option>
                    @endif

                    <option value="mandiri">Magang Mandiri</option>
                </select>
            </section>

            {{-- ================= INFO SURAT ================= --}}
            <section>
                <h3 class="font-semibold text-gray-700 mb-3">Informasi Surat</h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <input name="perihal" required
                        placeholder="Perihal Surat (Contoh: Permohonan Izin Praktek Kerja Lapangan (PKL)"
                        class="border rounded-lg px-4 py-2">

                    <input name="kepada_yth" required
                        placeholder="Kepada Yth. (Contoh: Human Capital)"
                        class="border rounded-lg px-4 py-2">
                </div>
            </section>

            {{-- ================= MITRA ================= --}}
            <section>
                <h3 class="font-semibold text-gray-700 mb-3">Informasi Mitra</h3>

                <input name="nama_mitra" required
                    placeholder="Nama Mitra"
                    class="w-full border rounded-lg px-4 py-2 mb-3">

                <textarea name="alamat_mitra" rows="3" required
                    placeholder="Alamat lengkap mitra (pastikan alamat sudah sesuai)"
                    class="w-full border rounded-lg px-4 py-2"></textarea>
            </section>

            {{-- ================= PERIODE ================= --}}
            <section>
                <h3 class="font-semibold text-gray-700 mb-3">Periode Magang</h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Tanggal Mulai
                        </label>
                        <input type="date" name="tanggal_mulai" required
                            class="w-full border rounded-lg px-4 py-2">
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600 mb-1">
                            Tanggal Selesai
                        </label>
                        <input type="date" name="tanggal_selesai" required
                            class="w-full border rounded-lg px-4 py-2">
                    </div>
                </div>

                <p class="text-sm text-gray-500 mt-2">
                    * Pastikan periode magang sudah benar dan sesuai.
                </p>

            </section>

            {{-- ================= MAHASISWA ================= --}}
            <section>
                <h3 class="font-semibold text-gray-700 mb-3">Data Mahasiswa</h3>

                <table class="w-full border text-sm" id="tabelMahasiswa">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-2 py-2 w-10">#</th>
                            <th class="border px-2 py-2">NPM</th>
                            <th class="border px-2 py-2">Nama</th>
                            <th class="border px-2 py-2">Semester</th>
                            <th class="border px-2 py-2">No. Kontak</th>
                            <th class="border px-2 py-2 w-14">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- PENGAJU (AUTO) --}}
                        <tr>
                            <td class="border px-2 py-2 text-center">1</td>

                            <td class="border px-2 py-2">
                                <input name="mahasiswa[0][npm]"
                                       value="{{ $user->npm }}"
                                       readonly
                                       class="w-full bg-gray-100 border rounded px-2 py-1">
                            </td>

                            <td class="border px-2 py-2">
                                <input name="mahasiswa[0][nama]"
                                       value="{{ $user->name }}"
                                       readonly
                                       class="w-full bg-gray-100 border rounded px-2 py-1">
                            </td>

                            <td class="border px-2 py-2">
                                <input name="mahasiswa[0][semester]"
                                       required
                                       class="w-full border rounded px-2 py-1">
                            </td>

                            <td class="border px-2 py-2">
                                <input name="mahasiswa[0][no_kontak]"
                                       value="{{ $user->telephonenumber }}"
                                       required
                                       class="w-full border rounded px-2 py-1">
                            </td>

                            <td class="border px-2 py-2 text-center">-</td>
                        </tr>
                    </tbody>
                </table>

                {{-- TAMBAHAN --}}
                <button type="button"
                        onclick="tambahMahasiswa()"
                        class="mt-3 px-4 py-2 bg-blue-600 text-white rounded">
                    + Tambah Mahasiswa
                </button>

                <p class="text-xs text-gray-500 mt-2">
                    * Data pengaju diisi otomatis dari akun login
                </p>
            </section>

            {{-- ================= ACTION ================= --}}
            <div class="flex justify-end gap-3">
                <a href="/mahasiswa/dashboard"
                   class="px-4 py-2 bg-gray-200 rounded">
                    Batal
                </a>
                <button class="px-6 py-2 bg-green-600 text-white rounded">
                    Ajukan Surat
                </button>
            </div>

        </div>
    </form>
</main>

{{-- ================= SCRIPT TAMBAHAN ================= --}}
<script>
let indexMahasiswa = 1;

function tambahMahasiswa() {
    const tbody = document.querySelector('#tabelMahasiswa tbody');
    const nomor = tbody.children.length + 1;

    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="border px-2 py-2 text-center">${nomor}</td>
        <td class="border px-2 py-2">
            <input name="mahasiswa[${indexMahasiswa}][npm]" required class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-2">
            <input name="mahasiswa[${indexMahasiswa}][nama]" required class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-2">
            <input name="mahasiswa[${indexMahasiswa}][semester]" required class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-2">
            <input name="mahasiswa[${indexMahasiswa}][no_kontak]" required class="w-full border rounded px-2 py-1">
        </td>
        <td class="border px-2 py-2 text-center">
            <button type="button" onclick="this.closest('tr').remove()"
                class="text-red-600 font-semibold">✕</button>
        </td>
    `;
    tbody.appendChild(tr);
    indexMahasiswa++;
}
</script>
@endsection
