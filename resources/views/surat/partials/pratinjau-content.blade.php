@php
    $previewView = \App\Support\SuratPreview::resolveView($surat);
@endphp

<div class="sakti-preview-document bg-white text-black rounded border border-gray-300 p-4 sm:p-6">
    @include($previewView, ['surat' => $surat])
</div>
