<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('set null');
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->decimal('unit_price', 10, 3);
            $table->decimal('pack_price', 10, 3)->nullable();
            $table->decimal('carton_price', 10, 3)->nullable();
            $table->integer('pack_quantity')->default(1);
            $table->integer('carton_quantity')->default(1);
            $table->decimal('cost_price', 10, 3)->nullable();
            $table->decimal('wholesale_price', 10, 3)->nullable();
            $table->decimal('retail_price', 10, 3)->nullable();
            $table->string('currency', 3)->default('TND');
            $table->string('unit', 50)->default('piece');
            $table->decimal('weight', 8, 2)->nullable();
            $table->json('dimensions')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(10);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_bestseller')->default(false);
            $table->boolean('is_on_sale')->default(false);
            $table->decimal('sale_price', 10, 3)->nullable();
            $table->timestamp('sale_start_date')->nullable();
            $table->timestamp('sale_end_date')->nullable();
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->json('nutritional_info')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('allergens')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('orders_count')->default(0);
            $table->decimal('rating_average', 3, 2)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'brand_id', 'is_active']);
            $table->index(['sku', 'barcode']);
            $table->fullText(['name', 'description', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
