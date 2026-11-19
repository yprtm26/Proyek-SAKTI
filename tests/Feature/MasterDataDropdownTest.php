<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\Prodi;
use App\Models\Semester;
use App\Models\SuratPengajuan;
use App\Models\User;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\SemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataDropdownTest extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([ProdiSeeder::class, SemesterSeeder::class]);
    }

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

    private function makeJenis(string $slug, string $target, array $fields): JenisSurat
    {
        $this->counter++;

        return JenisSurat::create([
            'nama' => 'Template Uji ' . $this->counter,
            'slug' => $slug,
            'target_user' => $target,
            'fields' => $fields,
            'alur' => ['admin'],
            'aktif' => true,
        ]);
    }

    private function semesterId(string $kode): int
    {
        return (int) Semester::query()->where('kode', $kode)->value('id');
    }

    public function test_api_prodi_hanya_mengembalikan_master_aktif_dan_dapat_dicari(): void
    {
        Prodi::create([
            'kode' => 'MJ',
            'nama' => 'Manajemen',
            'jenjang' => 'S1',
            'urutan' => 3,
            'aktif' => false,
        ]);

        $user = $this->makeUser('mahasiswa');

        $this->actingAs($user)
            ->getJson('/api/master/prodi')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.kode', 'TI')
            ->assertJsonPath('data.0.label', 'Teknik Informatika')
            ->assertJsonMissing(['kode' => 'MJ']);

        $this->actingAs($user)
            ->getJson('/api/master/prodi?q=perpustakaan')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.kode', 'PdSI');
    }

    public function test_api_semester_dapat_disaring_paruh_dan_tingkat(): void
    {
        $user = $this->makeUser('mahasiswa');

        $this->actingAs($user)
            ->getJson('/api/master/semester?jenis=paruh')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.label', 'Ganjil')
            ->assertJsonPath('data.1.label', 'Genap');

        $this->actingAs($user)
            ->getJson('/api/master/semester?jenis=tingkat')
            ->assertOk()
            ->assertJsonCount(8, 'data')
            ->assertJsonPath('data.0.label', 'Semester 1')
            ->assertJsonPath('data.7.label', 'Semester 8');

        $this->actingAs($user)
            ->getJson('/api/master/semester?q=ganjil')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.label', 'Ganjil');
    }

    public function test_api_dosen_hanya_mengembalikan_pengguna_berperan_dosen(): void
    {
        $this->makeUser('mahasiswa');
        $this->makeUser('admin');
        $penugas = $this->makeUser('dosen', [
            'name' => 'Dr. Penugas Utama',
            'nidn' => '04120001',
        ]);

        $user = $this->makeUser('mahasiswa');

        $this->actingAs($user)
            ->getJson('/api/master/dosen')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $penugas->id)
            ->assertJsonPath('data.0.label', 'Dr. Penugas Utama');

        $this->actingAs($user)
            ->getJson('/api/master/dosen?q=04120001')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $penugas->id);
    }

    public function test_api_master_mewajibkan_autentikasi(): void
    {
        $this->getJson('/api/master/prodi')->assertUnauthorized();
        $this->getJson('/api/master/semester')->assertUnauthorized();
        $this->getJson('/api/master/dosen')->assertUnauthorized();
    }

    public function test_api_validasi_mengembalikan_422_untuk_id_master_yang_tidak_ada(): void
    {
        $user = $this->makeUser('mahasiswa');
        $prodi = Prodi::query()->where('kode', 'TI')->firstOrFail();

        $this->actingAs($user)
            ->postJson('/api/master/validasi', [
                'prodi_id' => $prodi->id,
                'semester_id' => $this->semesterId('ganjil'),
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->actingAs($user)
            ->postJson('/api/master/validasi', [
                'prodi_id' => 999999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('prodi_id');

        $this->actingAs($user)
            ->postJson('/api/master/validasi', [
                'semester_id' => 999999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('semester_id');

        $this->actingAs($user)
            ->postJson('/api/master/validasi', [
                'dosen_id' => 999999,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dosen_id');
    }

    public function test_submit_dengan_semester_tidak_valid_ditolak_422_dan_tidak_menyimpan_surat(): void
    {
        $jenis = $this->makeJenis(
            'surat-keterangan-aktif',
            'mahasiswa',
            ['semester', 'tahun_akademik']
        );

        $this->actingAs($this->makeUser('mahasiswa'))
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, [
                'semester' => 999999,
                'tahun_akademik' => '2025/2026',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('semester');

        // Semester tingkat (1-8) bukan pilihan yang sah untuk form akademik.
        $this->actingAs($this->makeUser('mahasiswa'))
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, [
                'semester' => $this->semesterId('3'),
                'tahun_akademik' => '2025/2026',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('semester');

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_submit_semester_valid_menyimpan_id_dan_label_master(): void
    {
        $jenis = $this->makeJenis(
            'surat-keterangan-aktif',
            'mahasiswa',
            ['semester', 'tahun_akademik']
        );

        $semester = Semester::query()->where('kode', 'ganjil')->firstOrFail();

        $this->actingAs($this->makeUser('mahasiswa'))
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, [
                'semester' => $semester->id,
                'tahun_akademik' => '2025/2026',
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::query()->sole();

        $this->assertSame($semester->id, $surat->data['semester_id']);
        $this->assertSame('Ganjil', $surat->data['semester']);
        $this->assertSame('2025/2026', $surat->data['tahun_akademik']);
    }

    public function test_submit_magang_dengan_semester_tingkat_tidak_valid_ditolak_422(): void
    {
        $jenis = $this->makeJenis('surat-permohonan-magang', 'mahasiswa', []);

        $this->actingAs($this->makeUser('mahasiswa', ['prodi' => 'TI']))
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, [
                'tipe_magang' => 'reguler-ti',
                'perihal' => 'Permohonan Magang',
                'kepada_yth' => 'HRD',
                'nama_mitra' => 'PT Uji',
                'alamat_mitra' => 'Jakarta',
                'tanggal_mulai' => '2026-01-05',
                'tanggal_selesai' => '2026-03-05',
                'mahasiswa' => [[
                    'npm' => '22100001',
                    'nama' => 'Mahasiswa Uji',
                    'semester' => $this->semesterId('ganjil'),
                    'no_kontak' => '08123456789',
                ]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('mahasiswa.0.semester');
    }

    public function test_submit_dosen_dengan_prodi_atau_semester_tidak_valid_ditolak_422(): void
    {
        $jenis = $this->makeJenis(
            'surat-tugas-pengembangan-modul',
            'dosen',
            ['nama_dosen', 'nama_matkul', 'semester', 'tahun_akademik', 'prodi']
        );

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $payload = [
            'nama_dosen' => 'Nama Palsu',
            'nama_matkul' => 'Pemrograman Berorientasi Objek',
            'semester' => $this->semesterId('ganjil'),
            'tahun_akademik' => '2025/2026',
            'prodi_id' => 999999,
        ];

        $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('prodi_id');

        $payload['prodi_id'] = Prodi::query()->where('kode', 'TI')->value('id');
        $payload['semester'] = 999999;

        $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors('semester');

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_submit_dosen_dengan_master_valid_menyimpan_label_dan_mengunci_identitas(): void
    {
        $jenis = $this->makeJenis(
            'surat-tugas-pengembangan-modul',
            'dosen',
            ['nama_dosen', 'nama_matkul', 'semester', 'tahun_akademik', 'prodi']
        );

        $dosen = $this->makeUser('dosen', [
            'name' => 'Dr. Dosen Asli',
            'prodi' => 'TI',
        ]);
        $prodi = Prodi::query()->where('kode', 'PdSI')->firstOrFail();
        $semester = Semester::query()->where('kode', 'genap')->firstOrFail();

        $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'nama_dosen' => 'Nama Palsu',
                'nama_matkul' => 'Sistem Informasi',
                'semester' => $semester->id,
                'tahun_akademik' => '2025/2026',
                'prodi_id' => $prodi->id,
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::query()->sole();

        $this->assertSame('Dr. Dosen Asli', $surat->data['nama_dosen']);
        $this->assertSame('PdSI', $surat->data['prodi']);
        $this->assertSame('Perpustakaan dan Sains Informasi', $surat->data['prodi_nama']);
        $this->assertSame($prodi->id, $surat->data['prodi_id']);
        $this->assertSame($semester->id, $surat->data['semester_id']);
        $this->assertSame('Genap', $surat->data['semester']);
    }

    public function test_submit_dosen_penugas_tidak_valid_ditolak_422(): void
    {
        $jenis = $this->makeJenis('surat-tugas-kegiatan-kuliah', 'dosen', [
            'nama_penugas',
            'nama_ditugaskan',
            'jabatan_penugas',
            'jabatan_ditugaskan',
            'no_nip',
            'instansi_penugas',
            'instansi_ditugaskan',
            'nama_kegiatan',
            'sks',
            'tanggal',
            'waktu',
            'tempat',
        ]);

        $this->actingAs($this->makeUser('dosen'))
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'dosen_id' => 999999,
                'nama_ditugaskan' => 'Nama Palsu',
                'jabatan_penugas' => 'Dekan',
                'jabatan_ditugaskan' => 'Dosen',
                'no_nip' => '0000',
                'instansi_penugas' => 'Universitas YARSI',
                'instansi_ditugaskan' => 'FTI',
                'nama_kegiatan' => 'Kuliah',
                'sks' => 3,
                'tanggal' => '2026-01-05',
                'waktu' => '08.00',
                'tempat' => 'Ruang 501',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('dosen_id');

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_submit_dosen_penugas_valid_menyimpan_nama_dari_master(): void
    {
        $jenis = $this->makeJenis('surat-tugas-kegiatan-kuliah', 'dosen', [
            'nama_penugas',
            'nama_ditugaskan',
            'jabatan_penugas',
            'jabatan_ditugaskan',
            'no_nip',
            'instansi_penugas',
            'instansi_ditugaskan',
            'nama_kegiatan',
            'sks',
            'tanggal',
            'waktu',
            'tempat',
        ]);

        $pengaju = $this->makeUser('dosen', [
            'name' => 'Dr. Pengaju',
            'nidn' => '04120099',
        ]);
        $penugas = $this->makeUser('dosen', [
            'name' => 'Prof. Penugas',
            'nidn' => '04120077',
        ]);

        $this->actingAs($pengaju)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'dosen_id' => $penugas->id,
                'nama_ditugaskan' => 'Nama Palsu',
                'jabatan_penugas' => 'Dekan',
                'jabatan_ditugaskan' => 'Dosen',
                'no_nip' => '0000',
                'instansi_penugas' => 'Universitas YARSI',
                'instansi_ditugaskan' => 'FTI',
                'nama_kegiatan' => 'Kuliah',
                'sks' => 3,
                'tanggal' => '2026-01-05',
                'waktu' => '08.00',
                'tempat' => 'Ruang 501',
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::query()->sole();

        $this->assertSame($penugas->id, $surat->data['dosen_id']);
        $this->assertSame('Prof. Penugas', $surat->data['nama_penugas']);
        $this->assertSame($pengaju->name, $surat->data['nama_ditugaskan']);
        $this->assertSame($pengaju->nidn, $surat->data['no_nip']);
    }

    public function test_form_mahasiswa_menampilkan_dropdown_semester_termasuk_baris_magang(): void
    {
        $keterangan = $this->makeJenis(
            'surat-keterangan-aktif',
            'mahasiswa',
            ['semester', 'tahun_akademik']
        );
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $keterangan->id)
            ->assertOk()
            ->assertSee('name="semester"', false)
            ->assertSee('/api/master/semester', false)
            ->assertSee('data-combobox-search', false)
            ->assertSee('data-required="1"', false);

        $magang = $this->makeJenis('surat-permohonan-magang', 'mahasiswa', []);

        $this->actingAs($mahasiswa)
            ->get('/mahasiswa/surat/ajukan/' . $magang->id)
            ->assertOk()
            ->assertSee('barisMahasiswaTemplate', false)
            ->assertSee('mahasiswa[0][semester]', false)
            ->assertSee('__INDEX__', false)
            ->assertSee('aria-label="Semester"', false)
            ->assertSee('jenis', false);
    }

    public function test_form_dinamis_memakai_combobox_semester(): void
    {
        $jenis = $this->makeJenis('template-uji-semester', 'mahasiswa', ['semester']);

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/mahasiswa/surat/ajukan/' . $jenis->id)
            ->assertOk()
            ->assertSee('role="combobox"', false)
            ->assertSee('name="semester"', false)
            ->assertSee('/api/master/semester', false);
    }

    public function test_form_menampilkan_combobox_aksesibel_untuk_prodi_semester_dan_dosen(): void
    {
        $jenisModul = $this->makeJenis(
            'surat-tugas-pengembangan-modul',
            'dosen',
            ['nama_dosen', 'nama_matkul', 'semester', 'tahun_akademik', 'prodi']
        );

        $this->actingAs($this->makeUser('dosen'))
            ->get('/dosen/surat/ajukan/' . $jenisModul->id)
            ->assertOk()
            ->assertSee('data-combobox', false)
            ->assertSee('role="combobox"', false)
            ->assertSee('data-combobox-search', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('/api/master/prodi', false)
            ->assertSee('/api/master/semester', false);

        $jenisKuliah = $this->makeJenis('surat-tugas-kegiatan-kuliah', 'dosen', [
            'nama_penugas',
            'nama_ditugaskan',
            'jabatan_penugas',
            'jabatan_ditugaskan',
            'no_nip',
            'instansi_penugas',
            'instansi_ditugaskan',
            'nama_kegiatan',
            'sks',
            'tanggal',
            'waktu',
            'tempat',
        ]);

        $this->actingAs($this->makeUser('dosen'))
            ->get('/dosen/surat/ajukan/' . $jenisKuliah->id)
            ->assertOk()
            ->assertSee('name="dosen_id"', false)
            ->assertSee('/api/master/dosen', false);
    }
}
