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
        Schema::create('report_payrolls', function (Blueprint $table) {
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->integer('adjusment_salary')->nullable();
            $table->integer('tunjangan')->nullable();
            $table->integer('uangsaku')->nullable();
            $table->integer('insentif')->nullable();
            $table->integer('gajipokok')->nullable();
            $table->integer('overtime')->nullable();
            $table->integer('total_allowance')->nullable();
            $table->integer('kompensasi')->nullable();
            $table->integer('total')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_payrolls');
    }
};
