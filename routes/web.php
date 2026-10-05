<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Surat;
use App\Models\Disposisi;
use App\Models\User;
use Carbon\Carbon;

use App\Http\Controllers\AuthController;
use App\Models\JenisSurat;
use App\Models\SuratPengajuan;
use App\Models\SumberDaya;
use App\Exports\SuratPengajuanExport;
use App\Exports\DisposisiExport;
use Maatwebsite\Excel\Facades\Excel;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| VALIDASI SURAT (PUBLIK - QR CODE)
|--------------------------------------------------------------------------
*/
Route::get('/surat/validasi/{token}', function ($token) {

    $surat = \App\Models\SuratPengajuan::with(['user','jenisSurat'])
        ->where('qr_token', $token)
        ->where('status', 'disetujui_final')
        ->first();
    
    if (! $surat) {
        return response()->view('surat.tidak-valid', [], 404);
    }

    return view('surat.validasi', compact('surat'));
});

/*
|--------------------------------------------------------------------------
| SEMUA ROUTE WAJIB LOGIN
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

// MANUAL BOOK
Route::get('/sumberdaya', function () {

        $user = Auth::user();

        $data = \App\Models\SumberDaya::where(function($q) use ($user) {
            $q->whereNull('role') // semua boleh
            ->orWhere('role', $user->role);
        })->latest()->get();

        return view('sumberdaya.index', compact('data'));

    })->name('sumberdaya.index');

Route::get('/sumberdaya/download/{id}', function ($id) {
    $file = SumberDaya::findOrFail($id);

    return response()->download(
        storage_path('app/public/' . $file->file_path)
    );
});

// DISPOSISI
Route::get('/disposisi/{id}', function($id) {
        $disposisi = Disposisi::with(['surat','fromUser','toUser'])->findOrFail($id);

        if ($disposisi->to_user_id !== Auth::id()) abort(403);

        return view('disposisi.detail', compact('disposisi'));
    });

    Route::post('/disposisi/{id}/terima', function(Request $request, $id) {
        $disposisi = Disposisi::findOrFail($id);

        if ($disposisi->to_user_id !== Auth::id() || $disposisi->status !== 'pending') abort(403);

        $disposisi->update([
            'status' => 'diterima',
            'komentar' => $request->komentar ?? $disposisi->komentar
        ]);

        // Opsional: update status surat jika ini step terakhir
        // $disposisi->surat->update(['status' => 'disetujui_final']);

        return redirect('/disposisi/'.$id)->with('success','Disposisi diterima');
    });

    Route::post('/disposisi/{id}/tolak', function(Request $request, $id) {
        $disposisi = Disposisi::findOrFail($id);

        if ($disposisi->to_user_id !== Auth::id() || $disposisi->status !== 'pending') abort(403);

        $disposisi->update([
            'status' => 'ditolak',
            'komentar' => $request->komentar ?? $disposisi->komentar
        ]);

        return redirect('/disposisi/'.$id)->with('success','Disposisi ditolak');
    });

//----------------DOSEN---------------------
Route::get('/dosen/surat/{id}/pdf', function ($id) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                ->where('id', $id)
                ->where('status', 'disetujui_final')
                ->firstOrFail();

            if (
                Auth::user()->role === 'dosen' &&
                $surat->user_id !== Auth::id()
            ) {
                abort(403);
            }

            $view = 'pdf.dosen.' . $surat->jenisSurat->slug;

            if (!view()->exists($view)) {
                abort(404, 'Template PDF belum tersedia');
            }

            // QR URL (AMAN TANPA EXTENSION PHP)
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='
                . urlencode(url('/surat/validasi/'.$surat->qr_token));

            $pdf = Pdf::loadView($view, compact('surat','qrUrl'))
                ->setPaper('A4', 'portrait');

            return $pdf->download(
                str_replace(' ', '-', $surat->jenisSurat->nama)
                . '-' . $surat->user->nidn . '.pdf'
            );
        });

//--------------MAHASISWA-------------
Route::get('/mahasiswa/surat/{id}/pdf', function ($id) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                ->where('id', $id)
                ->where('status', 'disetujui_final')
                ->firstOrFail();
            
            if (
                Auth::user()->role === 'mahasiswa' &&
                $surat->user_id !== Auth::id()
            ) {
                abort(403);
            }

            // KHUSUS MAGANG
            if ($surat->jenisSurat->slug === 'surat-permohonan-magang') {
                $view = 'pdf.magang.' . $surat->data['tipe_magang'];
            } else {
                $view = 'pdf.' . $surat->jenisSurat->slug; // selain magang
            }

            if (!view()->exists($view)) {
                abort(404, 'Template PDF belum tersedia');
            }

            // QR URL
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='
                . urlencode(url('/surat/validasi/'.$surat->qr_token));

            $pdf = Pdf::loadView($view, compact('surat','qrUrl'))
                ->setPaper('A4', 'portrait');

            return $pdf->download(
                str_replace(' ', '-', $surat->jenisSurat->nama)
                . '-' . $surat->user->npm . '.pdf'
            );
        });

/*
|--------------------------------------------------------------------------
| NOTIFIKASI — SEMUA ROLE
|--------------------------------------------------------------------------
*/
Route::get('/notifikasi', function () {

    $notifications = \App\Models\Notification::where('user_id', Auth::id())
        ->orderBy('created_at', 'desc')
        ->paginate(15); // 

    return view('notifikasi.index', compact('notifications'));
});

/*
|--------------------------------------------------------------------------
| NOTIFIKASI — TANDAI SEMUA DIBACA
|--------------------------------------------------------------------------
*/
Route::post('/notifikasi/read-all', function () {

    \App\Models\Notification::where('user_id', Auth::id())
        ->where('is_read', false)
        ->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

    return back()->with('success', 'Semua notifikasi telah dibaca');
});


        /*
|--------------------------------------------------------------------------
| DOSEN
|-------------------------------------------------------------------------- --BARU--
*/
    Route::middleware('role:dosen')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | DASHBOARD DOSEN
        |--------------------------------------------------------------------------
        */
        Route::get('/dosen/dashboard', function (Request $request) {

            $search = $request->query('search');

            $jenisSurat = JenisSurat::where('aktif', true)
                ->where('target_user', 'dosen') 
                ->when($search, function ($q, $search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                })
                ->orderBy('nama')
                ->get();

            return view('dosen.dashboard', compact('jenisSurat', 'search'));
        });

        /*
        |--------------------------------------------------------------------------
        | FORM AJUKAN SURAT DOSEN
        |--------------------------------------------------------------------------
        */
        Route::get('/dosen/surat/ajukan/{id}', function ($id) {

            $jenisSurat = JenisSurat::findOrFail($id);

            // otomatis: mahasiswa/surat/form/{slug}.blade.php
            $view = 'dosen.surat.form.' . $jenisSurat->slug;

            if (!view()->exists($view)) {
                abort(404, 'Form surat belum tersedia');
            }

            return view($view, compact('jenisSurat'));
        });

        /*
        |--------------------------------------------------------------------------
        | SIMPAN PENGAJUAN SURAT DOSEN
        |--------------------------------------------------------------------------
        */
        Route::post('/dosen/surat/ajukan/{id}', function (Request $request, $id) {

            $jenisSurat = JenisSurat::findOrFail($id);

            // VALIDASI DINAMIS
            $rules = [];
            foreach ($jenisSurat->fields as $field) {
                $rules[$field] = 'required';
            }

            $validated = $request->validate($rules);

            $surat = SuratPengajuan::create([
                'user_id' => Auth::id(),
                'jenis_surat_id' => $jenisSurat->id,
                'data' => $validated,
                'status' => 'menunggu',
            ]);

            // NOTIFIKASI ADMIN
            foreach (\App\Models\User::where('role', 'admin')->get() as $admin) {
                pushNotif(
                    $admin->id,
                    'Surat Masuk',
                    'Pengajuan "' . $jenisSurat->nama . '" dari ' . Auth::user()->name,
                    '/admin/surat/' . $surat->id
                );
            }

            return redirect('/dosen/surat/' . $surat->id);
        });


        /*
        |--------------------------------------------------------------------------
        | DETAIL SURAT DOSEN
        |--------------------------------------------------------------------------
        */
        Route::get('/dosen/surat/{id}', function ($id) {

            $surat = SuratPengajuan::with('jenisSurat')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            // Tandai notifikasi dibaca
            Auth::user()->unreadNotifications
                ->where('data.surat_id', $surat->id)
                ->each(fn ($notif) => $notif->markAsRead());

            // TAMBAHKAN INI
            $anggota = json_decode($surat->anggota, true) ?? [];

            // DEFAULT
            $qrUrl = null;

            // HANYA JIKA FINAL
            if ($surat->status === 'disetujui_final') {
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='
                    . urlencode(url('/surat/validasi/' . $surat->qr_token));
            }

            $surat = SuratPengajuan::with(['jenisSurat','user'])
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

            return view('dosen.surat.detail', compact('surat','anggota'));
        });


        /*
        |--------------------------------------------------------------------------
        | BATALKAN SURAT (SELAMA MENUNGGU)
        |--------------------------------------------------------------------------
        */
        Route::post('/dosen/surat/{id}/batal', function ($id) {

            $surat = SuratPengajuan::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'menunggu')
                ->firstOrFail();

            $surat->delete();

            return redirect('/dosen/dashboard')
                ->with('success', 'Pengajuan surat dibatalkan');
        });

        /*
        |--------------------------------------------------------------------------
        | LOG / RIWAYAT SURAT DOSEN
        |--------------------------------------------------------------------------
        */
        Route::get('/dosen/surat', function (Request $request) {

            $query = SuratPengajuan::with('jenisSurat')
                ->where('user_id', Auth::id());

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $suratList = $query
                ->orderBy('created_at', 'desc')
                ->get();
            return view('dosen.surat.log', compact('suratList'));
        });

    });



    /*
    |--------------------------------------------------------------------------
    | MAHASISWA
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:mahasiswa')->group(function () {

        /*
        |----------------------------------------------------------------------
        | DASHBOARD MAHASISWA
        |----------------------------------------------------------------------
        */
        Route::get('/mahasiswa/dashboard', function (Request $request) {

            $search = $request->query('search');

            $jenisSurat = JenisSurat::where('aktif', true)
                ->where('target_user', 'mahasiswa')
                ->when($search, function ($query, $search) {
                    $query->where('nama', 'like', '%' . $search . '%');
                })
                ->orderBy('nama')
                ->get();

            return view('mahasiswa.dashboard', compact('jenisSurat', 'search'));
        });

        // FORM SURAT (DINAMIS BERDASARKAN JENIS)

        Route::get('/mahasiswa/surat/ajukan/{id}', function ($id) {

            $jenisSurat = JenisSurat::findOrFail($id);

            // otomatis: mahasiswa/surat/form/{slug}.blade.php
            $view = 'mahasiswa.surat.form.' . $jenisSurat->slug;

            if (!view()->exists($view)) {
                abort(404, 'Form surat belum tersedia');
            }

            return view($view, compact('jenisSurat'));
        });

        //SIMPAN PENGAJUAN SURAT
        Route::post('/mahasiswa/surat/ajukan/{id}', function (Request $request, $id) {

            $jenisSurat = JenisSurat::findOrFail($id);

            // SURAT REMEDIAL BAHASA INGGRIS
            if ($jenisSurat->slug === 'surat-pengantar-remedial-bahasa-inggris') {

                $request->validate([
                    'mahasiswa' => 'required|array|min:1',
                    'mahasiswa.*.nama' => 'required|string',
                    'mahasiswa.*.npm' => 'required|string',
                    'mahasiswa.*.mata_kuliah' => 'required|array|min:1',
                    'mahasiswa.*.mata_kuliah.*' => 'string',
                ]);

                $data = [
                    'mahasiswa' => collect($request->mahasiswa)->map(function ($mhs) {
                        return [
                            'nama' => $mhs['nama'],
                            'npm'  => $mhs['npm'],
                            'mata_kuliah' => array_values($mhs['mata_kuliah']),
                        ];
                    })->toArray(),
                ];

            }
            // Surat Permohonan Aktif
            elseif ($jenisSurat->slug === 'surat-permohonan-aktif-akademik') {

                $validated = $request->validate([
                    'semester' => 'required|in:ganjil,genap',
                    'tahun_akademik' => 'required|string',
                    'batas_pembayaran' => 'required|date',
                    'nominal_pembayaran' => 'required|numeric|min:1000',
                    'lampiran' => 'required|file|max:2048|mimes:pdf,jpg,jpeg,png',
                ]);

                $data = [
                    'semester' => $validated['semester'],
                    'tahun_akademik' => $validated['tahun_akademik'],
                    'batas_pembayaran' => $validated['batas_pembayaran'],
                    'nominal_pembayaran' => $validated['nominal_pembayaran'],
                ];
            }

            // Surat Permohonan Magang
            elseif ($jenisSurat->slug === 'surat-permohonan-magang') {

                $validated = $request->validate([
                    'tipe_magang' => 'required|in:reguler-ti,reguler-pdsi,mandiri',
                    'perihal' => 'required|string',
                    'kepada_yth' => 'required|string',
                    'nama_mitra' => 'required|string',
                    'alamat_mitra' => 'required|string',
                    'tanggal_mulai' => 'required|date',
                    'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
                    'mahasiswa' => 'required|array|min:1',
                    'mahasiswa.*.npm' => 'required|string',
                    'mahasiswa.*.nama' => 'required|string',
                    'mahasiswa.*.semester' => 'required|string',
                    'mahasiswa.*.no_kontak' => 'required|string',
                ]);

                // VALIDASI PRODI
                if (
                    $validated['tipe_magang'] === 'reguler-ti' &&
                    Auth::user()->prodi !== 'TI'
                ) abort(403);

                if (
                    $validated['tipe_magang'] === 'reguler-pdsi' &&
                    Auth::user()->prodi !== 'PdSI'
                ) abort(403);

                $data = $validated;
                $data['mahasiswa'] = collect($validated['mahasiswa'])->map(function ($m, $i) {
                    return [
                        'npm' => $m['npm'],
                        'nama' => $m['nama'],
                        'prodi' => Auth::user()->prodi,
                        'semester' => $m['semester'],
                        'no_kontak' => $m['no_kontak'],
                        'role_kontak' => $i === 0 ? 'pengaju' : 'anggota',
                    ];
                })->toArray();
            }

            /*elseif ($jenisSurat->slug === 'surat-rekomendasi') {

                $validated = $request->validate([
                    'ipk' => 'required|string',
                    'sks' => 'required|integer',
                    'tujuan' => 'required|in:magang,pekerjaan',
                    'pengalaman' => 'required|array',
                    'project' => 'nullable|array',
                ]);

                $data = [
                    'ipk' => $validated['ipk'],
                    'sks' => $validated['sks'],
                    'tujuan' => $validated['tujuan'],
                    'pengalaman' => array_filter($validated['pengalaman'] ?? []),
                    'project' => array_filter($validated['project'] ?? []),
                ];
            }*/

            // DEFAULT: SURAT LAIN
            else {

                $rules = [];
                foreach ($jenisSurat->fields as $field) {
                    $rules[$field] = 'required';
                }

                $validated = $request->validate($rules);
                $data = $validated;
            }

            // SIMPAN SURAT
            $surat = SuratPengajuan::create([
                'user_id' => Auth::id(),
                'jenis_surat_id' => $jenisSurat->id,
                'data' => $data,
                'status' => 'menunggu',
            ]);

            // ================= SIMPAN LAMPIRAN =================
            if ($request->hasFile('lampiran')) {

                $file = $request->file('lampiran');

                // Ambil info dasar
                $ext = $file->getClientOriginalExtension();
                $npm = Auth::user()->npm ?? 'user'; // ambil NPM
                $tanggal = now()->format('Ymd_His'); // format YYYYMMDD_HHMMSS
                $label = $request->input('label_lampiran', 'Lampiran');
                $labelSlug = str_replace(' ', '', ucwords($label));
                $namaFile = "{$labelSlug}_{$npm}_{$tanggal}.{$ext}";


                // Simpan file dengan nama custom di storage/public/lampiran-surat
                $path = $file->storeAs('lampiran-surat', $namaFile, 'public');

                \App\Models\SuratLampiran::create([
                    'surat_pengajuan_id' => $surat->id,
                    'nama_file_asli' => $file->getClientOriginalName(),
                    'path_file' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'label' => $label,
                ]);
            }


            // NOTIFIKASI ADMIN
            foreach (\App\Models\User::where('role', 'admin')->get() as $admin) {
                pushNotif(
                    $admin->id,
                    'Surat Masuk',
                    'Pengajuan "' . $jenisSurat->nama . '" dari ' . Auth::user()->name,
                    '/admin/surat/' . $surat->id
                );
            }

            return redirect('/mahasiswa/surat/' . $surat->id);
        });

        /*
        |----------------------------------------------------------------------
        | DETAIL SURAT MAHASISWA
        |----------------------------------------------------------------------
        */
        Route::get('/mahasiswa/surat/{id}', function ($id) {
            
            // ================================
            // AUTO MARK NOTIF AS READ
            // ================================
            \App\Models\Notification::where('user_id', Auth::id())
                ->where('url', '/mahasiswa/surat/' . $id)
                ->where('is_read', false)
                ->update(['is_read'=>true,'read_at'=>now()]);

            /*$surat = SuratPengajuan::with('jenisSurat')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();*/

            // KE DETAIL BERDASARKAN JENIS SURAT
            $surat = SuratPengajuan::with([
                'jenisSurat',
                'user',
                'children.jenisSurat'
            ])
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

            return view('mahasiswa.surat.detail', compact('surat'));
        });

        /*
        |----------------------------------------------------------------------
        | BATALKAN SURAT (SELAMA MENUNGGU)
        |----------------------------------------------------------------------
        */
        Route::post('/mahasiswa/surat/{id}/batal', function ($id) {

            $surat = SuratPengajuan::where('id', $id)
                ->where('user_id', Auth::id())
                ->where('status', 'menunggu')
                ->firstOrFail();

            $surat->delete();

            return redirect('/mahasiswa/dashboard');
        });

        /*
        |----------------------------------------------------------------------
        | route PDF 
        |----------------------------------------------------------------------
        
        Route::get('/mahasiswa/surat/{id}/pdf', function ($id) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                ->where('id', $id)
                ->where('status', 'disetujui_final')
                ->firstOrFail();
            
            if (
                Auth::user()->role === 'mahasiswa' &&
                $surat->user_id !== Auth::id()
            ) {
                abort(403);
            }

            $view = 'pdf.' . $surat->jenisSurat->slug;

            if (!view()->exists($view)) {
                abort(404, 'Template PDF belum tersedia');
            }

            // QR URL (AMAN TANPA EXTENSION PHP)
            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data='
                . urlencode(url('/surat/validasi/'.$surat->qr_token));

            $pdf = Pdf::loadView($view, compact('surat','qrUrl'))
                ->setPaper('A4', 'portrait');

            return $pdf->download(
                str_replace(' ', '-', $surat->jenisSurat->nama)
                . '-' . $surat->user->npm . '.pdf'
            );
        });*/

        /*
        |----------------------------------------------------------------------
        | LOG SURAT MAHASISWA
        |----------------------------------------------------------------------
        */
        Route::get('/mahasiswa/surat', function (Request $request) {

            $query = SuratPengajuan::with('jenisSurat')
                ->where('user_id', Auth::id());

            if ($request->status) {
                $query->where('status', $request->status);
            }

            $suratList = $query
                ->orderBy('created_at', 'desc')
                ->get();

            return view('mahasiswa.surat.log', compact('suratList'));
        });

    });

    /*
    |--------------------------------------------------------------------------
    | ADMIN (TU)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {

        // ================= DASHBOARD ADMIN - MAHASISWA =================
        Route::get('/admin/dashboard', function (Request $request) {

            $roleTarget = 'mahasiswa';

            $query = SuratPengajuan::with(['user','jenisSurat'])
                ->where('status', 'menunggu')
                ->whereHas('user', fn ($q) => $q->where('role', $roleTarget));;

            // SEARCH
            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('npm', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            // FILTER PRODI
            if ($request->prodi) {
                $query->whereHas('user', function ($u) use ($request) {
                    $u->where('prodi', $request->prodi);
                });
            }

            // FILTER JENIS SURAT
            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            // SORT wAKTU
            $sort = $request->sort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $sort);

            $suratPengajuan = $query->get();

            // dropdown
            $jenisSurat = JenisSurat::all();

            // JENIS SURAT KHUSUS MAHASISWA
            $jenisSurat = JenisSurat::where('target_user', $roleTarget)->get();
            return view('admin.dashboard', compact(
                'suratPengajuan',
                'jenisSurat'
            ));

        })->middleware(['auth','role:admin']);

        // ================= DASHBOARD ADMIN - DOSEN ================= 
        Route::get('/admin/dashboard-dosen', function (Request $request) {

            $roleTarget = 'dosen';

            $query = SuratPengajuan::with(['user','jenisSurat'])
                ->whereHas('user', fn ($q) => $q->where('role', $roleTarget))
                ->whereIn('status', ['menunggu', 'menunggu_admin_final']);

            // SEARCH
            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('nidn', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

             // FILTER JENIS SURAT
            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            // SORT wAKTU
            $sort = $request->sort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $sort);

            $suratPengajuan = $query->get();

            // dropdown
            $jenisSurat = JenisSurat::all();

            // JENIS SURAT KHUSUS DOSEN
            $jenisSurat = JenisSurat::where('target_user', $roleTarget)->get();

            return view('admin.dashboard-dosen', compact(
                'suratPengajuan',
                'jenisSurat'
            ));

        })->middleware(['auth','role:admin']);

        Route::get('/admin/surat/{id}', function ($id) {

            // AUTO MARK NOTIF AS READ
            \App\Models\Notification::where('user_id', Auth::id())
                ->where('url', '/admin/surat/' . $id)
                ->where('is_read', false)
                ->update(['is_read'=>true,'read_at'=>now()]);

            $surat = SuratPengajuan::with(['user','jenisSurat'])->findOrFail($id);
            /*if (!$surat->qr_token) {
                $surat->update([
                    'qr_token' => Str::uuid(),
                ]);
            }*/
            return view('surat.detail', compact('surat'));
        });

        Route::post('/admin/surat/{id}/setujui', function ($id) {

            $surat = SuratPengajuan::with('jenisSurat')->findOrFail($id);

            if ($surat->status !== 'menunggu') {
                return redirect('/admin/dashboard');
            }

            $alur = $surat->jenisSurat->alur; // contoh: ['admin','dekan']

            // Cari posisi admin di alur
            $indexAdmin = array_search('admin', $alur);

            // Ambil step setelah admin
            $nextStep = $alur[$indexAdmin + 1] ?? null;

            if ($nextStep === 'kaprodi') {
                $surat->update(['status' => 'menunggu_kaprodi']);
                $msg = 'Surat dikirim ke Kaprodi';
                // notif kaprodi
                $kaprodis = \App\Models\User::where('role', 'kaprodi')
                    ->where('prodi', $surat->user->prodi)->get();

                foreach ($kaprodis as $k) {
                    pushNotif(
                        $k->id,
                        'Surat Menunggu Persetujuan',
                        'Pengajuan "' . $surat->jenisSurat->nama . '" menunggu persetujuan Anda',
                        '/kaprodi/surat/' . $surat->id
                        
                    );
                }

            } elseif ($nextStep === 'dekan') {
                $surat->update(['status' => 'menunggu_dekan']);
                $msg = 'Surat dikirim ke Dekan';

                $dekans = \App\Models\User::where('role', 'dekan')->get();

                    foreach (\App\Models\User::where('role','dekan')->get() as $d) {
                        pushNotif($d->id,'Surat Menunggu Persetujuan',
                            'Pengajuan "' . $surat->jenisSurat->nama . '" menunggu persetujuan Anda',
                            '/dekan/surat/'.$surat->id);
                    }

                } else {

                    // KHUSUS SURAT DOSEN → JANGAN FINAL DULU
                    if ($surat->user->role === 'dosen') {

                        $surat->update([
                            'status' => 'menunggu_admin_final'
                        ]);

                        $msg = 'Surat menunggu finalisasi Admin';

                    } else {

                        // MAHASISWA → BOLEH FINAL LANGSUNG
                        $surat->status = 'disetujui_final';
                        $surat->tanggal_disetujui_final = now();
                        $surat->qr_token = $surat->qr_token ?? Str::uuid();
                        $surat->penandatangan = 'Admin Fakultas';
                        $surat->save();

                        $surat->refresh();
                        $surat->load(['user','jenisSurat']);

                        if (!$surat->nomor_surat) {
                            $surat->nomor_surat = generateNomorSurat($surat);
                            $surat->save();
                        }

                        $msg = 'Surat disetujui final';
                    }
                }

                // Permohonan ke Persetujuan
                if ($surat->jenisSurat->slug === 'surat-permohonan-aktif-akademik') {

                    // set permohonan final
                        $surat->update([
                        'status' => 'disetujui_final',
                        'tanggal_disetujui_final' => now(),
                        'qr_token' => $surat->qr_token ?? Str::uuid(),
                    ]);

                    // AUTO BUAT SURAT PERSETUJUAN
                    $jenisPersetujuan = JenisSurat::where(
                        'slug',
                        'surat-persetujuan-aktif-akademik'
                    )->firstOrFail();

                    $suratPersetujuan = SuratPengajuan::create([
                        'user_id' => $surat->user_id,
                        'jenis_surat_id' => $jenisPersetujuan->id,
                        'parent_id' => $surat->id,
                        'data' => [
                            'tanggal_permohonan_disetujui' => $surat->tanggal_disetujui_final,
                            'batas_pembayaran' => $surat->data['batas_pembayaran'],
                            'nominal_pembayaran' => $surat->data['nominal_pembayaran'],
                        ],
                        'status' => 'menunggu_kaprodi',
                    ]);

                    // NOTIFIKASI KAPRODI
                    $kaprodis = \App\Models\User::where('role', 'kaprodi')
                        ->where('prodi', $surat->user->prodi)
                        ->get();

                    foreach ($kaprodis as $k) {
                        pushNotif(
                            $k->id,
                            'Surat Persetujuan Aktif Akademik',
                            'Surat Persetujuan Aktif Akademik menunggu persetujuan Anda',
                            '/kaprodi/surat/' . $suratPersetujuan->id
                        );
                    }
                    
                    // NOTIFIKASI KE MAHASISWA
                    pushNotif(
                        $surat->user_id,
                        'Surat Persetujuan Aktif Dibuat',
                        'Surat Persetujuan Aktif Akademik telah dibuat dan menunggu persetujuan Kaprodi',
                        '/mahasiswa/surat/' . $suratPersetujuan->id
                    );

                    return redirect('/admin/dashboard')
                        ->with('success', 'Permohonan disetujui & surat persetujuan dibuat');
                }

            // === NOTIF KE MAHASISWA (DISETUJUI) ===
            $route = $surat->user->role === 'dosen'
                ? '/dosen/surat/' . $surat->id
                : '/mahasiswa/surat/' . $surat->id;

            $target = $surat->user->role === 'dosen'
                ? 'Dosen'
                : 'Mahasiswa';

            pushNotif(
                $surat->user_id,
                'Surat Disetujui',
                'Pengajuan "' . $surat->jenisSurat->nama . '" telah disetujui Admin',
                $route
            );

            return redirect('/admin/dashboard')->with('success', $msg);
        });

        Route::post('/admin/surat/{id}/finalisasi', function ($id, Request $request) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])->findOrFail($id);

            if ($surat->status !== 'menunggu_admin_final') {
                return redirect('/admin/dashboard');
            }

            //  VALIDASI INPUT
            $request->validate([
                'nomor_surat' => 'required|string',
                'tanggal_ttd' => 'required|date',
            ]);

            $nomorSuratBaru = $request->input('nomor_surat');

            // =========================
            // Cek nomor surat duplikat
            // =========================
            $exists = SuratPengajuan::where('nomor_surat', $nomorSuratBaru)
                        ->where('id', '<>', $surat->id)
                        ->exists();

            if ($exists) {
                return redirect()->back()
                    ->with('error', 'Nomor surat "' . $nomorSuratBaru . '" sudah digunakan!')
                    ->withInput();
            }

            // =========================
            // Finalisasi surat
            // =========================
            // GABUNG TANGGAL + JAM FINALISASI
            $tanggalTTD = Carbon::parse($request->tanggal_ttd)
                ->setTimeFromTimeString(now()->format('H:i:s'));

            $surat->update([
                'status' => 'disetujui_final',
                'nomor_surat' => $nomorSuratBaru,
                'tanggal_disetujui_final' => $tanggalTTD,
                'penandatangan' => 'Dr. Ummi Azizah Rachmawati, M.Kom.',
                'qr_token' => $surat->qr_token ?? Str::uuid(),
            ]);

            // notif ke DOSEN
            pushNotif(
                $surat->user_id,
                'Surat Telah Terbit',
                'Surat "' . $surat->jenisSurat->nama . '" telah selesai dan siap diunduh',
                '/dosen/surat/' . $surat->id
            );

            return redirect('/admin/dashboard')
                ->with('success','Surat dosen berhasil difinalisasi');
        });

        Route::post('/admin/surat/{id}/tolak', function (Request $request, $id) {

            $surat = SuratPengajuan::findOrFail($id);

            if ($surat->status !== 'menunggu') {
                return redirect('/admin/dashboard');
            }

            $surat->update([
                'status' => 'ditolak',
                'alasan_tolak' => $request->alasan_tolak ?? null,
            ]);

            // === NOTIF KE MAHASISWA/DOSEN (DITOLAK) ===
            $targetRole = $surat->user->role; // mahasiswa | dosen

            $url = $targetRole === 'mahasiswa'
                ? '/mahasiswa/surat/'.$surat->id
                : '/dosen/surat/'.$surat->id;

            pushNotif(
                $surat->user_id,
                'Surat Ditolak',
                'Pengajuan "' . $surat->jenisSurat->nama . '" ditolak'
                    . ($request->alasan_tolak ? ': '.$request->alasan_tolak : ''),
                $url
            );

            return redirect('/admin/dashboard')
                ->with('success', 'Surat ditolak admin');
        });

        // EXPORT LOG SURAT KE EXCEL
        Route::get('/admin/surat/export/excel', function (Request $request) {

            return Excel::download(
                new SuratPengajuanExport(
                    $request->status,
                    $request->prodi,
                    $request->jenis,
                    $request->sort,
                    $request->role
                ),
                'arsip-surat-'.$request->role.'-'.now()->format('Ymd_His').'.xlsx'
            );
        });

        Route::get('/admin/surat', function (Request $request) {

            $query = SuratPengajuan::with(['user','jenisSurat']);

            // SEARCH
            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('nidn', 'like', '%'.$request->q.'%')
                        ->orWhere('npm', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            // STATUS
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // PRODI
            if ($request->prodi) {
                $query->whereHas('user', fn($q) =>
                    $q->where('prodi', $request->prodi)
                );
            }

            // TANGGAL
            if ($request->tanggal) {
                $query->whereDate('updated_at', $request->tanggal);
            }

            // JENIS SURAT
            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            // ROLE (Mahasiswa / Dosen)
            if ($request->role) {
                $query->whereHas('user', function ($q) use ($request) {
                    $q->where('role', $request->role);
                });
            }

            // SORT
            $sort = $request->sort ?? 'desc';
            $query->orderBy('updated_at', $sort);

            $suratList = $query->get();
            $jenisSurat = JenisSurat::all();

            return view('admin.surat.log', compact(
                'suratList',
                'jenisSurat'
            ));
        });

    // Dashboard admin disposisi
    Route::get('/admin/disposisi', function() {

        $disposisiList = Disposisi::with(['surat','fromUser','toUser'])
            ->orderBy('created_at','desc')
            ->get(); // 🔥 TANPA WHERE

        return view('admin.disposisi.dashboard', compact('disposisiList'));
    });

    // EXPORT EXCEL DISPOSISI
    Route::get('/admin/disposisi/export/excel', function () {

        return Excel::download(
            new \App\Exports\DisposisiExport(),
            'disposisi-surat-masuk-'.now()->format('Ymd_His').'.xlsx'
        );

    });

    // Form buat disposisi baru
    Route::get('/admin/disposisi/create', function() {
        $users = User::whereIn('role',['dekan'])->get();
        return view('admin.disposisi.create', compact('users'));
    });

    // Simpan disposisi baru (ADMIN)
    Route::post('/admin/disposisi', function (Request $request) {

        $request->validate([
            'no_surat'          => 'required|string|max:100',
            'to_user_id'        => 'required|exists:users,id',
            'file'              => 'required|file|mimes:pdf,doc,docx',
            'perihal'           => 'required|string|max:255',
            'catatan'           => 'nullable|string',
            'tgl_surat'         => 'required|date',           // validasi tanggal surat
            'tgl_terima_surat'  => 'required|date',           // validasi tanggal diterima
            'nama_pengirim'     => 'required|string|max:255', // validasi nama pengirim
            'jumlah_pembayaran' => 'nullable|numeric',        // opsional, harus angka kalau diisi
            'pilihan_kirim'     => 'required|in:email,cetak', // validasi pilihan email/cetak
        ]);
        // 🔍 ambil user tujuan + validasi role
        $toUser = User::where('id', $request->to_user_id)
            ->whereIn('role', ['dosen', 'kaprodi', 'dekan'])
            ->first();

        if (!$toUser) {
            return back()
                ->withErrors(['to_user_id' => 'User tujuan tidak valid'])
                ->withInput();
        }

        // 📂 upload file
        $filePath = $request->file('file')->store('disposisi_files', 'public');

        // 📨 buat surat
        $surat = Surat::create([
            'no_surat'          => $request->no_surat,
            'perihal'           => $request->perihal,
            'file_path'         => $filePath,
            'created_by'        => Auth::id(),
            'jenis_surat_id'    => 1, // default
            'tgl_surat'         => $request->tgl_surat,          // input baru
            'tgl_terima_surat'  => $request->tgl_terima_surat,   // input baru
            'nama_pengirim'     => $request->nama_pengirim,      // input baru
            'jumlah_pembayaran' => $request->jumlah_pembayaran,  // opsional
            'pilihan_kirim'     => $request->pilihan_kirim, // validasi pilihan email/cetak
        ]);

        // 📌 buat disposisi
        $disposisi = Disposisi::create([
            'surat_id'     => $surat->id,
            'from_user_id' => Auth::id(),
            'to_user_id'   => $toUser->id,
            'prioritas'    => $request->prioritas,
            'catatan'      => $request->catatan,
            'status'       => 'terkirim',
        ]);

        // 🔔 notifikasi
        pushNotif(
            $toUser->id,
            'Disposisi Surat Masuk',
            'Ada disposisi baru dari ' . Auth::user()->name,
            '/disposisi/' . $disposisi->id
        );

        return redirect('/admin/disposisi')
            ->with('success', 'Disposisi berhasil dikirim');
    });


    });

    // Dashboard disposisi untuk semua role
    Route::get('/disposisi', function() {

        $user = Auth::user();

        // 🔥 KHUSUS DEKAN → lihat semua
        if ($user->role === 'dekan') {

            $disposisiList = Disposisi::with(['surat','fromUser','toUser'])
                ->orderBy('created_at','desc')
                ->get();

        } else {

            // 🔒 Dosen & Kaprodi hanya lihat yang terlibat
            $disposisiList = Disposisi::with(['surat','fromUser','toUser'])
                ->where(function($q) use ($user) {
                    $q->where('to_user_id', $user->id)
                    ->orWhere('from_user_id', $user->id);
                })
                ->orderBy('created_at','desc')
                ->get();
        }

        return view('disposisi.dashboard', compact('disposisiList'));
    });

    Route::get('/disposisi/{id}', function($id) {
        $disposisi = Disposisi::with(['surat','fromUser','toUser'])->findOrFail($id);
        $user = Auth::user();

        // 🔥 ADMIN & DEKAN BOLEH LIHAT SEMUA
        if (!in_array($user->role, ['admin','dekan'])) {

            if (
                $disposisi->to_user_id !== $user->id &&
                $disposisi->from_user_id !== $user->id
            ) {
                abort(403, 'Anda tidak memiliki akses ke disposisi ini.');
            }
        }

        // ✅ TAMBAHKAN INI
        $users = \App\Models\User::whereIn('role', ['dosen','kaprodi','dekan'])
            ->where('id', '!=', $disposisi->to_user_id) // jangan kirim ke diri sendiri
            ->get();

        \App\Models\Notification::where('user_id', Auth::id())
            ->where('url', '/disposisi/' . $id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return view('disposisi.detail', compact('disposisi', 'users'));
    });

    // Terima disposisi
    Route::post('/disposisi/{id}/terima', function($id) {
        $disposisi = Disposisi::with('surat')->findOrFail($id);

        if ($disposisi->to_user_id !== Auth::id() || Auth::user()->role === 'admin') {
            abort(403);
        }

        // ✅ disposisi selesai
        $disposisi->update([
            'status' => 'selesai'
        ]);

        // ✅ surat juga selesai
        if ($disposisi->surat) {
            $disposisi->surat->update([
                'status' => 'selesai'
            ]);
        }

        return back()->with('success','Disposisi diterima. Surat selesai.');
    });

   Route::post('/disposisi/{id}/lempar', function (Request $request, $id) {

        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'catatan'    => 'nullable|string',
        ]);

        $disposisi = Disposisi::findOrFail($id);

        // 🔒 hanya penerima aktif yang boleh lempar
        if (
            $disposisi->to_user_id !== Auth::id() ||
            $disposisi->status === 'selesai' ||
            Auth::user()->role === 'admin'
        ) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // ❌ tidak boleh kirim ke diri sendiri
        if ($request->to_user_id == Auth::id()) {
            return back()
                ->withErrors(['to_user_id' => 'Tidak boleh mengirim ke diri sendiri'])
                ->withInput();
        }

        $toUser = User::where('id', $request->to_user_id)
            ->whereIn('role', ['dosen', 'kaprodi', 'dekan'])
            ->first();

        if (!$toUser) {
            return back()
                ->withErrors(['to_user_id' => 'User tujuan tidak valid'])
                ->withInput();
        }

        // ✅ UPDATE record yang sama (TIDAK CREATE BARU)
        $disposisi->update([
            'from_user_id' => Auth::id(),
            'to_user_id'   => $toUser->id,
            'catatan'      => $request->catatan,
            'status'       => 'terkirim',
        ]);

        // 🔔 notif
        pushNotif(
            $toUser->id,
            'Disposisi Diteruskan',
            'Disposisi diteruskan oleh ' . Auth::user()->name,
            '/disposisi/' . $disposisi->id
        );

        return back()->with('success', 'Disposisi berhasil dikirim ke user lain.');
    });


    Route::post('/disposisi/{id}/kirim-admin', function (Request $request, $id) {

        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $disposisi = Disposisi::findOrFail($id);

        if (
            $disposisi->to_user_id !== Auth::id() ||
            $disposisi->status === 'selesai'
        ) {
            abort(403);
        }

        $admin = \App\Models\User::where('role', 'admin')->first();

        if (!$admin) {
            return back()->withErrors('Admin tidak ditemukan');
        }

        // ✅ UPDATE saja
        $disposisi->update([
            'from_user_id' => Auth::id(),
            'to_user_id'   => $admin->id,
            'catatan'      => $request->catatan,
            'status'       => 'terkirim',
        ]);

        pushNotif(
            $admin->id,
            'Disposisi ke Admin',
            'Disposisi diteruskan oleh ' . Auth::user()->name,
            '/disposisi/' . $disposisi->id
        );

        return back()->with('success', 'Disposisi berhasil dikirim ke admin.');
    });

    Route::post('/disposisi/{id}/upload-file', function (Request $request, $id) {

        $request->validate([
            'file_tambahan' => 'required|file|max:5120',
        ]);

        $disposisi = Disposisi::findOrFail($id);

        // 🔒 hanya admin & hanya disposisi miliknya
        if (
            Auth::user()->role !== 'admin' ||
            $disposisi->to_user_id !== Auth::id()
        ) {
            abort(403);
        }

        // ✅ SIMPAN FILE
        $path = $request->file('file_tambahan')
            ->store('disposisi_file_tambahan', 'public');

        // ✅ UPDATE KOLOM DISPOSISI (INI YANG KEMARIN GAK KEJADI)
        $disposisi->update([
            'file_tambahan' => $path
        ]);

        return redirect('/disposisi/'.$disposisi->id);
    });

    Route::post('/admin/disposisi/{id}/selesai', function ($id) {
        $disposisi = \App\Models\Disposisi::findOrFail($id);

        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $disposisi->status = 'selesai';
        $disposisi->save();

        // 🔍 cari pengirim asli / terakhir sebelum admin
        $previousDisposisi = \App\Models\Disposisi::where('surat_id', $disposisi->surat_id)
        ->where('to_user_id', Auth::id())       // penerima = admin
        ->where('from_user_id', '!=', Auth::id()) // dikirim oleh user biasa
        ->latest('created_at')
        ->first();

        if($previousDisposisi?->fromUser) {
            pushNotif(
                $previousDisposisi->fromUser->id,
                'Disposisi Selesai',
                'Disposisi "' . $disposisi->surat->no_surat . '" telah selesai oleh admin.',
                '/disposisi/' . $disposisi->id
            );
        }

        return redirect('/admin/disposisi')->with('success', 'Disposisi telah selesai.');
    });

    
    /*
    |--------------------------------------------------------------------------
    | KAPRODI
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:kaprodi')->group(function () {

        Route::get('/kaprodi/dashboard', function (Request $request) {

            $query = SuratPengajuan::with(['user','jenisSurat'])
                ->where('status', 'menunggu_kaprodi')
                ->whereHas('jenisSurat', function ($q) {
                    $q->whereJsonContains('alur', 'kaprodi');
                })
                ->whereHas('user', function ($q) {
                    $q->where('prodi', Auth::user()->prodi);
                });

            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('npm', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            $sort = $request->sort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $sort);

            $suratPengajuan = $query->get();
            $jenisSurat = JenisSurat::whereJsonContains('alur','kaprodi')->get();

            return view('kaprodi.dashboard', compact(
                'suratPengajuan',
                'jenisSurat'
            ));
        });

        /*
        |--------------------------------------------------------------------------
        | ARSIP SURAT FAKULTAS (READ ONLY) - KAPRODI
        |--------------------------------------------------------------------------
        */
        Route::get('/kaprodi/arsip-fakultas', function (Request $request) {

            $query = SuratPengajuan::with(['user','jenisSurat'])
                ->where('status', 'disetujui_final')
                ->whereHas('jenisSurat', function ($q) {
                    // surat fakultas = ada DEKAN di alur
                    $q->whereJsonContains('alur', 'dekan');
                })
                ->whereHas('user', function ($q) {
                    // FILTER PRODI KAPRODI LOGIN
                    $q->where('prodi', Auth::user()->prodi);
                });

            // FILTER OPSIONAL
            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            if ($request->tanggal) {
                $query->whereDate('tanggal_disetujui_final', $request->tanggal);
            }

            $suratList = $query
                ->orderBy('tanggal_disetujui_final', 'desc')
                ->get();

            $jenisSurat = JenisSurat::whereJsonContains('alur', 'dekan')->get();

            return view('kaprodi.arsip-fakultas', compact(
                'suratList',
                'jenisSurat'
            ));
        });


        /*
        |--------------------------------------------------------------------------
        | DETAIL ARSIP SURAT FAKULTAS - KAPRODI (READ ONLY)
        |--------------------------------------------------------------------------
        */
        Route::get('/kaprodi/arsip-fakultas/{id}', function ($id) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                ->where('status', 'disetujui_final')
                ->whereHas('jenisSurat', function ($q) {
                    // surat fakultas = ada dekan
                    $q->whereJsonContains('alur', 'dekan');
                })
                ->whereHas('user', function ($q) {
                    // filter prodi kaprodi login
                    $q->where('prodi', Auth::user()->prodi);
                })
                ->findOrFail($id);

            return view('surat.detail', compact('surat'));
        });


        // Kaprodi menyetujui / tolak

        Route::get('/kaprodi/surat/{id}', function ($id) {

            // AUTO MARK NOTIF AS READ (KAPRODI)

            \App\Models\Notification::where('user_id', Auth::id())
                ->where('url', '/kaprodi/surat/' . $id)
                ->where('is_read', false)
                ->update(['is_read'=>true,'read_at'=>now()]);

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                    ->whereHas('jenisSurat', fn ($q) =>
                        $q->whereJsonContains('alur','kaprodi')
                    )
                    ->whereHas('user', fn ($q) =>
                        $q->where('prodi', Auth::user()->prodi)
                    )
                    ->findOrFail($id);

                return view('surat.detail', compact('surat'));
            });;

        Route::post('/kaprodi/surat/{id}/setujui', function ($id) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                ->findOrFail($id);

            if ($surat->status !== 'menunggu_kaprodi') {
                return redirect('/kaprodi/dashboard');
            }

            // JIKA PERLU DEKAN
            if ($surat->jenisSurat->perlu_dekan) {
                $surat->update(['status' => 'menunggu_dekan']);

                // ================================
                // NOTIF KE DEKAN
                // ================================
                foreach (\App\Models\User::where('role','dekan')->get() as $d) {
                    pushNotif(
                        $d->id,
                        'Surat Menunggu Persetujuan',
                        'Pengajuan "' . $surat->jenisSurat->nama . '" menunggu persetujuan Anda',
                        '/dekan/surat/' . $surat->id
                    );
                }
                return redirect('/kaprodi/dashboard')
                    ->with('success', 'Surat dikirim ke Dekan');
            }

            // FINAL DI KAPRODI (QR Kaprodi)
            $surat->status = 'disetujui_final';
            $surat->tanggal_disetujui_final = now();
            $surat->qr_token = $surat->qr_token ?? Str::uuid();
            $surat->penandatangan = namaKaprodi($surat->user->prodi);
            $surat->save();
            
            $surat->refresh();
            $surat->load(['user','jenisSurat']);

            if (!$surat->nomor_surat) {
                $surat->nomor_surat = generateNomorSurat($surat);
                $surat->save();
            }

            // ================================
            // NOTIF KE MAHASISWA
            // ================================
            pushNotif(
                $surat->user_id,
                'Surat Disetujui',
                'Pengajuan "' . $surat->jenisSurat->nama . '" telah disetujui Kaprodi',
                '/mahasiswa/surat/'.$surat->id
            );

            return redirect('/kaprodi/dashboard')
                ->with('success', 'Surat disetujui');
        });

        Route::post('/kaprodi/surat/{id}/tolak', function ($id) {

            $surat = SuratPengajuan::findOrFail($id);
            $surat->update(['status' => 'ditolak']);

            // ================================
            // NOTIF KE MAHASISWA
            // ================================
            pushNotif(
                $surat->user_id,
                'Surat Ditolak',
                'Pengajuan "' . $surat->jenisSurat->nama . '" ditolak oleh Kaprodi',
                '/mahasiswa/surat/'.$surat->id
            );

            return redirect('/kaprodi/dashboard')
                ->with('success', 'Surat ditolak');
        });

        // LOG SURAT KAPRODI
        Route::get('/kaprodi/surat', function (Request $request) {

            $query = SuratPengajuan::with(['user','jenisSurat'])
                ->whereIn('status', ['disetujui_final','ditolak'])
                ->whereHas('jenisSurat', function ($q) {
                    $q->whereJsonContains('alur', 'kaprodi');
                })
                ->whereHas('user', function ($q) {
                    $q->where('prodi', Auth::user()->prodi);
                });

            // SEARCH
            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('npm', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            // STATUS
            if ($request->status) {
                $query->where('status', $request->status);
            }

            // FILTER JENIS SURAT
            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            // FILTER TANGGAL
            if ($request->tanggal) {
                $query->whereDate('updated_at', $request->tanggal);
            }

            // SORT
            $sort = $request->sort ?? 'desc';
            $query->orderBy('updated_at', $sort);

            $suratList = $query->get();

            $jenisSurat = JenisSurat::whereJsonContains('alur','kaprodi')->get();

            return view('kaprodi.surat.log', compact(
                'suratList',
                'jenisSurat'
            ));
        });

    });

    /*
    |--------------------------------------------------------------------------
    | DEKAN
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:dekan')->group(function () {

        // ================= DASHBOARD DEKAN - MAHASISWA ================= --BARU--
        Route::get('/dekan/dashboard', function (Request $request) {

            $roleTarget = 'mahasiswa';

            $query = SuratPengajuan::with(['user', 'jenisSurat'])
                ->where('status', 'menunggu_dekan')
                ->whereHas('jenisSurat', function ($q) {
                    $q->whereJsonContains('alur', 'dekan');
                })
                ->whereHas('user', function ($q) use ($roleTarget) {
                    $q->where('role', $roleTarget);
                });


            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('npm', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            $sort = $request->sort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $sort);

            $suratPengajuan = $query->get();
            $jenisSurat = JenisSurat::whereJsonContains('alur','dekan')->get();

            // ✅ JENIS SURAT KHUSUS MAHASISWA
            $jenisSurat = JenisSurat::where('target_user', $roleTarget)->get();
            // contoh kolom: untuk_role = mahasiswa / dosen

            return view('dekan.dashboard', compact(
                'suratPengajuan',
                'jenisSurat'
            ));
        })->middleware(['auth','role:dekan']);

        // ================= DASHBOARD DEKAN - DOSEN ================= --BARU--
        Route::get('/dekan/dashboard-dosen', function (Request $request) {

            $roleTarget = 'dosen';

            $query = SuratPengajuan::with(['user', 'jenisSurat'])
                ->where('status', 'menunggu_dekan')
                ->whereHas('jenisSurat', function ($q) {
                    $q->whereJsonContains('alur', 'dekan');
                })
                ->whereHas('user', function ($q) use ($roleTarget) {
                    $q->where('role', $roleTarget);
                });

            //  SEARCH
            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('nidn', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            $sort = $request->sort === 'asc' ? 'asc' : 'desc';
            $query->orderBy('created_at', $sort);

            $suratPengajuan = $query->get();
            $jenisSurat = JenisSurat::whereJsonContains('alur','dekan')->get();

            // ✅ JENIS SURAT KHUSUS DOSEN
            $jenisSurat = JenisSurat::where('target_user', $roleTarget)->get();

            return view('dekan.dashboard-dosen', compact(
                'suratPengajuan',
                'jenisSurat'
            ));

        })->middleware(['auth','role:dekan']);

        Route::get('/dekan/surat/{id}', function ($id) {

            // ================================
            // AUTO MARK NOTIF AS READ (DEKAN)
            // ================================
            \App\Models\Notification::where('user_id', Auth::id())
                ->where('url', '/dekan/surat/' . $id)
                ->where('is_read', false)
                ->update(['is_read'=>true,'read_at'=>now()]);

            $surat = SuratPengajuan::with(['user','jenisSurat'])
                    ->whereHas('jenisSurat', fn ($q) =>
                        $q->whereJsonContains('alur','dekan')
                    )
                    ->findOrFail($id);

                return view('surat.detail', compact('surat'));
            });

        Route::post('/dekan/surat/{id}/setujui', function ($id) {

            $surat = SuratPengajuan::with(['user','jenisSurat'])->findOrFail($id);

            if ($surat->status !== 'menunggu_dekan') {
                return redirect('/dekan/dashboard');
            }

            // ============================
            // JIKA DOSEN → KIRIM KE ADMIN
            // ============================
            if ($surat->user->role === 'dosen') {

                $surat->update([
                    'status' => 'menunggu_admin_final'
                ]);

                // 🔔 notif ke ADMIN
                foreach (\App\Models\User::where('role','admin')->get() as $admin) {
                    pushNotif(
                        $admin->id,
                        'Finalisasi Surat Dosen',
                        'Surat dosen "' . $surat->jenisSurat->nama . '" menunggu finalisasi',
                        '/admin/surat/' . $surat->id
                    );
                }

                return redirect('/dekan/dashboard')
                    ->with('success','Surat disetujui & dikirim ke Admin');

            }

            // ============================
            // JIKA MAHASISWA → LANGSUNG FINAL
            // ============================
            $surat->status = 'disetujui_final';
            $surat->tanggal_disetujui_final = now();
            $surat->nomor_surat = generateNomorSurat($surat);
            $surat->qr_token = $surat->qr_token ?? Str::uuid();
            $surat->penandatangan = 'Dr. Ummi Azizah Rachmawati, M.Kom.';
            $surat->save();

            // notif ke MAHASISWA
            pushNotif(
                $surat->user_id,
                'Surat Disetujui',
                'Pengajuan "' . $surat->jenisSurat->nama . '" telah disetujui dan siap diunduh',
                '/mahasiswa/surat/' . $surat->id
            );

            return redirect('/dekan/dashboard')
                ->with('success','Surat mahasiswa disetujui final');
        });  

        Route::post('/dekan/surat/{id}/tolak', function ($id) {

            $surat = SuratPengajuan::findOrFail($id);
            $surat->update(['status' => 'ditolak']);

            $route = $surat->user->role === 'dosen'
                ? '/dosen/surat/' . $surat->id
                : '/mahasiswa/surat/' . $surat->id;

            $target = $surat->user->role === 'dosen'
                ? 'Dosen'
                : 'Mahasiswa';

            pushNotif(
                $surat->user_id,
                'Surat Ditolak',
                'Pengajuan "' . $surat->jenisSurat->nama . '" ditolak oleh Dekan',
                $route
            );

            return redirect('/dekan/dashboard')
                ->with('success', 'Surat ditolak');
        });
        // LOG SURAT DEKAN
        Route::get('/dekan/surat', function (Request $request) {

            $query = SuratPengajuan::with(['user','jenisSurat'])
                ->whereIn('status', ['disetujui_final','ditolak'])
                ->whereHas('jenisSurat', function ($q) {
                    $q->whereJsonContains('alur', 'dekan');
                });

            // FILTER & SEARCH
            if ($request->q) {
                $query->where(function ($q) use ($request) {
                    $q->whereHas('user', function ($u) use ($request) {
                        $u->where('name', 'like', '%'.$request->q.'%')
                        ->orWhere('nidn', 'like', '%'.$request->q.'%')
                        ->orWhere('npm', 'like', '%'.$request->q.'%');
                    })
                    ->orWhereHas('jenisSurat', function ($j) use ($request) {
                        $j->where('nama', 'like', '%'.$request->q.'%');
                    });
                });
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->jenis) {
                $query->where('jenis_surat_id', $request->jenis);
            }

            if ($request->tanggal) {
                $query->whereDate('updated_at', $request->tanggal);
            }

            $sort = $request->sort ?? 'desc';
            $query->orderBy('updated_at', $sort);

            $suratList = $query->get();
            $jenisSurat = JenisSurat::whereJsonContains('alur','dekan')->get();

            return view('dekan.surat.log', compact(
                'suratList',
                'jenisSurat'
            ));
        });
    });

    Route::get('/profile', function () {
        return view('profile.index', [
            'user' => Auth::user()
        ]);
    })->name('profile.index');

    Route::get('/profile/edit', function () {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    })->middleware('auth')->name('profile.edit');

    Route::post('/profile/update', function (Request $request) {

    /** @var \App\Models\User $user */
    $user = Auth::user();

    // ===============================
    // VALIDASI DASAR (SEMUA ROLE)
    // ===============================
    $rules = [
        // 'email' => 'nullable|email|users,email,' . $user->id,
        'email' => 'nullable|email',
        'telephonenumber' => 'nullable|string|max:20',
    ];

    // ===============================
    // KHUSUS DOSEN → BOLEH EDIT
    // ===============================
    if ($user->role === 'dosen') {
        $rules['name']  = 'required|string|max:255';
        $rules['prodi'] = 'nullable|in:TI,PdSI';
    }

    // ===============================
    // KHUSUS ADMIN → GANTI PASSWORD
    // ===============================
    if ($user->role === 'admin') {
        $rules['password'] = 'nullable|min:6|confirmed';
    }

    $validated = $request->validate($rules);

    // ===============================
    // DATA UMUM
    // ===============================
    $data = [
        'email' => $validated['email'],
        'telephonenumber' => $validated['telephonenumber'] ?? $user->telephonenumber,
    ];

    // ===============================
    // UPDATE DOSEN
    // ===============================
    if ($user->role === 'dosen') {
        $data['name']  = $validated['name'];
        $data['prodi'] = $validated['prodi'];
    }

    // ===============================
    // UPDATE PASSWORD ADMIN
    // ===============================
    if ($user->role === 'admin' && !empty($validated['password'])) {
        $data['password'] = Hash::make($validated['password']);
    }

    $user->update($data);

    return back()->with('success', 'Profil berhasil diperbarui');
})->middleware('auth');

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATOR (SUPER ADMIN)
    |--------------------------------------------------------------------------
    | TUGAS:
    | - Membuat akun admin / kaprodi / dekan
    | - Melihat & menghapus akun tersebut
    */

    Route::middleware(['auth', 'role:administrator'])->group(function () {

        // ================= DASHBOARD ADMINISTRATOR =================
        Route::get('/administrator/dashboard', function () {

            $users = User::whereIn('role', ['admin', 'kaprodi', 'dekan'])->get();

            return view('administrator.dashboard', compact('users'));
        });

        // ================= FORM TAMBAH AKUN =================
        Route::get('/administrator/tambah-akun', function () {
            return view('administrator.tambah-akun');
        });

        // ================= SIMPAN AKUN BARU =================
        Route::post('/administrator/simpan-akun', function (Request $request) {

            $request->validate([
                'name'     => 'required|string|max:255',
                'username' => 'required|string|max:50|unique:users,username',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'role'     => 'required|in:admin,kaprodi,dekan',
                // khusus kaprodi
                'prodi'    => 'nullable|required_if:role,kaprodi|in:TI,PdSI',
            ]);

            User::create([
                'name'     => $request->name,
                'username' => $request->username,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'prodi'    => $request->role === 'kaprodi' ? $request->prodi : null,
            ]);

            return redirect('/administrator/dashboard')
                ->with('success', 'Akun berhasil ditambahkan');

        })->middleware(['auth', 'role:administrator']);

        // ================= HAPUS AKUN =================
        Route::delete('/administrator/hapus-akun/{id}', function ($id) {

            User::where('id', $id)
                ->whereIn('role', ['admin', 'kaprodi', 'dekan'])
                ->delete();

            return back()->with('success', 'Akun berhasil dihapus');
        });

    });

});