<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->comment('Public enquiry URL segment');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo')->nullable();
            $table->string('subscription_plan')->nullable();
            $table->date('subscription_starts_at')->nullable();
            $table->date('subscription_ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('enquiry_enabled')->default(true);
            $table->json('settings')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};
