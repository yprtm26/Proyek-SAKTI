@php
    $personelList = \App\Support\SuratPersonelSync::readFromSurat($surat);
@endphp

@if(count($personelList) > 0)
<div class="bg-white rounded shadow p-6 mb-6">
    <h3 class="font-semibold text-gray-800 mb-4">
        Daftar Anggota dan Peran Kegiatan
    </h3>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-800">
                <tr>
                    <th scope="col" class="px-3 py-2 text-center w-12">No</th>
                    <th scope="col" class="px-3 py-2 text-left">Nama Lengkap</th>
                    <th scope="col" class="px-3 py-2 text-left">Peran dalam Kegiatan</th>
                    <th scope="col" class="px-3 py-2 text-left">Instansi / NIM / NIDN</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($personelList as $idx => $anggota)
                    <tr>
                        <td class="px-3 py-2 text-center text-gray-700">{{ $idx + 1 }}</td>
                        <td class="px-3 py-2 font-medium text-gray-900">{{ $anggota['nama'] !== '' ? $anggota['nama'] : '-' }}</td>
                        <td class="px-3 py-2 text-gray-800">{{ $anggota['peran'] !== '' ? $anggota['peran'] : '-' }}</td>
                        <td class="px-3 py-2 text-gray-700">{{ $anggota['instansi'] !== '' ? $anggota['instansi'] : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
