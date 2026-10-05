@extends('layouts.app')

@section('content')

{{-- ================= HEADER ================= --}}
<div class="mb-6">
    <h2 class="text-xl font-semibold text-gray-700">
        {{ $jenisSurat->nama }}
    </h2>
    <p class="text-sm text-gray-500">
        Isi data berikut dengan benar
    </p>
</div>

<form method="POST" action="/mahasiswa/surat/ajukan/{{ $jenisSurat->id }}"
      class="space-y-6">
    @csrf

    {{-- ================= FORM UTAMA ================= --}}
    <div class="bg-white rounded shadow p-6 space-y-4">

        {{-- PERIHAL --}}
        <div>
            <label class="block text-sm text-gray-600">Perihal</label>
            <input type="text" name="perihal" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Permohonan Izin Observasi Layanan Rujukan Perpustakaan">
        </div>

        {{-- KEPADA --}}
        <div>
            <label class="block text-sm text-gray-600">Kepada Yth.</label>
            <input type="text" name="kepada_yth" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Kepala Perpustakaan">
        </div>

        {{-- INSTANSI --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-gray-600">Nama Instansi</label>
                <input type="text" name="nama_instansi" required
                       class="w-full border rounded px-3 py-2 mt-1"
                       placeholder="Contoh: Perpustakaan Nasional Republik Indonesia">
            </div>

            <div>
                <label class="block text-sm text-gray-600">Kota Instansi</label>
                <input type="text" name="kota_instansi" required
                       class="w-full border rounded px-3 py-2 mt-1"
                       placeholder="Contoh: Jakarta">
            </div>
        </div>

        {{-- MATA KULIAH --}}
        <div>
            <label class="block text-sm text-gray-600">Mata Kuliah</label>
            <input type="text" name="mata_kuliah" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Jasa Rujukan Informasi">
        </div>

        {{-- JENIS KEGIATAN --}}
        <div>
            <label class="block text-sm text-gray-600">Jenis Kegiatan</label>
            <input type="text" name="jenis_kegiatan" required
                   class="w-full border rounded px-3 py-2 mt-1"
                   placeholder="Contoh: Observasi Layanan Rujukan di Perpustakaan">
        </div>
    </div>

    {{-- ================= ANGGOTA TIM ================= --}}
    <div class="bg-white rounded shadow p-6">
        <div class="flex justify-between items-center mb-3">
            <h3 class="text-gray-700">
                <span class="font-semibold">Anggota Tim</span>
                <span class="font-normal">(opsional)</span>
            </h3>
            <button type="button"
                    onclick="addRow()"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-2 rounded">
                + Tambah Anggota
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full border">
                <thead class="bg-gray-100 text-sm">
                    <tr>
                        <th class="border px-3 py-2">No</th>
                        <th class="border px-3 py-2">Nama</th>
                        <th class="border px-3 py-2">NPM</th>
                        <th class="border px-3 py-2">Aksi</th>
                    </tr>
                </thead>
                <tbody id="anggotaTable">
                    {{-- BARIS PERTAMA (PEMOHON) --}}
                    <tr>
                        <td class="border px-3 py-2 text-center">1</td>
                        <td class="border px-3 py-2">
                            <input type="text" name="anggota_tim[0][nama]"
                                   value="{{ auth()->user()->name }}"
                                   class="w-full border rounded px-2 py-1" readonly>
                        </td>
                        <td class="border px-3 py-2">
                            <input type="text" name="anggota_tim[0][npm]"
                                   value="{{ auth()->user()->npm }}"
                                   class="w-full border rounded px-2 py-1" readonly>
                        </td>
                        <td class="border px-3 py-2 text-center text-gray-400">
                            Pemohon
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ================= BUTTON ================= --}}
    <div class="flex justify-end gap-3">
        <a href="/mahasiswa/dashboard"
           class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded">
            Batal
        </a>
        <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded">
            Ajukan Surat
        </button>
    </div>
</form>

{{-- ================= JS ================= --}}
<script>
    let index = 1;

    function addRow() {
        const table = document.getElementById('anggotaTable');
        const row = document.createElement('tr');

        row.innerHTML = `
            <td class="border px-3 py-2 text-center">${index+1}</td>
            <td class="border px-3 py-2">
                <input type="text" name="anggota_tim[${index}][nama]"
                       class="w-full border rounded px-2 py-1" required>
            </td>
            <td class="border px-3 py-2">
                <input type="text" name="anggota_tim[${index}][npm]"
                       class="w-full border rounded px-2 py-1" required>
            </td>
            <td class="border px-3 py-2 text-center">
                <button type="button"
                        onclick="this.closest('tr').remove(); updateNumber();"
                        class="text-red-600 text-sm">
                    Hapus
                </button>
            </td>
        `;
        table.appendChild(row);
        index++;
    }

    function updateNumber() {
        const rows = document.querySelectorAll('#anggotaTable tr');
        rows.forEach((row, i) => {
            row.children[0].innerText = i + 1;
        });
    }
</script>

@endsection
