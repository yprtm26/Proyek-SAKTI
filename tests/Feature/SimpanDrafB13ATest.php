<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpanDrafB13ATest extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role, array $attributes = []): User
    {
        $this->counter++;

        return User::create(array_merge([
            'name' => ucfirst($role) . ' ' . $this->counter,
            'username' => $role . $this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => in_array($role, ['mahasiswa', 'dosen'], true) ? 'TI' : null,
            'npm' => $role === 'mahasiswa' ? '2210' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '0412' . $this->counter : null,
        ], $attributes));
    }

    private function makeTemplate(
        string $targetUser = 'mahasiswa',
        bool $aktif = true,
        array $fields = ['judul_kegiatan', 'deskripsi'],
        ?string $nama = null
    ): JenisSurat {
        $this->counter++;

        return JenisSurat::create([
            'nama' => $nama ?? 'Template Draf ' . $this->counter,
            'slug' => 'template-draf-' . $this->counter,
            'target_user' => $targetUser,
            'fields' => $fields,
            'alur' => ['admin'],
            'aktif' => $aktif,
        ]);
    }

    public function test_simpan_draf_dengan_isian_parsial_tersimpan_berstatus_draf(): void
    {
        $template = $this->makeTemplate();
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->post('/surat/draf', [
                'jenis_surat_id' => $template->id,
                'judul_kegiatan' => 'Draf belum lengkap',
                // 'deskripsi' sengaja tidak diisi.
            ])
            ->assertRedirect();

        $draf = SuratPengajuan::firstOrFail();

        $this->assertSame($mahasiswa->id, $draf->user_id);
        $this->assertSame($template->id, $draf->jenis_surat_id);
        $this->assertSame('draf', $draf->status);
        $this->assertTrue($draf->isDraf());
        $this->assertSame('Draf belum lengkap', $draf->data['judul_kegiatan']);
        $this->assertArrayNotHasKey('deskripsi', $draf->data);
    }

    public function test_simpan_draf_menolak_jika_template_belum_dipilih(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->from('/mahasiswa/dashboard')
            ->post('/surat/draf', ['judul_kegiatan' => 'Tanpa template'])
            ->assertRedirect('/mahasiswa/dashboard')
            ->assertSessionHasErrors('jenis_surat_id');

        $this->assertDatabaseCount('surat_pengajuan', 0);

        $this->actingAs($mahasiswa)
            ->postJson('/surat/draf', ['judul_kegiatan' => 'Tanpa template'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('jenis_surat_id');
    }

    public function test_simpan_draf_menolak_template_nonaktif_dan_template_role_lain(): void
    {
        $nonaktif = $this->makeTemplate('mahasiswa', false);
        $milikDosen = $this->makeTemplate('dosen', true);
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->from('/mahasiswa/dashboard')
            ->post('/surat/draf', [
                'jenis_surat_id' => $nonaktif->id,
                'judul_kegiatan' => 'Template nonaktif',
            ])
            ->assertSessionHasErrors('jenis_surat_id');

        $this->actingAs($mahasiswa)
            ->from('/mahasiswa/dashboard')
            ->post('/surat/draf', [
                'jenis_surat_id' => $milikDosen->id,
                'judul_kegiatan' => 'Template dosen',
            ])
            ->assertSessionHasErrors('jenis_surat_id');

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_simpan_draf_mengunci_identitas_pengusul_di_server(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'nama', 'npm', 'prodi', 'judul_kegiatan',
        ]);
        $mahasiswa = $this->makeUser('mahasiswa', [
            'name' => 'Nama Asli',
            'npm' => '22109999',
            'prodi' => 'TI',
        ]);

        $this->actingAs($mahasiswa)
            ->post('/surat/draf', [
                'jenis_surat_id' => $template->id,
                'nama' => 'Nama Palsu',
                'npm' => '00000000',
                'prodi' => 'PdSI',
                'judul_kegiatan' => 'Draf identitas',
            ])
            ->assertRedirect();

        $draf = SuratPengajuan::firstOrFail();

        $this->assertSame('Nama Asli', $draf->data['nama']);
        $this->assertSame('22109999', $draf->data['npm']);
        $this->assertSame('TI', $draf->data['prodi']);
    }

    public function test_draf_tidak_muncul_di_antrean_admin_tu(): void
    {
        $template = $this->makeTemplate();
        $pemilikDraf = $this->makeUser('mahasiswa', ['name' => 'PEMILIK_DRAF_UNIK']);
        $pemilikSubmitted = $this->makeUser('mahasiswa', ['name' => 'PEMILIK_SUBMIT_UNIK']);
        $admin = $this->makeUser('admin');

        SuratPengajuan::create([
            'user_id' => $pemilikDraf->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Draf tersembunyi'],
            'status' => 'draf',
        ]);

        SuratPengajuan::create([
            'user_id' => $pemilikSubmitted->id,
            'jenis_surat_id' => $template->id,
            'data' => ['judul_kegiatan' => 'Pengajuan masuk antrean'],
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->get('/admin/surat')
            ->assertOk()
            ->assertDontSee('PEMILIK_DRAF_UNIK')
            ->assertSee('PEMILIK_SUBMIT_UNIK');
    }

    public function test_simpan_draf_tidak_mengirim_notifikasi_ke_admin(): void
    {
        $template = $this->makeTemplate();
        $mahasiswa = $this->makeUser('mahasiswa');
        $this->makeUser('admin');

        $this->actingAs($mahasiswa)
            ->post('/surat/draf', [
                'jenis_surat_id' => $template->id,
                'judul_kegiatan' => 'Draf tanpa notifikasi',
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_endpoint_simpan_draf_hanya_untuk_pengusul(): void
    {
        $template = $this->makeTemplate();

        $this->actingAs($this->makeUser('admin'))
            ->postJson('/surat/draf', [
                'jenis_surat_id' => $template->id,
                'judul_kegiatan' => 'Dilarang',
            ])
            ->assertForbidden();
    }

    public function test_form_dinamis_memiliki_tombol_simpan_draf_dan_hidden_template(): void
    {
        $template = $this->makeTemplate();
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('Simpan Draf')
            ->assertSee('name="jenis_surat_id"', false)
            ->assertSee('formaction="' . url('/surat/draf') . '"', false)
            ->assertSee('formnovalidate', false);
    }
}
