<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Pengajuan Surat</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com/3.4.1"></script>

    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-fadeIn {
            animation: fadeIn 0.8s ease-in-out;
        }
    </style>
</head>

<body class="min-h-screen">

<div class="flex min-h-screen">

    <!-- ================= LEFT : LOGIN BOX ================= -->
    <div class="w-full md:w-1/2 flex items-center justify-center bg-orange-500">
        <div class="w-full max-w-sm p-8 animate-fadeIn
            bg-white/95
            rounded-2xl
            shadow-2xl
            border border-gray-100">


            <!-- Logo -->
            <div class="flex justify-center mb-4">
                <img src="{{ asset('img/logo.jpeg') }}" alt="Logo Universitas YARSI" class="w-36">
            </div>

            @if ($errors->any())
                <div class="mb-4 text-sm text-red-600 text-center">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ url('/login') }}" class="space-y-4">
                @csrf

                <!-- Username -->
                <div>
                    <label class="block text-sm mb-1 text-gray-700">Username</label>
                    <input type="text" name="username" required
                        class="w-full px-4 py-2 border rounded-lg
                        focus:ring-2 focus:ring-orange-500">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm mb-1 text-gray-700">Password</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                            class="w-full px-4 py-2 pr-12 border rounded-lg
                            focus:ring-2 focus:ring-orange-500">

                        <button type="button"
                            onclick="togglePassword()"
                            id="toggleIcon"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-orange-500">

                            <svg id="eyeClosed" xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
                                <line x1="3" y1="3" x2="21" y2="21"/>
                            </svg>

                            <svg id="eyeOpen" xmlns="http://www.w3.org/2000/svg"
                                class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M1.5 12s4-7.5 10.5-7.5S22.5 12 22.5 12s-4 7.5-10.5 7.5S1.5 12 1.5 12z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>

                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-orange-500 hover:bg-orange-600
                    text-white py-2 rounded-lg font-semibold transition">
                    Login
                </button>

                <div class="text-center">
                    <a href="https://www.yarsi.ac.id/ganti-password-akun-yarsi"
                       class="text-sm text-gray-600 hover:text-orange-500">
                        Lupa Password?
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- ================= RIGHT : BACKGROUND IMAGE ================= -->
<div class="hidden md:block w-1/2 relative text-white">

    <!-- Background Image -->
    <div class="absolute inset-0 bg-cover bg-center"
         style="background-image: url('{{ asset('img/kampus.jpeg') }}');">
    </div>

    <!-- Orange Overlay -->
    <div class="absolute inset-0 bg-orange-500/70"></div>

    <!-- Text Content -->
    <div class="relative z-10 h-full flex items-center px-16">
        <div class="max-w-lg animate-fadeIn">
            <h1 class="text-4xl font-bold mb-4 leading-tight">
                Selamat Datang di SAKTI
            </h1>
            <p class="text-lg leading-relaxed text-orange-100">
                Layanan Sistem Administrasi Akademik
            </p>
            <p class="text-lg leading-relaxed text-orange-100">
                Fakultas Teknologi Informasi
                Universitas YARSI
            </p>
        </div>
    </div>

</div>

</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const eyeOpen = document.getElementById('eyeOpen');  
        const eyeClosed = document.getElementById('eyeClosed'); 

        if (input.type === 'password') {
            input.type = 'text';
            eyeOpen.classList.remove('hidden');  
            eyeClosed.classList.add('hidden');   
        } else {
            input.type = 'password';
            eyeOpen.classList.add('hidden');     
            eyeClosed.classList.remove('hidden'); 
        }
    }
</script>

</body>
</html>