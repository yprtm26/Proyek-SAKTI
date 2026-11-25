<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifikasiRevisiTuB20Test extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role): User
    {
        $this->counter++;

        return User::create([
            'name' => ucfirst($role) . ' B20 ' . $this->counter,
            'username' => $role . '_b20_' . $this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => $role === 'admin' ? null : 'TI',
            'npm' => $role === 'mahasiswa' ? '221020' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '04200' . $this->counter : null,
        ]);
    }

    private function makeSurat(User $pengusul, string $status = 'menunggu'): SuratPengajuan
    {
        $this->counter++;
        $template = JenisSurat::create([
            'nama' => 'Template Verifikasi ' . $this->counter,
            'slug' => 'template-verifikasi-' . $this->counter,
            'target_user' => $pengusul->role === 'dosen' ? 'dosen' : 'mahasiswa',
            'fields' => ['keperluan'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        return SuratPengajuan::create([
            'user_id' => $pengusul->id,
            'jenis_surat_id' => $template->id,
            'data' => ['keperluan' => 'Keperluan uji'],
            'status' => $status,
        ]);
    }

    public function test_admin_tu_dapat_menyetujui_verifikasi_sehingga_status_menjadi_diverifikasi_tu(): void
    {
        $admin = $this->makeUser('admin');
        $mahasiswa = $this->makeUser('mahasiswa');
        $surat = $this->makeSurat($mahasiswa, 'menunggu');

        $response = $this->actingAs($admin)
            ->post("/admin/surat/{$surat->id}/verifikasi");

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('surat_pengajuan', [
            'id' => $surat->id,
            'status' => 'diverifikasi_tu',
        ]);

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat')
            ->assertOk()
            ->assertSee('Diverifikasi TU');
    }

    public function test_admin_tu_dapat_mengembalikan_surat_perlu_revisi_dengan_catatan_wajib(): void
    {
        $admin = $this->makeUser('admin');
        $mahasiswa = $this->makeUser('mahasiswa');
        $surat = $this->makeSurat($mahasiswa, 'menunggu');

        $catatan = 'Lampirkan scan KTM terbaru dan perbaiki tahun akademik.';

        $response = $this->actingAs($admin)
            ->post("/admin/surat/{$surat->id}/revisi", [
                'catatan_revisi' => $catatan,
            ]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('surat_pengajuan', [
            'id' => $surat->id,
            'status' => 'perlu_revisi',
            'catatan_revisi' => $catatan,
        ]);

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat')
            ->assertOk()
            ->assertSee('Perlu Revisi')
            ->assertSee($catatan);
    }

    public function test_sistem_memblokir_pengembalian_revisi_jika_catatan_revisi_kosong(): void
    {
        $admin = $this->makeUser('admin');
        $mahasiswa = $this->makeUser('mahasiswa');
        $surat = $this->makeSurat($mahasiswa, 'menunggu');

        $this->actingAs($admin)
            ->from('/admin/dashboard')
            ->post("/admin/surat/{$surat->id}/revisi", [
                'catatan_revisi' => '   ',
            ])
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHasErrors('catatan_revisi');

        $this->actingAs($admin)
            ->postJson("/admin/surat/{$surat->id}/revisi", [
                'catatan_revisi' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('catatan_revisi');

        $this->assertDatabaseHas('surat_pengajuan', [
            'id' => $surat->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_non_admin_ditolak_403_pada_endpoint_verifikasi_dan_revisi(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $kaprodi = $this->makeUser('kaprodi');
        $surat = $this->makeSurat($mahasiswa, 'menunggu');

        $this->actingAs($mahasiswa)
            ->post("/admin/surat/{$surat->id}/verifikasi")
            ->assertForbidden();

        $this->actingAs($kaprodi)
            ->post("/admin/surat/{$surat->id}/revisi", ['catatan_revisi' => 'Revisi'])
            ->assertForbidden();
    }

    public function test_dasbor_tu_menampilkan_tombol_verifikasi_dan_modal_revisi(): void
    {
        $admin = $this->makeUser('admin');
        $mahasiswa = $this->makeUser('mahasiswa');
        $this->makeSurat($mahasiswa, 'menunggu');

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('Setujui Verifikasi');
        $response->assertSee('Kembalikan Revisi');
        $response->assertSee('id="modalRevisiTu"', false);
    }
}
