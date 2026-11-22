<?php

namespace App\Services;

use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PersonelSuratService
{
    /**
     * B-10 B: simpan bulk daftar personel surat.
     *
     * Seluruh baris lama diganti dengan daftar baru agar data personel
     * selalu konsisten dengan isian form (dipakai saat pengajuan baru,
     * simpan draf, maupun kirim ulang draf).
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function sync(SuratPengajuan $surat, array $rows, string $targetUser, ?User $pengusul = null): void
    {
        $pengusul ??= $surat->user;
        $personel = $this->normalize($rows, $targetUser, $pengusul);

        DB::transaction(function () use ($surat, $personel) {
            $surat->personel()->delete();

            if ($personel !== []) {
                $surat->personel()->createMany($personel);
            }
        });
    }

    /**
     * Bersihkan baris kosong, batasi panjang teks, dan kunci baris pertama
     * sebagai pengusul (nama selalu diambil dari akun yang login).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function normalize(array $rows, string $targetUser, ?User $pengusul): array
    {
        $hasil = [];

        foreach (array_values($rows) as $row) {
            if (! is_array($row)) {
                continue;
            }

            $nama = trim((string) ($row['nama'] ?? ''));
            $peran = trim((string) ($row['peran'] ?? $row['keterangan'] ?? ''));
            $identitas = trim((string) ($row['instansi'] ?? $row['npm'] ?? $row['nidn'] ?? ''));

            // Baris yang benar-benar kosong tidak perlu disimpan.
            if ($nama === '' && $peran === '' && $identitas === '') {
                continue;
            }

            $hasil[] = [
                'nama' => mb_substr($nama, 0, 150),
                'peran' => $peran === '' ? null : mb_substr($peran, 0, 100),
                'instansi' => $identitas === '' ? null : mb_substr($identitas, 0, 150),
                'urutan' => count($hasil),
            ];
        }

        if ($hasil === []) {
            return [];
        }

        // Baris pertama selalu pengusul, bukan nama yang diketik di form.
        if ($pengusul !== null) {
            $hasil[0]['nama'] = mb_substr($pengusul->name, 0, 150);

            if ($targetUser === 'mahasiswa' && filled($pengusul->npm)) {
                $hasil[0]['instansi'] = mb_substr((string) $pengusul->npm, 0, 150);
            }
        }

        return $hasil;
    }
}
