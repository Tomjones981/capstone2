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
        Schema::create('paslip', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->unsignedBigInteger('payroll_id');
            $table->string('pag_ibig_contribution');
            $table->string('philhealth_contribution');
            $table->string('SSS_contribution');
            $table->string('SSS_loan_deduction');
            $table->string('pag_ibig_loan_deduction');
            $table->date('date');
            $table->timestamps();

            $table->foreign('faculty_id')->references('id')->on('faculty')->onDelete('cascade');
            $table->foreign('payroll_id')->references('id')->on('payroll')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paslip');
    }
};
