<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->string('indeks_kegiatan', 50)->nullable()->after('kode');
        });

        DB::table('jenis_surat')->whereIn('slug', [
            'surat-tugas-kegiatan-kuliah',
            'surat-tugas-pengembangan-rps',
            'surat-tugas-pengembangan-modul',
        ])->update(['indeks_kegiatan' => 'pendidikan']);

        DB::table('jenis_surat')->whereIn('slug', [
            'surat-tugas-penulisan-publikasi-artikel-ilmiah',
            'surat-tugas-penulisan-laporan-penelitian',
            'surat-tugas-penulisan-dan-publikasi-penelitian',
            'surat-tugas-penulisan-buku-ajar',
            'surat-tugas-pendaftaran-hak-kekayaan-intelektual',
        ])->update(['indeks_kegiatan' => 'penelitian']);

        DB::table('jenis_surat')->whereIn('slug', [
            'surat-tugas-pelatihan',
            'surat-tugas-narasumber',
        ])->update(['indeks_kegiatan' => 'pengabdian']);

        DB::table('jenis_surat')->where('slug', 'surat-tugas-juri-lomba')
            ->update(['indeks_kegiatan' => 'kemahasiswaan']);

    }

    public function down(): void
    {
        Schema::table('jenis_surat', function (Blueprint $table) {
            $table->dropColumn('indeks_kegiatan');
        });
    }
};
