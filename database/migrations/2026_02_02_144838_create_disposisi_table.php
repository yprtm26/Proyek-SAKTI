<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('surat_id')
                ->constrained('surat')
                ->cascadeOnDelete();

            $table->foreignId('from_user_id')
                ->constrained('users');

            $table->foreignId('to_user_id')
                ->constrained('users');

            $table->text('catatan')->nullable();

            $table->string('file_tambahan')->nullable();

            // PRIORITAS DISPOSISI
            $table->enum('prioritas', ['sangat_segera', 'segera', 'biasa'])
                ->default('biasa');

            // STATUS DISPOSISI
            $table->enum('status', ['terkirim', 'dibaca', 'selesai'])
                ->default('terkirim');

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi');
    }
};
