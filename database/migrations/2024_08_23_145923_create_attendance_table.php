<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->string('date');
            $table->string('schedule')->nullable();
            $table->string('remarks')->nullable();
            $table->string('absent')->nullable();
            $table->string('time_in')->nullable();
            $table->string('breaks')->nullable();
            $table->string('time_out')->nullable();
            $table->string('hours_worked')->nullable();
            $table->string('late')->nullable();
            $table->string('overbreak')->nullable();
            $table->string('ot')->nullable();
            $table->string('undertime')->nullable();
            $table->string('night_differential')->nullable();
            $table->string('nd_ot')->nullable();
            $table->timestamps();

            $table->foreign('faculty_id')->references('id')->on('faculty')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance');
    }
};
