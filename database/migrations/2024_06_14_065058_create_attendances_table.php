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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('karyawan_id')->nullable();
            $table->date('date')->nullable();
            $table->string('shift')->nullable();
            $table->time('schedule_in')->nullable();
            $table->time('schedule_out')->nullable();
            $table->string('attendance_code')->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->time('overtime_checkin')->nullable();
            $table->time('overtime_checkout')->nullable();
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
