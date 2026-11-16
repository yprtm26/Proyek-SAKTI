<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LdapRecord\Laravel\LdapUserAuthenticator;
use LdapRecord\Laravel\LdapUserRepository;
use LdapRecord\Models\OpenLDAP\User as LdapUser;
use Mockery;
use Tests\TestCase;

class SessionSecurityTest extends TestCase
{
    use RefreshDatabase;

    private const LDAP_PASSWORD = 'dummy-password-for-test';

    protected function setUp(): void
    {
        parent::setUp();

        // Gunakan driver database agar sesi benar-benar tersimpan di server
        // dan bisa diverifikasi/dihapus lintas request dalam pengujian.
        config([
            'session.driver' => 'database',
            'session.connection' => null,
            'session.lifetime' => 30,
            'session.absolute_lifetime' => 480,
            'session.secure' => false,
            'session.http_only' => true,
            'session.same_site' => 'lax',
        ]);

        $this->app['session']->forgetDrivers();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function makeUser(string $role = 'mahasiswa'): User
    {
        return User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => self::LDAP_PASSWORD,
            'role' => $role,
            'prodi' => $role === 'mahasiswa' ? 'TI' : null,
            'npm' => $role === 'mahasiswa' ? '000000000001' : null,
        ]);
    }

    /**
     * Ganti koneksi LDAP asli dengan mock: pencarian user dan bind password
     * tidak pernah menyentuh server LDAP.
     */
    private function fakeLdapFor(User $user): void
    {
        $ldapUser = new LdapUser([
            'entryuuid' => [(string) Str::uuid()],
            'cn' => [$user->username],
            'displayName' => [$user->name],
            'title' => [$user->role === 'mahasiswa' ? 'M' : 'D'],
            'description' => [$user->npm ?? $user->nidn],
            'mail' => ['testuser@example.test'],
            'mailAlternateAddress' => ['testuser.alias@example.test'],
            'telephoneNumber' => ['0800000000'],
        ]);
        $ldapUser->setDn('cn='.$user->username.',dc=example,dc=test');

        // Database user harus punya GUID yang sama dengan object LDAP.
        $user->forceFill(['guid' => $ldapUser->getConvertedGuid()])->save();

        $repository = Mockery::mock(LdapUserRepository::class);
        $repository->shouldReceive('findByCredentials')
            ->once()
            ->andReturn($ldapUser);

        $authenticator = Mockery::mock(LdapUserAuthenticator::class);
        $authenticator->shouldReceive('setEloquentModel')->andReturnSelf();
        $authenticator->shouldReceive('attempt')
            ->once()
            ->andReturn(true);

        // LdapRecord membuat repository/authenticator lewat app(...) dengan
        // parameter tambahan. Binding closure memastikan mock tetap dipakai
        // meskipun container melakukan contextual build.
        $this->app->bind(LdapUserRepository::class, fn () => $repository);
        $this->app->bind(LdapUserAuthenticator::class, fn () => $authenticator);
        $this->app['auth']->forgetGuards();
    }

    private function login(User $user): \Illuminate\Testing\TestResponse
    {
        return $this->post('/login', [
            'username' => $user->username,
            'password' => self::LDAP_PASSWORD,
        ]);
    }

    public function test_id_sesi_berbeda_sebelum_dan_sesudah_login_ldap(): void
    {
        $user = $this->makeUser();
        $this->fakeLdapFor($user);

        // Kunjungi halaman login sebagai tamu untuk mendapatkan ID sesi awal.
        $this->get('/login')->assertOk();
        $sessionIdBeforeLogin = session()->getId();

        // Bawa cookie sesi tamu tersebut saat login, seperti browser asli.
        $this->withCookie(config('session.cookie'), $sessionIdBeforeLogin);

        $this->login($user)->assertRedirect('/mahasiswa/dashboard');

        $sessionIdAfterLogin = session()->getId();

        $this->assertNotEmpty($sessionIdBeforeLogin);
        $this->assertNotEmpty($sessionIdAfterLogin);
        $this->assertNotSame(
            $sessionIdBeforeLogin,
            $sessionIdAfterLogin,
            'ID sesi harus diregenerasi setelah login LDAP berhasil (anti session fixation).'
        );

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_cookie_sesi_lama_ditolak_setelah_logout(): void
    {
        $user = $this->makeUser();
        $this->fakeLdapFor($user);

        $this->login($user)->assertRedirect('/mahasiswa/dashboard');

        $oldSessionId = session()->getId();
        $this->assertAuthenticated();
        $this->assertDatabaseHas('sessions', ['id' => $oldSessionId]);

        // Simulasikan browser mengirim cookie sesi yang valid saat logout.
        $this->withCookie(config('session.cookie'), $oldSessionId);

        $logout = $this->post('/logout');
        $logout->assertRedirect('/login');

        // Cookie sesi harus dikedaluwarsakan di browser.
        $expiredCookie = collect($logout->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('session.cookie'));

        $this->assertNotNull($expiredCookie, 'Response logout harus menghapus cookie sesi.');
        $this->assertTrue($expiredCookie->isCleared());

        // Sesi lama harus sudah dihancurkan di server.
        $this->assertDatabaseMissing('sessions', ['id' => $oldSessionId]);

        // Paksa store sesi dan guard membaca ulang dari database, lalu kirim
        // cookie lama seolah-olah penyerang memegang cookie sebelum logout.
        $this->app['session']->forgetDrivers();
        $this->app['auth']->forgetGuards();

        $this->withCookie(config('session.cookie'), $oldSessionId)
            ->get('/mahasiswa/dashboard')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_sesi_hanya_menyimpan_identitas_minimal_tanpa_password(): void
    {
        $user = $this->makeUser();
        $this->fakeLdapFor($user);

        $this->login($user)->assertRedirect('/mahasiswa/dashboard');

        $identity = session('auth_identity');

        $this->assertSame($user->id, $identity['user_id']);
        $this->assertSame($user->name, $identity['nama']);
        $this->assertSame('mahasiswa', $identity['role']);
        $this->assertSame($user->npm, $identity['npm']);

        $sessionPayload = json_encode(session()->all());

        $this->assertArrayNotHasKey('password', session()->all());
        $this->assertStringNotContainsString(self::LDAP_PASSWORD, $sessionPayload);
        $this->assertStringNotContainsString($user->fresh()->password, $sessionPayload);
    }

    public function test_idle_timeout_30_menit_memaksa_logout(): void
    {
        $user = $this->makeUser();
        $this->fakeLdapFor($user);

        $this->login($user)->assertRedirect('/mahasiswa/dashboard');

        Carbon::setTestNow(now()->addMinutes(31));

        $this->get('/mahasiswa/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_absolute_timeout_8_jam_memaksa_logout(): void
    {
        $user = $this->makeUser();
        $this->fakeLdapFor($user);

        $this->login($user)->assertRedirect('/mahasiswa/dashboard');

        Carbon::setTestNow(now()->addHours(8)->addMinute());

        $this->get('/mahasiswa/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHas('status');

        $this->assertGuest();
    }

    public function test_cookie_sesi_memakai_httponly_samesite_lax_dan_secure_dari_env(): void
    {
        $user = $this->makeUser();
        $this->fakeLdapFor($user);

        $response = $this->login($user);
        $response->assertRedirect('/mahasiswa/dashboard');

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($item) => $item->getName() === config('session.cookie'));

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertFalse($cookie->isSecure()); // SESSION_SECURE_COOKIE=false di lokal
    }
}
