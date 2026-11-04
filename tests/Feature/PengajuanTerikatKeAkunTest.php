<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanTerikatKeAkunTest extends TestCase
{
    use RefreshDatabase;

    private function mahasiswa(): User
    {
        return User::create([
            'name' => 'Mahasiswa Uji',
            'username' => 'mhs-uji',
            'password' => 'password',
            'role' => 'mahasiswa',
            'npm' => '12345678',
            'prodi' => 'TI',
        ]);
    }

    public function test_profil_pengusul_hanya_mengembalikan_data_user_yang_login(): void
    {
        $user = $this->mahasiswa();

        $this->actingAs($user)
            ->getJson('/api/profil-pengusul')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.name', 'Mahasiswa Uji')
            ->assertJsonPath('data.npm', '12345678')
            ->assertJsonPath('data.prodi', 'TI');
    }

    public function test_profil_pengusul_mewajibkan_autentikasi(): void
    {
        $this->get('/api/profil-pengusul')->assertRedirect('/login');
    }

    public function test_pengajuan_dosen_mengikat_user_id_dan_mengabaikan_identitas_palsu(): void
    {
        $user = User::create([
            'name' => 'Dosen Asli',
            'username' => 'dsn-uji',
            'password' => 'password',
            'role' => 'dosen',
            'nidn' => '9876543210',
            'prodi' => 'PdSI',
        ]);

        $jenis = JenisSurat::create([
            'nama' => 'Surat Uji',
            'slug' => 'surat-tugas-uji',
            'target_user' => 'dosen',
            'fields' => ['nama_dosen', 'prodi', 'topik'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        $this->actingAs($user)
            ->post('/dosen/surat/ajukan/'.$jenis->id, [
                'nama_dosen' => 'Nama Palsu',
                'prodi' => 'TI',
                'topik' => 'Topik pengajuan',
                'user_id' => 999999,
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::query()->sole();
        $this->assertSame($user->id, $surat->user_id);
        $this->assertSame('Dosen Asli', $surat->data['nama_dosen']);
        $this->assertSame('PdSI', $surat->data['prodi']);
    }

    public function test_pengajuan_mahasiswa_mengunci_pemohon_pertama_di_data_kelompok(): void
    {
        $user = $this->mahasiswa();
        $jenis = JenisSurat::create([
            'nama' => 'Observasi Uji',
            'slug' => 'surat-observasi-tugas-mata-kuliah',
            'fields' => ['anggota_tim'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        $this->actingAs($user)
            ->post('/mahasiswa/surat/ajukan/'.$jenis->id, [
                'perihal' => 'Observasi',
                'kepada_yth' => 'Penerima',
                'nama_instansi' => 'Instansi',
                'kota_instansi' => 'Jakarta',
                'mata_kuliah' => 'Pengujian',
                'jenis_kegiatan' => 'Observasi',
                'anggota_tim' => [
                    ['nama' => 'Nama Palsu', 'npm' => '99999999'],
                    ['nama' => 'Anggota', 'npm' => '87654321'],
                ],
            ])
            ->assertRedirect();

        $surat = SuratPengajuan::query()->sole();
        $this->assertSame($user->id, $surat->user_id);
        $this->assertSame('Mahasiswa Uji', $surat->data['anggota_tim'][0]['nama']);
        $this->assertSame('12345678', $surat->data['anggota_tim'][0]['npm']);
        $this->assertSame('Anggota', $surat->data['anggota_tim'][1]['nama']);
    }

    public function test_formulir_pengajuan_memuat_identitas_akun_terkunci(): void
    {
        $user = $this->mahasiswa();
        $jenis = JenisSurat::create([
            'nama' => 'Surat Keterangan Aktif',
            'slug' => 'surat-keterangan-aktif',
            'fields' => ['semester', 'tahun_akademik'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        $this->actingAs($user)
            ->get('/mahasiswa/surat/ajukan/'.$jenis->id)
            ->assertOk()
            ->assertSee('Mahasiswa Uji')
            ->assertSee('12345678')
            ->assertSee('disabled');
    }
}
