<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('internship_student_id')->constrained('internship_students')->cascadeOnDelete();
            $table->foreignId('certificate_template_id')->nullable()->constrained('certificate_templates')->nullOnDelete();
            $table->string('certificate_number')->unique();
            $table->string('encrypted_token')->unique();
            $table->date('issue_date');
            $table->date('completion_date')->nullable();
            $table->json('custom_fields')->nullable();
            $table->string('status')->default('active');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_certificates');
    }
};
