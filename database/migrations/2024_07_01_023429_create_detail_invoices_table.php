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
        Schema::create('detail_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->integer('gajipokok')->nullable();
            $table->integer('biayatransport')->nullable();
            $table->integer('bpjs_tk')->nullable();
            $table->integer('bpjs_kesehatan')->nullable();
            $table->integer('jaminan_pensiun')->nullable();
            $table->integer('management_fee')->nullable();
            $table->integer('total_biaya_jasa')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('lokasi')->nullable();
            $table->integer('tunjangan_jabatan')->nullable();
            $table->integer('insentif')->nullable();
            $table->integer('kompensasi')->nullable();
            $table->integer('rapelan_bpjs')->nullable();
            $table->integer('total_gaji')->nullable();
            $table->integer('biaya_aplikasi')->nullable();
            $table->integer('potongan')->nullable();
            $table->integer('total_pembayaran')->nullable();
            $table->integer('total_hari_kerja')->nullable();
            $table->integer('realisasi_hari_kerja')->nullable();
            $table->integer('absen')->nullable();
            $table->integer('presentase_kehadiran')->nullable();
            $table->integer('biaya_lembur')->nullable();
            $table->integer('realisasi_invoice')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_invoices');
    }
};
