@extends('layouts.app')
@section('content')

<h2 class="text-xl font-semibold mb-2">
    {{ $jenisSurat->nama }}
</h2>
<p class="text-sm text-gray-500 mb-6">
    Pilih mata kuliah remedial untuk setiap mahasiswa
</p>

<form method="POST"
      action="/mahasiswa/surat/ajukan/{{ $jenisSurat->id }}"
      class="space-y-6">
@csrf

<div class="bg-white p-6 rounded shadow">

    <div class="flex justify-between items-center mb-4">
        <h3 class="font-semibold text-gray-700">
            Daftar Mahasiswa
        </h3>
        <button type="button"
                onclick="addRow()"
                class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-2 rounded">
            + Tambah Mahasiswa
        </button>
    </div>

    <div class="overflow-x-auto">
    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border px-3 py-2">No</th>
                <th class="border px-3 py-2">Nama</th>
                <th class="border px-3 py-2">NPM</th>
                <th class="border px-3 py-2">Mata Kuliah Remedial</th>
                <th class="border px-3 py-2">Aksi</th>
            </tr>
        </thead>

        <tbody id="mhsTable">
            {{-- PEMOHON --}}
            <tr>
                <td class="border px-3 py-2 text-center">1</td>
                <td class="border px-3 py-2">
                    <input name="mahasiswa[0][nama]"
                           value="{{ auth()->user()->name }}"
                           readonly
                           class="w-full border rounded px-2 py-1 bg-gray-100">
                </td>
                <td class="border px-3 py-2">
                    <input name="mahasiswa[0][npm]"
                           value="{{ auth()->user()->npm }}"
                           readonly
                           class="w-full border rounded px-2 py-1 bg-gray-100">
                </td>
                <td class="border px-3 py-2">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        @for($i=1;$i<=6;$i++)
                        <label class="flex items-center gap-1">
                            <input type="checkbox"
                                   name="mahasiswa[0][mata_kuliah][]"
                                   value="Bahasa Inggris {{ $i }}">
                            Bahasa Inggris {{ $i }}
                        </label>
                        @endfor
                    </div>
                </td>
                <td class="border px-3 py-2 text-center text-gray-400">
                    Pemohon
                </td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

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

<script>
let index = 1;

function addRow() {
    const table = document.getElementById('mhsTable');
    const row = document.createElement('tr');

    let checkboxHTML = '';
    for (let i = 1; i <= 6; i++) {
        checkboxHTML += `
        <label class="flex items-center gap-1">
            <input type="checkbox"
                   name="mahasiswa[${index}][mata_kuliah][]"
                   value="Bahasa Inggris ${i}">
            Bahasa Inggris ${i}
        </label>`;
    }

    row.innerHTML = `
        <td class="border px-3 py-2 text-center">${index+1}</td>
        <td class="border px-3 py-2">
            <input name="mahasiswa[${index}][nama]"
                   class="w-full border rounded px-2 py-1" required>
        </td>
        <td class="border px-3 py-2">
            <input name="mahasiswa[${index}][npm]"
                   class="w-full border rounded px-2 py-1" required>
        </td>
        <td class="border px-3 py-2">
            <div class="grid grid-cols-2 gap-2 text-xs">
                ${checkboxHTML}
            </div>
        </td>
        <td class="border px-3 py-2 text-center">
            <button type="button"
                    onclick="this.closest('tr').remove(); renumber();"
                    class="text-red-600 text-xs">
                Hapus
            </button>
        </td>
    `;

    table.appendChild(row);
    index++;
}

function renumber() {
    document.querySelectorAll('#mhsTable tr').forEach((tr, i) => {
        tr.children[0].innerText = i + 1;
    });
}
</script>

@endsection
