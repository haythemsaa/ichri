<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyReward;
use App\Models\LoyaltyRedemption;
use App\Models\LoyaltyTransaction;

class LoyaltyProgramTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'store_name' => 'Test Store',
            'is_verified' => true,
        ]);
    }

    /** @test */
    public function user_can_get_loyalty_points()
    {
        LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 1500,
            'lifetime_points' => 6000,
            'tier' => 'gold',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/points');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'points',
                    'lifetime_points',
                    'tier',
                    'tier_multiplier',
                    'next_tier',
                    'points_to_next_tier',
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'points' => 1500,
                    'lifetime_points' => 6000,
                    'tier' => 'gold',
                    'tier_multiplier' => 1.5,
                ]
            ]);
    }

    /** @test */
    public function loyalty_points_are_auto_created_if_not_exist()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/points');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'points' => 0,
                    'lifetime_points' => 0,
                    'tier' => 'bronze',
                ]
            ]);

        $this->assertDatabaseHas('loyalty_points', [
            'user_id' => $this->user->id,
            'tier' => 'bronze',
        ]);
    }

    /** @test */
    public function user_can_get_loyalty_transactions()
    {
        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 500,
            'lifetime_points' => 500,
            'tier' => 'bronze',
        ]);

        LoyaltyTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'earn',
            'points' => 100,
            'balance_after' => 500,
            'description' => 'Order #123',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/transactions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'type',
                            'points',
                            'balance_after',
                            'description',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_filter_transactions_by_type()
    {
        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 500,
        ]);

        LoyaltyTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'earn',
            'points' => 100,
            'balance_after' => 100,
        ]);

        LoyaltyTransaction::create([
            'user_id' => $this->user->id,
            'type' => 'redeem',
            'points' => -50,
            'balance_after' => 50,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/transactions?type=earn');

        $response->assertStatus(200);
        $transactions = $response->json('data.data');

        $this->assertCount(1, $transactions);
        $this->assertEquals('earn', $transactions[0]['type']);
    }

    /** @test */
    public function user_can_add_points()
    {
        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 0,
            'lifetime_points' => 0,
            'tier' => 'bronze',
        ]);

        $earnedPoints = $loyaltyPoint->addPoints(100, 'earn', 'Order', 123, 'Order #123');

        $loyaltyPoint->refresh();

        $this->assertEquals(100, $loyaltyPoint->points);
        $this->assertEquals(100, $loyaltyPoint->lifetime_points);
        $this->assertEquals(100, $earnedPoints);

        $this->assertDatabaseHas('loyalty_transactions', [
            'user_id' => $this->user->id,
            'type' => 'earn',
            'points' => 100,
            'balance_after' => 100,
        ]);
    }

    /** @test */
    public function points_are_multiplied_by_tier()
    {
        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 0,
            'lifetime_points' => 10000,
            'tier' => 'platinum', // 2x multiplier
        ]);

        $earnedPoints = $loyaltyPoint->addPoints(100, 'earn', 'Order', 123);

        $loyaltyPoint->refresh();

        $this->assertEquals(200, $earnedPoints); // 100 * 2
        $this->assertEquals(200, $loyaltyPoint->points);
    }

    /** @test */
    public function tier_upgrades_automatically()
    {
        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 0,
            'lifetime_points' => 900,
            'tier' => 'bronze',
        ]);

        // Add 100 points to reach 1000 (silver threshold)
        $loyaltyPoint->addPoints(100, 'earn');

        $loyaltyPoint->refresh();

        $this->assertEquals('silver', $loyaltyPoint->tier);
    }

    /** @test */
    public function user_can_redeem_points()
    {
        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 500,
            'lifetime_points' => 500,
            'tier' => 'bronze',
        ]);

        $success = $loyaltyPoint->redeemPoints(200, 1, 'Redeemed reward');

        $loyaltyPoint->refresh();

        $this->assertTrue($success);
        $this->assertEquals(300, $loyaltyPoint->points);

        $this->assertDatabaseHas('loyalty_transactions', [
            'user_id' => $this->user->id,
            'type' => 'redeem',
            'points' => -200,
            'balance_after' => 300,
        ]);
    }

    /** @test */
    public function cannot_redeem_more_points_than_available()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient points');

        $loyaltyPoint = LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 100,
            'lifetime_points' => 100,
            'tier' => 'bronze',
        ]);

        $loyaltyPoint->redeemPoints(200);
    }

    /** @test */
    public function user_can_get_all_rewards()
    {
        LoyaltyReward::create([
            'name' => 'Free Delivery',
            'type' => 'free_delivery',
            'points_cost' => 100,
            'is_active' => true,
        ]);

        LoyaltyReward::create([
            'name' => '10 TND Discount',
            'type' => 'discount',
            'points_cost' => 500,
            'config' => ['amount' => 10],
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/rewards');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'rewards' => [
                        '*' => [
                            'id',
                            'name',
                            'type',
                            'points_cost',
                            'can_afford',
                            'points_needed',
                        ]
                    ],
                    'user_points',
                ]
            ]);
    }

    /** @test */
    public function rewards_show_affordability_status()
    {
        LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 300,
        ]);

        LoyaltyReward::create([
            'name' => 'Cheap Reward',
            'type' => 'discount',
            'points_cost' => 200,
            'is_active' => true,
        ]);

        LoyaltyReward::create([
            'name' => 'Expensive Reward',
            'type' => 'discount',
            'points_cost' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/rewards');

        $rewards = $response->json('data.rewards');

        $cheapReward = collect($rewards)->firstWhere('name', 'Cheap Reward');
        $expensiveReward = collect($rewards)->firstWhere('name', 'Expensive Reward');

        $this->assertTrue($cheapReward['can_afford']);
        $this->assertEquals(0, $cheapReward['points_needed']);

        $this->assertFalse($expensiveReward['can_afford']);
        $this->assertEquals(200, $expensiveReward['points_needed']);
    }

    /** @test */
    public function user_can_redeem_reward()
    {
        LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 600,
            'lifetime_points' => 600,
            'tier' => 'bronze',
        ]);

        $reward = LoyaltyReward::create([
            'name' => '10 TND Discount',
            'type' => 'discount',
            'points_cost' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/loyalty/rewards/{$reward->id}/redeem");

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'redemption',
                    'remaining_points',
                ]
            ]);

        $this->assertDatabaseHas('loyalty_redemptions', [
            'user_id' => $this->user->id,
            'reward_id' => $reward->id,
            'points_spent' => 500,
            'status' => 'approved',
        ]);

        $loyaltyPoint = LoyaltyPoint::where('user_id', $this->user->id)->first();
        $this->assertEquals(100, $loyaltyPoint->points);
    }

    /** @test */
    public function cannot_redeem_reward_with_insufficient_points()
    {
        LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 100,
        ]);

        $reward = LoyaltyReward::create([
            'name' => '10 TND Discount',
            'type' => 'discount',
            'points_cost' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/loyalty/rewards/{$reward->id}/redeem");

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Points insuffisants',
            ]);
    }

    /** @test */
    public function user_can_get_redemptions()
    {
        $reward = LoyaltyReward::create([
            'name' => 'Test Reward',
            'type' => 'discount',
            'points_cost' => 100,
            'is_active' => true,
        ]);

        LoyaltyRedemption::create([
            'user_id' => $this->user->id,
            'reward_id' => $reward->id,
            'points_spent' => 100,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/redemptions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'reward_id',
                            'points_spent',
                            'status',
                            'redemption_code',
                            'reward',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_cancel_redemption()
    {
        LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 0,
            'lifetime_points' => 1000,
        ]);

        $redemption = LoyaltyRedemption::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'points_spent' => 500,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/loyalty/redemptions/{$redemption->id}/cancel");

        $response->assertStatus(200);

        $redemption->refresh();
        $this->assertEquals('cancelled', $redemption->status);

        // Points should be refunded
        $loyaltyPoint = LoyaltyPoint::where('user_id', $this->user->id)->first();
        $this->assertEquals(500, $loyaltyPoint->points);
    }

    /** @test */
    public function user_can_get_tier_information()
    {
        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/tiers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'tiers' => [
                        '*' => [
                            'name',
                            'threshold',
                            'multiplier',
                            'benefits',
                        ]
                    ],
                    'current_tier',
                    'lifetime_points',
                ]
            ]);

        $tiers = $response->json('data.tiers');
        $this->assertCount(4, $tiers);
    }

    /** @test */
    public function redemption_code_is_auto_generated()
    {
        $redemption = LoyaltyRedemption::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'points_spent' => 100,
            'status' => 'pending',
        ]);

        $this->assertNotNull($redemption->redemption_code);
        $this->assertEquals(12, strlen($redemption->redemption_code));
    }

    /** @test */
    public function redemption_expires_in_30_days_by_default()
    {
        $redemption = LoyaltyRedemption::create([
            'user_id' => $this->user->id,
            'reward_id' => 1,
            'points_spent' => 100,
            'status' => 'pending',
        ]);

        $this->assertNotNull($redemption->expires_at);
        $this->assertTrue($redemption->expires_at->isAfter(now()->addDays(29)));
        $this->assertTrue($redemption->expires_at->isBefore(now()->addDays(31)));
    }

    /** @test */
    public function reward_quantity_decrements_on_redemption()
    {
        LoyaltyPoint::create([
            'user_id' => $this->user->id,
            'points' => 1000,
        ]);

        $reward = LoyaltyReward::create([
            'name' => 'Limited Reward',
            'type' => 'product',
            'points_cost' => 500,
            'quantity_available' => 10,
            'is_active' => true,
        ]);

        $this->actingAs($this->user, 'api')
            ->postJson("/api/v1/loyalty/rewards/{$reward->id}/redeem");

        $reward->refresh();
        $this->assertEquals(9, $reward->quantity_available);
    }

    /** @test */
    public function inactive_rewards_are_not_shown()
    {
        LoyaltyReward::create([
            'name' => 'Active Reward',
            'type' => 'discount',
            'points_cost' => 100,
            'is_active' => true,
        ]);

        LoyaltyReward::create([
            'name' => 'Inactive Reward',
            'type' => 'discount',
            'points_cost' => 100,
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->user, 'api')
            ->getJson('/api/v1/loyalty/rewards');

        $rewards = $response->json('data.rewards');
        $this->assertCount(1, $rewards);
        $this->assertEquals('Active Reward', $rewards[0]['name']);
    }
}
