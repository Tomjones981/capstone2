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
        Schema::create('employment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('faculty_id');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract', 'terminated']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'inactive' ]);
            $table->string('note');
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
        Schema::dropIfExists('employment');
    }
};
