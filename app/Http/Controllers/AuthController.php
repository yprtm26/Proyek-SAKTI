<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LdapAvailability;
use App\Services\SecurityAuditLogger;
use App\Support\SensitiveDataSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    const LOCKOUT_THRESHOLD = 5;

    const LOCKOUT_MINUTES = 1;

    public function __construct(private LdapAvailability $ldap)
    {
    }
    // // tampilkan form login
    // public function showLogin()
    // {
    //     return view('auth.login');
    // }

    // // proses login
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required',
    //         'password' => 'required',
    //     ]);

    //     $credentials = $request->only('username', 'password');

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();

    //         $user = Auth::user();

    //         switch ($user->role) {
    //             case 'admin':
    //                 return redirect('/admin/dashboard');

    //             case 'mahasiswa':
    //                 return redirect('/mahasiswa/dashboard');

    //             case 'dosen':
    //                 return redirect('/dosen/dashboard');

    //             case 'kaprodi':
    //                 return redirect('/kaprodi/dashboard');

    //             case 'dekan':
    //                 return redirect('/dekan/dashboard');

    //             default:
    //                 Auth::logout();
    //                 return redirect('/login');
    //         }
    //     }

    //     return back()->withErrors([
    //         'username' => 'Username atau password salah',
    //     ]);
    // }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        // Cek apakah akun sedang terkunci
        if ($user && $user->locked_until) {
            if ($user->locked_until->isFuture()) {
                $secondsRemaining = (int) Carbon::now()->diffInSeconds($user->locked_until, false);
                if ($secondsRemaining > 0) {
                    $minutes = ceil($secondsRemaining / 60);

                    SecurityAuditLogger::logLoginFailed(
                        $request->username,
                        $request,
                        'Akun terkunci sementara'
                    );

                    return back()->withInput($request->only('username'))->withErrors([
                        'username' => "Akun Anda dikunci sementara karena {$user->failed_login_attempts} kali salah password. Silakan coba lagi dalam {$minutes} menit.",
                    ])->with('lockout_seconds', $secondsRemaining);
                }
            }

            // Masa kunci selesai, hapus lock tapi JANGAN reset counter
            $user->update([
                'locked_until' => null,
            ]);
        }

        $credentials = [
            'cn' => $request->username,
            'password' => $request->password,
            'fallback' => [
                'username' => $request->username,
                'password' => $request->password,
            ],
        ];

        // Gagal Aman (Fail-Closed): cek konektivitas LDAP lebih dahulu.
        // Tanpa preflight, LdapRecord dapat menelan exception koneksi dan
        // fallback ke password database, sehingga login bisa lolos saat LDAP mati.
        try {
            $this->ldap->ensureAvailable();
            $loginSuccess = Auth::attempt($credentials);
        } catch (\Throwable $e) {
            $incidentId = 'ERR-' . date('Ymd') . '-' . strtoupper(Str::random(4));
            Log::error(
                '[SYSTEM_ERROR] Gangguan Layanan Autentikasi/LDAP: '.SensitiveDataSanitizer::message($e->getMessage()),
                [
                    'incident_id' => $incidentId,
                    'identifier'  => SensitiveDataSanitizer::identifier($request->username),
                    'ip_address'  => $request->ip(),
                    'exception'   => get_class($e),
                    'file'        => basename($e->getFile()) . ':' . $e->getLine(),
                ]
            );

            SecurityAuditLogger::logLoginFailed($request->username, $request, "Layanan Autentikasi Gagal / Fail-Closed ({$incidentId})");

            return back()->withInput($request->only('username'))->withErrors([
                'username' => "Layanan autentikasi kampus sedang mengalami gangguan teknis. Permintaan ditolak demi keamanan (ID: {$incidentId}).",
            ]);
        }

        if ($loginSuccess) {
            // Cegah Session Fixation: ID sesi lama dibuang dan diganti ID baru
            // tepat setelah verifikasi LDAP berhasil.
            $request->session()->regenerate();

            /** @var User $authenticatedUser */
            $authenticatedUser = Auth::user();
            if ($authenticatedUser) {
                $authenticatedUser->update([
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                ]);

                $this->storeMinimalSessionIdentity($request, $authenticatedUser);
                SecurityAuditLogger::logLoginSuccess($authenticatedUser, $request);
            }

            switch ($authenticatedUser->role) {
                case 'admin':
                    return redirect('/admin/dashboard');

                case 'administrator':
                    return redirect('/administrator/dashboard');

                case 'mahasiswa':
                    return redirect('/mahasiswa/dashboard');

                case 'dosen':
                    return redirect('/dosen/dashboard');

                case 'kaprodi':
                    return redirect('/kaprodi/dashboard');

                case 'dekan':
                    return redirect('/dekan/dashboard');

                default:
                    return $this->logout($request);
            }
        }

        // Login gagal: catat audit keamanan (tanpa mencatat password)
        SecurityAuditLogger::logLoginFailed($request->username, $request, 'Kredensial tidak cocok');

        // Catat percobaan jika user ada di database
        if ($user) {
            $attempts = $user->failed_login_attempts + 1;

            if ($attempts >= self::LOCKOUT_THRESHOLD) {
                $lockMinutes = self::LOCKOUT_MINUTES;
                $lockedUntil = Carbon::now()->addMinutes($lockMinutes);

                $user->update([
                    'failed_login_attempts' => $attempts,
                    'locked_until' => $lockedUntil,
                ]);

                $secondsRemaining = $lockMinutes * 60;

                return back()->withInput($request->only('username'))->withErrors([
                    'username' => "Akun Anda dikunci sementara karena {$attempts} kali salah password. Silakan coba lagi dalam {$lockMinutes} menit.",
                ])->with('lockout_seconds', $secondsRemaining);
            }

            $user->update(['failed_login_attempts' => $attempts]);
            $sisaMenujuKunci = self::LOCKOUT_THRESHOLD - $attempts;

            return back()->withInput($request->only('username'))->withErrors([
                'username' => "Username atau password salah. Sisa percobaan sebelum akun dikunci: {$sisaMenujuKunci} kali.",
            ]);
        }

        return back()->withInput($request->only('username'))->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

    /**
     * Simpan hanya identitas minimal yang dibutuhkan aplikasi.
     *
     * Password (plaintext/hash), kredensial bind LDAP, dan atribut sensitif
     * LDAP tidak boleh masuk ke sesi. Laravel sendiri hanya menyimpan ID user
     * pada guard; data di bawah ini adalah identitas tampilan yang aman.
     */
    private function storeMinimalSessionIdentity(Request $request, User $user): void
    {
        $session = $request->session();

        $session->put([
            'auth_identity' => [
                'user_id' => $user->getAuthIdentifier(),
                'nama' => $user->name,
                'role' => $user->role,
                'npm' => $user->npm,
                'nidn' => $user->nidn,
            ],
            // Timestamp untuk idle timeout (diperbarui middleware) dan
            // absolute timeout (tidak pernah diperpanjang).
            'auth_login_at' => now()->getTimestamp(),
            'auth_last_activity' => now()->getTimestamp(),
        ]);

        // Defense in depth: buang kunci sensitif apabila pernah tersimpan
        // oleh kode lama atau oleh proses lain.
        $session->forget([
            'password',
            'password_confirmation',
            'credentials',
            'ldap_password',
            'ldap_credentials',
            'bind_password',
            'userpassword',
        ]);
    }

    /**
     * Logout aman: hapus autentikasi, hancurkan sesi di server, ganti token
     * CSRF, lalu kedaluwarsakan cookie sesi di browser.
     */
    public function logout(Request $request)
    {
        $currentUser = Auth::user();
        if ($currentUser) {
            SecurityAuditLogger::logLogout($currentUser, $request);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Queue (bukan withCookie) agar cookie kedaluwarsa ditambahkan setelah
        // StartSession menulis cookie sesi baru pada response.
        Cookie::queue(
            Cookie::forget(
                config('session.cookie'),
                config('session.path'),
                config('session.domain')
            )
        );

        return redirect('/login');
    }
}
