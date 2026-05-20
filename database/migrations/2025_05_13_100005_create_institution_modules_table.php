<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institution_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('module_key');
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->unique(['institution_id', 'module_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institution_modules');
    }
};
