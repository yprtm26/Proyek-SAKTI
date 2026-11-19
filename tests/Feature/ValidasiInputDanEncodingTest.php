<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\Semester;
use App\Models\SuratPengajuan;
use App\Models\User;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\SemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidasiInputDanEncodingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([ProdiSeeder::class, SemesterSeeder::class]);
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'User Uji ' . ucfirst($role),
            'username' => $role . '_' . bin2hex(random_bytes(4)),
            'password' => 'password123',
            'role' => $role,
            'prodi' => in_array($role, ['mahasiswa', 'dosen'], true) ? 'TI' : null,
            'npm' => $role === 'mahasiswa' ? '1402021001' : null,
            'nidn' => $role === 'dosen' ? '0412000001' : null,
        ], $attributes));
    }

    private function semesterTingkatId(): int
    {
        return (int) Semester::query()->where('kode', '5')->value('id');
    }

    /**
     * Test 1: Input NPM berisi karakter SQL ditolak (misalnya 422) dan tidak mengubah query.
     */
    public function test_input_npm_berisi_karakter_sql_ditolak_422_dan_tidak_mengubah_query(): void
    {
        $mahasiswa = $this->makeUser('mahasiswa', [
            'username' => 'mhs_sqli',
            'npm' => '1402021001',
        ]);

        $jenis = JenisSurat::create([
            'nama' => 'Surat Permohonan Magang',
            'slug' => 'surat-permohonan-magang',
            'target_user' => 'mahasiswa',
            'fields' => ['tipe_magang', 'perihal', 'kepada_yth', 'nama_mitra', 'alamat_mitra', 'mahasiswa', 'tanggal_mulai', 'tanggal_selesai'],
            'alur' => ['admin'],
            'aktif' => true,
        ]);

        // Payload SQL Injection pada NPM anggota kelompok
        $sqlInjectionNPM = "1402021001' OR '1'='1";

        $response = $this->actingAs($mahasiswa)
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, [
                'tipe_magang' => 'reguler-ti',
                'perihal' => 'Pengajuan Magang',
                'kepada_yth' => 'Pimpinan Mitra',
                'nama_mitra' => 'PT Keamanan Siber',
                'alamat_mitra' => 'Jakarta Barat',
                'tanggal_mulai' => '2026-03-01',
                'tanggal_selesai' => '2026-03-31',
                'mahasiswa' => [
                    [
                        'npm' => '1402021001',
                        'nama' => 'Mahasiswa Pengaju',
                        'semester' => $this->semesterTingkatId(),
                        'no_kontak' => '08123456789',
                    ],
                    [
                        'npm' => $sqlInjectionNPM,
                        'nama' => 'Mahasiswa Injeksi',
                        'semester' => $this->semesterTingkatId(),
                        'no_kontak' => '08123456780',
                    ],
                ],
            ]);

        // Verifikasi request ditolak dengan status HTTP 422 Unprocessable Content
        $response->assertStatus(422);
        $response->assertJsonValidationErrors('mahasiswa.1.npm');
        $this->assertSame(
            'Format NPM tidak valid. Harap masukkan 10 digit angka resmi.',
            $response->json('errors')['mahasiswa.1.npm'][0]
        );

        // Verifikasi tidak ada data surat yang tersimpan di database
        $this->assertDatabaseCount('surat_pengajuan', 0);
        $this->assertDatabaseMissing('surat_pengajuan', [
            'user_id' => $mahasiswa->id,
        ]);

        // Verifikasi bahwa query database berbasis ORM tidak dieksploitasi oleh injeksi string
        $queryResult = User::where('npm', $sqlInjectionNPM)->first();
        $this->assertNull($queryResult, 'Query binding ORM menganggap payload SQL sebagai nilai literal biasa, bukan potongan sintaks SQL.');
    }

    /**
     * Test 2: Nama pengguna (misalnya dari LDAP) berisi tag <script> tampil sebagai teks biasa (output encoding).
     */
    public function test_nama_berisi_tag_script_tampil_sebagai_teks_biasa_dan_terencode(): void
    {
        // Simulasikan pengguna (mahasiswa) dengan atribut nama dari LDAP yang mengandung script XSS
        $xssName = "Budi <script>alert('XSS')</script> Santoso";

        $mahasiswa = $this->makeUser('mahasiswa', [
            'name' => $xssName,
            'username' => 'budi_xss',
            'npm' => '1402021099',
        ]);

        // Kunjungi halaman dashboard mahasiswa yang menampilkan layout dengan profil pengguna
        $response = $this->actingAs($mahasiswa)
            ->get('/mahasiswa/dashboard');

        $response->assertOk();

        // 1. Tag script unescaped TIDAK BOLEH muncul mentah di dalam response HTML
        $response->assertDontSee("<script>alert('XSS')</script>", false);

        // 2. Blade engine secara otomatis meng-encode karakter < dan > menjadi &lt; dan &gt;
        //    sehingga teks tampil sebagai tulisan biasa bagi pengguna
        $response->assertSeeText($xssName);
        $response->assertSee(e($xssName), false);
    }
}
