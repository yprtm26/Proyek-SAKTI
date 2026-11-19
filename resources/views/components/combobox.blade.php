@props([
    'name',
    'id' => null,
    'label' => null,
    'endpoint',
    'placeholder' => 'Pilih opsi...',
    'searchPlaceholder' => 'Cari...',
    'value' => null,
    'valueLabel' => null,
    'params' => [],
    'required' => false,
    'disabled' => false,
    'hint' => null,
    'errorKey' => null,
    'emptyText' => 'Tidak ada data yang cocok.',
    'ariaLabel' => null,
])

@php
    $namaField = $name;
    $idField = $id ?: 'combobox-' . \Illuminate\Support\Str::slug(str_replace(['[', ']'], '-', $namaField));

    $nilaiTerpilih = old($namaField, $value);
    $labelTerpilih = old($namaField . '_label', $valueLabel);

    $opsiAwal = [];
    if (filled($nilaiTerpilih)) {
        $opsiAwal[] = [
            'id' => $nilaiTerpilih,
            'value' => $nilaiTerpilih,
            'label' => filled($labelTerpilih) ? $labelTerpilih : (string) $nilaiTerpilih,
        ];
    }

    $kunciError = $errorKey ?: $namaField;
@endphp

<div class="sakti-combobox relative"
     id="{{ $idField }}-wrapper"
     data-combobox
     data-endpoint="{{ $endpoint }}"
     data-params="{{ json_encode($params) }}"
     data-options="{{ json_encode($opsiAwal) }}"
     data-placeholder="{{ $placeholder }}"
     data-label="{{ $label ?? 'opsi' }}"
     @if($disabled) data-disabled="1" @endif>

    @if($label)
        <label id="{{ $idField }}-label"
               for="{{ $idField }}"
               class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }}
            @if($required)
                <span class="text-red-500" aria-hidden="true">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        <button type="button"
                id="{{ $idField }}"
                data-combobox-trigger
                role="combobox"
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-controls="{{ $idField }}-listbox"
                aria-label="{{ $ariaLabel ?? $label ?? $placeholder }}"
                @if($label) aria-labelledby="{{ $idField }}-label" @endif
                @if($disabled) disabled @endif
                class="w-full flex items-center justify-between gap-2 border border-gray-300 rounded-lg px-3 py-2 text-left bg-white
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-600 focus-visible:ring-offset-2
                       disabled:bg-gray-100 disabled:cursor-not-allowed">
            <span data-combobox-value
                  class="{{ filled($labelTerpilih) ? 'text-gray-800' : 'text-gray-400' }} truncate">
                {{ filled($labelTerpilih) ? $labelTerpilih : $placeholder }}
            </span>

            <svg xmlns="http://www.w3.org/2000/svg"
                 class="h-4 w-4 text-gray-500 flex-shrink-0"
                 viewBox="0 0 20 20"
                 fill="currentColor"
                 aria-hidden="true">
                <path fill-rule="evenodd"
                      d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                      clip-rule="evenodd" />
            </svg>
        </button>

        <div data-combobox-popup
             class="hidden absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg">
            <div class="p-2 border-b border-gray-100">
                <input type="text"
                       data-combobox-search
                       role="searchbox"
                       aria-autocomplete="list"
                       aria-controls="{{ $idField }}-listbox"
                       placeholder="{{ $searchPlaceholder }}"
                       autocomplete="off"
                       class="w-full rounded border border-gray-300 px-3 py-2 text-sm
                              focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-600">
            </div>

            <ul id="{{ $idField }}-listbox"
                data-combobox-list
                role="listbox"
                @if($label) aria-labelledby="{{ $idField }}-label" @endif
                class="max-h-60 overflow-y-auto py-1"></ul>

            <p data-combobox-loading class="hidden px-3 py-2 text-sm text-gray-500">
                Memuat data...
            </p>

            <p data-combobox-empty class="hidden px-3 py-2 text-sm text-gray-500">
                {{ $emptyText }}
            </p>
        </div>
    </div>

    <input type="hidden"
           name="{{ $namaField }}"
           value="{{ $nilaiTerpilih }}"
           data-combobox-input
           @if($required) data-required="1" @endif>

    <p data-combobox-error class="hidden text-sm text-red-600 mt-1" aria-live="polite"></p>

    @error($kunciError)
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror

    @if($hint)
        <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
    @endif
</div>
