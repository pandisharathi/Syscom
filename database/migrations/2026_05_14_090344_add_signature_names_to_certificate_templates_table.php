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
            $table->string('left_signature_name')->nullable()->after('left_signature_title');
            $table->string('right_signature_name')->nullable()->after('right_signature_title');
            $table->boolean('show_left_signature_name')->default(true)->after('left_signature_name');
            $table->boolean('show_right_signature_name')->default(true)->after('right_signature_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table) {
            $table->dropColumn([
                'left_signature_name', 
                'right_signature_name', 
                'show_left_signature_name', 
                'show_right_signature_name'
            ]);
        });
    }
};
