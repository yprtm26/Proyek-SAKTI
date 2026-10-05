@extends('layouts.app')

@section('content')

<div class="bg-white rounded-lg shadow">

    {{-- HEADER --}}
    <div class="bg-green-600 text-white px-6 py-3 rounded-t-lg font-semibold">
        Panduan & Sumber Daya
    </div>

    <div class="p-6">

        {{-- DESKRIPSI --}}
        <div class="mb-4 text-sm text-gray-600">
            Silakan download panduan atau dokumen yang tersedia di bawah ini.
        </div>

        {{-- TABLE --}}
        <table class="w-full text-sm border">
            <thead class="bg-gray-200">
                <tr>
                    <th class="p-2 w-12">No</th>
                    <th class="p-2 text-left">Nama File</th>
                    <th class="p-2 text-center w-28">Download</th>
                </tr>
            </thead>

            <tbody>
                @forelse($data as $i => $item)
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-2 text-center">{{ $i+1 }}</td>
                    <td class="p-2">{{ $item->nama }}</td>
                    <td class="p-2 text-center">

                        <a href="/sumberdaya/download/{{ $item->id }}"
                           class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs">
                            Download
                        </a>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-6 text-gray-500">
                        Belum ada file tersedia
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

@endsection