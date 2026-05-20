<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('internship_batch_id')->constrained('internship_batches')->cascadeOnDelete();
            $table->date('attendance_date');
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['internship_batch_id', 'attendance_date'], 'internship_att_batch_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_attendances');
    }
};
