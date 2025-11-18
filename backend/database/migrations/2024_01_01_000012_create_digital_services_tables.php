<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des services digitaux (top-up, factures)
        Schema::create('digital_service_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // L'épicier qui vend le service
            $table->string('transaction_ref')->unique(); // Référence unique
            $table->enum('service_type', [
                'mobile_topup',      // Recharge mobile
                'electricity_bill',  // Facture STEG
                'water_bill',        // Facture SONEDE
                'internet_bill',     // Facture internet
                'phone_bill',        // Facture téléphone fixe
                'game_card',         // Carte de jeu
                'other'
            ]);
            $table->string('provider'); // Ooredoo, Orange, Tunisie Telecom, STEG, SONEDE
            $table->string('recipient_number'); // Numéro du client final
            $table->decimal('amount', 10, 3);
            $table->decimal('commission', 10, 3)->default(0); // Commission pour l'épicier
            $table->decimal('cost', 10, 3)->nullable(); // Coût pour ichri.tn
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('external_ref')->nullable(); // Référence du provider
            $table->text('response_data')->nullable(); // Réponse du provider
            $table->text('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'service_type', 'status']);
            $table->index('transaction_ref');
            $table->index('created_at');
        });

        // Table de configuration des commissions
        Schema::create('digital_service_commissions', function (Blueprint $table) {
            $table->id();
            $table->enum('service_type', [
                'mobile_topup',
                'electricity_bill',
                'water_bill',
                'internet_bill',
                'phone_bill',
                'game_card',
                'other'
            ]);
            $table->string('provider')->nullable(); // Null = tous les providers
            $table->decimal('commission_percentage', 5, 2)->default(0); // ex: 3.00 pour 3%
            $table->decimal('commission_fixed', 10, 3)->default(0); // Commission fixe
            $table->decimal('min_commission', 10, 3)->default(0);
            $table->decimal('max_commission', 10, 3)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['service_type', 'provider', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('digital_service_commissions');
        Schema::dropIfExists('digital_service_transactions');
    }
};
