<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Ditolak | SAKTI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
        {{-- ICON ACCESS DENIED --}}
        <div class="w-16 h-16 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Akses Ditolak (403)</h1>
        <p class="text-gray-600 text-sm leading-relaxed mb-6">
            Anda tidak memiliki izin atau hak akses untuk membuka halaman atau dokumen persuratan ini. Kejadian akses ini telah dicatat ke dalam log audit keamanan.
        </p>

        <a href="{{ url('/') }}" class="inline-flex justify-center items-center px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg text-sm transition">
            Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
