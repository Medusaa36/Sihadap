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
        Schema::create('absensi_models', function (Blueprint $table) {
            $table->id();
            $table->string('nip'); // relasi ke pegawai_model
            $table->timestamp('waktu_absen');
            $table->string('status')->default('Hadir'); // status umum
            $table->string('keterangan')->nullable(); // otomatis: Hadir Tepat Waktu / Hadir Terlambat
            $table->string('foto_bukti')->nullable();
            $table->timestamps();
            $table->foreign('nip')->references('nip')->on('pegawai_model')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_models');
    }
};
