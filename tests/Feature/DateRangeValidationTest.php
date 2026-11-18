<?php

namespace Tests\Feature;

use App\Models\JenisSurat;
use App\Models\Semester;
use App\Models\SuratPengajuan;
use App\Models\User;
use App\Support\DateRange;
use Database\Seeders\ProdiSeeder;
use Database\Seeders\SemesterSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DateRangeValidationTest extends TestCase
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
            'nama' => 'Template Uji Tanggal ' . $this->counter,
            'slug' => $slug,
            'target_user' => $target,
            'fields' => $fields,
            'alur' => ['admin'],
            'aktif' => true,
        ]);
    }

    private function semesterTingkatId(): int
    {
        return (int) Semester::query()->where('kode', '3')->value('id');
    }

    private function payloadMagang(array $overrides = []): array
    {
        return array_merge([
            'tipe_magang' => 'reguler-ti',
            'perihal' => 'Permohonan Magang',
            'kepada_yth' => 'Human Capital',
            'nama_mitra' => 'PT Uji Teknologi',
            'alamat_mitra' => 'Jakarta Pusat',
            'tanggal_mulai' => '2026-01-05',
            'tanggal_selesai' => '2026-03-05',
            'mahasiswa' => [[
                'npm' => '22100001',
                'nama' => 'Mahasiswa Uji',
                'semester' => $this->semesterTingkatId(),
                'no_kontak' => '08123456789',
            ]],
        ], $overrides);
    }

    public function test_magang_menolak_tanggal_selesai_sebelum_tanggal_mulai(): void
    {
        $jenis = $this->makeJenis('surat-permohonan-magang', 'mahasiswa');

        $this->actingAs($this->makeUser('mahasiswa', ['prodi' => 'TI']))
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, $this->payloadMagang([
                'tanggal_mulai' => '2026-03-10',
                'tanggal_selesai' => '2026-03-09',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('tanggal_selesai')
            ->assertJsonPath(
                'errors.tanggal_selesai.0',
                DateRange::RANGE_MESSAGE
            );

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_magang_menerima_tanggal_selesai_sama_dengan_tanggal_mulai(): void
    {
        $jenis = $this->makeJenis('surat-permohonan-magang', 'mahasiswa');

        $this->actingAs($this->makeUser('mahasiswa', ['prodi' => 'TI']))
            ->post('/mahasiswa/surat/ajukan/' . $jenis->id, $this->payloadMagang([
                'tanggal_mulai' => '2026-03-10',
                'tanggal_selesai' => '2026-03-10',
            ]))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $surat = SuratPengajuan::query()->sole();

        $this->assertSame('2026-03-10', $surat->data['tanggal_mulai']);
        $this->assertSame('2026-03-10', $surat->data['tanggal_selesai']);
    }

    public function test_tanggal_harus_berformat_yyyy_mm_dd(): void
    {
        $jenis = $this->makeJenis('surat-permohonan-magang', 'mahasiswa');

        $this->actingAs($this->makeUser('mahasiswa', ['prodi' => 'TI']))
            ->postJson('/mahasiswa/surat/ajukan/' . $jenis->id, $this->payloadMagang([
                'tanggal_mulai' => '05/01/2026',
            ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors('tanggal_mulai')
            ->assertJsonPath(
                'errors.tanggal_mulai.0',
                'Tanggal Mulai harus berformat YYYY-MM-DD (contoh: 2026-11-18).'
            );

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_dinamis_menolak_rentang_tanggal_terbalik(): void
    {
        $jenis = $this->makeJenis(
            'template-uji-rentang-tanggal',
            'dosen',
            ['tanggal_mulai', 'tanggal_selesai']
        );

        $this->actingAs($this->makeUser('dosen'))
            ->postJson('/dosen/surat/ajukan/' . $jenis->id, [
                'tanggal_mulai' => '2026-05-10',
                'tanggal_selesai' => '2026-05-09',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('tanggal_selesai')
            ->assertJsonPath(
                'errors.tanggal_selesai.0',
                DateRange::RANGE_MESSAGE
            );

        $this->assertDatabaseCount('surat_pengajuan', 0);
    }

    public function test_form_magang_menampilkan_date_picker_aksesibel(): void
    {
        $jenis = $this->makeJenis('surat-permohonan-magang', 'mahasiswa');

        $this->actingAs($this->makeUser('mahasiswa', ['prodi' => 'TI']))
            ->get('/mahasiswa/surat/ajukan/' . $jenis->id)
            ->assertOk()
            ->assertSee('type="date"', false)
            ->assertSee('data-sakti-datepicker', false)
            ->assertSee('data-datepicker-trigger', false)
            ->assertSee('aria-haspopup="dialog"', false)
            ->assertSee('data-min-field="tanggal_mulai"', false)
            ->assertSee('Tidak boleh lebih awal daripada tanggal mulai.', false);
    }

    public function test_form_dinamis_merender_date_picker_untuk_field_tanggal(): void
    {
        $jenis = $this->makeJenis(
            'template-uji-date-picker',
            'mahasiswa',
            ['tanggal_mulai', 'tanggal_selesai']
        );

        $this->actingAs($this->makeUser('mahasiswa'))
            ->get('/mahasiswa/surat/ajukan/' . $jenis->id)
            ->assertOk()
            ->assertSee('name="tanggal_mulai"', false)
            ->assertSee('name="tanggal_selesai"', false)
            ->assertSee('data-sakti-datepicker', false)
            ->assertSee('data-datepicker-trigger', false)
            ->assertSee('data-min-field="tanggal_mulai"', false);
    }
}
