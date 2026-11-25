<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AntreanFifoTuB19Test extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role, string $name): User
    {
        $this->counter++;

        return User::create([
            'name' => $name,
            'username' => $role . '_b19_' . $this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => $role === 'admin' ? null : 'TI',
            'npm' => $role === 'mahasiswa' ? '221019' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '04190' . $this->counter : null,
        ]);
    }

    private function makeTemplate(string $targetUser = 'mahasiswa'): JenisSurat
    {
        $this->counter++;

        return JenisSurat::create([
            'nama' => 'Template FIFO ' . $this->counter,
            'slug' => 'template-fifo-' . $this->counter,
            'target_user' => $targetUser,
            'fields' => ['keperluan'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);
    }

    public function test_dasbor_tu_mengurutkan_antrean_secara_fifo_dan_menampilkan_waktu_masuk(): void
    {
        $admin = $this->makeUser('admin', 'Admin TU');
        $mhsLama = $this->makeUser('mahasiswa', 'PENGUSUL_PERTAMA_FIFO');
        $mhsTengah = $this->makeUser('mahasiswa', 'PENGUSUL_KEDUA_FIFO');
        $mhsBaru = $this->makeUser('mahasiswa', 'PENGUSUL_KETIGA_FIFO');
        $template = $this->makeTemplate('mahasiswa');

        $suratBaru = SuratPengajuan::create([
            'user_id' => $mhsBaru->id,
            'jenis_surat_id' => $template->id,
            'data' => ['keperluan' => 'Baru'],
            'status' => 'menunggu',
        ]);
        $suratBaru->forceFill(['created_at' => now()->subHours(1)])->saveQuietly();

        $suratLama = SuratPengajuan::create([
            'user_id' => $mhsLama->id,
            'jenis_surat_id' => $template->id,
            'data' => ['keperluan' => 'Lama'],
            'status' => 'menunggu',
        ]);
        $suratLama->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();

        $suratTengah = SuratPengajuan::create([
            'user_id' => $mhsTengah->id,
            'jenis_surat_id' => $template->id,
            'data' => ['keperluan' => 'Tengah'],
            'status' => 'menunggu',
        ]);
        $suratTengah->forceFill(['created_at' => now()->subHours(12)])->saveQuietly();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Waktu Masuk');
        $response->assertSee('Prioritas Utama');
        $response->assertSee($suratLama->created_at->format('d/m/Y H:i'));
        $response->assertSeeInOrder([
            'PENGUSUL_PERTAMA_FIFO',
            'PENGUSUL_KEDUA_FIFO',
            'PENGUSUL_KETIGA_FIFO',
        ]);

        // Cek toggle ke Terbaru (?sort=desc) dan kembali ke Terlama (?sort=asc)
        $descResponse = $this->actingAs($admin)->get('/admin/dashboard?sort=desc');
        $descResponse->assertOk();
        $descResponse->assertSeeInOrder([
            'PENGUSUL_KETIGA_FIFO',
            'PENGUSUL_KEDUA_FIFO',
            'PENGUSUL_PERTAMA_FIFO',
        ]);
    }

    public function test_surat_draf_dan_selesai_tidak_masuk_antrean_aktif_fifo_tu(): void
    {
        $admin = $this->makeUser('admin', 'Admin TU');
        $mhsAktif = $this->makeUser('mahasiswa', 'MHS_AKTIF_ANTRIAN');
        $mhsDraf = $this->makeUser('mahasiswa', 'MHS_DRAF_TERSEMBUNYI');
        $mhsSelesai = $this->makeUser('mahasiswa', 'MHS_SELESAI_TERSEMBUNYI');
        $mhsDitolak = $this->makeUser('mahasiswa', 'MHS_DITOLAK_TERSEMBUNYI');
        $template = $this->makeTemplate('mahasiswa');

        SuratPengajuan::create([
            'user_id' => $mhsAktif->id,
            'jenis_surat_id' => $template->id,
            'data' => [],
            'status' => 'menunggu',
        ]);
        SuratPengajuan::create([
            'user_id' => $mhsDraf->id,
            'jenis_surat_id' => $template->id,
            'data' => [],
            'status' => 'draf',
        ]);
        SuratPengajuan::create([
            'user_id' => $mhsSelesai->id,
            'jenis_surat_id' => $template->id,
            'data' => [],
            'status' => 'disetujui_final',
        ]);
        SuratPengajuan::create([
            'user_id' => $mhsDitolak->id,
            'jenis_surat_id' => $template->id,
            'data' => [],
            'status' => 'ditolak',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('MHS_AKTIF_ANTRIAN');
        $response->assertDontSee('MHS_DRAF_TERSEMBUNYI');
        $response->assertDontSee('MHS_SELESAI_TERSEMBUNYI');
        $response->assertDontSee('MHS_DITOLAK_TERSEMBUNYI');
    }

    public function test_dasbor_tu_dosen_juga_terurut_fifo_secara_default(): void
    {
        $admin = $this->makeUser('admin', 'Admin TU');
        $dosenLama = $this->makeUser('dosen', 'DOSEN_LAMA_FIFO');
        $dosenBaru = $this->makeUser('dosen', 'DOSEN_BARU_FIFO');
        $template = $this->makeTemplate('dosen');

        $baru = SuratPengajuan::create([
            'user_id' => $dosenBaru->id,
            'jenis_surat_id' => $template->id,
            'data' => ['nama_dosen' => 'DOSEN_BARU_FIFO', 'nidn' => '0419099'],
            'status' => 'menunggu',
        ]);
        $baru->forceFill(['created_at' => now()->subMinutes(10)])->saveQuietly();

        $lama = SuratPengajuan::create([
            'user_id' => $dosenLama->id,
            'jenis_surat_id' => $template->id,
            'data' => ['nama_dosen' => 'DOSEN_LAMA_FIFO', 'nidn' => '0419088'],
            'status' => 'menunggu',
        ]);
        $lama->forceFill(['created_at' => now()->subDays(1)])->saveQuietly();

        $response = $this->actingAs($admin)->get('/admin/dashboard-dosen');

        $response->assertOk();
        $response->assertSeeInOrder([
            'DOSEN_LAMA_FIFO',
            'DOSEN_BARU_FIFO',
        ]);
    }
}
