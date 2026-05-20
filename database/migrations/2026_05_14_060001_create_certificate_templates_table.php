<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name');
            $table->string('background_image')->nullable();
            $table->string('logo_position')->default('top-center');
            $table->string('primary_color')->default('#1e3a5f');
            $table->string('secondary_color')->default('#c9a84c');
            $table->string('accent_color')->default('#d4af37');
            $table->string('font_family')->default('Georgia, serif');
            $table->text('border_style')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_templates');
    }
};
