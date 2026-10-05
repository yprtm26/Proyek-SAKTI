@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto p-6">
    
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-700">
            {{ $jenisSurat->nama }}
        </h2>
        <p class="text-sm text-gray-500">
            Isi data berikut dengan benar
        </p>
    </div>

    <form method="POST"
          action="{{ url('/dosen/surat/ajukan/'.$jenisSurat->id) }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        <table class="w-full border mt-2" id="anggota-table">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-2 w-1/12">No</th>
                        <th class="border p-2">Nama</th>
                        <th class="border p-2 w-1/12">Hapus</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border p-2 text-center nomor">1</td>
                        <td class="border p-2">
                            <input type="text"
                                name="anggota[0][nama]"
                                class="w-full border rounded px-2 py-1"
                                placeholder="Nama lengkap & gelar"
                                required>
                        </td>
                        <td class="border p-2 text-center">
                            <button type="button"
                                onclick="hapusBaris(this)"
                                class="text-red-600 font-bold">
                                ✕
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
        <div>
            <button type="button"
                onclick="tambahBaris()"
                class="mt-2 px-3 py-1 bg-blue-600 text-white rounded">
                + Tambah Anggota
            </button>
        </div>

        {{-- JUDUL BUKU AJAR --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Judul Buku Ajar
            </label>
            <input type="text"
                   name="judul"
                   value="{{ old('judul') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Judul Buku Ajar"
                   required>
        </div>

        {{-- PROGRAM STUDI --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Program Studi
            </label>
            <input type="text"
                   name="prodi"
                   value="{{ old('prodi') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        {{-- TANGGAL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tanggal Pelaksanaan
            </label>
            <input type="date"
                   name="tanggal"
                   value="{{ old('tanggal') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
        </div>

        {{-- TEMPAT --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Tempat Mengirimkan Pendaftaran
            </label>
            <input type="text"
                   name="tempat"
                   value="{{ old('tempat') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Jenderal Kekayaan Intelektual, Kementerian Hukum dan Hak Asasi Manusia"
                   required>
        </div>

        {{-- ACTION --}}
        <div class="flex justify-end gap-3 pt-4">
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-green-500 hover:bg-green-600 text-white font-semibold">
                Ajukan Surat
            </button>

            <a href="{{ url('/dosen/dashboard') }}"
               class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300">
                Batal
            </a>
        </div>

    </form>

</main>

<script>
let indexAnggota = 1;

function tambahBaris() {
    const tbody = document.querySelector('#anggota-table tbody');

    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="border p-2 text-center nomor"></td>
        <td class="border p-2">
            <input type="text"
                name="anggota[${indexAnggota}][nama]"
                class="w-full border rounded px-2 py-1"
                placeholder="Nama lengkap & gelar"
                required>
        </td>
        <td class="border p-2 text-center">
            <button type="button"
                onclick="hapusBaris(this)"
                class="text-red-600 font-bold">
                ✕
            </button>
        </td>
    `;

    tbody.appendChild(row);
    indexAnggota++;
    updateNomor();
}

function hapusBaris(btn) {
    const tbody = document.querySelector('#anggota-table tbody');

    if (tbody.rows.length === 1) {
        alert('Minimal satu anggota harus ada');
        return;
    }

    btn.closest('tr').remove();
    updateNomor();
}

function updateNomor() {
    document.querySelectorAll('.nomor').forEach((el, i) => {
        el.innerText = i + 1;
    });
}
</script>
@endsection
