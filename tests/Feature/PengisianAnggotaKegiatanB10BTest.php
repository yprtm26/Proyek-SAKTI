<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\SuratPersonel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengisianAnggotaKegiatanB10BTest extends TestCase
{
    use RefreshDatabase;

    private function createDosen(): User
    {
        return User::create([
            'name'     => 'Dr. Dosen Penguji, M.Kom.',
            'username' => 'dosen_uji',
            'password' => bcrypt('password123'),
            'role'     => 'dosen',
            'nidn'     => '0123456789',
            'prodi'    => 'TI',
        ]);
    }

    private function createMahasiswa(): User
    {
        return User::create([
            'name'     => 'Mahasiswa Penguji',
            'username' => 'mhs_uji',
            'password' => bcrypt('password123'),
            'role'     => 'mahasiswa',
            'npm'      => '1402024010',
            'prodi'    => 'TI',
        ]);
    }

    private function createTemplateTugas(): JenisSurat
    {
        return JenisSurat::create([
            'nama'            => 'Surat Tugas Kegiatan Ilmiah',
            'slug'            => 'surat-tugas-kegiatan-ilmiah',
            'target_user'     => 'dosen',
            'is_active'       => true,
            'alur'            => ['dekan'],
            'indeks_kegiatan' => 'Pendidikan',
            'fields'          => [
                'nama_penugas',
                'jabatan_penugas',
                'instansi_penugas',
                'nama_kegiatan',
                'tempat',
                'tanggal',
            ],
        ]);
    }

    /**
     * Skenario 1 (Acceptance Criteria 1):
     * Pengusul dapat mengajukan surat dengan menambahkan daftar anggota dan peran
     * secara dinamis, dan sistem menyimpannya ke tabel surat_personel secara bulk.
     */
    public function test_pengusul_dapat_mengajukan_surat_dengan_daftar_anggota_secara_bulk(): void
    {
        $dosen = $this->createDosen();
        $template = $this->createTemplateTugas();

        $payload = [
            'nama_penugas'     => 'Dekan FTI',
            'jabatan_penugas'  => 'Dekan',
            'instansi_penugas' => 'Universitas YARSI',
            'nama_kegiatan'    => 'Seminar Nasional Informatika 2026',
            'tempat'           => 'Auditorium Lantai 4',
            'tanggal'          => '2026-12-01',
            'personel'         => [
                [
                    'nama'     => 'Dr. Dosen Penguji, M.Kom.',
                    'peran'    => 'Ketua Pelaksana',
                    'instansi' => 'Universitas YARSI (0123456789)',
                ],
                [
                    'nama'     => 'Ahmad Rifai, S.Kom., M.Kom.',
                    'peran'    => 'Narasumber Utama',
                    'instansi' => 'Universitas Indonesia',
                ],
                [
                    'nama'     => 'Budi Setiawan',
                    'peran'    => 'Asisten Fasilitator',
                    'instansi' => 'HIMTI YARSI (1402024099)',
                ],
            ],
        ];

        $response = $this->actingAs($dosen)->post('/dosen/surat/ajukan/' . $template->id, $payload);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('surat_pengajuan', [
            'user_id'        => $dosen->id,
            'jenis_surat_id' => $template->id,
        ]);

        $surat = SuratPengajuan::where('user_id', $dosen->id)->first();
        $this->assertNotNull($surat);

        // Verifikasi relasi hasMany personel
        $personel = $surat->personel;
        $this->assertCount(3, $personel);

        // Verifikasi urutan dan isi
        $this->assertEquals('Dr. Dosen Penguji, M.Kom.', $personel[0]->nama);
        $this->assertEquals('Ketua Pelaksana', $personel[0]->peran);
        $this->assertEquals(1, $personel[0]->urutan);

        $this->assertEquals('Ahmad Rifai, S.Kom., M.Kom.', $personel[1]->nama);
        $this->assertEquals('Narasumber Utama', $personel[1]->peran);
        $this->assertEquals(2, $personel[1]->urutan);

        $this->assertEquals('Budi Setiawan', $personel[2]->nama);
        $this->assertEquals('Asisten Fasilitator', $personel[2]->peran);
        $this->assertEquals(3, $personel[2]->urutan);
    }

    /**
     * Skenario 2 (Acceptance Criteria 3):
     * Sistem memblokir submit form jika ada baris anggota yang kolom NAMANYA belum diisi.
     */
    public function test_sistem_memblokir_submit_jika_ada_baris_anggota_yang_namanya_kosong(): void
    {
        $dosen = $this->createDosen();
        $template = $this->createTemplateTugas();

        $payload = [
            'nama_penugas'     => 'Dekan FTI',
            'jabatan_penugas'  => 'Dekan',
            'instansi_penugas' => 'Universitas YARSI',
            'nama_kegiatan'    => 'Workshop Keamanan Web',
            'tempat'           => 'Lab Jaringan',
            'tanggal'          => '2026-12-05',
            'personel'         => [
                [
                    'nama'     => 'Dr. Dosen Penguji, M.Kom.',
                    'peran'    => 'Penanggung Jawab',
                    'instansi' => 'FTI YARSI',
                ],
                [
                    'nama'     => '', // KOSONG -> Wajib diblokir (422)
                    'peran'    => 'Anggota Pelaksana',
                    'instansi' => 'FTI YARSI',
                ],
            ],
        ];

        $response = $this->actingAs($dosen)->post('/dosen/surat/ajukan/' . $template->id, $payload);

        $response->assertSessionHasErrors(['personel.1.nama']);
        $this->assertDatabaseCount('surat_pengajuan', 0);
        $this->assertDatabaseCount('surat_personel', 0);
    }

    /**
     * Skenario 3 (Acceptance Criteria 3):
     * Sistem memblokir submit form jika ada baris anggota yang kolom PERANNYA belum diisi.
     */
    public function test_sistem_memblokir_submit_jika_ada_baris_anggota_yang_perannya_kosong(): void
    {
        $dosen = $this->createDosen();
        $template = $this->createTemplateTugas();

        $payload = [
            'nama_penugas'     => 'Dekan FTI',
            'jabatan_penugas'  => 'Dekan',
            'instansi_penugas' => 'Universitas YARSI',
            'nama_kegiatan'    => 'Pelatihan Git',
            'tempat'           => 'Lab Komputer',
            'tanggal'          => '2026-12-10',
            'personel'         => [
                [
                    'nama'     => 'Dr. Dosen Penguji, M.Kom.',
                    'peran'    => 'Instruktur',
                    'instansi' => 'FTI YARSI',
                ],
                [
                    'nama'     => 'Dina Mardiana',
                    'peran'    => '', // KOSONG -> Wajib diblokir (422)
                    'instansi' => 'Universitas YARSI',
                ],
            ],
        ];

        $response = $this->actingAs($dosen)->post('/dosen/surat/ajukan/' . $template->id, $payload);

        $response->assertSessionHasErrors(['personel.1.peran']);
        $this->assertDatabaseCount('surat_pengajuan', 0);
        $this->assertDatabaseCount('surat_personel', 0);
    }

    /**
     * Skenario 4 (Acceptance Criteria 2):
     * Memverifikasi keberadaan komponen view dengan tombol tambah & hapus yang
     * memenuhi target sentuh minimal 44x44px dan focus ring kontras >= 3:1.
     */
    public function test_komponen_view_tabel_personel_memenuhi_standar_aksesibilitas(): void
    {
        $user = $this->createDosen();

        $view = $this->actingAs($user)->view('surat.partials.tabel-personel');

        // Target sentuh / klik minimal 44x44px
        $view->assertSee('min-h-[44px]', false);
        $view->assertSee('min-w-[44px]', false);

        // Focus ring kontras tinggi (>= 3:1)
        $view->assertSee('focus:ring-2', false);
        $view->assertSee('focus:ring-offset-2', false);
        $view->assertSee('focus:ring-blue-600', false);
        $view->assertSee('focus:ring-red-600', false);

        // Aksesibilitas ARIA dan ID
        $view->assertSee('aria-label="Tambah baris anggota kegiatan baru"', false);
        $view->assertSee('tbody-personel', false);
        $view->assertSee('btn-tambah-personel', false);
    }

    /**
     * Skenario 5 (API Endpoint & Guard Keamanan):
     * API bulk update personel berfungsi untuk sinkronisasi dan memblokir akses IDOR.
     */
    public function test_api_bulk_personel_berhasil_dan_dilindungi_anti_idor(): void
    {
        $dosenPemilik = $this->createDosen();
        $dosenLain = User::create([
            'name'     => 'Dosen Lain',
            'username' => 'dosen_lain',
            'password' => bcrypt('password'),
            'role'     => 'dosen',
            'nidn'     => '9876543210',
            'prodi'    => 'TI',
        ]);

        $template = $this->createTemplateTugas();
        $surat = SuratPengajuan::create([
            'user_id'        => $dosenPemilik->id,
            'jenis_surat_id' => $template->id,
            'data'           => ['kegiatan' => 'Rapat Riset'],
            'status'         => 'menunggu',
        ]);

        // Pemilik berhasil simpan bulk via API
        $response = $this->actingAs($dosenPemilik)->postJson('/api/surat/' . $surat->id . '/personel', [
            'personel' => [
                ['nama' => 'Anggota A', 'peran' => 'Sekretaris', 'instansi' => 'YARSI'],
                ['nama' => 'Anggota B', 'peran' => 'Bendahara', 'instansi' => 'YARSI'],
            ],
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data');

        $this->assertEquals(2, $surat->fresh()->personel->count());

        // Dosen lain dilarang keras mengubah (403 IDOR)
        $this->actingAs($dosenLain)
            ->postJson('/api/surat/' . $surat->id . '/personel', [
                'personel' => [
                    ['nama' => 'Hacker', 'peran' => 'Infiltrator'],
                ],
            ])
            ->assertStatus(403);
    }
}
