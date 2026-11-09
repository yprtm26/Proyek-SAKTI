<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PemilihanTemplateAktifTest extends TestCase
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
        array $fields = ['judul_kegiatan'],
        ?string $slug = null
    ): JenisSurat {
        $this->counter++;

        return JenisSurat::create([
            'nama' => 'Template Uji ' . $this->counter,
            'slug' => $slug ?? 'template-uji-' . $this->counter,
            'target_user' => $targetUser,
            'fields' => $fields,
            'alur' => ['admin'],
            'aktif' => $aktif,
        ]);
    }

    public function test_dashboard_mahasiswa_hanya_menampilkan_template_aktif_milik_mahasiswa(): void
    {
        $aktif = $this->makeTemplate('mahasiswa', true);
        $nonaktif = $this->makeTemplate('mahasiswa', false);
        $milikDosen = $this->makeTemplate('dosen', true);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/mahasiswa/dashboard')
            ->assertOk()
            ->assertSee($aktif->nama)
            ->assertDontSee($nonaktif->nama)
            ->assertDontSee($milikDosen->nama);
    }

    public function test_dashboard_dosen_hanya_menampilkan_template_aktif_milik_dosen(): void
    {
        $aktif = $this->makeTemplate('dosen', true);
        $nonaktif = $this->makeTemplate('dosen', false);
        $milikMahasiswa = $this->makeTemplate('mahasiswa', true);

        $this->actingAs($this->makeUser('dosen'))
            ->get('/dosen/dashboard')
            ->assertOk()
            ->assertSee($aktif->nama)
            ->assertDontSee($nonaktif->nama)
            ->assertDontSee($milikMahasiswa->nama);
    }

    public function test_template_nonaktif_tidak_dapat_dibuka_maupun_diajukan(): void
    {
        $nonaktifMahasiswa = $this->makeTemplate('mahasiswa', false, ['judul_kegiatan']);
        $nonaktifDosen = $this->makeTemplate('dosen', false, ['nama_dosen']);

        $mahasiswa = $this->makeUser('mahasiswa');
        $dosen = $this->makeUser('dosen');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $nonaktifMahasiswa->id)
            ->assertNotFound();

        $this->actingAs($mahasiswa)
            ->post('/mahasiswa/surat/ajukan/' . $nonaktifMahasiswa->id, [
                'judul_kegiatan' => 'Kegiatan yang tidak boleh masuk',
            ])
            ->assertNotFound();

        $this->actingAs($dosen)
            ->get('/dosen/surat/ajukan/' . $nonaktifDosen->id)
            ->assertNotFound();

        $this->actingAs($dosen)
            ->post('/dosen/surat/ajukan/' . $nonaktifDosen->id, [
                'nama_dosen' => 'Nama Palsu',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_template_milik_role_lain_tidak_dapat_dipakai(): void
    {
        $milikDosen = $this->makeTemplate('dosen', true, ['nama_dosen']);
        $milikMahasiswa = $this->makeTemplate('mahasiswa', true, ['judul_kegiatan']);

        $mahasiswa = $this->makeUser('mahasiswa');
        $dosen = $this->makeUser('dosen');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $milikDosen->id)
            ->assertNotFound();

        $this->actingAs($mahasiswa)
            ->post('/mahasiswa/surat/ajukan/' . $milikDosen->id, [
                'nama_dosen' => 'Nama Palsu',
            ])
            ->assertNotFound();

        $this->actingAs($dosen)
            ->get('/dosen/surat/ajukan/' . $milikMahasiswa->id)
            ->assertNotFound();

        $this->actingAs($dosen)
            ->post('/dosen/surat/ajukan/' . $milikMahasiswa->id, [
                'judul_kegiatan' => 'Kegiatan lintas role',
            ])
            ->assertNotFound();

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_dinamis_merender_field_sesuai_template(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'judul_kegiatan',
            'tanggal_kegiatan',
            'deskripsi',
        ]);

        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('Judul Kegiatan')
            ->assertSee('Tanggal Kegiatan')
            ->assertSee('Deskripsi')
            ->assertSee('name="judul_kegiatan"', false)
            ->assertSee('name="tanggal_kegiatan"', false)
            ->assertSee('name="deskripsi"', false)
            ->assertSee('type="date"', false)
            ->assertSee($mahasiswa->name);
    }

    public function test_pengajuan_template_dinamis_menyimpan_data_field(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'judul_kegiatan',
            'tanggal_kegiatan',
            'deskripsi',
        ]);
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->post('/mahasiswa/surat/ajukan/' . $template->id, [
                'judul_kegiatan' => 'Seminar Teknologi',
                'tanggal_kegiatan' => '2026-11-20',
                'deskripsi' => 'Seminar tahunan program studi.',
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::firstOrFail();

        $this->assertSame($mahasiswa->id, $surat->user_id);
        $this->assertSame($template->id, $surat->jenis_surat_id);
        $this->assertSame('menunggu', $surat->status);
        $this->assertSame([
            'judul_kegiatan' => 'Seminar Teknologi',
            'tanggal_kegiatan' => '2026-11-20',
            'deskripsi' => 'Seminar tahunan program studi.',
        ], $surat->data);
    }

    public function test_form_dinamis_dosen_mengunci_identitas_dan_menyimpan_field(): void
    {
        $template = $this->makeTemplate('dosen', true, [
            'nama_dosen',
            'judul_kegiatan',
            'prodi',
            'no_nip',
        ]);

        $dosen = $this->makeUser('dosen', [
            'name' => 'Dr. Dosen Asli',
            'prodi' => 'TI',
            'nidn' => '04129999',
        ]);

        $this->actingAs($dosen)
            ->get('/dosen/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('name="nama_dosen"', false)
            ->assertSee('name="prodi"', false)
            ->assertSee('name="no_nip"', false)
            ->assertSee('readonly', false);

        $this->actingAs($dosen)
            ->post('/dosen/surat/ajukan/' . $template->id, [
                'nama_dosen' => 'Nama Palsu',
                'judul_kegiatan' => 'Pelatihan Dosen',
                'prodi' => 'PdSI',
                'no_nip' => '000000',
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::firstOrFail();

        $this->assertSame($dosen->id, $surat->user_id);
        $this->assertSame('Dr. Dosen Asli', $surat->data['nama_dosen']);
        $this->assertSame('TI', $surat->data['prodi']);
        $this->assertSame('04129999', $surat->data['no_nip']);
        $this->assertSame('Pelatihan Dosen', $surat->data['judul_kegiatan']);
    }

    public function test_pengajuan_template_dinamis_mewajibkan_semua_field(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'judul_kegiatan',
            'tanggal_kegiatan',
        ]);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->from('/mahasiswa/surat/ajukan/' . $template->id)
            ->post('/mahasiswa/surat/ajukan/' . $template->id, [
                'judul_kegiatan' => '',
            ])
            ->assertRedirect('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertSessionHasErrors(['judul_kegiatan', 'tanggal_kegiatan']);

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_dinamis_mahasiswa_mengunci_identitas_di_server(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'nama',
            'npm',
            'prodi',
            'judul_kegiatan',
        ]);

        $mahasiswa = $this->makeUser('mahasiswa', [
            'name' => 'Nama Asli',
            'npm' => '22109999',
            'prodi' => 'TI',
        ]);

        $this->actingAs($mahasiswa)
            ->post('/mahasiswa/surat/ajukan/' . $template->id, [
                'nama' => 'Nama Palsu',
                'npm' => '00000000',
                'prodi' => 'PdSI',
                'judul_kegiatan' => 'Kegiatan',
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::firstOrFail();

        $this->assertSame('Nama Asli', $surat->data['nama']);
        $this->assertSame('22109999', $surat->data['npm']);
        $this->assertSame('TI', $surat->data['prodi']);
    }

    public function test_form_dinamis_tidak_menampilkan_identitas_palsu_setelah_validasi_gagal(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'nama',
            'npm',
            'prodi',
            'judul_kegiatan',
        ]);

        $mahasiswa = $this->makeUser('mahasiswa', [
            'name' => 'Nama Asli',
            'npm' => '22109999',
            'prodi' => 'TI',
        ]);

        $this->actingAs($mahasiswa)
            ->from('/mahasiswa/surat/ajukan/' . $template->id)
            ->post('/mahasiswa/surat/ajukan/' . $template->id, [
                'nama' => 'Nama Palsu',
                'npm' => '00000000',
                'prodi' => 'PdSI',
                'judul_kegiatan' => '',
            ])
            ->assertRedirect('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertSessionHasErrors('judul_kegiatan');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('Nama Asli')
            ->assertDontSee('Nama Palsu');
    }

    public function test_form_dinamis_merender_tipe_input_yang_bervariasi(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'tanggal_kegiatan',
            'waktu_kegiatan',
            'nominal_pembayaran',
            'link_jurnal',
            'email',
            'deskripsi',
            'judul_kegiatan',
        ]);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('type="date"', false)
            ->assertSee('type="time"', false)
            ->assertSee('type="number"', false)
            ->assertSee('type="url"', false)
            ->assertSee('type="email"', false)
            ->assertSee('<textarea', false)
            ->assertSee('type="text"', false);
    }

    public function test_form_khusus_tetap_digunakan_untuk_template_yang_sudah_punya_view(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, [
            'semester',
            'tahun_akademik',
        ], 'surat-keterangan-aktif');

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/mahasiswa/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('Semester Aktif')
            ->assertSee('name="semester"', false)
            ->assertSee('name="tahun_akademik"', false);
    }

    public function test_endpoint_template_surat_hanya_mengembalikan_template_aktif_sesuai_role(): void
    {
        $aktifMahasiswa = $this->makeTemplate('mahasiswa', true);
        $nonaktifMahasiswa = $this->makeTemplate('mahasiswa', false);
        $aktifDosen = $this->makeTemplate('dosen', true);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->getJson('/api/template-surat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $aktifMahasiswa->id)
            ->assertJsonMissing(['id' => $nonaktifMahasiswa->id])
            ->assertJsonMissing(['id' => $aktifDosen->id]);

        $this->actingAs($this->makeUser('dosen'))
            ->getJson('/api/template-surat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $aktifDosen->id);
    }

    public function test_endpoint_template_surat_menolak_filter_role_lain(): void
    {
        $templateDosen = $this->makeTemplate('dosen', true, ['nama_dosen']);
        $templateMahasiswa = $this->makeTemplate('mahasiswa', true, ['judul_kegiatan']);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->getJson('/api/template-surat?target_user=dosen')
            ->assertForbidden();

        $this->actingAs($this->makeUser('dosen'))
            ->getJson('/api/template-surat?target_user=mahasiswa')
            ->assertForbidden();

        $this->actingAs($this->makeUser('mahasiswa'))
            ->getJson('/api/template-surat')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $templateMahasiswa->id)
            ->assertJsonMissing(['id' => $templateDosen->id]);
    }

    public function test_endpoint_template_surat_hanya_dapat_diakses_pengusul(): void
    {
        $this->makeTemplate('mahasiswa', true);

        $this->actingAs($this->makeUser('admin'))
            ->getJson('/api/template-surat')
            ->assertForbidden();
    }

    public function test_endpoint_template_surat_mengembalikan_kunci_yang_dibutuhkan(): void
    {
        $template = $this->makeTemplate('mahasiswa', true, ['judul_kegiatan']);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->getJson('/api/template-surat')
            ->assertOk()
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'nama',
                        'slug',
                        'kode',
                        'target_user',
                        'fields',
                        'url_ajukan',
                    ],
                ],
            ])
            ->assertJsonPath('data.0.id', $template->id)
            ->assertJsonPath('data.0.url_ajukan', url('/mahasiswa/surat/ajukan/' . $template->id));
    }

    public function test_endpoint_template_surat_mendukung_pencarian_nama(): void
    {
        $keterangan = $this->makeTemplate('mahasiswa', true, ['judul_kegiatan']);
        $keterangan->update(['nama' => 'Surat Keterangan Aktif']);

        $magang = $this->makeTemplate('mahasiswa', true, ['judul_kegiatan']);
        $magang->update(['nama' => 'Surat Permohonan Magang']);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->getJson('/api/template-surat?search=Keterangan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $keterangan->id)
            ->assertJsonMissing(['id' => $magang->id]);
    }

    public function test_endpoint_template_surat_menolak_filter_role_tidak_valid(): void
    {
        $this->actingAs($this->makeUser('mahasiswa'))
            ->getJson('/api/template-surat?target_user=admin')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['target_user']);
    }

    public function test_endpoint_template_surat_mewajibkan_login(): void
    {
        $this->get('/api/template-surat')->assertRedirect('/login');
    }
}
