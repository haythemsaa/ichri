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
        // Team Members table
        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('role', ['owner', 'admin', 'manager', 'employee', 'viewer'])->default('employee');
            $table->json('permissions')->nullable();
            $table->decimal('spending_limit', 10, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();
            
            $table->unique(['account_id', 'user_id']);
            $table->index('account_id');
            $table->index('user_id');
            $table->index('role');
        });

        // Team Invitations table
        Schema::create('team_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('users')->onDelete('cascade');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->enum('role', ['admin', 'manager', 'employee', 'viewer'])->default('employee');
            $table->json('permissions')->nullable();
            $table->string('token', 64)->unique();
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->index('token');
            $table->index(['account_id', 'email']);
        });

        // Team Activity Log
        Schema::create('team_activity_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('team_member_id')->constrained('team_members')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'created_at']);
            $table->index('user_id');
            $table->index(['entity_type', 'entity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_activity_log');
        Schema::dropIfExists('team_invitations');
        Schema::dropIfExists('team_members');
    }
};
