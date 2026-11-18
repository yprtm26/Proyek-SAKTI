<?php

namespace App\Support;

/**
 * Aturan validasi tanggal kegiatan (B-09).
 *
 * Sumber aturan tunggal untuk field tanggal pada form dinamis maupun form
 * khusus, sehingga format dan urutan tanggal selalu diperiksa di server.
 */
class DateRange
{
    /**
     * Pesan standar saat tanggal selesai mendahului tanggal mulai.
     */
    public const RANGE_MESSAGE = 'Tanggal selesai tidak boleh lebih awal daripada tanggal mulai.';

    /**
     * Pasangan field awal -> akhir yang dikenali.
     *
     * @var array<string, string>
     */
    private const PAIRS = [
        'tanggal_mulai' => 'tanggal_selesai',
        'tanggal_awal' => 'tanggal_akhir',
    ];

    /**
     * Field tanggal mengikuti konvensi nama `tanggal`, `tanggal_*`, atau `*_tanggal`.
     */
    public static function isDateField(string $field): bool
    {
        return $field === 'tanggal'
            || str_starts_with($field, 'tanggal_')
            || str_ends_with($field, '_tanggal');
    }

    /**
     * Ambil pasangan rentang tanggal yang kedua field-nya ada di form.
     *
     * @param  array<int, string>  $fields
     * @return array<int, array{0: string, 1: string}>
     */
    public static function pairs(array $fields): array
    {
        $pairs = [];

        foreach (self::PAIRS as $start => $end) {
            if (in_array($start, $fields, true) && in_array($end, $fields, true)) {
                $pairs[] = [$start, $end];
            }
        }

        return $pairs;
    }

    /**
     * Aturan validasi untuk seluruh field tanggal pada form.
     *
     * Tanggal wajib memakai format YYYY-MM-DD (sesuai nilai input date dan
     * teks yang di-parse di frontend), lalu tanggal akhir diperiksa agar
     * tidak mendahului tanggal awal.
     *
     * @param  array<int, string>  $fields
     * @return array<string, array<int, string>>
     */
    public static function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if (self::isDateField($field)) {
                $rules[$field] = ['required', 'date_format:Y-m-d'];
            }
        }

        foreach (self::pairs($fields) as [$start, $end]) {
            $rules[$end] = array_values(array_unique(array_merge(
                $rules[$end] ?? ['required', 'date_format:Y-m-d'],
                ['after_or_equal:' . $start]
            )));
        }

        return $rules;
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk field tanggal.
     *
     * @param  array<int, string>  $fields
     * @return array<string, string>
     */
    public static function messages(array $fields): array
    {
        $messages = [];

        foreach ($fields as $field) {
            if (! self::isDateField($field)) {
                continue;
            }

            $label = self::label($field);

            $messages[$field . '.required'] = $label . ' wajib diisi.';
            $messages[$field . '.date'] = $label . ' harus berupa tanggal yang valid.';
            $messages[$field . '.date_format'] = $label . ' harus berformat YYYY-MM-DD (contoh: 2026-11-18).';
        }

        foreach (self::pairs($fields) as [$start, $end]) {
            $messages[$end . '.after_or_equal'] = self::rangeMessage($start, $end);
        }

        return $messages;
    }

    /**
     * Pesan kesalahan rentang tanggal untuk pasangan field tertentu.
     */
    public static function rangeMessage(string $start, string $end = 'tanggal_selesai'): string
    {
        if ($start === 'tanggal_mulai' && $end === 'tanggal_selesai') {
            return self::RANGE_MESSAGE;
        }

        return self::label($end) . ' tidak boleh lebih awal daripada ' . strtolower(self::label($start)) . '.';
    }

    public static function label(string $field): string
    {
        return labelFieldSurat($field);
    }
}
