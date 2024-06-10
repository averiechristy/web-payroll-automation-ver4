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
        Schema::create('posisis', function (Blueprint $table) {
            $table->id();
            $table->string('kode_orange')->nullable();
            $table->string('jenis_pekerjaan')->nullable();
            $table->string('posisi')->nullable();
            $table->integer('standarisasi_upah')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posisis');
    }
    
};
