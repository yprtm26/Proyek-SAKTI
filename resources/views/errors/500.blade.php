<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Terjadi Kendala Sistem | SAKTI</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-lg w-full bg-white rounded-xl shadow-lg border border-gray-200 p-8 text-center">
        {{-- ICON KENDALA --}}
        <div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Terjadi Kendala Sistem</h1>
        <p class="text-gray-600 text-sm leading-relaxed mb-6">
            Terjadi kendala teknis pada sistem persuratan SAKTI saat memproses permintaan Anda.
            Permintaan ditolak demi keamanan data sivitas akademika. Detail kendala telah dicatat ke dalam log keamanan server.
        </p>

        {{-- BOX INCIDENT ID --}}
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
            <span class="block text-xs uppercase tracking-wider text-orange-700 font-semibold mb-1">ID Kejadian (Incident ID)</span>
            <span class="font-mono text-lg font-bold text-gray-900 select-all">{{ $incidentId ?? 'ERR-' . date('Ymd') . '-0001' }}</span>
            <p class="text-xs text-gray-500 mt-2">
                Sertakan ID Kejadian ini saat melapor ke Helpdesk FTI Universitas YARSI agar kendala dapat ditelusuri.
            </p>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/') }}" class="inline-flex justify-center items-center px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg text-sm transition">
                Kembali ke Beranda
            </a>
            <button onclick="window.history.back()" class="inline-flex justify-center items-center px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium rounded-lg text-sm transition">
                Halaman Sebelumnya
            </button>
        </div>
    </div>
</body>
</html>
