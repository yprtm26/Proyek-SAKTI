<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Sistem Pengajuan Surat' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- TAILWIND --}}
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col">

@php
    use App\Models\Notification;

    $user = auth()->user();

    $titleRole = match($user->role) {
            'admin'   => 'Admin TU<br>Fakultas Teknologi Informasi',
            'administrator'   => 'Administrator<br>Fakultas Teknologi Informasi',
            'kaprodi' => 'Kepala Prodi<br>' . namaProdi($user->prodi),
            'dekan'   => 'Dekan<br>Fakultas Teknologi Informasi',
            'dosen'   => 'Dosen<br>Fakultas Teknologi Informasi',
             default   => 'Mahasiswa<br>' . namaProdi($user->prodi)
    };
@endphp

{{-- ================= HEADER ================= --}}
<header class="bg-gradient-to-r from-orange-600 to-orange-500 text-white shadow">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">

        {{-- LEFT --}}
        <div class="flex items-center gap-4">
            @auth
            <a href="{{ auth()->user()->dashboardUrl() }}"
                class="flex items-center gap-4 hover:opacity-90 transition">
            @endauth
                <img src="{{ asset('img/logo.jpeg') }}"
                    class="h-12 bg-white rounded p-1">

                <h1 class="text-lg font-semibold leading-tight">
                    {!! $titleRole !!}
                </h1>
            </a>
        </div>

        {{-- RIGHT --}}
        <div class="flex items-center gap-4 h-12">

            {{-- ================= NOTIFIKASI ================= --}}
            <div class="relative group">

                {{-- ICON --}}
                <button onclick="toggleNotif()"
                        class="relative flex items-center justify-center w-8 h-8">

                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="h-6 w-6 text-white"
                         fill="none" viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032
                              2.032 0 0118 14.158V11a6.002
                              6.002 0 00-4-5.659V4a2
                              2 0 10-4 0v1.341C7.67
                              6.165 6 8.388 6 11v3.159
                              c0 .538-.214 1.055-.595
                              1.436L4 17h5m6 0v1a3
                              3 0 11-6 0v-1m6 0H9" />
                    </svg>

                    {{-- BADGE --}}
                    @if($unreadCount > 0)
                        <span class="absolute -top-2 -right-2
                                     bg-red-600 text-white text-xs
                                     px-1.5 py-0.5 rounded-full">
                            {{ $unreadCount }}
                        </span>
                    @endif
                </button>

                {{-- DROPDOWN --}}
                <div id="notifDropdown"
                     class="hidden absolute right-0 mt-3 w-96 bg-white
                            rounded-lg shadow-lg border z-50">

                    <div class="px-4 py-3 border-b
                                font-semibold text-gray-700">
                        Notifikasi
                    </div>

                    <div class="max-h-96 overflow-y-auto">

                        @forelse($notifications as $notif)
                            <a href="{{ $notif->url ?? '#' }}"
                               class="block px-4 py-3 border-b text-sm
                               {{ !$notif->is_read
                                   ? 'bg-orange-50 hover:bg-orange-100'
                                   : 'hover:bg-gray-50' }}">

                                <div class="font-semibold text-gray-800">
                                    {{ $notif->title }}
                                </div>

                                <div class="text-gray-600 text-xs mt-1">
                                    {{ $notif->message }}
                                </div>

                                <div class="text-gray-400 text-xs mt-1">
                                    {{ optional($notif->created_at)->diffForHumans() }}
                                </div>
                            </a>
                        @empty
                            <div class="px-4 py-6 text-center
                                        text-gray-500 text-sm">
                                Tidak ada notifikasi
                            </div>
                        @endforelse

                    </div>

                    <div class="px-4 py-2 text-center text-sm bg-gray-50">
                        <a href="/notifikasi"
                           class="text-blue-600 hover:underline">
                            Lihat semua notifikasi
                        </a>
                    </div>
                </div>
            </div>

            {{-- ================= MANUAL BOOK ================= --}}
            <div class="relative group">

                <a href="{{ route('sumberdaya.index') }}"
                class="flex items-center justify-center w-8 h-8 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </a>

                <div class="absolute bottom-[-30px] left-1/2 -translate-x-1/2
                            bg-black text-white text-xs px-2 py-1 rounded
                            opacity-0 group-hover:opacity-100
                            transition duration-200 ease-in-out
                            pointer-events-none whitespace-nowrap">
                    Panduan & Sumber Daya
                </div>
            </div>

            <div class="h-5 w-px bg-white/30 mx-2"></div>

            {{-- ================= USER ================= --}}
            <div class="flex items-center gap-4 h-10">
                <div class="text-right leading-tight">
                    <p class="text-sm font-medium">{{ $user->name }}</p>
                    <a href="{{ route('profile.index') }}"
                    class="text-xs hover:underline">
                        Lihat Profil
                    </a>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="flex items-center gap-2 bg-red-600 text-white px-4 py-2
                            rounded-md text-sm font-medium transition duration-200
                            hover:bg-orange-700 focus:ring-2 focus:ring-red-400 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"
                            fill="currentColor" class="size-4">
                            <path fill-rule="evenodd" d="M14 4.75A2.75 2.75 0 0 0 11.25 2h-3A2.75 2.75 0 0 0 5.5 4.75v.5a.75.75 0 0 0 1.5 0v-.5c0-.69.56-1.25 1.25-1.25h3c.69 0 1.25.56 1.25 1.25v6.5c0 .69-.56 1.25-1.25 1.25h-3c-.69 0-1.25-.56-1.25-1.25v-.5a.75.75 0 0 0-1.5 0v.5A2.75 2.75 0 0 0 8.25 14h3A2.75 2.75 0 0 0 14 11.25v-6.5Zm-9.47.47a.75.75 0 0 0-1.06 0L1.22 7.47a.75.75 0 0 0 0 1.06l2.25 2.25a.75.75 0 1 0 1.06-1.06l-.97-.97h7.19a.75.75 0 0 0 0-1.5H3.56l.97-.97a.75.75 0 0 0 0-1.06Z"
                                clip-rule="evenodd" />
                        </svg>
                        Logout
                    </button>
                </form>
            </div>

        </div>
    </div>
</header>

{{-- ================= CONTENT ================= --}}
<main class="flex-1 w-full">
    <div class="max-w-7xl mx-auto px-6 py-6">
        @yield('content')
    </div>
</main>

@if(session('success'))
    <div class="fixed bottom-6 right-6 bg-green-600 text-white
                px-4 py-2 rounded shadow-lg text-sm">
        {{ session('success') }}
    </div>
@endif

<script>
    function toggleNotif() {
        const el = document.getElementById('notifDropdown');
        el.classList.toggle('hidden');
    }

    document.addEventListener('click', function (e) {
        const notif = document.getElementById('notifDropdown');
        if (!e.target.closest('.relative')) {
            notif?.classList.add('hidden');
        }
    });
</script>

{{-- ================= INFO KONTAK ================= --}}
<section class="bg-gray-200 border-t">
    <div class="max-w-7xl mx-auto px-6 py-4
                flex flex-col md:flex-row justify-between gap-6
                text-sm text-gray-700">

        {{-- ALAMAT --}}
        <div class="flex gap-3 flex-1">
            <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 11c1.657 0 3-1.343 3-3S13.657 5 12 5
                         9 6.343 9 8s1.343 3 3 3z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 22s8-4.5 8-11a8 8 0 10-16 0c0 6.5 8 11 8 11z"/>
            </svg>
            <div>
                <p class="font-semibold text-gray-800">Alamat</p>
                <p>
                    Fakultas Teknologi Informasi<br>
                    Lt. 5 Menara YARSI, Universitas YARSI<br>
                    Jl. Letjen Suprapto No.Kav.13, RT.10/RW.5,<br>
                    Cempaka Putih, Kota Jakarta Pusat, Indonesia
                </p>
            </div>
        </div>

        {{-- KONTAK --}}
        <div class="flex gap-3 flex-1 md:justify-center">
            <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 5a2 2 0 012-2h3l2 5-2 1
                         a11 11 0 005 5l1-2 5 2v3
                         a2 2 0 01-2 2A16 16 0 013 5z"/>
            </svg>
            <div>
                <p class="font-semibold text-gray-800">Kontak</p>
                <p>Email:
                    <a href="mailto:fti@yarsi.ac.id"
                       class="text-blue-600 hover:underline">
                        fti@yarsi.ac.id
                    </a>
                </p>
            </div>
        </div>

        {{-- JAM LAYANAN --}}
        <div class="flex gap-3 flex-1 md:justify-end">
            <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0
                         9 9 0 0118 0z"/>
            </svg>
            <div>
                <p class="font-semibold text-gray-800">Jam Layanan</p>
                <p>Senin – Jumat</p>
                <p>08.00 – 16.00 WIB</p>
            </div>
        </div>

    </div>
</section>
{{-- ================= FOOTER ================= --}}
<footer class="bg-gray-300 border-t">
    <div class="max-w-7xl mx-auto px-6 py-2
                text-center text-xs text-gray-700">
        <strong>© SAKTI 2026</strong> | Way2Go Team
    </div>
</footer>
</body>
</html>