<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LoginLockoutTest extends TestCase
{
    use RefreshDatabase;

    private function createTestUser(): User
    {
        return User::create([
            'name'     => 'Test User',
            'username' => 'testuser',
            'password' => 'password123',
            'role'     => 'mahasiswa',
        ]);
    }

    private function failLogin(int $times = 1): \Illuminate\Testing\TestResponse
    {
        $response = null;
        for ($i = 0; $i < $times; $i++) {
            $response = $this->post('/login', [
                'username' => 'testuser',
                'password' => 'salah',
            ]);
        }
        return $response;
    }

    public function test_gagal_1x_2x_counter_naik_tapi_belum_kunci(): void
    {
        $user = $this->createTestUser();

        $this->failLogin(2);

        $user->refresh();
        $this->assertEquals(2, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_gagal_3x_kunci_1_menit(): void
    {
        $user = $this->createTestUser();

        $response = $this->failLogin(3);

        $user->refresh();
        $this->assertEquals(3, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());
        $response->assertSessionHas('lockout_seconds', 60);
    }

    public function test_gagal_6x_kunci_2_menit(): void
    {
        $user = $this->createTestUser();

        // Gagal 3x → kunci 1 menit
        $this->failLogin(3);

        // Lewati kunci pertama
        $user->refresh();
        $user->update(['locked_until' => Carbon::now()->subSecond()]);

        // Gagal 3x lagi (total 6x) → kunci 2 menit
        $response = $this->failLogin(3);

        $user->refresh();
        $this->assertEquals(6, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $response->assertSessionHas('lockout_seconds', 120);
    }

    public function test_gagal_9x_kunci_3_menit(): void
    {
        $user = $this->createTestUser();
        // Set counter langsung ke 8 (sudah lewati 2 kunci sebelumnya)
        $user->update(['failed_login_attempts' => 8, 'locked_until' => null]);

        $response = $this->failLogin(1); // jadi total 9

        $user->refresh();
        $this->assertEquals(9, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $response->assertSessionHas('lockout_seconds', 180);
    }

    public function test_password_benar_ditolak_saat_terkunci(): void
    {
        $user = $this->createTestUser();
        $user->update([
            'failed_login_attempts' => 3,
            'locked_until' => Carbon::now()->addMinutes(1),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('username');
        $response->assertSessionHas('lockout_seconds');
        $this->assertGuest();
    }

    public function test_login_berhasil_setelah_kunci_habis_dan_counter_reset(): void
    {
        $user = $this->createTestUser();
        $user->update([
            'failed_login_attempts' => 6,
            'locked_until' => Carbon::now()->subSecond(),
        ]);

        $response = $this->post('/login', [
            'username' => 'testuser',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/mahasiswa/dashboard');
        $this->assertAuthenticated();

        $user->refresh();
        $this->assertEquals(0, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_sisa_percobaan_tampil_saat_belum_kunci(): void
    {
        $user = $this->createTestUser();

        $response = $this->failLogin(1);

        $error = $response->getSession()->get('errors')->first('username');
        $this->assertStringContainsString('2 kali', $error); // 3-1 = sisa 2
    }

    public function test_username_tidak_dikenal_pesan_generik(): void
    {
        $response = $this->post('/login', [
            'username' => 'tidakada',
            'password' => 'apapun',
        ]);

        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString(
            'salah',
            $response->getSession()->get('errors')->first('username')
        );
    }
}
