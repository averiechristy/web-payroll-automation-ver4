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
        Schema::create('penempatans', function (Blueprint $table) {
            $table->id();
            $table->string('wilayah')->nullable();
            $table->string('divisi')->nullable();
            $table->string('kcu_induk')->nullable();
            $table->string('nama_unit_kerja')->nullable();
            $table->string('kode_cabang_pembayaran')->nullable();
            $table->string('RCC_pembayaran')->nullable();
            $table->string('singkatan_divisi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penempatans');
    }
};
