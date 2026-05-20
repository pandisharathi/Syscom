<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status')->default('present')->comment('present, absent');
            $table->timestamps();

            $table->unique(['attendance_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
