<?php

namespace App\Support;

use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Validasi terpadu untuk form dinamis pengajuan surat (B-08, B-09, B-10 A).
 *
 * Menyatukan aturan master dropdown, format dan rentang tanggal,
 * serta format dan urutan waktu 24 jam agar konsisten antara
 * form dosen dan form mahasiswa.
 */
class DynamicSuratValidator
{
    /**
     * Validasi request pengajuan dinamis dan kembalikan data tervalidasi + ternormalisasi.
     *
     * @param  array<int, string>  $lockedIdentityFields
     * @return array<string, mixed>
     */
    public static function validate(
        Request $request,
        JenisSurat $jenisSurat,
        array $lockedIdentityFields = []
    ): array {
        $fields = (array) ($jenisSurat->fields ?? []);
        $rules = [];

        foreach ($fields as $field) {
            if ($field === 'personel') {
                continue;
            }

            if ($field === 'semester') {
                $rules['semester'] = MasterData::semesterRules(true, 'paruh');
                continue;
            }

            if ($field === 'prodi') {
                if (
                    $request->has('prodi_id')
                    || in_array($jenisSurat->slug, ['surat-tugas-pengembangan-modul', 'surat-tugas-pengembangan-rps'], true)
                ) {
                    $rules['prodi_id'] = MasterData::prodiRules(true);
                    $rules['prodi'] = 'nullable';
                } else {
                    $rules['prodi'] = 'nullable';
                }
                continue;
            }

            if ($field === 'nama_penugas') {
                if ($request->has('dosen_id') || $jenisSurat->slug === 'surat-tugas-kegiatan-kuliah') {
                    $rules['dosen_id'] = MasterData::dosenRules(true);
                } else {
                    $rules['nama_penugas'] = 'required';
                }
                continue;
            }

            if (in_array($field, $lockedIdentityFields, true)) {
                $rules[$field] = 'nullable';
                continue;
            }

            $rules[$field] = 'required';
        }

        // B-09: aturan format YYYY-MM-DD dan urutan tanggal selesai >= tanggal mulai
        foreach (DateRange::rules($fields) as $field => $dateRules) {
            $rules[$field] = $dateRules;
        }

        // B-10 A: aturan format waktu 24 jam HH:mm
        foreach (TimeRange::rules($fields) as $field => $timeRules) {
            $rules[$field] = $timeRules;
        }

        $messages = array_merge(
            DateRange::messages($fields),
            TimeRange::messages($fields),
            [
                'prodi_id.required' => 'Program studi wajib dipilih.',
                'prodi_id.exists' => 'Program studi yang dipilih tidak valid.',
                'semester.required' => 'Semester wajib dipilih.',
                'semester.exists' => 'Semester yang dipilih tidak valid.',
                'dosen_id.required' => 'Dosen penugas wajib dipilih.',
                'dosen_id.exists' => 'Dosen penugas yang dipilih tidak valid.',
            ]
        );

        $validator = Validator::make($request->all(), $rules, $messages);
        $validator->after(function ($v) use ($request, $fields): void {
            TimeRange::validateOrder($request->all(), $fields, $v);
        });

        $validated = $validator->validate();

        // Normalisasi nilai waktu dari pemisah titik (08.00) ke titik dua (08:00)
        foreach ($fields as $field) {
            if (TimeRange::isTimeField($field) && isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = TimeRange::normalize($validated[$field]);
            }
        }

        return $validated;
    }
}
