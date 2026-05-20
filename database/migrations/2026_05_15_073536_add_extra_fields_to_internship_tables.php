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
        Schema::table('internship_students', function (Blueprint $table) {
            $table->string('department')->nullable()->after('educational_qualification');
        });

        Schema::table('internship_certificates', function (Blueprint $table) {
            $table->string('internship_title')->nullable()->after('certificate_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('internship_students', function (Blueprint $table) {
            $table->dropColumn('department');
        });

        Schema::table('internship_certificates', function (Blueprint $table) {
            $table->dropColumn('internship_title');
        });
    }
};
