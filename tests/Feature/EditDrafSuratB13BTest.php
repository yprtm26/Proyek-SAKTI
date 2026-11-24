<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-13 B — Pembukaan dan pengubahan draf surat milik pengusul
 *
 * Acceptance Criteria yang diuji:
 *   AC-1: Pengusul dapat membuka draf miliknya; semua isian sebelumnya terisi.
 *   AC-2: Pengusul dapat menekan 'Kirim Pengajuan' untuk ubah status jadi 'menunggu'.
 *   AC-3: Sistem mengembalikan 403 Forbidden bila pengusul mencoba membuka draf
 *         milik akun lain via URL ID.
 */
class EditDrafSuratB13BTest extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role, array $attr = []): User
    {
        $this->counter++;
        return User::create(array_merge([
            'name'     => ucfirst($role) . ' ' . $this->counter,
            'username' => $role . $this->counter,
            'password' => 'password',
            'role'     => $role,
            'prodi'    => in_array($role, ['mahasiswa', 'dosen'], true) ? 'TI' : null,
            'npm'      => $role === 'mahasiswa' ? '22100' . $this->counter : null,
            'nidn'     => $role === 'dosen' ? '04120' . $this->counter : null,
        ], $attr));
    }

    private function makeTemplate(
        string $targetUser = 'mahasiswa',
        array $fields = ['judul_kegiatan', 'deskripsi'],
    ): JenisSurat {
        $this->counter++;
        return JenisSurat::create([
            'nama'        => 'Template B13B-' . $this->counter,
            'slug'        => 'template-b13b-' . $this->counter,
            'target_user' => $targetUser,
            'fields'      => $fields,
            'alur'        => ['admin'],
            'aktif'       => true,
        ]);
    }

    private function makeDraf(User $user, JenisSurat $template, array $data = []): SuratPengajuan
    {
        return SuratPengajuan::create([
            'user_id'        => $user->id,
            'jenis_surat_id' => $template->id,
            'data'           => array_merge(['judul_kegiatan' => 'Draft Awal'], $data),
            'status'         => 'draf',
        ]);
    }

    // =========================================================================
    // AC-1: Halaman edit draf terbuka dan menampilkan isian lama
    // =========================================================================

    public function test_mahasiswa_dapat_membuka_halaman_edit_draf_miliknya(): void
    {
        $template  = $this->makeTemplate('mahasiswa');
        $mahasiswa = $this->makeUser('mahasiswa');
        $draf      = $this->makeDraf($mahasiswa, $template, [
            'judul_kegiatan' => 'Kegiatan Draf Lama',
            'deskripsi'      => 'Deskripsi awal',
        ]);

        $response = $this->actingAs($mahasiswa)
            ->get("/mahasiswa/surat/{$draf->id}/edit-draf");

        $response->assertStatus(200);
        // Halaman harus memuat nilai lama dari draf
        $response->assertSee('Kegiatan Draf Lama');
        $response->assertSee('Deskripsi awal');
        // Harus ada tombol Kirim Pengajuan
        $response->assertSee('Kirim Pengajuan');
        // Harus ada tombol Simpan Draf
        $response->assertSee('Simpan Draf');
    }

    public function test_dosen_dapat_membuka_halaman_edit_draf_miliknya(): void
    {
        $template = $this->makeTemplate('dosen', ['nama_ditugaskan', 'judul_kegiatan']);
        $dosen    = $this->makeUser('dosen');
        $draf     = $this->makeDraf($dosen, $template, ['judul_kegiatan' => 'Penelitian Draf']);

        $response = $this->actingAs($dosen)
            ->get("/dosen/surat/{$draf->id}/edit-draf");

        $response->assertStatus(200);
        $response->assertSee('Penelitian Draf');
    }

    // =========================================================================
    // AC-3: 403 jika buka draf milik akun lain
    // =========================================================================

    public function test_mahasiswa_lain_tidak_dapat_membuka_draf_milik_orang_lain(): void
    {
        $template    = $this->makeTemplate('mahasiswa');
        $pemilik     = $this->makeUser('mahasiswa');
        $penyusup    = $this->makeUser('mahasiswa');
        $draf        = $this->makeDraf($pemilik, $template);

        // Penyusup mencoba buka draf milik pemilik
        $response = $this->actingAs($penyusup)
            ->get("/mahasiswa/surat/{$draf->id}/edit-draf");

        $response->assertStatus(403);
    }

    public function test_dosen_lain_tidak_dapat_membuka_draf_milik_dosen_lain(): void
    {
        $template  = $this->makeTemplate('dosen');
        $pemilik   = $this->makeUser('dosen');
        $penyusup  = $this->makeUser('dosen');
        $draf      = $this->makeDraf($pemilik, $template);

        $response = $this->actingAs($penyusup)
            ->get("/dosen/surat/{$draf->id}/edit-draf");

        $response->assertStatus(403);
    }

    public function test_endpoint_post_update_draf_mengembalikan_403_jika_bukan_pemilik(): void
    {
        $template  = $this->makeTemplate('mahasiswa');
        $pemilik   = $this->makeUser('mahasiswa');
        $penyusup  = $this->makeUser('mahasiswa');
        $draf      = $this->makeDraf($pemilik, $template);

        $response = $this->actingAs($penyusup)
            ->post("/mahasiswa/surat/{$draf->id}/update-draf", [
                '_token'          => csrf_token(),
                'jenis_surat_id'  => $template->id,
                'judul_kegiatan'  => 'Manipulasi data',
                'action'          => 'submit',
            ]);

        $response->assertStatus(403);

        // Memastikan data di DB tidak berubah
        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'draf',
        ]);
    }

    // =========================================================================
    // AC-2: Simpan ulang draf (tetap draf)
    // =========================================================================

    public function test_mahasiswa_dapat_memperbarui_isian_draf_tanpa_mengirim_pengajuan(): void
    {
        $template  = $this->makeTemplate('mahasiswa');
        $mahasiswa = $this->makeUser('mahasiswa');
        $draf      = $this->makeDraf($mahasiswa, $template, ['judul_kegiatan' => 'Lama']);

        $response = $this->actingAs($mahasiswa)
            ->post("/mahasiswa/surat/{$draf->id}/update-draf", [
                '_token'          => csrf_token(),
                'jenis_surat_id'  => $template->id,
                'judul_kegiatan'  => 'Judul Baru Diperbarui',
                'action'          => 'draft',    // simpan draf
            ]);

        $response->assertRedirect('/mahasiswa/surat');
        $response->assertSessionHas('success');

        // Status harus tetap 'draf'
        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'draf',
        ]);

        // Data harus diperbarui
        $draf->refresh();
        $this->assertEquals('Judul Baru Diperbarui', $draf->data['judul_kegiatan']);
    }

    // =========================================================================
    // AC-2: Kirim pengajuan dari draf → status menjadi 'menunggu'
    // =========================================================================

    public function test_mahasiswa_dapat_mengirim_pengajuan_dari_draf_sehingga_status_menjadi_menunggu(): void
    {
        $template  = $this->makeTemplate('mahasiswa');
        $mahasiswa = $this->makeUser('mahasiswa');
        $draf      = $this->makeDraf($mahasiswa, $template, [
            'judul_kegiatan' => 'Kegiatan Final',
            'deskripsi'      => 'Deskripsi lengkap',
        ]);

        $response = $this->actingAs($mahasiswa)
            ->post("/mahasiswa/surat/{$draf->id}/update-draf", [
                '_token'          => csrf_token(),
                'jenis_surat_id'  => $template->id,
                'judul_kegiatan'  => 'Kegiatan Final Dikirim',
                'deskripsi'       => 'Deskripsi lengkap',
                'action'          => 'submit',   // kirim pengajuan
            ]);

        // Redirect ke detail surat
        $response->assertRedirect("/mahasiswa/surat/{$draf->id}");
        $response->assertSessionHas('success');

        // Status harus berubah dari 'draf' menjadi 'menunggu'
        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'menunggu',
        ]);
    }

    public function test_dosen_dapat_mengirim_pengajuan_dari_draf_sehingga_status_menjadi_menunggu(): void
    {
        $template = $this->makeTemplate('dosen', ['judul_kegiatan', 'deskripsi']);
        $dosen    = $this->makeUser('dosen');
        $draf     = $this->makeDraf($dosen, $template, [
            'judul_kegiatan' => 'Penelitian',
            'deskripsi'      => 'Deskripsi penelitian',
        ]);

        $response = $this->actingAs($dosen)
            ->post("/dosen/surat/{$draf->id}/update-draf", [
                '_token'          => csrf_token(),
                'jenis_surat_id'  => $template->id,
                'judul_kegiatan'  => 'Penelitian Dikirim',
                'deskripsi'       => 'Deskripsi penelitian lengkap',
                'action'          => 'submit',
            ]);

        $response->assertRedirect("/dosen/surat/{$draf->id}");

        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'menunggu',
        ]);
    }

    // =========================================================================
    // Validasi: submit tanpa isian wajib harus gagal
    // =========================================================================

    public function test_kirim_pengajuan_dari_draf_gagal_jika_field_wajib_kosong(): void
    {
        $template  = $this->makeTemplate('mahasiswa', ['judul_kegiatan', 'deskripsi']);
        $mahasiswa = $this->makeUser('mahasiswa');
        $draf      = $this->makeDraf($mahasiswa, $template);

        // Submit tanpa deskripsi (field wajib)
        $response = $this->actingAs($mahasiswa)
            ->post("/mahasiswa/surat/{$draf->id}/update-draf", [
                '_token'          => csrf_token(),
                'jenis_surat_id'  => $template->id,
                'judul_kegiatan'  => 'Ada',
                // 'deskripsi' sengaja tidak dikirim
                'action'          => 'submit',
            ]);

        $response->assertSessionHasErrors(['deskripsi']);

        // Status harus tetap 'draf'
        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'draf',
        ]);
    }

    public function test_simpan_draf_ulang_boleh_dengan_isian_parsial_tanpa_error(): void
    {
        $template  = $this->makeTemplate('mahasiswa', ['judul_kegiatan', 'deskripsi']);
        $mahasiswa = $this->makeUser('mahasiswa');
        $draf      = $this->makeDraf($mahasiswa, $template);

        // Simpan draf dengan hanya judul (deskripsi kosong) — harus berhasil
        $response = $this->actingAs($mahasiswa)
            ->post("/mahasiswa/surat/{$draf->id}/update-draf", [
                '_token'         => csrf_token(),
                'jenis_surat_id' => $template->id,
                'judul_kegiatan' => 'Judul Parsial',
                'action'         => 'draft',
            ]);

        $response->assertRedirect('/mahasiswa/surat');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'draf',
        ]);
    }

    // =========================================================================
    // Guard: surat berstatus 'menunggu' tidak bisa diakses via edit-draf
    // =========================================================================

    public function test_surat_non_draf_tidak_dapat_diakses_via_endpoint_edit_draf(): void
    {
        $template  = $this->makeTemplate('mahasiswa');
        $mahasiswa = $this->makeUser('mahasiswa');

        // Surat sudah berstatus 'menunggu'
        $surat = SuratPengajuan::create([
            'user_id'        => $mahasiswa->id,
            'jenis_surat_id' => $template->id,
            'data'           => ['judul_kegiatan' => 'Sudah Diajukan'],
            'status'         => 'menunggu',
        ]);

        $response = $this->actingAs($mahasiswa)
            ->get("/mahasiswa/surat/{$surat->id}/edit-draf");

        // Harus 404 karena route memfilter WHERE status = 'draf'
        $response->assertStatus(404);
    }

    // =========================================================================
    // Draf yang sudah dikirim tidak muncul di antrean admin
    // =========================================================================

    public function test_surat_yang_dikirim_dari_draf_muncul_di_antrean_admin(): void
    {
        $template  = $this->makeTemplate('mahasiswa');
        $mahasiswa = $this->makeUser('mahasiswa');
        $admin     = $this->makeUser('admin');
        $draf      = $this->makeDraf($mahasiswa, $template, [
            'judul_kegiatan' => 'Final dari Draf',
            'deskripsi'      => 'Lengkap',
        ]);

        // Kirim dari draf
        $this->actingAs($mahasiswa)
            ->post("/mahasiswa/surat/{$draf->id}/update-draf", [
                '_token'          => csrf_token(),
                'jenis_surat_id'  => $template->id,
                'judul_kegiatan'  => 'Final dari Draf',
                'deskripsi'       => 'Lengkap',
                'action'          => 'submit',
            ]);

        // Antrean admin menampilkan nama pengusul, jenis surat, dan status
        // (bukan isian JSON `data` seperti 'Final dari Draf').
        $response = $this->actingAs($admin)
            ->get('/admin/dashboard');

        $response->assertStatus(200)
            ->assertSee($template->nama)
            ->assertSee($mahasiswa->name)
            ->assertSee('menunggu');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'url'     => '/admin/surat/' . $draf->id,
        ]);
    }

    // =========================================================================
    // REGRESI: template yang punya form khusus tetap bisa diedit dari draf
    // =========================================================================

    public function test_edit_draf_template_dengan_form_khusus_memunculkan_isian_lama(): void
    {
        $template = $this->makeTemplate('dosen', [
            'nama_ditugaskan', 'nama_kegiatan', 'sks', 'tanggal', 'waktu', 'tempat',
        ]);
        $template->update(['slug' => 'surat-tugas-kegiatan-kuliah']);

        $dosen = $this->makeUser('dosen');
        $draf  = $this->makeDraf($dosen, $template, [
            'nama_kegiatan' => 'Praktikum Sistem Operasi',
            'tempat'        => 'Lab A-301',
            'sks'           => '2',
        ]);

        $response = $this->actingAs($dosen)
            ->get("/dosen/surat/{$draf->id}/edit-draf");

        $response->assertStatus(200)
            ->assertSee('Praktikum Sistem Operasi')
            ->assertSee('Lab A-301')
            ->assertSee('action="' . url("/dosen/surat/{$draf->id}/update-draf") . '"', false)
            ->assertSee('Kirim Pengajuan');
    }

    public function test_kirim_draf_dari_form_khusus_memperbarui_surat_yang_sama(): void
    {
        $template = $this->makeTemplate('dosen', [
            'nama_ditugaskan', 'nama_kegiatan', 'sks', 'tanggal', 'waktu', 'tempat',
        ]);
        $template->update(['slug' => 'surat-tugas-kegiatan-kuliah']);

        $dosen = $this->makeUser('dosen');
        $draf  = $this->makeDraf($dosen, $template, ['nama_kegiatan' => 'Kegiatan Awal']);

        $this->actingAs($dosen)
            ->post("/dosen/surat/{$draf->id}/update-draf", [
                'jenis_surat_id'  => $template->id,
                'nama_ditugaskan' => $dosen->name,
                'nama_kegiatan'   => 'Kegiatan Diperbarui',
                'sks'             => '2',
                'tanggal'         => '2026-11-25',
                'waktu'           => '08:00',
                'tempat'          => 'Lab B',
                'action'          => 'submit',
            ])
            ->assertRedirect("/dosen/surat/{$draf->id}");

        // Harus tetap satu surat (tidak membuat pengajuan baru lewat route create).
        $this->assertDatabaseCount('surat_pengajuan', 1);
        $this->assertDatabaseHas('surat_pengajuan', [
            'id'     => $draf->id,
            'status' => 'menunggu',
        ]);
        $this->assertSame('Kegiatan Diperbarui', $draf->fresh()->data['nama_kegiatan']);
    }
}
