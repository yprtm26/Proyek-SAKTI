<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
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

            $user = Auth::user();

            switch ($user->role) {
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

        // SEMENTARA SEBELUM LDAP
        return back()->withErrors([
            'username' => 'Username atau password salah',
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
