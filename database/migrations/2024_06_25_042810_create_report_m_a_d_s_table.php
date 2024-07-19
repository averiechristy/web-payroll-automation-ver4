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
        Schema::create('report_m_a_d_s', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mad_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->date('tanggal_lembur')->nullable();
            $table->string('jenis_hari')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->integer('jumlah_jam_lembur')->nullable();
            $table->integer('jam_pertama')->nullable();
            $table->integer('jam_kedua')->nullable();
            $table->integer('jam_ketiga')->nullable();
            $table->integer('jam_keempat')->nullable();
            $table->integer('biaya_jam_pertama')->nullable();
            $table->integer('biaya_jam_kedua')->nullable();
            $table->integer('biaya_jam_ketiga')->nullable();
            $table->integer('biaya_jam_keempat')->nullable();
            $table->integer('subtotal')->nullable();
            $table->integer('management_fee')->nullable();
            $table->integer('management_fee_amount')->nullable();
            $table->integer('total_sebelum_ppn')->nullable();
            $table->string('keterangan_lembur')->nullable();
            $table->string('keterangan_perbaikan')->nullable();
            $table->integer('gaji')->nullable();
            $table->integer('tujangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_m_a_d_s');
    }
};
