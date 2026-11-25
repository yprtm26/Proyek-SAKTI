<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class ConfigurationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(array $attributes = []): User
    {
        return User::create(array_merge([
            'name' => 'Mahasiswa Uji',
            'username' => 'mahasiswa.uji',
            'password' => 'password123',
            'role' => 'mahasiswa',
            'prodi' => 'TI',
            'npm' => '1402024099',
        ], $attributes));
    }

    public function test_gitignore_mengecualikan_env_cadangan_dan_log_tanpa_mengabaikan_env_example(): void
    {
        $gitignore = file_get_contents(base_path('.gitignore'));

        $this->assertNotFalse($gitignore);
        $lines = array_map('trim', preg_split('/\R/', $gitignore) ?: []);

        $this->assertContains('.env', $lines);
        $this->assertContains('.env.backup', $lines);
        $this->assertContains('.env.production', $lines);
        $this->assertContains('*.log', $lines);
        $this->assertNotContains('.env.example', $lines);
    }

    public function test_env_example_mencantumkan_variabel_secret_tanpa_nilai(): void
    {
        $envExample = file_get_contents(base_path('.env.example'));

        $this->assertNotFalse($envExample);

        foreach (['APP_KEY=', 'DB_PASSWORD=', 'LDAP_HOST=', 'LDAP_BASE_DN=', 'LDAP_USERNAME=', 'LDAP_PASSWORD='] as $expectedKey) {
            $this->assertMatchesRegularExpression(
                '/^'.preg_quote($expectedKey, '/').'$/m',
                $envExample,
                "Variabel {$expectedKey} harus ada di .env.example tanpa nilai."
            );
        }

        // Pastikan konfigurasi LDAP di kode tidak menanamkan host/base_dn/kredensial kampus.
        $ldapConfig = file_get_contents(config_path('ldap.php'));
        $this->assertNotFalse($ldapConfig);
        $this->assertStringNotContainsString('pdc.yarsi.ac.id', $ldapConfig);
        $this->assertStringNotContainsString('dc=yarsi,dc=ac,dc=id', $ldapConfig);
    }

    public function test_akses_file_sensitif_ditolak_dengan_403_atau_404(): void
    {
        $sensitivePaths = [
            '/.env',
            '/%2eenv',
            '/.env.backup',
            '/.env.production',
            '/.git/config',
            '/.git/HEAD',
            '/storage/logs/laravel.log',
            '/laravel.log',
            '/backup.sql',
            '/database.sqlite',
            '/backup.bak',
            '/backup.zip',
            '/composer.json',
            '/artisan',
        ];

        foreach ($sensitivePaths as $path) {
            $response = $this->get($path);
            $this->assertContains(
                $response->getStatusCode(),
                [403, 404],
                "Path sensitif {$path} tidak boleh dapat diakses (status: {$response->getStatusCode()})."
            );
            $this->assertStringNotContainsString('APP_KEY=', $response->getContent());
            $this->assertStringNotContainsString('[core]', $response->getContent());
        }
    }

    public function test_respons_aplikasi_memiliki_header_keamanan_wajib(): void
    {
        $responses = [
            $this->get('/login'),
            $this->get('/halaman-yang-tidak-ada-untuk-uji-404'),
            $this->get('/.env'),
        ];

        foreach ($responses as $response) {
            $response->assertHeader('X-Content-Type-Options', 'nosniff');
            $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
            $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');

            $csp = (string) $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        }
    }

    public function test_cors_membatasi_origin_dan_menolak_origin_asing(): void
    {
        Config::set('cors.allowed_origins', ['http://localhost']);

        $user = $this->createUser();

        // Origin yang diizinkan memperoleh header Access-Control-Allow-Origin yang sesuai.
        $allowedResponse = $this->actingAs($user)
            ->withHeaders(['Origin' => 'http://localhost'])
            ->getJson('/api/profil-pengusul');

        $allowedResponse->assertOk();
        $allowedResponse->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
        $allowedResponse->assertHeader('X-Content-Type-Options', 'nosniff');

        // Origin asing tidak boleh mendapat izin untuk origin-nya sendiri maupun wildcard '*'.
        $disallowedResponse = $this->actingAs($user)
            ->withHeaders(['Origin' => 'https://evil.example'])
            ->getJson('/api/profil-pengusul');

        $disallowedResponse->assertOk();
        $this->assertNotSame('*', $disallowedResponse->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('https://evil.example', $disallowedResponse->headers->get('Access-Control-Allow-Origin'));

        // Saat daftar origin berisi beberapa origin, origin asing tidak mendapat header CORS sama sekali.
        Config::set('cors.allowed_origins', ['http://localhost', 'http://127.0.0.1:8000']);
        $multiOriginDisallowed = $this->actingAs($user)
            ->withHeaders(['Origin' => 'https://evil.example'])
            ->getJson('/api/profil-pengusul');

        $multiOriginDisallowed->assertOk();
        $this->assertNull($multiOriginDisallowed->headers->get('Access-Control-Allow-Origin'));

        // Preflight OPTIONS dari origin asing juga tidak boleh mengembalikan header izinkan.
        $preflightDisallowed = $this->withHeaders([
            'Origin' => 'https://evil.example',
            'Access-Control-Request-Method' => 'GET',
        ])->options('/api/profil-pengusul');

        $this->assertNotSame('*', $preflightDisallowed->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('https://evil.example', $preflightDisallowed->headers->get('Access-Control-Allow-Origin'));
        $this->assertNull($preflightDisallowed->headers->get('Access-Control-Allow-Origin'));
    }
}
