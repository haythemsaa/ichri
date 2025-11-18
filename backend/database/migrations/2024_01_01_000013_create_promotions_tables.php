<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Table des promotions
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable()->unique(); // Code promo optionnel
            $table->text('description')->nullable();
            $table->enum('type', [
                'percentage',        // Réduction en pourcentage
                'fixed_amount',      // Réduction montant fixe
                'bogo',              // Buy One Get One
                'bundle',            // Bundle deal (2+1, 3+2, etc.)
                'tier_pricing',      // Prix dégressif par quantité
                'free_delivery',     // Livraison gratuite
                'loyalty_points',    // Points de fidélité multipliés
            ]);
            $table->json('config'); // Configuration spécifique au type
            $table->decimal('discount_value', 10, 3)->nullable(); // Valeur de la réduction
            $table->decimal('min_purchase', 10, 3)->nullable(); // Montant minimum d'achat
            $table->integer('usage_limit_per_user')->nullable(); // Limite par utilisateur
            $table->integer('usage_limit_total')->nullable(); // Limite totale
            $table->integer('usage_count')->default(0); // Compteur d'utilisation
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->text('terms')->nullable(); // Conditions d'utilisation
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index('code');
        });

        // Table de liaison promotions-produits
        Schema::create('promotion_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promotion_id', 'product_id']);
        });

        // Table de liaison promotions-catégories
        Schema::create('promotion_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['promotion_id', 'category_id']);
        });

        // Table d'utilisation des promotions
        Schema::create('promotion_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('discount_amount', 10, 3);
            $table->timestamps();

            $table->index(['promotion_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_usages');
        Schema::dropIfExists('promotion_categories');
        Schema::dropIfExists('promotion_products');
        Schema::dropIfExists('promotions');
    }
};
