<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('store_name')->nullable();
            $table->enum('store_type', ['epicerie', 'superette', 'mini_market', 'autre'])->default('epicerie');
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('region')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('store_photo')->nullable();
            $table->string('patente_number')->nullable();
            $table->string('cin_number')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('credit_score')->default(0);
            $table->decimal('credit_limit', 10, 3)->default(500.000);
            $table->decimal('credit_used', 10, 3)->default(0.000);
            $table->enum('credit_level', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->string('fcm_token')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['phone', 'is_active']);
            $table->index(['city', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
