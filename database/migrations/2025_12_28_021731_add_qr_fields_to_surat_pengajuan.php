<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $table->uuid('qr_token')->nullable()->unique()->after('tanggal_disetujui_final');
            $table->string('penandatangan')->nullable()->after('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('surat_pengajuan', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'penandatangan']);
        });
    }
};
