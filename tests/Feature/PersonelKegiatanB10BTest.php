<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\SuratPersonel;
use App\Models\User;
use App\Support\SuratPersonelSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * B-10 B: Pengisian daftar anggota dan peran dalam kegiatan.
 *
 * Menguji:
 *  AC-1: Pengusul dapat menambah & menghapus baris anggota (nama, peran, instansi/NIM) secara dinamis.
 *  AC-2: Tombol tambah & hapus baris memiliki target klik >= 44x44px dan focus ring kontras >= 3:1.
 *  AC-3: Sistem memblokir submit form jika ada baris anggota yang kolom nama atau perannya kosong.
 *  Checklist Backend: Tabel relasi `surat_personel` dan endpoint simpan bulk anggota terproteksi.
 */
class PersonelKegiatanB10BTest extends TestCase
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
            'npm' => $role === 'mahasiswa' ? '22100' . $this->counter : null,
            'nidn' => $role === 'dosen' ? '04120' . $this->counter : null,
        ], $attributes));
    }

    private function makeTemplate(string $targetUser = 'dosen', array $fields = ['nama_kegiatan', 'personel']): JenisSurat
    {
        $this->counter++;

        return JenisSurat::create([
            'nama' => 'Template Kegiatan ' . $this->counter,
            'slug' => 'template-kegiatan-' . $this->counter,
            'target_user' => $targetUser,
            'fields' => $fields,
            'alur' => ['admin'],
            'aktif' => true,
        ]);
    }

    public function test_form_merender_komponen_personel_dengan_aksesibilitas_dan_atribut_dinamis(): void
    {
        $template = $this->makeTemplate('dosen', ['nama_kegiatan', 'personel']);
        $dosen = $this->makeUser('dosen');

        $response = $this->actingAs($dosen)
            ->get('/dosen/surat/ajukan/' . $template->id);

        $response->assertOk()
            ->assertSee('data-sakti-personel', false)
            ->assertSee('id="btn-tambah-personel"', false)
            ->assertSee('data-personel-remove', false)
            ->assertSee('sakti-personel-btn-add', false)
            ->assertSee('sakti-personel-btn-remove', false)
            ->assertSee('personel[0][nama]', false)
            ->assertSee('personel[0][peran]', false)
            ->assertSee('personel[0][instansi]', false)
            ->assertSee('css/personel-list.css', false)
            ->assertSee('js/personel-list.js', false);
    }

    public function test_pengajuan_dengan_banyak_anggota_tersimpan_ke_tabel_relasi_surat_personel(): void
    {
        $template = $this->makeTemplate('dosen', ['nama_kegiatan', 'personel']);
        $dosen = $this->makeUser('dosen');

        $response = $this->actingAs($dosen)
            ->post('/dosen/surat/ajukan/' . $template->id, [
                'nama_kegiatan' => 'Workshop Keamanan Siber',
                'personel' => [
                    ['nama' => 'Dr. Andi', 'peran' => 'Ketua Pelaksana', 'instansi' => 'FTI YARSI'],
                    ['nama' => 'Siti Rahma', 'peran' => 'Anggota', 'instansi' => '2210099'],
                ],
            ]);

        $surat = SuratPengajuan::firstOrFail();
        $response->assertRedirect('/dosen/surat/' . $surat->id);

        $this->assertDatabaseCount('surat_personel', 2);
        $this->assertDatabaseHas('surat_personel', [
            'surat_pengajuan_id' => $surat->id,
            'nama' => 'Dr. Andi',
            'peran' => 'Ketua Pelaksana',
            'instansi' => 'FTI YARSI',
            'urutan' => 1,
        ]);
        $this->assertDatabaseHas('surat_personel', [
            'surat_pengajuan_id' => $surat->id,
            'nama' => 'Siti Rahma',
            'peran' => 'Anggota',
            'instansi' => '2210099',
            'urutan' => 2,
        ]);

        $this->actingAs($dosen)
            ->get('/dosen/surat/' . $surat->id)
            ->assertOk()
            ->assertSee('Dr. Andi')
            ->assertSee('Ketua Pelaksana')
            ->assertSee('Siti Rahma');
    }

    public function test_submit_diblokir_jika_baris_anggota_memiliki_nama_atau_peran_kosong(): void
    {
        $template = $this->makeTemplate('dosen', ['nama_kegiatan', 'personel']);
        $dosen = $this->makeUser('dosen');

        // Nama kosong pada baris kedua
        $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $template->id, [
                'nama_kegiatan' => 'Seminar Nasional',
                'personel' => [
                    ['nama' => 'Dr. Andi', 'peran' => 'Ketua', 'instansi' => 'FTI'],
                    ['nama' => '', 'peran' => 'Anggota', 'instansi' => '2210001'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('personel.1.nama');

        // Peran kosong pada baris pertama
        $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $template->id, [
                'nama_kegiatan' => 'Seminar Nasional',
                'personel' => [
                    ['nama' => 'Dr. Andi', 'peran' => '   ', 'instansi' => 'FTI'],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('personel.0.peran');

        $this->assertDatabaseCount('surat_pengajuan', 0);
        $this->assertDatabaseCount('surat_personel', 0);
    }

    public function test_submit_mahasiswa_diblokir_jika_baris_anggota_kosong_dan_tersimpan_bila_valid(): void
    {
        $template = $this->makeTemplate('mahasiswa', ['nama_kegiatan', 'personel']);
        $mahasiswa = $this->makeUser('mahasiswa');

        $this->actingAs($mahasiswa)
            ->postJson('/mahasiswa/surat/ajukan/' . $template->id, [
                'nama_kegiatan' => 'Lomba Gemastik',
                'personel' => [
                    ['nama' => $mahasiswa->name, 'peran' => ''],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('personel.0.peran');

        $this->assertDatabaseCount('surat_pengajuan', 0);

        $this->actingAs($mahasiswa)
            ->post('/mahasiswa/surat/ajukan/' . $template->id, [
                'nama_kegiatan' => 'Lomba Gemastik',
                'personel' => [
                    ['nama' => $mahasiswa->name, 'peran' => 'Ketua Tim', 'instansi' => $mahasiswa->npm],
                    ['nama' => 'Rizky Pratama', 'peran' => 'Anggota', 'instansi' => '2210888'],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseCount('surat_personel', 2);
    }

    public function test_simpan_draf_boleh_parsial_dan_edit_draf_memuat_ulang_anggota(): void
    {
        $template = $this->makeTemplate('dosen', ['nama_kegiatan', 'personel']);
        $dosen = $this->makeUser('dosen');

        $this->actingAs($dosen)
            ->post('/surat/draf', [
                'jenis_surat_id' => $template->id,
                'nama_kegiatan' => 'Draf Kegiatan',
                'personel' => [
                    ['nama' => 'Budi Santoso', 'peran' => '', 'instansi' => 'FTI'],
                ],
            ])
            ->assertRedirect();

        $draf = SuratPengajuan::firstOrFail();
        $this->assertSame('draf', $draf->status);
        $this->assertDatabaseCount('surat_personel', 1);

        // Halaman edit draf harus menampilkan data anggota lama
        $this->actingAs($dosen)
            ->get('/dosen/surat/' . $draf->id . '/edit-draf')
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('data-sakti-personel', false);

        // Kirim dari draf dengan peran kosong harus diblokir
        $this->actingAs($dosen)
            ->postJson('/dosen/surat/' . $draf->id . '/update-draf', [
                'jenis_surat_id' => $template->id,
                'nama_kegiatan' => 'Draf Kegiatan',
                'personel' => [
                    ['nama' => 'Budi Santoso', 'peran' => '', 'instansi' => 'FTI'],
                ],
                'action' => 'submit',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('personel.0.peran');

        // Kirim dari draf setelah peran dilengkapi harus sukses & update relasi
        $this->actingAs($dosen)
            ->post('/dosen/surat/' . $draf->id . '/update-draf', [
                'jenis_surat_id' => $template->id,
                'nama_kegiatan' => 'Kegiatan Final',
                'personel' => [
                    ['nama' => 'Budi Santoso', 'peran' => 'Narasumber', 'instansi' => 'FTI'],
                    ['nama' => 'Citra Dewi', 'peran' => 'Moderator', 'instansi' => 'FTI'],
                ],
                'action' => 'submit',
            ])
            ->assertRedirect('/dosen/surat/' . $draf->id);

        $this->assertDatabaseHas('surat_pengajuan', [
            'id' => $draf->id,
            'status' => 'menunggu',
        ]);
        $this->assertDatabaseCount('surat_personel', 2);
        $this->assertDatabaseHas('surat_personel', [
            'surat_pengajuan_id' => $draf->id,
            'nama' => 'Citra Dewi',
            'peran' => 'Moderator',
            'urutan' => 2,
        ]);
    }

    public function test_endpoint_simpan_bulk_anggota_mengganti_relasi_dan_memproteksi_kepemilikan(): void
    {
        $template = $this->makeTemplate('dosen', ['nama_kegiatan', 'personel']);
        $pemilik = $this->makeUser('dosen');
        $penyusup = $this->makeUser('dosen');

        $surat = SuratPengajuan::create([
            'user_id' => $pemilik->id,
            'jenis_surat_id' => $template->id,
            'data' => ['nama_kegiatan' => 'Pelatihan'],
            'status' => 'draf',
        ]);

        // Penyusup ditolak 403
        $this->actingAs($penyusup)
            ->postJson('/surat/' . $surat->id . '/personel', [
                'personel' => [
                    ['nama' => 'Penyusup', 'peran' => 'Ketua'],
                ],
            ])
            ->assertForbidden();

        $this->actingAs($penyusup)
            ->getJson('/surat/' . $surat->id . '/personel')
            ->assertForbidden();

        // Pemilik gagal validasi bila baris kosong
        $this->actingAs($pemilik)
            ->postJson('/surat/' . $surat->id . '/personel', [
                'personel' => [
                    ['nama' => 'Ketua Sah', 'peran' => ''],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('personel.0.peran');

        // Pemilik berhasil simpan bulk
        $this->actingAs($pemilik)
            ->postJson('/surat/' . $surat->id . '/personel', [
                'personel' => [
                    ['nama' => 'Ketua Sah', 'peran' => 'Ketua', 'instansi' => 'FTI'],
                    ['nama' => 'Anggota Satu', 'peran' => 'Sekretaris', 'instansi' => '2210055'],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.0.nama', 'Ketua Sah')
            ->assertJsonPath('data.1.peran', 'Sekretaris');

        // Simpan bulk kedua kali menggantikan daftar sebelumnya tanpa duplikasi
        $this->actingAs($pemilik)
            ->postJson('/surat/' . $surat->id . '/personel', [
                'personel' => [
                    ['nama' => 'Pengganti', 'peran' => 'Koordinator', 'instansi' => 'YARSI'],
                ],
            ])
            ->assertOk();

        $this->assertSame(1, SuratPersonel::where('surat_pengajuan_id', $surat->id)->count());
        $this->assertDatabaseHas('surat_personel', [
            'surat_pengajuan_id' => $surat->id,
            'nama' => 'Pengganti',
            'peran' => 'Koordinator',
            'urutan' => 1,
        ]);
    }

    public function test_form_surat_tugas_kegiatan_kuliah_merender_dan_menyimpan_personel(): void
    {
        $template = JenisSurat::create([
            'nama' => 'Surat Tugas Kegiatan Kuliah',
            'slug' => 'surat-tugas-kegiatan-kuliah',
            'target_user' => 'dosen',
            'fields' => [
                'nama_ditugaskan',
                'no_nip',
                'prodi',
                'jabatan_penugas',
                'instansi_penugas',
                'jabatan_ditugaskan',
                'instansi_ditugaskan',
                'nama_kegiatan',
                'sks',
                'tanggal',
                'waktu',
                'tempat',
            ],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        $dosen = $this->makeUser('dosen');

        $this->actingAs($dosen)
            ->get('/dosen/surat/ajukan/' . $template->id)
            ->assertOk()
            ->assertSee('data-sakti-personel', false)
            ->assertSee('Ketua / Penanggung Jawab');

        $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $template->id, [
                'nama_ditugaskan' => $dosen->name,
                'no_nip' => $dosen->nidn,
                'prodi' => $dosen->prodi,
                'jabatan_penugas' => 'Dekan FTI',
                'instansi_penugas' => 'Universitas YARSI',
                'jabatan_ditugaskan' => 'Dosen Tetap',
                'instansi_ditugaskan' => 'FTI YARSI',
                'nama_kegiatan' => 'Praktikum Pemrograman Web',
                'sks' => '3',
                'tanggal' => '2026-11-26',
                'waktu' => '08:00',
                'tempat' => 'Lab C-201',
                'personel' => [
                    ['nama' => $dosen->name, 'peran' => ''],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('personel.0.peran');

        $this->actingAs($dosen)
            ->post('/dosen/surat/ajukan/' . $template->id, [
                'nama_ditugaskan' => $dosen->name,
                'no_nip' => $dosen->nidn,
                'prodi' => $dosen->prodi,
                'jabatan_penugas' => 'Dekan FTI',
                'instansi_penugas' => 'Universitas YARSI',
                'jabatan_ditugaskan' => 'Dosen Tetap',
                'instansi_ditugaskan' => 'FTI YARSI',
                'nama_kegiatan' => 'Praktikum Pemrograman Web',
                'sks' => '3',
                'tanggal' => '2026-11-26',
                'waktu' => '08:00',
                'tempat' => 'Lab C-201',
                'personel' => [
                    ['nama' => $dosen->name, 'peran' => 'Ketua / Penanggung Jawab', 'instansi' => 'FTI YARSI'],
                    ['nama' => 'Asisten Lab', 'peran' => 'Asisten Praktikum', 'instansi' => '2210101'],
                ],
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::firstOrFail();
        $this->assertSame(2, SuratPersonel::where('surat_pengajuan_id', $surat->id)->count());
    }
}
