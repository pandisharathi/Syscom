<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('internship_attendance_id')->constrained('internship_attendances')->cascadeOnDelete();
            $table->foreignId('internship_student_id')->constrained('internship_students')->cascadeOnDelete();
            $table->string('status')->default('present');
            $table->timestamps();

            $table->unique(['internship_attendance_id', 'internship_student_id'], 'internship_att_detail_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_attendance_details');
    }
};
