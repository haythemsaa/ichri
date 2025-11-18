<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Promotion;
use App\Models\Product;
use App\Models\Category;
use App\Models\PromotionUsage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class PromotionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'phone' => '+21698123456',
            'is_verified' => true,
            'is_active' => true,
        ]);

        $this->token = JWTAuth::fromUser($this->user);
    }

    /** @test */
    public function it_can_list_active_promotions()
    {
        // Create active promotions
        Promotion::factory()->count(3)->create([
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        // Create inactive promotion
        Promotion::factory()->create([
            'is_active' => false,
        ]);

        // Create expired promotion
        Promotion::factory()->create([
            'is_active' => true,
            'start_date' => now()->subDays(10),
            'end_date' => now()->subDays(1),
        ]);

        $response = $this->getJson('/api/v1/promotions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.promotions')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'promotions' => [
                        '*' => [
                            'id',
                            'name',
                            'type',
                            'discount_value',
                            'start_date',
                            'end_date',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_list_featured_promotions()
    {
        Promotion::factory()->count(2)->create([
            'is_active' => true,
            'is_featured' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        Promotion::factory()->create([
            'is_active' => true,
            'is_featured' => false,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/v1/promotions/featured');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data.promotions');

        $promotions = $response->json('data.promotions');
        foreach ($promotions as $promotion) {
            $this->assertTrue($promotion['is_featured']);
        }
    }

    /** @test */
    public function it_can_validate_promo_code()
    {
        $promotion = Promotion::factory()->create([
            'code' => 'RAMADAN2024',
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/validate', [
            'code' => 'RAMADAN2024',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'valid' => true,
                    'promotion' => [
                        'code' => 'RAMADAN2024',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_rejects_invalid_promo_code()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/validate', [
            'code' => 'INVALID_CODE',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_rejects_expired_promo_code()
    {
        $promotion = Promotion::factory()->create([
            'code' => 'EXPIRED2023',
            'is_active' => true,
            'start_date' => now()->subDays(30),
            'end_date' => now()->subDays(1),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/validate', [
            'code' => 'EXPIRED2023',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'data' => [
                    'valid' => false,
                ]
            ]);
    }

    /** @test */
    public function it_can_calculate_percentage_discount()
    {
        $promotion = Promotion::factory()->create([
            'type' => 'percentage',
            'discount_value' => 20, // 20%
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $cartItems = [
            ['product_id' => 1, 'quantity' => 2, 'unit_price' => 50],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => $cartItems,
            'order_amount' => 100.000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $discount = $response->json('data.discount_amount');
        $this->assertEquals(20.000, $discount); // 20% of 100
    }

    /** @test */
    public function it_can_calculate_fixed_amount_discount()
    {
        $promotion = Promotion::factory()->create([
            'type' => 'fixed_amount',
            'discount_value' => 15, // 15 TND
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => [],
            'order_amount' => 100.000,
        ]);

        $response->assertStatus(200);

        $discount = $response->json('data.discount_amount');
        $this->assertEquals(15.000, $discount);
    }

    /** @test */
    public function it_respects_minimum_purchase_requirement()
    {
        $promotion = Promotion::factory()->create([
            'type' => 'percentage',
            'discount_value' => 10,
            'min_purchase' => 50, // Minimum 50 TND
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => [],
            'order_amount' => 30.000, // Below minimum
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Order amount does not meet minimum purchase requirement',
            ]);
    }

    /** @test */
    public function it_enforces_usage_limit_per_user()
    {
        $promotion = Promotion::factory()->create([
            'type' => 'percentage',
            'discount_value' => 10,
            'usage_limit_per_user' => 1,
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        // User already used this promotion once
        PromotionUsage::factory()->create([
            'promotion_id' => $promotion->id,
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => [],
            'order_amount' => 100.000,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'You have reached the usage limit for this promotion',
            ]);
    }

    /** @test */
    public function it_enforces_total_usage_limit()
    {
        $promotion = Promotion::factory()->create([
            'type' => 'percentage',
            'discount_value' => 10,
            'usage_limit_total' => 10,
            'usage_count' => 10, // Already used 10 times
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => [],
            'order_amount' => 100.000,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
            ]);
    }

    /** @test */
    public function it_can_apply_bogo_promotion()
    {
        $product = Product::factory()->create(['price' => 10.000]);

        $promotion = Promotion::factory()->create([
            'type' => 'bogo',
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $promotion->products()->attach($product->id);

        $cartItems = [
            [
                'product_id' => $product->id,
                'quantity' => 4, // Buy 4, get 2 free
                'unit_price' => 10.000,
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => $cartItems,
            'order_amount' => 40.000,
        ]);

        $response->assertStatus(200);

        // Should get 2 items free (floor(4/2) = 2)
        $discount = $response->json('data.discount_amount');
        $this->assertEquals(20.000, $discount); // 2 * 10 TND
    }

    /** @test */
    public function it_can_apply_bundle_promotion()
    {
        $product = Product::factory()->create(['price' => 15.000]);

        $promotion = Promotion::factory()->create([
            'type' => 'bundle',
            'config' => ['buy' => 2, 'get' => 1], // Buy 2, get 1 free
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $promotion->products()->attach($product->id);

        $cartItems = [
            [
                'product_id' => $product->id,
                'quantity' => 6, // 2 sets of (buy 2, get 1)
                'unit_price' => 15.000,
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', [
            'promotion_id' => $promotion->id,
            'cart_items' => $cartItems,
            'order_amount' => 90.000,
        ]);

        $response->assertStatus(200);

        // Should get 2 items free (floor(6/(2+1)) = 2 sets, 2*1 free items)
        $discount = $response->json('data.discount_amount');
        $this->assertEquals(30.000, $discount); // 2 * 15 TND
    }

    /** @test */
    public function it_applies_promotion_to_specific_products()
    {
        $product1 = Product::factory()->create(['price' => 20.000]);
        $product2 = Product::factory()->create(['price' => 30.000]);

        $promotion = Promotion::factory()->create([
            'type' => 'percentage',
            'discount_value' => 50, // 50% off
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $promotion->products()->attach($product1->id);

        $cartItems = [
            ['product_id' => $product1->id, 'quantity' => 1, 'unit_price' => 20.000],
            ['product_id' => $product2->id, 'quantity' => 1, 'unit_price' => 30.000],
        ];

        // The promotion should only apply to product1
        // Total: 50 TND, but only 20 TND is eligible for 50% discount
        // Expected discount: 10 TND (50% of 20)
    }

    /** @test */
    public function it_applies_promotion_to_specific_categories()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'price' => 25.000,
        ]);

        $promotion = Promotion::factory()->create([
            'type' => 'percentage',
            'discount_value' => 20, // 20% off
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $promotion->categories()->attach($category->id);
    }

    /** @test */
    public function it_requires_authentication_for_promo_operations()
    {
        $response = $this->postJson('/api/v1/promotions/validate', []);
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/promotions/apply', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function promotions_list_is_public()
    {
        Promotion::factory()->count(3)->create([
            'is_active' => true,
            'start_date' => now()->subDays(1),
            'end_date' => now()->addDays(7),
        ]);

        $response = $this->getJson('/api/v1/promotions');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.promotions');
    }

    /** @test */
    public function it_validates_required_fields_for_apply()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/promotions/apply', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['promotion_id', 'order_amount']);
    }
}
