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
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->string('title_main')->default('CERTIFICATE')->after('name');
            $table->string('title_sub')->default('OF INTERNSHIP')->after('title_main');
            $table->string('left_signature_title')->default('CEO')->after('show_certificate_id');
            $table->string('right_signature_title')->default('Program Coordinator')->after('left_signature_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn(['title_main', 'title_sub', 'left_signature_title', 'right_signature_title']);
        });
    }
};
