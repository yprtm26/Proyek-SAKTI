<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PenyaringanSuratTugasIndeksTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_menyaring_daftar_formulir_dengan_indeks_yang_dipilih(): void
    {
        $dosen = User::create([
            'name' => 'Dosen Uji',
            'username' => 'dosen.uji',
            'password' => 'password123',
            'role' => 'dosen',
        ]);

        JenisSurat::create([
            'nama' => 'Surat Tugas Kuliah',
            'slug' => 'surat-tugas-kegiatan-kuliah',
            'kode' => 'ST PP.20.05',
            'indeks_kegiatan' => 'pendidikan',
            'target_user' => 'dosen',
            'alur' => ['admin', 'dekan'],
            'fields' => ['nama_kegiatan'],
            'aktif' => true,
        ]);

        JenisSurat::create([
            'nama' => 'Surat Tugas Artikel',
            'slug' => 'surat-tugas-penulisan-publikasi-artikel-ilmiah',
            'kode' => 'ST PN.10',
            'indeks_kegiatan' => 'penelitian',
            'target_user' => 'dosen',
            'alur' => ['admin', 'dekan'],
            'fields' => ['judul_artikel'],
            'aktif' => true,
        ]);

        $response = $this->actingAs($dosen)->get('/dosen/dashboard?indeks_kegiatan=pendidikan');

        $response->assertOk();
        $response->assertSee('Surat Tugas Kuliah');
        $response->assertDontSee('Surat Tugas Artikel');
        $response->assertSee('Kemahasiswaan');
    }

    public function test_indeks_tidak_valid_ditolak_dengan_daftar_kosong(): void
    {
        $dosen = User::create([
            'name' => 'Dosen Uji',
            'username' => 'dosen.uji',
            'password' => 'password123',
            'role' => 'dosen',
        ]);

        $response = $this->actingAs($dosen)->get('/dosen/dashboard?indeks_kegiatan=lainnya');

        $response->assertOk();
        $response->assertSee('Tidak ada formulir aktif pada kategori Lainnya.');
    }

}
