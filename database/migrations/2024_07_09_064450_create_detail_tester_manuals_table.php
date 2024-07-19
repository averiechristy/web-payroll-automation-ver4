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
        Schema::create('detail_tester_manuals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('testermanual_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->string('tanggallembur')->nullable();
            $table->float('totaljamlemburharikerja')->nullable();
            $table->float('totaljamlemburharilibur')->nullable();
            $table->integer('biayalemburrekap')->nullable();
            $table->integer('totalharikerja')->nullable();
            $table->integer('realisasiharikerja')->nullable();
            $table->integer('absen')->nullable();
            $table->integer('presentase_kehadiran')->nullable();
            $table->integer('biayalembur')->nullable();
            $table->integer('biayajasaperbulan')->nullable();
            $table->integer('realisasiinvoice')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_tester_manuals');
    }
};
