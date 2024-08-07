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
        Schema::create('konfigurasis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organisasi_id')->nullable();
            $table->unsignedBigInteger('penempatan_id')->nullable();
            $table->boolean('hitung_allowance')->nullable();
            $table->enum('perhitungan_payroll', ['kalender', 'harikerja'])->nullable();
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
        Schema::dropIfExists('konfigurasis');
    }
};
