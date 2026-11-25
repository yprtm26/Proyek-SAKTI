<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HapusDrafB14Test extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role, array $attr = []): User
    {
        $this->counter++;

        return User::create(array_merge([
            'name' => ucfirst($role) . ' B14 ' . $this->counter,
            'username' => $role . '_b14_' . $this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => in_array($role, ['mahasiswa', 'dosen'], true) ? 'TI' : null,
            'npm' => $role === 'mahasiswa' ? '221014' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '04140' . $this->counter : null,
        ], $attr));
    }

    private function makeTemplate(string $targetUser = 'mahasiswa'): JenisSurat
    {
        $this->counter++;

        return JenisSurat::create([
            'nama' => 'Template Hapus Draf ' . $this->counter,
            'slug' => 'template-hapus-draf-' . $this->counter,
            'target_user' => $targetUser,
            'fields' => ['judul_kegiatan'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);
    }

    public function test_pengusul_dapat_menghapus_permanen_draf_miliknya(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $template = $this->makeTemplate('mahasiswa');
        $draf = SuratPengajuan::create([
            'user_id' => $mahasiswa->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Draf akan dihapus'],
            'status' => 'draf',
        ]);

        $response = $this->actingAs($mahasiswa)
            ->delete("/surat/{$draf->id}/draf");

        $response->assertRedirect('/mahasiswa/surat');
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('surat_pengajuan', ['id' => $draf->id]);
    }

    public function test_dosen_dapat_menghapus_permanen_draf_lewat_endpoint_dosen(): void
    {
        $dosen = $this->makeUser('dosen');
        $template = $this->makeTemplate('dosen');
        $draf = SuratPengajuan::create([
            'user_id' => $dosen->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Draf dosen'],
            'status' => 'draf',
        ]);

        $response = $this->actingAs($dosen)
            ->deleteJson("/dosen/surat/{$draf->id}/draf");

        $response->assertOk();
        $this->assertDatabaseMissing('surat_pengajuan', ['id' => $draf->id]);
    }

    public function test_pengusul_lain_ditolak_403_saat_menghapus_draf_orang_lain(): void
    {
        $pemilik = $this->makeUser('mahasiswa');
        $penyusup = $this->makeUser('mahasiswa');
        $template = $this->makeTemplate('mahasiswa');
        $draf = SuratPengajuan::create([
            'user_id' => $pemilik->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Milik orang lain'],
            'status' => 'draf',
        ]);

        $this->actingAs($penyusup)
            ->deleteJson("/surat/{$draf->id}/draf")
            ->assertForbidden();

        $this->assertDatabaseHas('surat_pengajuan', ['id' => $draf->id]);
    }

    public function test_api_menolak_penghapusan_surat_yang_statusnya_bukan_draf(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $template = $this->makeTemplate('mahasiswa');

        foreach (['menunggu', 'diverifikasi_tu', 'perlu_revisi', 'disetujui_final'] as $status) {
            $surat = SuratPengajuan::create([
                'user_id' => $mahasiswa->id,
                'jenis_surat_id' => $template->id,
                'data' => ['judul_kegiatan' => 'Surat status ' . $status],
                'status' => $status,
            ]);

            $this->actingAs($mahasiswa)
                ->deleteJson("/surat/{$surat->id}/draf")
                ->assertUnprocessable();

            $this->assertDatabaseHas('surat_pengajuan', [
                'id' => $surat->id,
                'status' => $status,
            ]);
        }
    }

    public function test_riwayat_menampilkan_modal_hapus_draf_dan_tombol_hapus_nonaktif_pada_surat_diajukan(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');
        $template = $this->makeTemplate('mahasiswa');

        SuratPengajuan::create([
            'user_id' => $mahasiswa->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Draf aktif'],
            'status' => 'draf',
        ]);

        SuratPengajuan::create([
            'user_id' => $mahasiswa->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Sudah diajukan'],
            'status' => 'menunggu',
        ]);

        $response = $this->actingAs($mahasiswa)->get('/mahasiswa/surat');

        $response->assertOk();
        $response->assertSee('id="modalHapusDraf"', false);
        $response->assertSee('data-delete-draft-trigger', false);
        $response->assertSee('aria-disabled="true"', false);
    }
}
