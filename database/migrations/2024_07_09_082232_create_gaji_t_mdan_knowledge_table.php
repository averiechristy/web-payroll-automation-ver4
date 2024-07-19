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
        Schema::create('gaji_t_mdan_knowledge', function (Blueprint $table) {
            $table->id();
            $table->integer('bulan')->nullable();
            $table->integer('tahun')->nullable();
            $table->string('created_by')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gaji_t_mdan_knowledge');
    }
};