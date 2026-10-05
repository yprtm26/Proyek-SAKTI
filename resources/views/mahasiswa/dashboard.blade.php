@extends('layouts.app')

@section('content')
<main class="max-w-7xl mx-auto px-6 py-8">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-8">
        <h2 class="text-2xl font-semibold text-gray-700 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
            </svg>
            Daftar Form Surat Mahasiswa
        </h2>

        <div class="flex items-center gap-3">

            {{-- SEARCH --}}
            <form id="searchForm" method="GET" action="{{ url('/mahasiswa/dashboard') }}" class="relative">
                <input
                    type="text"
                    id="searchInput"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari..."
                    autocomplete="off"
                    class="pl-11 pr-10 py-2 w-full
                        border border-gray-300 rounded-full
                        focus:outline-none focus:ring-2 focus:ring-green-400">

                <svg class="absolute left-4 top-2.5 w-5 h-5 text-gray-400"
                    fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-4.35-4.35M16.65 10.825a5.825 5.825 0 11-11.65 0 5.825 5.825 0 0111.65 0z"/>
                </svg>

                @if(request('search'))
                    <a href="{{ url('/mahasiswa/dashboard') }}"
                    class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                        ✕
                    </a>
                @endif
            </form>

            {{-- LOG SURAT --}}
            <a href="{{ url('/mahasiswa/surat') }}"
                class="inline-flex items-center gap-2
                        px-4 py-2
                        bg-white border border-gray-300
                        text-gray-700 text-sm font-medium
                        rounded-full
                        hover:bg-gray-100 hover:border-gray-400
                        transition">

                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="w-4 h-4 text-gray-500"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12.75V6.75a3 3 0 013-3h4.28
                            a1.5 1.5 0 011.06.44l1.72 1.72
                            a1.5 1.5 0 001.06.44H18.75
                            a3 3 0 013 3v3.75" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12.75A3.75 3.75 0 016 9h15.75
                            a3.75 3.75 0 013.75 3.75v4.5
                            A3.75 3.75 0 0118.75 21H6
                            a3.75 3.75 0 01-3.75-3.75v-4.5z" />
                    </svg>

                    Log Surat
                </a>
        </div>
    </div>

    {{-- CARD LIST --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6 items-stretch">

        @foreach($jenisSurat as $surat)
            <div class="bg-white border border-gray-200
                    rounded-xl p-5
                    shadow-sm hover:shadow-md
                    hover:-translate-y-0.5 transition-all duration-200
                    flex flex-col items-center text-center h-full">

                <div class="flex justify-center text-gray-500 mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke-width="1.5"
                        stroke="currentColor"
                        class="w-10 h-10">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15
                            a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0
                            A2.25 2.25 0 0 0 19.5 4.5h-15
                            a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243
                            a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615
                            a2.25 2.25 0 0 1-2.36 0L3.32 8.91
                            a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                </div>

                <h3 class="text-sm font-semibold text-gray-800 mb-4 line-clamp-2">
                    {{ $surat->nama }}
                </h3>

                <a href="{{ url('/mahasiswa/surat/ajukan/'.$surat->id) }}"
                class="w-full bg-green-500 hover:bg-green-600
                        text-white text-sm font-semibold
                        py-2.5 rounded-lg transition mt-auto">
                    + Ajukan Surat
                </a>
            </div>

        @endforeach

        {{-- JIKA KOSONG --}}
        @if($jenisSurat->isEmpty())
            <div class="col-span-full text-center text-gray-500 mt-6">
                Jenis surat tidak ditemukan
            </div>
        @endif

    </div>
</main>

<script>
    let timer = null;

    document.getElementById('searchInput').addEventListener('input', function () {
        clearTimeout(timer);

        timer = setTimeout(() => {
            document.getElementById('searchForm').submit();
        }, 400); 
    });
</script>

@endsection
