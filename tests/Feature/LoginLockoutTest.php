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

    public function test_gagal_1x_sampai_4x_counter_naik_tapi_belum_dikunci(): void
    {
        $user = $this->createTestUser();

        $this->failLogin(4);

        $user->refresh();
        $this->assertEquals(4, $user->failed_login_attempts);
        $this->assertNull($user->locked_until);
    }

    public function test_gagal_5x_kunci_1_menit(): void
    {
        $user = $this->createTestUser();

        $response = $this->failLogin(5);

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $this->assertTrue($user->locked_until->isFuture());
        $response->assertSessionHas('lockout_seconds', 60);
    }

    public function test_percobaan_ke_6_ditolak_selama_masa_kunci(): void
    {
        $user = $this->createTestUser();

        $this->failLogin(5);
        $user->refresh();
        $response = $this->post('/login', ['username' => 'testuser', 'password' => 'salah']);

        $user->refresh();
        $this->assertEquals(5, $user->failed_login_attempts);
        $this->assertNotNull($user->locked_until);
        $response->assertSessionHas('lockout_seconds');
    }

    public function test_password_benar_ditolak_saat_terkunci(): void
    {
        $user = $this->createTestUser();
        $user->update([
            'failed_login_attempts' => 5,
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
            'failed_login_attempts' => 5,
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

        $response = $this->failLogin(3);

        $error = $response->getSession()->get('errors')->first('username');
        $this->assertStringContainsString('2 kali', $error); // 5-3 = sisa 2
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
