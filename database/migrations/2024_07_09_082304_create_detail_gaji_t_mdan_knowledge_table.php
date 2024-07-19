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
        Schema::create('detail_gaji_t_mdan_knowledge', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('detailgajitm_id')->nullable();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->integer('gaji_tm')->nullable();
            $table->integer('cadangan_tfknowledge')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_gaji_t_mdan_knowledge');
    }
};
