@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto p-6">

    {{-- JUDUL --}}
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">
            Tambah Akun Pengguna
        </h2>
    </div>

    {{-- FORM --}}
    <form method="POST"
          action="{{ url('/administrator/simpan-akun') }}"
          class="bg-white shadow rounded-lg p-6 space-y-5">
        @csrf

        {{-- NAMA LENGKAP --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Nama Lengkap (beserta gelar)
            </label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- USERNAME --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Username Login
            </label>
            <input type="text"
                   name="username"
                   value="{{ old('username') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
            @error('username')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- EMAIL --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Email Aktif
            </label>
            <input type="email"
                   name="email"
                   value="{{ old('email') }}"
                   class="w-full border rounded-lg px-3 py-2"
                   required>
            @error('email')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- PASSWORD --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Password Awal
            </label>
            <input type="password"
                   name="password"
                   class="w-full border rounded-lg px-3 py-2"
                   placeholder="Minimal 6 karakter"
                   required>
            @error('password')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- ROLE --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Peran Pengguna
            </label>
            <select name="role"
                    class="w-full border rounded-lg px-3 py-2"
                    required>
                <option value="">-- Pilih Role --</option>
                <option value="admin" {{ old('role')=='admin'?'selected':'' }}>
                    Admin
                </option>
                <option value="kaprodi" {{ old('role')=='kaprodi'?'selected':'' }}>
                    Kaprodi
                </option>
                <option value="dekan" {{ old('role')=='dekan'?'selected':'' }}>
                    Dekan
                </option>
            </select>
            @error('role')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- PRODI (KHUSUS KAPRODI) --}}
        <div id="prodiField" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Program Studi
            </label>
            <select name="prodi"
                    class="w-full border rounded-lg px-3 py-2">
                <option value="">-- Pilih Prodi --</option>
                <option value="TI" {{ old('prodi')=='TI'?'selected':'' }}>
                    Teknik Informatika
                </option>
                <option value="PdSI" {{ old('prodi')=='PdSI'?'selected':'' }}>
                    Perpustakaan dan Sains Informasi
                </option>
            </select>
            @error('prodi')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- AKSI --}}
        <div class="flex justify-end gap-3 pt-4">
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold">
                Simpan Akun
            </button>

            <a href="{{ url('/administrator/dashboard') }}"
               class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700">
                Kembali
            </a>
        </div>

    </form>


</main>

<script>
    const roleSelect = document.querySelector('[name="role"]');
    const prodiField = document.getElementById('prodiField');

    function toggleProdi() {
        if (roleSelect.value === 'kaprodi') {
            prodiField.style.display = 'block';
        } else {
            prodiField.style.display = 'none';
        }
    }

    // jalan saat load (biar old value kebaca)
    toggleProdi();

    // jalan saat berubah
    roleSelect.addEventListener('change', toggleProdi);
</script>
@endsection