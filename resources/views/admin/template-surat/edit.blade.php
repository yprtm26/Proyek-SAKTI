@extends('layouts.app')

@section('content')
<main class="max-w-3xl mx-auto">

    {{-- JUDUL --}}
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-800">
            Edit Template Surat
        </h2>
        <p class="text-sm text-gray-500 mt-1">
            Perbarui informasi template "{{ $jenisSurat->nama }}".
        </p>
    </div>

    @include('admin.template-surat._form', [
        'jenisSurat'  => $jenisSurat,
        'action'      => route('admin.template-surat.update', $jenisSurat),
        'method'      => 'PUT',
        'submitLabel' => 'Perbarui Template',
    ])

</main>
@endsection
