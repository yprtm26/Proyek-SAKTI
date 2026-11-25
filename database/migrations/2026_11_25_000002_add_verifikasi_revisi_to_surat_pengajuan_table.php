<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            if (! Schema::hasColumn('surat_pengajuan', 'catatan_revisi')) {
                $table->text('catatan_revisi')->nullable()->after('alasan_tolak');
            }
            if (! Schema::hasColumn('surat_pengajuan', 'diverifikasi_tu_at')) {
                $table->timestamp('diverifikasi_tu_at')->nullable()->after('catatan_revisi');
            }
        });
    }

    public function down(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $cols = array_values(array_filter([
                Schema::hasColumn('surat_pengajuan', 'catatan_revisi') ? 'catatan_revisi' : null,
                Schema::hasColumn('surat_pengajuan', 'diverifikasi_tu_at') ? 'diverifikasi_tu_at' : null,
            ]));
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
