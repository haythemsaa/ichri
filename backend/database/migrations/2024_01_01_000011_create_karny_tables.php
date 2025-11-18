<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des clients finaux de l'épicier (customers of the grocer)
        Schema::create('karny_customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // L'épicier
            $table->string('name'); // Nom du client final
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('qr_code')->unique(); // QR code pour identifier le client
            $table->decimal('credit_limit', 10, 3)->default(0); // Limite de crédit
            $table->decimal('current_balance', 10, 3)->default(0); // Balance actuelle
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_active']);
            $table->index('qr_code');
        });

        // Table des transactions de crédit (achats à crédit)
        Schema::create('karny_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // L'épicier
            $table->foreignId('karny_customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['credit', 'payment']); // crédit = achat, payment = remboursement
            $table->decimal('amount', 10, 3);
            $table->text('description')->nullable();
            $table->timestamp('due_date')->nullable(); // Date d'échéance
            $table->enum('status', ['pending', 'paid', 'overdue'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable(); // cash, mobile, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['karny_customer_id', 'status']);
            $table->index('due_date');
        });

        // Table des rappels automatiques
        Schema::create('karny_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('karny_transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('karny_customer_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['sms', 'whatsapp', 'call'])->default('sms');
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['scheduled_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karny_reminders');
        Schema::dropIfExists('karny_transactions');
        Schema::dropIfExists('karny_customers');
    }
};
