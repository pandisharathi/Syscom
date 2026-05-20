<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batch_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('batch_id')->constrained('batches')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamps();

            $table->unique(['batch_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('batch_student');
    }
};
