<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TemplateSuratController extends Controller
{
    /**
     * Daftar template surat yang aktif untuk halaman pengusul.
     *
     * Hanya role mahasiswa/dosen yang boleh mengakses endpoint ini. Pengusul
     * hanya menerima template aktif milik role-nya sendiri, sehingga template
     * nonaktif maupun template role lain tidak pernah bisa dipilih.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_user' => ['nullable', 'in:mahasiswa,dosen'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $targetUser = $validated['target_user'] ?? null;

        // Pengusul hanya boleh melihat template milik role-nya sendiri.
        // Filter role lain ditolak agar daftar template tidak bocor lintas role.
        if (in_array($user?->role, ['mahasiswa', 'dosen'], true)) {
            if ($targetUser !== null && $targetUser !== $user->role) {
                abort(403, 'Anda hanya dapat melihat template surat untuk role Anda.');
            }

            $targetUser = $user->role;
        }

        $templates = JenisSurat::query()
            ->aktif()
            ->when($targetUser, fn ($query) => $query->untukPengguna($targetUser))
            ->cari($validated['search'] ?? null)
            ->orderBy('nama')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $templates->map(fn (JenisSurat $template) => [
                'id' => $template->id,
                'nama' => $template->nama,
                'slug' => $template->slug,
                'kode' => $template->kode,
                'target_user' => $template->target_user,
                'fields' => $template->fields ?? [],
                'url_ajukan' => $this->urlAjukan($template),
            ])->values(),
        ]);
    }

    private function urlAjukan(JenisSurat $template): string
    {
        $prefix = $template->target_user === 'dosen' ? 'dosen' : 'mahasiswa';

        return url('/' . $prefix . '/surat/ajukan/' . $template->id);
    }
}
