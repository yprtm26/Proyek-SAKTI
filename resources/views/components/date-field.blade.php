@props([
    'name',
    'id' => null,
    'label' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'min' => null,
    'max' => null,
    'minField' => null,
    'hint' => null,
    'errorKey' => null,
    'showError' => true,
    'ariaLabel' => null,
])

@php
    $namaField = $name;
    $idField = $id ?: 'tanggal-' . \Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $namaField));

    $nilai = old($namaField, $value);
    $kunciError = $errorKey ?: $namaField;
    $punyaError = $showError && $errors->has($kunciError);

    $idPetunjuk = $idField . '-petunjuk';
    $idError = $idField . '-error';
    $idKalender = $idField . '-kalender';

    $deskripsi = collect([
        $hint ? $idPetunjuk : null,
        $punyaError ? $idError : null,
    ])->filter()->implode(' ');

    $labelAksesibel = $ariaLabel ?? $label ?? labelFieldSurat($namaField);
@endphp

<div class="sakti-datepicker relative"
     data-sakti-datepicker
     data-datepicker-label="{{ $labelAksesibel }}">

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
        <input type="date"
               id="{{ $idField }}"
               name="{{ $namaField }}"
               value="{{ $nilai }}"
               data-sakti-date-input
               @if($min) min="{{ $min }}" @endif
               @if($max) max="{{ $max }}" @endif
               @if($minField) data-min-field="{{ $minField }}" @endif
               @if($required) required aria-required="true" @endif
               @if($disabled) disabled @endif
               @if($readonly) readonly @endif
               @if($deskripsi) aria-describedby="{{ $deskripsi }}" @endif
               aria-invalid="{{ $punyaError ? 'true' : 'false' }}"
               @if(! $label) aria-label="{{ $labelAksesibel }}" @endif
               class="sakti-datepicker-input w-full border border-gray-400 rounded-lg px-3 py-2 pr-11 text-gray-900 bg-white
                      focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-700 focus-visible:ring-offset-2
                      disabled:bg-gray-100 disabled:cursor-not-allowed">

        <button type="button"
                data-datepicker-trigger
                aria-haspopup="dialog"
                aria-expanded="false"
                aria-controls="{{ $idKalender }}"
                aria-label="Buka kalender {{ $labelAksesibel }}"
                @if($disabled) disabled @endif
                class="sakti-datepicker-trigger absolute inset-y-0 right-0 flex w-10 items-center justify-center
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
                <path stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M8 7V3m8 4V3M3.5 9.5h17M5 5h14a1.5 1.5 0 011.5 1.5v13A1.5 1.5 0 0119 21H5a1.5 1.5 0 01-1.5-1.5v-13A1.5 1.5 0 015 5z" />
            </svg>
        </button>
    </div>

    @if($hint)
        <p id="{{ $idPetunjuk }}" class="text-xs text-gray-600 mt-1">{{ $hint }}</p>
    @endif

    <p data-date-range-error
       class="hidden text-sm font-medium text-red-700 mt-1"
       role="alert"
       aria-live="polite"></p>

    @if($punyaError)
        <p id="{{ $idError }}" class="text-sm text-red-700 mt-1" role="alert">
            {{ $errors->first($kunciError) }}
        </p>
    @endif
</div>
