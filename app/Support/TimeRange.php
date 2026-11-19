<?php

namespace App\Support;

use Illuminate\Validation\Validator;

/**
 * Aturan validasi waktu kegiatan format 24 jam (B-10 A).
 *
 * Mengatur format HH:mm dan validasi urutan waktu mulai dan selesai
 * saat kegiatan berlangsung pada hari yang sama.
 */
class TimeRange
{
    public const FORMAT_MESSAGE = 'Format waktu harus HH:mm 24 jam (contoh: 08:30 atau 14:00).';
    public const RANGE_MESSAGE = 'Waktu selesai tidak boleh lebih awal atau sama dengan waktu mulai pada tanggal yang sama.';
    public const REQUIRED_MESSAGE = 'Waktu wajib diisi.';

    /**
     * Pasangan field waktu awal -> akhir yang dikenali.
     *
     * @var array<string, string>
     */
    public const PAIRS = [
        'waktu_mulai' => 'waktu_selesai',
        'waktu_awal' => 'waktu_akhir',
        'jam_mulai' => 'jam_selesai',
    ];

    /**
     * Pasangan field tanggal terkait untuk pasangan waktu.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    public const DATE_PAIRS_FOR_TIME = [
        'waktu_mulai' => ['tanggal_mulai', 'tanggal_selesai'],
        'waktu_awal' => ['tanggal_awal', 'tanggal_akhir'],
        'jam_mulai' => ['tanggal_mulai', 'tanggal_selesai'],
    ];

    /**
     * Pola deteksi field waktu.
     */
    public static function isTimeField(string $field): bool
    {
        return $field === 'waktu'
            || $field === 'jam'
            || str_starts_with($field, 'waktu_')
            || str_ends_with($field, '_waktu')
            || str_starts_with($field, 'jam_')
            || str_ends_with($field, '_jam');
    }

    /**
     * Ambil pasangan waktu yang kedua field-nya ada di daftar field form.
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
     * Aturan validasi field waktu.
     *
     * Memastikan string waktu 24 jam HH:mm (00:00 - 23:59).
     *
     * @param  array<int, string>  $fields
     * @return array<string, array<int, string>>
     */
    public static function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            if (self::isTimeField($field)) {
                $rules[$field] = ['required', 'regex:/^(?:[01]\d|2[0-3])[:.][0-5]\d$/'];
            }
        }

        return $rules;
    }

    /**
     * Pesan validasi berbahasa Indonesia untuk field waktu.
     *
     * @param  array<int, string>  $fields
     * @return array<string, string>
     */
    public static function messages(array $fields): array
    {
        $messages = [];

        foreach ($fields as $field) {
            if (! self::isTimeField($field)) {
                continue;
            }

            $label = self::label($field);

            $messages[$field . '.required'] = $label . ' wajib diisi.';
            $messages[$field . '.regex'] = $label . ' harus berformat HH:mm 24 jam (contoh: 08:30 atau 14:00).';
            $messages[$field . '.date_format'] = $label . ' harus berformat HH:mm 24 jam (contoh: 08:30 atau 14:00).';
        }

        return $messages;
    }

    /**
     * Normalisasi string waktu dari pemisah titik (.) ke titik dua (:).
     */
    public static function normalize(string $value): string
    {
        return str_replace('.', ':', trim($value));
    }

    /**
     * Validasi urutan waktu pada hari yang sama.
     *
     * Jika tanggal selesai sama dengan tanggal mulai (atau hanya ada satu tanggal),
     * waktu selesai wajib lebih akhir daripada waktu mulai.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $fields
     */
    public static function validateOrder(array $data, array $fields, Validator $validator): void
    {
        foreach (self::pairs($fields) as [$startKey, $endKey]) {
            $startTime = $data[$startKey] ?? null;
            $endTime = $data[$endKey] ?? null;

            if (! is_string($startTime) || ! is_string($endTime)) {
                continue;
            }

            $startTime = trim($startTime);
            $endTime = trim($endTime);

            if (! self::isValidTime($startTime) || ! self::isValidTime($endTime)) {
                continue;
            }

            $startTimeNorm = self::normalize($startTime);
            $endTimeNorm = self::normalize($endTime);

            // Periksa tanggal terkait bila ada
            $isSameDate = self::checkIsSameDate($data, $startKey, $fields);

            if ($isSameDate) {
                // Bandingkan jam: format HH:mm leksikografis sama dengan kronologis
                if (strcmp($endTimeNorm, $startTimeNorm) <= 0) {
                    $validator->errors()->add($endKey, self::RANGE_MESSAGE);
                }
            }
        }
    }

    /**
     * Cek apakah kegiatan berada pada hari yang sama.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $fields
     */
    public static function checkIsSameDate(array $data, string $startKey, array $fields): bool
    {
        // 1. Cek pasangan tanggal spesifik
        if (isset(self::DATE_PAIRS_FOR_TIME[$startKey])) {
            [$startDateKey, $endDateKey] = self::DATE_PAIRS_FOR_TIME[$startKey];

            if (isset($data[$startDateKey], $data[$endDateKey])) {
                return trim((string) $data[$startDateKey]) === trim((string) $data[$endDateKey]);
            }
        }

        // 2. Cek pasangan tanggal umum jika ada
        foreach (DateRange::pairs($fields) as [$startDateKey, $endDateKey]) {
            if (isset($data[$startDateKey], $data[$endDateKey])) {
                return trim((string) $data[$startDateKey]) === trim((string) $data[$endDateKey]);
            }
        }

        // 3. Cek single tanggal kegiatan (misal 'tanggal', 'tanggal_kegiatan', 'tanggal_lomba', dll)
        foreach ($fields as $field) {
            if (DateRange::isDateField($field) && isset($data[$field])) {
                return true;
            }
        }

        // 4. Default: tanpa konteks multi-hari, asumsikan satu hari yang sama
        return true;
    }

    public static function isValidTime(string $value): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3])[:.][0-5]\d$/', $value) === 1;
    }

    public static function label(string $field): string
    {
        return labelFieldSurat($field);
    }
}
