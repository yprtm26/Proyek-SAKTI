<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiwayatPengusulB27Test extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role): User
    {
        $this->counter++;

        return User::create([
            'name' => ucfirst($role) . ' B27 ' . $this->counter,
            'username' => $role . '_b27_' . $this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => 'TI',
            'npm' => $role === 'mahasiswa' ? '221027' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '04270' . $this->counter : null,
        ]);
    }

    private function relativeLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');
        $channels = [
            hexdec(substr($hex, 0, 2)) / 255,
            hexdec(substr($hex, 2, 2)) / 255,
            hexdec(substr($hex, 4, 2)) / 255,
        ];

        $lin = array_map(
            fn (float $c): float => $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4,
            $channels
        );

        return 0.2126 * $lin[0] + 0.7152 * $lin[1] + 0.0722 * $lin[2];
    }

    private function contrastRatio(string $fgHex, string $bgHex): float
    {
        $l1 = $this->relativeLuminance($fgHex);
        $l2 = $this->relativeLuminance($bgHex);

        return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
    }

    public function test_pengusul_hanya_melihat_riwayat_pengajuan_miliknya_beserta_nomor_tanggal_status(): void
    {
        $mhsA = $this->makeUser('mahasiswa');
        $mhsB = $this->makeUser('mahasiswa');

        $jenisA = JenisSurat::create([
            'nama' => 'SURAT_MILIK_AKUN_A',
            'slug' => 'surat-milik-a',
            'target_user' => 'mahasiswa',
            'fields' => ['keperluan'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        $jenisB = JenisSurat::create([
            'nama' => 'SURAT_RAHASIA_AKUN_B',
            'slug' => 'surat-milik-b',
            'target_user' => 'mahasiswa',
            'fields' => ['keperluan'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        $suratA = SuratPengajuan::create([
            'user_id' => $mhsA->id,
            'jenis_surat_id' => $jenisA->id,
            'nomor_surat' => '001/FTI/XI/2026',
            'data' => ['keperluan' => 'Uji A'],
            'status' => 'disetujui_final',
        ]);

        SuratPengajuan::create([
            'user_id' => $mhsB->id,
            'jenis_surat_id' => $jenisB->id,
            'nomor_surat' => '999/FTI/XI/2026',
            'data' => ['keperluan' => 'Uji B'],
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($mhsA)->get('/riwayat-surat');

        $response->assertOk();
        $response->assertSee('Nomor Surat');
        $response->assertSee('001/FTI/XI/2026');
        $response->assertSee('SURAT_MILIK_AKUN_A');
        $response->assertSee($suratA->created_at->format('d/m/Y H:i'));
        $response->assertSee('Disetujui');
        $response->assertDontSee('SURAT_RAHASIA_AKUN_B');
        $response->assertDontSee('999/FTI/XI/2026');

        $this->actingAs($mhsA)
            ->getJson('/api/riwayat-surat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nomor_surat', '001/FTI/XI/2026');
    }

    public function test_riwayat_menampilkan_empty_state_saat_akun_belum_pernah_membuat_surat(): void
    {
        $mahasiswaBaru = $this->makeUser('mahasiswa');

        $response = $this->actingAs($mahasiswaBaru)->get('/mahasiswa/surat');

        $response->assertOk();
        $response->assertSee('Belum Ada Riwayat Pengajuan Surat');
        $response->assertSee('Ajukan Surat Pertama');
    }

    public function test_seluruh_badge_status_memiliki_rasio_kontras_teks_minimal_4_5_banding_1(): void
    {
        $statuses = [
            'draf',
            'menunggu',
            'diverifikasi_tu',
            'perlu_revisi',
            'menunggu_kaprodi',
            'menunggu_dekan',
            'menunggu_admin_final',
            'disetujui_final',
            'ditolak',
        ];

        foreach ($statuses as $status) {
            $meta = statusBadgeMeta($status);
            $ratio = $this->contrastRatio($meta['fg'], $meta['bg']);

            $this->assertGreaterThanOrEqual(
                4.5,
                $ratio,
                "Kontras badge status '{$status}' ({$meta['fg']} pada {$meta['bg']}) kurang dari 4.5:1: {$ratio}"
            );
        }
    }
}
