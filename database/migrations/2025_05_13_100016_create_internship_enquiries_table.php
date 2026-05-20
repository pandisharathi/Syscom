<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internship_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('internship_course_id')->nullable()->constrained('internship_courses')->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('contact_number')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('educational_qualification')->nullable();
            $table->string('college_name')->nullable();
            $table->string('gender')->nullable();
            $table->string('interested_course_text')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('preferred_timing')->nullable();
            $table->text('message')->nullable();
            $table->string('resume_path')->nullable();
            $table->string('status')->default('new')->comment('new, contacted, interested, enrolled, rejected');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internship_enquiries');
    }
};
