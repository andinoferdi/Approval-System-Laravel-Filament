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
        Schema::create('pengajuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('kegiatan');
            $table->dateTime('jadwal_mulai');
            $table->dateTime('jadwal_akhir');
            $table->tinyInteger('status')->default(1)->comment('1=pending_manager, 2=pending_kadep, 3=pending_hrd, 4=disetujui, 5=ditolak');
            $table->string('dokumen_pendukung')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuans');
    }
};
