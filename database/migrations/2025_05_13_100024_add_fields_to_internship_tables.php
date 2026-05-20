<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('internship_courses', function (Blueprint $table) {
            $table->string('course_image')->nullable()->after('description');
            $table->date('start_date')->nullable()->after('course_image');
            $table->date('end_date')->nullable()->after('start_date');
        });

        Schema::table('internship_batches', function (Blueprint $table) {
            $table->unsignedSmallInteger('number_of_days')->nullable()->after('capacity');
        });

        Schema::table('internship_students', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('educational_qualification')->nullable()->after('date_of_birth');
            $table->string('college_name')->nullable()->after('educational_qualification');
            $table->string('city')->nullable()->after('address');
            $table->string('state')->nullable()->after('city');
            $table->string('pincode')->nullable()->after('state');
            $table->string('photo')->nullable()->after('pincode');
            $table->date('joining_date')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('internship_courses', function (Blueprint $table) {
            $table->dropColumn(['course_image', 'start_date', 'end_date']);
        });

        Schema::table('internship_batches', function (Blueprint $table) {
            $table->dropColumn('number_of_days');
        });

        Schema::table('internship_students', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number', 'date_of_birth', 'educational_qualification',
                'college_name', 'city', 'state', 'pincode', 'photo', 'joining_date',
            ]);
        });
    }
};
