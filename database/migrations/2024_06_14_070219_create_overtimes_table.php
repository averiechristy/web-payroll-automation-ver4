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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->string('branch')->nullable();
            $table->date('date')->nullable();
            $table->float('overtime_duration')->nullable();
            $table->float('overtime_payment')->nullable();
            $table->float('overtime_rate')->nullable();
            $table->integer('total_payment')->nullable();
            $table->string('creaated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};
