<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\Semester;
use App\Models\SuratPengajuan;
use App\Models\User;
use App\Support\TimeRange;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\SemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimeRangeValidationTest extends TestCase
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

    private function makeJenis(string $slug, string $target, array $fields = []): JenisSurat
    {
        $this->counter++;

        return JenisSurat::create([
            'nama' => 'Template Uji Waktu ' . $this->counter,
            'slug' => $slug,
            'target_user' => $target,
            'fields' => $fields,
            'alur' => ['admin'],
            'aktif' => true,
        ]);
    }

    public function test_form_menolak_format_waktu_tidak_valid(): void
    {
        $jenis = $this->makeJenis('surat-seminar-uji-waktu', 'dosen', [
            'nama_kegiatan',
            'tanggal',
            'waktu_mulai',
            'waktu_selesai',
        ]);

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $response = $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'nama_kegiatan' => 'Seminar Teknologi',
                'tanggal' => '2026-11-20',
                'waktu_mulai' => '25:00', // Jam tidak valid
                'waktu_selesai' => '10:00',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('waktu_mulai');

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_menolak_waktu_selesai_lebih_awal_pada_hari_yang_sama(): void
    {
        $jenis = $this->makeJenis('surat-lomba-waktu', 'dosen', [
            'nama_kegiatan',
            'tanggal',
            'waktu_mulai',
            'waktu_selesai',
        ]);

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $response = $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'nama_kegiatan' => 'Lomba Inovasi',
                'tanggal' => '2026-11-20',
                'waktu_mulai' => '14:00',
                'waktu_selesai' => '13:00', // Lebih awal daripada waktu mulai
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('waktu_selesai')
            ->assertJsonPath('errors.waktu_selesai.0', TimeRange::RANGE_MESSAGE);

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_menolak_waktu_selesai_sama_dengan_waktu_mulai_pada_hari_yang_sama(): void
    {
        $jenis = $this->makeJenis('surat-rapat-waktu', 'dosen', [
            'nama_kegiatan',
            'tanggal',
            'waktu_mulai',
            'waktu_selesai',
        ]);

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $response = $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'nama_kegiatan' => 'Rapat Jurusan',
                'tanggal' => '2026-11-20',
                'waktu_mulai' => '09:00',
                'waktu_selesai' => '09:00', // Sama persis
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('waktu_selesai')
            ->assertJsonPath('errors.waktu_selesai.0', TimeRange::RANGE_MESSAGE);

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_menerima_waktu_selesai_lebih_akhir_pada_hari_yang_sama(): void
    {
        $jenis = $this->makeJenis('surat-kuliah-tamu-waktu', 'dosen', [
            'nama_kegiatan',
            'tanggal',
            'waktu_mulai',
            'waktu_selesai',
        ]);

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $response = $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'nama_kegiatan' => 'Kuliah Tamu Industri',
                'tanggal' => '2026-11-20',
                'waktu_mulai' => '08:30',
                'waktu_selesai' => '11:00',
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('surat_pengajuan', 1);

        $surat = SuratPengajuan::first();
        $this->assertSame('08:30', $surat->data['waktu_mulai']);
        $this->assertSame('11:00', $surat->data['waktu_selesai']);
    }

    public function test_form_menerima_waktu_lintas_hari_jika_tanggal_selesai_berbeda(): void
    {
        $jenis = $this->makeJenis('surat-kegiatan-menginap', 'dosen', [
            'nama_kegiatan',
            'tanggal_mulai',
            'tanggal_selesai',
            'waktu_mulai',
            'waktu_selesai',
        ]);

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $response = $this->actingAs($dosen)
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'nama_kegiatan' => 'Kemah Ilmiah',
                'tanggal_mulai' => '2026-11-20',
                'tanggal_selesai' => '2026-11-21',
                'waktu_mulai' => '22:00',
                'waktu_selesai' => '04:00', // Boleh lebih kecil karena tanggal berikutnya
            ]);

        $response->assertStatus(302);
        $this->assertDatabaseCount('surat_pengajuan', 1);
    }

    public function test_form_dinamis_merender_komponen_time_picker_untuk_field_waktu(): void
    {
        $jenis = $this->makeJenis('surat-tugas-workshop-waktu', 'dosen', [
            'nama_kegiatan',
            'tanggal',
            'waktu',
        ]);

        $dosen = $this->makeUser('dosen', ['prodi' => 'TI']);

        $response = $this->actingAs($dosen)
            ->get('/dosen/surat/ajukan/' . $jenis->id);

        $response->assertStatus(200);
        $response->assertSee('data-sakti-timepicker', false);
        $response->assertSee('data-sakti-time-input', false);
        $response->assertSee('placeholder="HH:mm"', false);
        $response->assertSee('data-timepicker-trigger', false);
    }
}
