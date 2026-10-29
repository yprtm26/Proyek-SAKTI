<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\SumberDaya;
use App\Models\SuratPengajuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class HakAksesRoleTest extends TestCase
{
    use RefreshDatabase;

    private int $counter = 0;

    private function makeUser(string $role, array $attributes = []): User
    {
        $this->counter++;

        return User::create(array_merge([
            'name' => ucfirst($role).' '.$this->counter,
            'username' => $role.$this->counter,
            'password' => 'password',
            'role' => $role,
            'prodi' => in_array($role, ['mahasiswa', 'dosen', 'kaprodi'], true) ? 'TI' : null,
        ], $attributes));
    }

    public function test_tamu_diarahkan_ke_halaman_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
        $this->get('/mahasiswa/dashboard')->assertRedirect('/login');
    }

    public function test_setiap_role_dapat_mengakses_dashboard_sendiri(): void
    {
        $dashboard = [
            'mahasiswa' => '/mahasiswa/dashboard',
            'dosen' => '/dosen/dashboard',
            'admin' => '/admin/dashboard',
            'kaprodi' => '/kaprodi/dashboard',
            'dekan' => '/dekan/dashboard',
            'administrator' => '/administrator/dashboard',
        ];

        foreach ($dashboard as $role => $url) {
            $this->actingAs($this->makeUser($role))
                ->get($url)
                ->assertOk();
        }
    }

    public function test_role_lain_tidak_dapat_mengakses_dashboard_admin(): void
    {
        $this->actingAs($this->makeUser('dekan'))
            ->get('/admin/dashboard')
            ->assertForbidden();

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/admin/dashboard')
            ->assertForbidden();
    }

    public function test_role_lain_tidak_dapat_mengakses_dashboard_dekan(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->get('/dekan/dashboard')
            ->assertForbidden();
    }

    public function test_middleware_mendukung_lebih_dari_satu_role(): void
    {
        Route::middleware(['web', 'auth', 'role:admin,dekan'])
            ->get('/_test/multi-role', fn () => 'ok');

        $this->actingAs($this->makeUser('admin'))
            ->get('/_test/multi-role')
            ->assertOk();

        $this->actingAs($this->makeUser('dekan'))
            ->get('/_test/multi-role')
            ->assertOk();

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/_test/multi-role')
            ->assertForbidden();
    }

    public function test_file_sumber_daya_hanya_dapat_diunduh_role_yang_sesuai(): void
    {
        File::ensureDirectoryExists(storage_path('app/public'));
        Storage::disk('public')->put('hak-akses-test.txt', 'isi file uji');

        $file = SumberDaya::create([
            'nama' => 'Panduan Dosen',
            'file_path' => 'hak-akses-test.txt',
        ]);
        $file->role = 'dosen';
        $file->save();

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/sumberdaya/download/'.$file->id)
            ->assertForbidden();

        $this->actingAs($this->makeUser('dosen'))
            ->get('/sumberdaya/download/'.$file->id)
            ->assertOk();

        Storage::disk('public')->delete('hak-akses-test.txt');
    }

    public function test_pdf_surat_hanya_untuk_pemilik_atau_staf(): void
    {
        $pemilik = $this->makeUser('mahasiswa');
        $mahasiswaLain = $this->makeUser('mahasiswa');
        $admin = $this->makeUser('admin');

        $jenis = JenisSurat::create([
            'nama' => 'Surat Uji',
            'slug' => 'surat-uji-tanpa-template',
            'alur' => ['admin'],
            'fields' => [],
            'aktif' => true,
        ]);

        $surat = SuratPengajuan::create([
            'user_id' => $pemilik->id,
            'jenis_surat_id' => $jenis->id,
            'data' => ['semester' => '1', 'tahun_akademik' => '2026/2027'],
            'status' => 'disetujui_final',
            'nomor_surat' => '001/TEST/2026',
            'qr_token' => (string) Str::uuid(),
            'tanggal_disetujui_final' => now(),
        ]);

        // Mahasiswa lain tidak boleh mengunduh surat milik orang lain.
        $this->actingAs($mahasiswaLain)
            ->get("/mahasiswa/surat/{$surat->id}/pdf")
            ->assertForbidden();

        // Pemilik dan staf lolos otorisasi (404 karena template PDF uji tidak ada).
        $this->actingAs($pemilik)
            ->get("/mahasiswa/surat/{$surat->id}/pdf")
            ->assertNotFound();

        $this->actingAs($admin)
            ->get("/mahasiswa/surat/{$surat->id}/pdf")
            ->assertNotFound();

        // Surat mahasiswa tidak dapat diunduh lewat endpoint PDF dosen.
        $this->actingAs($pemilik)
            ->get("/dosen/surat/{$surat->id}/pdf")
            ->assertNotFound();
    }
}
