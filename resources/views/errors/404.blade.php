<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Dokumen Tidak Ditemukan | SAKTI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/sakti-ui.css') }}">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <main class="max-w-md w-full bg-white rounded-xl shadow-sm border border-gray-300 p-8 text-center">
        <div class="w-16 h-16 bg-orange-100 text-orange-800 rounded-full flex items-center justify-center mx-auto mb-4" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Halaman Tidak Ditemukan (404)</h1>
        <p class="text-gray-700 text-sm leading-relaxed mb-6">
            Dokumen surat atau halaman yang Anda cari tidak tersedia atau telah dihapus dari sistem.
        </p>

        @auth
            <a href="{{ auth()->user()->dashboardUrl() }}"
               class="inline-flex justify-center items-center min-h-[44px] px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white font-semibold rounded-lg text-sm transition focus-visible:outline-none">
                Kembali ke Dashboard
            </a>
        @else
            <a href="{{ route('login') }}"
               class="inline-flex justify-center items-center min-h-[44px] px-5 py-2.5 bg-orange-700 hover:bg-orange-800 text-white font-semibold rounded-lg text-sm transition focus-visible:outline-none">
                Kembali ke Halaman Login
            </a>
        @endauth
    </main>
</body>
</html>
