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
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            $table->index('email', 'idx_users_email');
            $table->index('phone', 'idx_users_phone');
            $table->index('role', 'idx_users_role');
            $table->index(['email_verified_at', 'phone_verified_at'], 'idx_users_verified');
            $table->index('created_at', 'idx_users_created');
        });

        // Products table indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index('sku', 'idx_products_sku');
            $table->index('category_id', 'idx_products_category');
            $table->index('brand_id', 'idx_products_brand');
            $table->index('is_active', 'idx_products_active');
            $table->index(['is_active', 'stock_quantity'], 'idx_products_available');
            $table->index('created_at', 'idx_products_created');
        });

        // Orders table indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_number', 'idx_orders_number');
            $table->index('user_id', 'idx_orders_user');
            $table->index('status', 'idx_orders_status');
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index(['status', 'created_at'], 'idx_orders_status_date');
            $table->index('created_at', 'idx_orders_created');
            $table->index('updated_at', 'idx_orders_updated');
        });

        // Order items table indexes
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('order_id', 'idx_order_items_order');
            $table->index('product_id', 'idx_order_items_product');
            $table->index(['order_id', 'product_id'], 'idx_order_items_order_product');
        });

        // Cart items table indexes
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('user_id', 'idx_cart_items_user');
            $table->index('product_id', 'idx_cart_items_product');
            $table->index(['user_id', 'product_id'], 'idx_cart_items_user_product');
            $table->index('created_at', 'idx_cart_items_created');
        });

        // Categories table indexes
        Schema::table('categories', function (Blueprint $table) {
            $table->index('parent_id', 'idx_categories_parent');
            $table->index('is_active', 'idx_categories_active');
            $table->index('sort_order', 'idx_categories_sort');
        });

        // Loyalty points table indexes (if it exists)
        if (Schema::hasTable('loyalty_points')) {
            Schema::table('loyalty_points', function (Blueprint $table) {
                $table->index('user_id', 'idx_loyalty_points_user');
                $table->index('points', 'idx_loyalty_points_amount');
                $table->index('current_tier', 'idx_loyalty_points_tier');
                $table->index('tier_expires_at', 'idx_loyalty_points_tier_exp');
            });
        }

        // Loyalty transactions table indexes (if it exists)
        if (Schema::hasTable('loyalty_transactions')) {
            Schema::table('loyalty_transactions', function (Blueprint $table) {
                $table->index('loyalty_point_id', 'idx_loyalty_trans_point');
                $table->index('type', 'idx_loyalty_trans_type');
                $table->index('created_at', 'idx_loyalty_trans_created');
                $table->index(['loyalty_point_id', 'type'], 'idx_loyalty_trans_point_type');
            });
        }

        // Team members table indexes (if it exists)
        if (Schema::hasTable('team_members')) {
            Schema::table('team_members', function (Blueprint $table) {
                $table->index('account_id', 'idx_team_members_account');
                $table->index('user_id', 'idx_team_members_user');
                $table->index('role', 'idx_team_members_role');
                $table->index('is_active', 'idx_team_members_active');
            });
        }

        // Karny customers table indexes (if it exists)
        if (Schema::hasTable('karny_customers')) {
            Schema::table('karny_customers', function (Blueprint $table) {
                $table->index('store_id', 'idx_karny_customers_store');
                $table->index('qr_code', 'idx_karny_customers_qr');
                $table->index('status', 'idx_karny_customers_status');
                $table->index(['store_id', 'status'], 'idx_karny_customers_store_status');
            });
        }

        // Digital service transactions table indexes (if it exists)
        if (Schema::hasTable('digital_service_transactions')) {
            Schema::table('digital_service_transactions', function (Blueprint $table) {
                $table->index('user_id', 'idx_digital_trans_user');
                $table->index('service_type', 'idx_digital_trans_service');
                $table->index('status', 'idx_digital_trans_status');
                $table->index('created_at', 'idx_digital_trans_created');
                $table->index(['user_id', 'status'], 'idx_digital_trans_user_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_phone');
            $table->dropIndex('idx_users_role');
            $table->dropIndex('idx_users_verified');
            $table->dropIndex('idx_users_created');
        });

        // Products table
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_sku');
            $table->dropIndex('idx_products_category');
            $table->dropIndex('idx_products_brand');
            $table->dropIndex('idx_products_active');
            $table->dropIndex('idx_products_available');
            $table->dropIndex('idx_products_created');
        });

        // Orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_number');
            $table->dropIndex('idx_orders_user');
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_user_status');
            $table->dropIndex('idx_orders_status_date');
            $table->dropIndex('idx_orders_created');
            $table->dropIndex('idx_orders_updated');
        });

        // Order items table
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex('idx_order_items_order');
            $table->dropIndex('idx_order_items_product');
            $table->dropIndex('idx_order_items_order_product');
        });

        // Cart items table
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex('idx_cart_items_user');
            $table->dropIndex('idx_cart_items_product');
            $table->dropIndex('idx_cart_items_user_product');
            $table->dropIndex('idx_cart_items_created');
        });

        // Categories table
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex('idx_categories_parent');
            $table->dropIndex('idx_categories_active');
            $table->dropIndex('idx_categories_sort');
        });

        // Loyalty points table (if exists)
        if (Schema::hasTable('loyalty_points')) {
            Schema::table('loyalty_points', function (Blueprint $table) {
                $table->dropIndex('idx_loyalty_points_user');
                $table->dropIndex('idx_loyalty_points_amount');
                $table->dropIndex('idx_loyalty_points_tier');
                $table->dropIndex('idx_loyalty_points_tier_exp');
            });
        }

        // Loyalty transactions table (if exists)
        if (Schema::hasTable('loyalty_transactions')) {
            Schema::table('loyalty_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_loyalty_trans_point');
                $table->dropIndex('idx_loyalty_trans_type');
                $table->dropIndex('idx_loyalty_trans_created');
                $table->dropIndex('idx_loyalty_trans_point_type');
            });
        }

        // Team members table (if exists)
        if (Schema::hasTable('team_members')) {
            Schema::table('team_members', function (Blueprint $table) {
                $table->dropIndex('idx_team_members_account');
                $table->dropIndex('idx_team_members_user');
                $table->dropIndex('idx_team_members_role');
                $table->dropIndex('idx_team_members_active');
            });
        }

        // Karny customers table (if exists)
        if (Schema::hasTable('karny_customers')) {
            Schema::table('karny_customers', function (Blueprint $table) {
                $table->dropIndex('idx_karny_customers_store');
                $table->dropIndex('idx_karny_customers_qr');
                $table->dropIndex('idx_karny_customers_status');
                $table->dropIndex('idx_karny_customers_store_status');
            });
        }

        // Digital service transactions table (if exists)
        if (Schema::hasTable('digital_service_transactions')) {
            Schema::table('digital_service_transactions', function (Blueprint $table) {
                $table->dropIndex('idx_digital_trans_user');
                $table->dropIndex('idx_digital_trans_service');
                $table->dropIndex('idx_digital_trans_status');
                $table->dropIndex('idx_digital_trans_created');
                $table->dropIndex('idx_digital_trans_user_status');
            });
        }
    }
};
