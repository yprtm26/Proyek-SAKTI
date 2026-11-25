<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PratinjauSuratB28Test extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role): User
    {
        $this->counter++;

        return User::create([
            'name' => ucfirst($role) . ' B28 ' . $this->counter,
            'username' => $role . '_b28_' . $this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => $role === 'admin' ? null : 'TI',
            'npm' => $role === 'mahasiswa' ? '221028' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '04280' . $this->counter : null,
        ]);
    }

    private function makeSurat(User $pengusul, string $status = 'menunggu'): SuratPengajuan
    {
        $this->counter++;
        $template = JenisSurat::create([
            'nama' => 'Surat Tugas Uji Pratinjau ' . $this->counter,
            'slug' => 'surat-uji-pratinjau-' . $this->counter,
            'target_user' => $pengusul->role === 'dosen' ? 'dosen' : 'mahasiswa',
            'fields' => ['keperluan', 'tempat'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        return SuratPengajuan::create([
            'user_id' => $pengusul->id,
            'jenis_surat_id' => $template->id,
            'data' => [
                'keperluan' => 'Observasi Lapangan Resmi',
                'tempat' => 'Menara YARSI Lt. 5',
            ],
            'status' => $status,
        ]);
    }

    public function test_pengusul_dan_admin_tu_dapat_melihat_pratinjau_dokumen_surat_terformat(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $admin = $this->makeUser('admin');
        $surat = $this->makeSurat($mahasiswa, 'menunggu');

        $respPengusul = $this->actingAs($mahasiswa)->get("/surat/{$surat->id}/pratinjau?fragment=1");
        $respPengusul->assertOk();
        $respPengusul->assertSee('sakti-preview-document', false);
        $respPengusul->assertSee('Observasi Lapangan Resmi');
        $respPengusul->assertSee('Menara YARSI Lt. 5');
        $respPengusul->assertSee('color: #000000', false);

        $respAdmin = $this->actingAs($admin)->get("/surat/{$surat->id}/pratinjau");
        $respAdmin->assertOk();
        $respAdmin->assertSee('Pratinjau Dokumen Surat');
        $respAdmin->assertSee('Observasi Lapangan Resmi');
    }

    public function test_tombol_pratinjau_dan_modal_escape_tersedia_di_riwayat_dan_dasbor_tu(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $admin = $this->makeUser('admin');
        $this->makeSurat($mahasiswa, 'menunggu');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat')
            ->assertOk()
            ->assertSee('Pratinjau')
            ->assertSee('id="modalPratinjauSurat"', false)
            ->assertSee('Tutup (Esc)');

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Pratinjau')
            ->assertSee('id="modalPratinjauSurat"', false);

        $surat = SuratPengajuan::firstOrFail();
        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/' . $surat->id)
            ->assertOk()
            ->assertDontSee('data-preview-trigger', false);
    }

    public function test_pratinjau_mengembalikan_404_yang_aman_jika_id_tidak_ada_di_database(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');

        $response = $this->actingAs($mahasiswa)->get('/surat/999999/pratinjau');

        $response->assertNotFound();
        $response->assertSee('404');
        $response->assertDontSee('Stack trace');
        $response->assertDontSee('SQLSTATE');
    }

    public function test_pengusul_lain_ditolak_403_saat_pratinjau_surat_milik_orang_lain(): void
    {
        $pemilik = $this->makeUser('mahasiswa');
        $penyusup = $this->makeUser('mahasiswa');
        $surat = $this->makeSurat($pemilik, 'menunggu');

        $this->actingAs($penyusup)
            ->get("/surat/{$surat->id}/pratinjau")
            ->assertForbidden();
    }

    public function test_admin_tu_mendapat_404_jika_mencoba_pratinjau_draf_pengusul(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $admin = $this->makeUser('admin');
        $draf = $this->makeSurat($mahasiswa, 'draf');

        $this->actingAs($admin)
            ->get("/surat/{$draf->id}/pratinjau")
            ->assertNotFound();
    }
}
