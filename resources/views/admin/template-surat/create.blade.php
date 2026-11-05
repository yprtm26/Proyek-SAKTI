@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto">

    {{-- JUDUL --}}
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">
            Buat Template Surat Baru
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Lengkapi informasi template dan tambahkan field dinamis yang akan diisi oleh pengusul.
        </p>
    </div>

    @include('admin.template-surat._form', [
        'jenisSurat'  => null,
        'action'      => route('admin.template-surat.store'),
        'submitLabel' => 'Simpan Template',
    ])

</main>
@endsection
