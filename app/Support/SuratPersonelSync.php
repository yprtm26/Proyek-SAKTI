<?php

namespace App\Support;

use App\Models\SuratPengajuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Validasi dan sinkronisasi relasi anggota kegiatan (B-10 B).
 *
 * Menyatukan aturan validasi baris personel, sanitasi input,
 * serta sinkronisasi antara tabel `surat_personel` dan JSON `data.personel`.
 */
class SuratPersonelSync
{
    public const EMPTY_ROW_MESSAGE = 'Setiap baris anggota wajib mengisi nama dan peran kegiatan.';
    public const MIN_ONE_MESSAGE = 'Minimal satu anggota kegiatan harus diisi.';

    /**
     * Periksa apakah template atau request membawa daftar personel.
     *
     * @param  array<int, string>  $templateFields
     */
    public static function shouldValidate(Request $request, array $templateFields = []): bool
    {
        return in_array('personel', $templateFields, true) || $request->has('personel');
    }

    /**
     * Validasi baris personel untuk pengiriman final atau simpan bulk.
     *
     * @return array<int, array{nama: string, peran: string, instansi: ?string}>
     *
     * @throws ValidationException
     */
    public static function validateRows(Request $request, bool $required = true): array
    {
        $raw = $request->input('personel');

        $rules = [
            'personel' => $required ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'personel.*.nama' => ['required', 'string', 'max:255'],
            'personel.*.peran' => ['required', 'string', 'max:255'],
            'personel.*.instansi' => ['nullable', 'string', 'max:255'],
        ];

        $messages = [
            'personel.required' => self::MIN_ONE_MESSAGE,
            'personel.array' => 'Format daftar anggota kegiatan tidak valid.',
            'personel.min' => self::MIN_ONE_MESSAGE,
            'personel.*.nama.required' => 'Nama anggota wajib diisi pada setiap baris.',
            'personel.*.nama.string' => 'Nama anggota harus berupa teks.',
            'personel.*.nama.max' => 'Nama anggota maksimal 255 karakter.',
            'personel.*.peran.required' => 'Peran anggota wajib diisi pada setiap baris.',
            'personel.*.peran.string' => 'Peran anggota harus berupa teks.',
            'personel.*.peran.max' => 'Peran anggota maksimal 255 karakter.',
            'personel.*.instansi.string' => 'Instansi/NIM/NIDN harus berupa teks.',
            'personel.*.instansi.max' => 'Instansi/NIM/NIDN maksimal 255 karakter.',
        ];

        $validator = Validator::make(['personel' => $raw], $rules, $messages);

        $validator->after(function ($v) use ($raw): void {
            if (! is_array($raw)) {
                return;
            }

            foreach ($raw as $index => $row) {
                if (! is_array($row)) {
                    $v->errors()->add("personel.$index.nama", self::EMPTY_ROW_MESSAGE);
                    continue;
                }

                $nama = trim((string) ($row['nama'] ?? ''));
                $peran = trim((string) ($row['peran'] ?? ''));

                if ($nama === '' && ! $v->errors()->has("personel.$index.nama")) {
                    $v->errors()->add("personel.$index.nama", 'Nama anggota wajib diisi pada setiap baris.');
                }

                if ($peran === '' && ! $v->errors()->has("personel.$index.peran")) {
                    $v->errors()->add("personel.$index.peran", 'Peran anggota wajib diisi pada setiap baris.');
                }
            }
        });

        $validator->validate();

        return self::normalizeRows(is_array($raw) ? $raw : []);
    }

    /**
     * Normalisasi dan sanitasi baris personel (dipakai untuk final dan draf parsial).
     *
     * @param  array<int|string, mixed>  $rawRows
     * @return array<int, array{nama: string, peran: string, instansi: string}>
     */
    public static function normalizeRows(array $rawRows): array
    {
        $normalized = [];

        foreach ($rawRows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $nama = self::cleanText($row['nama'] ?? '');
            $peran = self::cleanText($row['peran'] ?? '');
            $instansi = self::cleanText($row['instansi'] ?? '');

            $normalized[] = [
                'nama' => $nama,
                'peran' => $peran,
                'instansi' => $instansi,
            ];
        }

        return array_values($normalized);
    }

    /**
     * Simpan baris personel ke tabel relasi `surat_personel` dan sinkronkan JSON `data.personel`.
     *
     * @param  array<int, array{nama: string, peran: string, instansi?: ?string}>  $rows
     */
    public static function syncToSurat(SuratPengajuan $surat, array $rows): void
    {
        $cleanRows = self::normalizeRows($rows);

        DB::transaction(function () use ($surat, $cleanRows): void {
            $surat->personel()->delete();

            foreach ($cleanRows as $index => $row) {
                $surat->personel()->create([
                    'nama' => $row['nama'],
                    'peran' => $row['peran'],
                    'instansi' => $row['instansi'] !== '' ? $row['instansi'] : null,
                    'urutan' => $index + 1,
                ]);
            }

            $data = (array) ($surat->data ?? []);
            $data['personel'] = $cleanRows;
            $surat->update(['data' => $data]);
        });
    }

    /**
     * Ambil daftar personel terurut dari relasi DB atau fallback ke JSON draf lama.
     *
     * @return array<int, array{nama: string, peran: string, instansi: string}>
     */
    public static function readFromSurat(SuratPengajuan $surat): array
    {
        $relasi = $surat->relationLoaded('personel')
            ? $surat->personel
            : $surat->personel()->get();

        if ($relasi->isNotEmpty()) {
            return $relasi->map(fn ($item) => [
                'nama' => (string) $item->nama,
                'peran' => (string) $item->peran,
                'instansi' => (string) ($item->instansi ?? ''),
            ])->values()->all();
        }

        $jsonRows = $surat->data['personel'] ?? [];

        return is_array($jsonRows) ? self::normalizeRows($jsonRows) : [];
    }

    private static function cleanText(mixed $value): string
    {
        if (! is_scalar($value)) {
            return '';
        }

        return mb_substr(trim(strip_tags((string) $value)), 0, 255);
    }
}
