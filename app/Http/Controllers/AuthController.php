<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use App\Models\User;

class AuthController extends Controller
{
    const LOCKOUT_THRESHOLD = 5;
    const LOCKOUT_MINUTES = 1;
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            /** @var User $authenticatedUser */
            $authenticatedUser = Auth::user();
            if ($authenticatedUser) {
                $authenticatedUser->update([
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                ]);
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
                    Auth::logout();
                    return redirect('/login');
            }
        }

        // Login gagal: catat percobaan jika user ada di database
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

    // logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
