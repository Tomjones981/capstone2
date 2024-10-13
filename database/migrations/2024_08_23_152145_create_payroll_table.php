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
        Schema::create('payroll', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('faculty_rate_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('attendance_id');
            $table->decimal('total_work_hours', 10, 2 );
            $table->string('overloads' );
            $table->decimal('overtime_pay', 10, 2 );
            $table->decimal('bonus', 10, 2 );
            $table->decimal('allowance', 10, 2 );
            $table->string('deduction' );
            $table->date('start_date' );
            $table->date('end_date' );
            $table->timestamps();

            $table->foreign('faculty_id')->references('id')->on('faculty')->onDelete('cascade');
            $table->foreign('faculty_rate_id')->references('id')->on('faculty_rates')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('unit')->onDelete('cascade');
            $table->foreign('attendance_id')->references('id')->on('attendance')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll');
    }
};
