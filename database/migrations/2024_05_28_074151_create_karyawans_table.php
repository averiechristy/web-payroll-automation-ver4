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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('NIK')->nullable();
            $table->string('payroll_code')->nullable();
            $table->string('nama_karyawan')->nullable();
            $table->string('NIK_KTP')->nullable();
            $table->unsignedBigInteger('penempatan_id')->nullable();
            $table->unsignedBigInteger('posisi_id')->nullable();
            $table->integer('upah_pokok')->nullable();
            $table->integer('tunjangan_spv')->nullable();
            $table->integer('management_fee')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('leader')->nullable();
            $table->string('status_karyawan')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
