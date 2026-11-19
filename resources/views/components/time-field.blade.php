@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'minField' => null,
    'hint' => null,
    'errorKey' => null,
    'showError' => true,
    'ariaLabel' => null,
])

@php
    $namaField = $name;
    $idField = $id ?: 'waktu-' . \Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $namaField));

    $nilai = old($namaField, $value);
    $kunciError = $errorKey ?: $namaField;
    $punyaError = $showError && $errors->has($kunciError);

    $idPetunjuk = $idField . '-petunjuk';
    $idError = $idField . '-error';
    $idPicker = $idField . '-picker';

    $deskripsi = collect([
        $hint ? $idPetunjuk : null,
        $punyaError ? $idError : null,
    ])->filter()->implode(' ');

    $labelAksesibel = $ariaLabel ?? $label ?? labelFieldSurat($namaField);
@endphp

<div class="sakti-timepicker relative"
     data-sakti-timepicker
     data-timepicker-label="{{ $labelAksesibel }}">

    @if($label)
        <label for="{{ $idField }}"
               class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-600" aria-hidden="true">*</span>
                <span class="sr-only">(wajib)</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <input type="time"
               id="{{ $idField }}"
               name="{{ $namaField }}"
               value="{{ $nilai }}"
               data-sakti-time-input
               placeholder="HH:mm"
               maxlength="5"
               inputmode="numeric"
               autocomplete="off"
               @if($minField) data-min-field="{{ $minField }}" @endif
               @if($required) required aria-required="true" @endif
               @if($disabled) disabled @endif
               @if($readonly) readonly @endif
               @if($deskripsi) aria-describedby="{{ $deskripsi }}" @endif
               aria-invalid="{{ $punyaError ? 'true' : 'false' }}"
               @if(! $label) aria-label="{{ $labelAksesibel }}" @endif
               class="sakti-timepicker-input w-full border border-gray-400 rounded-lg px-3 py-2 pr-12 text-gray-900 bg-white font-mono
                      focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-700 focus-visible:ring-offset-2
                      disabled:bg-gray-100 disabled:cursor-not-allowed">

        <button type="button"
                data-timepicker-trigger
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-controls="{{ $idPicker }}"
                aria-label="Pilih waktu {{ $labelAksesibel }}"
                @if($disabled) disabled @endif
                class="sakti-timepicker-trigger absolute inset-y-0 right-0 flex w-11 items-center justify-center
                       rounded-r-lg border-l border-gray-300 bg-gray-50 text-gray-700
                       hover:bg-orange-50 hover:text-orange-800
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-700 focus-visible:ring-inset
                       disabled:cursor-not-allowed disabled:opacity-50">
            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5"
                 viewBox="0 0 24 24"
                 fill="none"
                 stroke="currentColor"
                 stroke-width="2"
                 aria-hidden="true">
                <circle cx="12" cy="12" r="9" />
                <polyline points="12 7 12 12 15 15" />
            </svg>
        </button>
    </div>

    @if($hint)
        <p id="{{ $idPetunjuk }}" class="text-xs text-gray-600 mt-1">{{ $hint }}</p>
    @endif

    @if($punyaError)
        <p id="{{ $idError }}" class="text-xs text-red-600 mt-1" role="alert">
            {{ $errors->first($kunciError) }}
        </p>
    @endif
</div>
