<?php

namespace App\Support;

use App\Models\Prodi;
use App\Models\Semester;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;

/**
 * Sumber tunggal aturan validasi dan data pilihan (option) master
 * prodi, semester, dan dosen untuk dropdown B-08.
 */
class MasterData
{
    public static function prodiRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::exists('prodi', 'id')->where(
                fn ($query) => $query->where('aktif', true)
            ),
        ];
    }

    public static function semesterRules(bool $required = false, ?string $jenis = null): array
    {
        $exists = Rule::exists('semester', 'id')
            ->where(fn ($query) => $query->where('aktif', true));

        if (in_array($jenis, ['paruh', 'tingkat'], true)) {
            $exists->where('jenis', $jenis);
        }

        return [
            $required ? 'required' : 'nullable',
            'integer',
            $exists,
        ];
    }

    public static function dosenRules(bool $required = false): array
    {
        return [
            $required ? 'required' : 'nullable',
            'integer',
            Rule::exists('users', 'id')->where(
                fn ($query) => $query->where('role', 'dosen')
            ),
        ];
    }

    public static function prodiOptions(?string $q = null, int $limit = 50): array
    {
        $q = is_string($q) ? trim($q) : null;

        return Prodi::query()
            ->aktif()
            ->when(filled($q), function (Builder $query) use ($q) {
                $term = '%' . $q . '%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('nama', 'like', $term)
                        ->orWhere('kode', 'like', $term);
                });
            })
            ->orderBy('urutan')
            ->orderBy('nama')
            ->limit(self::limit($limit, 100))
            ->get()
            ->map(fn (Prodi $prodi) => [
                'id' => $prodi->id,
                'value' => $prodi->id,
                'kode' => $prodi->kode,
                'label' => $prodi->nama,
                'description' => $prodi->jenjang,
            ])
            ->all();
    }

    public static function semesterOptions(?string $q = null, ?string $jenis = null, int $limit = 50): array
    {
        $q = is_string($q) ? trim($q) : null;

        return Semester::query()
            ->aktif()
            ->jenis($jenis)
            ->when(filled($q), function (Builder $query) use ($q) {
                $term = '%' . $q . '%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('nama', 'like', $term)
                        ->orWhere('kode', 'like', $term);
                });
            })
            ->orderBy('jenis')
            ->orderBy('urutan')
            ->limit(self::limit($limit, 100))
            ->get()
            ->map(fn (Semester $semester) => [
                'id' => $semester->id,
                'value' => $semester->id,
                'kode' => $semester->kode,
                'label' => $semester->nama,
                'description' => $semester->jenis === 'paruh'
                    ? 'Semester paruh'
                    : 'Semester tingkat',
                'jenis' => $semester->jenis,
            ])
            ->all();
    }

    public static function dosenOptions(?string $q = null, ?string $prodi = null, int $limit = 100): array
    {
        $q = is_string($q) ? trim($q) : null;
        $prodi = is_string($prodi) ? trim($prodi) : null;

        return User::query()
            ->where('role', 'dosen')
            ->when(filled($prodi), fn (Builder $query) => $query->where('prodi', $prodi))
            ->when(filled($q), function (Builder $query) use ($q) {
                $term = '%' . $q . '%';
                $query->where(function (Builder $inner) use ($term) {
                    $inner->where('name', 'like', $term)
                        ->orWhere('nidn', 'like', $term);
                });
            })
            ->orderBy('name')
            ->limit(self::limit($limit, 100))
            ->get()
            ->map(function (User $dosen) {
                $keterangan = collect([
                    filled($dosen->nidn) ? 'NIDN: ' . $dosen->nidn : null,
                    filled($dosen->prodi) ? namaProdi($dosen->prodi) : null,
                ])->filter()->implode(' • ');

                return [
                    'id' => $dosen->id,
                    'value' => $dosen->id,
                    'label' => $dosen->name,
                    'description' => $keterangan !== '' ? $keterangan : null,
                    'nidn' => $dosen->nidn,
                    'prodi' => $dosen->prodi,
                ];
            })
            ->all();
    }

    public static function findSemester(int|string|null $id): ?Semester
    {
        if (blank($id)) {
            return null;
        }

        return Semester::query()->aktif()->find($id);
    }

    public static function findProdi(int|string|null $id): ?Prodi
    {
        if (blank($id)) {
            return null;
        }

        return Prodi::query()->aktif()->find($id);
    }

    private static function limit(int $limit, int $max): int
    {
        return max(1, min($limit, $max));
    }
}
