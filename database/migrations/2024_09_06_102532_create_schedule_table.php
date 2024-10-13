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
        Schema::create('schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->date('date_from');
            $table->date('date_to');
            $table->time('time_start');
            $table->time('time_end');
            $table->enum('loading', [  'regular', 'overload']);
            $table->timestamps();

            $table->foreign('faculty_id')
            ->references('id')
            ->on('faculty')
            ->onDelete('cascade'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule');
    }
};
