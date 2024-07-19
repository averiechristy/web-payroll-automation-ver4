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
        Schema::create('detail_lemburs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lembur_id')->nullable();
            $table->float('total_jam_pertama_hari_kerja')->nullable();
            $table->float('total_jam_kedua_hari_kerja')->nullable();
            $table->float('total_jam_kedua_hari_libur')->nullable();
            $table->float('total_jam_ketiga_hari_libur')->nullable();
            $table->float('total_jam_keempat_hari_libur')->nullable();
            $table->integer('total_biaya_jam_pertama_hari_kerja')->nullable();
            $table->integer('total_biaya_jam_kedua_hari_kerja')->nullable();
            $table->integer('total_biaya_jam_kedua_hari_libur')->nullable();
            $table->integer('total_biaya_jam_ketiga_hari_libur')->nullable();
            $table->integer('total_biaya_jam_keempat_hari_libur')->nullable();
            $table->float('total_jam')->nullable();
            $table->integer('total_biaya_lembur')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_lemburs');
    }
};
