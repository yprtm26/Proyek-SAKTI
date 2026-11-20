<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\LdapAvailability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class ErrorAndLoggingSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Mahasiswa Penguji',
            'username' => '1402024010',
            'password' => 'password123',
            'role' => 'mahasiswa',
            'npm' => '1402024010',
            'prodi' => 'TI',
        ], $attributes));
    }

    /**
     * Test 1 (Wajib Rubrik Mode A):
     * Error sistem yang dipicu hanya menampilkan pesan umum dan ID kejadian,
     * serta TIDAK membocorkan stack trace, query database, atau path berkas.
     * URL pada log juga tidak boleh menyimpan token query string.
     */
    public function test_error_sistem_hanya_menampilkan_pesan_umum_dan_id_kejadian_tanpa_stack_trace(): void
    {
        Log::spy();
        Config::set('app.debug', false);

        $response = $this->get('/_test/trigger-error?token=TOKEN-RAHASIA-UJI');

        $response->assertStatus(500);
        $response->assertSee('Terjadi Kendala Sistem');
        $response->assertSee('ID Kejadian');

        $content = $response->getContent();
        $this->assertMatchesRegularExpression('/ERR-\d{8}-[A-Z0-9]{4}/', $content);

        // WAJIB: tidak boleh membocorkan detail teknis sensitif.
        $response->assertDontSee('RuntimeException');
        $response->assertDontSee('Simulasi galat internal basis data');
        $response->assertDontSee('routes/web.php');
        $response->assertDontSee('Stack trace');

        // Log boleh memuat ID kejadian, tetapi token di URL wajib hilang.
        Log::shouldHaveReceived('error')
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[SYSTEM_ERROR]')
                    && isset($context['incident_id'])
                    && ! str_contains($message, 'TOKEN-RAHASIA-UJI')
                    && ! str_contains((string) ($context['url'] ?? ''), 'TOKEN-RAHASIA-UJI');
            });
    }

    /**
     * Test 2 (Wajib Rubrik Mode A):
     * Login gagal tercatat di log keamanan dengan NPM dan password sama
     * sekali tidak tercatat. Pencarian user LDAP di-mock (tidak ditemukan),
     * lalu autentikasi jatuh ke akun database lokal.
     */
    public function test_login_gagal_tercatat_di_log_keamanan_dengan_npm_tanpa_password(): void
    {
        Log::spy();

        $secretPassword = 'SuperSecretPasswordYangTidakBolehMasukLog123!';
        $npmUji = '1402024010';

        $this->createUser();

        $response = $this->post('/login', [
            'username' => $npmUji,
            'password' => $secretPassword,
        ]);

        $response->assertSessionHasErrors(['username']);
        $this->assertGuest();

        Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context) use ($npmUji, $secretPassword) {
                $isSecurityAudit = str_contains($message, '[SECURITY_AUDIT]')
                    && str_contains($message, 'Login Gagal');

                $hasIdentifier = ($context['identifier'] ?? null) === $npmUji;
                $hasEvent = ($context['event'] ?? null) === 'AUTH_LOGIN_FAILED';
                $hasAction = ($context['action'] ?? null) === 'AUTH_LOGIN';
                $hasResult = ($context['result'] ?? null) === 'FAILED';

                $serialized = json_encode($context);
                $passwordNotExposed = ! str_contains($serialized, $secretPassword);

                return $isSecurityAudit && $hasIdentifier && $hasEvent
                    && $hasAction && $hasResult && $passwordNotExposed;
            });
    }

    /**
     * Test 3 (Gagal Aman / Fail-Closed):
     * Saat LDAP di-mock mati, kredensial yang benar sekalipun wajib ditolak,
     * pengguna tetap guest, dan log error memuat Incident ID.
     */
    public function test_kegagalan_layanan_ldap_menolak_login_secara_aman_fail_closed(): void
    {
        Log::spy();

        $user = $this->createUser([
            'password' => 'PasswordValid123!',
        ]);

        $ldap = Mockery::mock(LdapAvailability::class);
        $ldap->shouldReceive('ensureAvailable')
            ->once()
            ->andThrow(new \RuntimeException('Connection to LDAP server timeout.'));
        $this->app->instance(LdapAvailability::class, $ldap);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'PasswordValid123!',
        ]);

        // Login wajib DITOLAK meskipun password database benar.
        $response->assertSessionHasErrors(['username']);
        $this->assertGuest();
        $this->assertMatchesRegularExpression(
            '/ERR-\d{8}-[A-Z0-9]{4}/',
            session('errors')->first('username')
        );

        // Log teknis memuat Incident ID.
        Log::shouldHaveReceived('error')
            ->withArgs(function ($message, $context) {
                return str_contains($message, '[SYSTEM_ERROR]')
                    && isset($context['incident_id']);
            });

        // Log audit memuat NPM, tetapi tidak memuat password.
        Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context) use ($user) {
                return str_contains($message, '[SECURITY_AUDIT]')
                    && ($context['identifier'] ?? null) === $user->npm
                    && ($context['event'] ?? null) === 'AUTH_LOGIN_FAILED'
                    && ! str_contains(json_encode($context), 'PasswordValid123!');
            });
    }

    /**
     * Test 4 (Akses Ditolak 403):
     * Percobaan akses ke rute terproteksi tanpa izin tercatat di log audit.
     */
    public function test_akses_rute_ditolak_403_mencatat_log_audit_keamanan(): void
    {
        Log::spy();

        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get('/admin/dashboard');

        $response->assertStatus(403);
        $response->assertSee('Akses Ditolak');

        Log::shouldHaveReceived('warning')
            ->withArgs(function ($message, $context) use ($user) {
                return str_contains($message, '[SECURITY_AUDIT]')
                    && ($context['event'] ?? null) === 'ACCESS_DENIED_403'
                    && ($context['identifier'] ?? null) === $user->npm
                    && ($context['action'] ?? null) === 'ACCESS_ROUTE'
                    && ($context['result'] ?? null) === 'DENIED_403';
            });
    }

    /**
     * Test 5 (Login Berhasil):
     * Login sukses mencatat event AUTH_LOGIN_SUCCESS.
     */
    public function test_login_sukses_mencatat_log_audit_keamanan(): void
    {
        Log::spy();

        $user = $this->createUser([
            'password' => 'password-valid-123',
        ]);

        $response = $this->post('/login', [
            'username' => $user->username,
            'password' => 'password-valid-123',
        ]);

        $this->assertAuthenticatedAs($user);

        Log::shouldHaveReceived('info')
            ->withArgs(function ($message, $context) use ($user) {
                return str_contains($message, '[SECURITY_AUDIT]')
                    && ($context['event'] ?? null) === 'AUTH_LOGIN_SUCCESS'
                    && ($context['action'] ?? null) === 'AUTH_LOGIN'
                    && ($context['result'] ?? null) === 'SUCCESS'
                    && ($context['identifier'] ?? null) === $user->npm;
            });
    }

    /**
     * Test 6 (Perubahan Peran):
     * Perubahan role oleh administrator mencatat aktor, target, role lama,
     * role baru, IP, aksi, dan hasil.
     */
    public function test_perubahan_role_mencatat_log_audit_keamanan(): void
    {
        Log::spy();

        $admin = $this->createUser([
            'name' => 'Administrator Sistem',
            'username' => 'administrator-uji',
            'npm' => null,
            'role' => 'administrator',
            'prodi' => null,
        ]);

        $target = $this->createUser([
            'name' => 'Admin TU',
            'username' => 'admin-tu-uji',
            'npm' => null,
            'role' => 'admin',
            'prodi' => null,
        ]);

        $response = $this->actingAs($admin)
            ->post('/administrator/ubah-role/' . $target->id, [
                'role' => 'dekan',
            ]);

        $response->assertRedirect();
        $this->assertSame('dekan', $target->fresh()->role);

        Log::shouldHaveReceived('notice')
            ->withArgs(function ($message, $context) use ($admin, $target) {
                return str_contains($message, '[SECURITY_AUDIT]')
                    && ($context['event'] ?? null) === 'ROLE_CHANGED'
                    && ($context['admin_actor'] ?? null) === $admin->username
                    && ($context['target_user'] ?? null) === $target->username
                    && ($context['old_role'] ?? null) === 'admin'
                    && ($context['new_role'] ?? null) === 'dekan'
                    && ($context['action'] ?? null) === 'ROLE_CHANGE'
                    && ($context['result'] ?? null) === 'SUCCESS';
            });
    }
}
