<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DigitalServiceTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class DigitalServiceTest extends TestCase
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
    public function it_can_list_available_digital_services()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/digital-services/services');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'services' => [
                        '*' => [
                            'type',
                            'providers',
                            'commission_percentage',
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Verify that all expected service types are present
        $services = $response->json('data.services');
        $serviceTypes = array_column($services, 'type');

        $this->assertContains('mobile_topup', $serviceTypes);
        $this->assertContains('electricity_bill', $serviceTypes);
        $this->assertContains('water_bill', $serviceTypes);
    }

    /** @test */
    public function it_can_process_mobile_topup()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'mobile_topup',
            'provider' => 'ooredoo',
            'recipient_number' => '98123456',
            'amount' => 10.000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'transaction' => [
                        'transaction_ref',
                        'service_type',
                        'provider',
                        'amount',
                        'commission',
                        'status',
                    ]
                ]
            ]);

        // Verify commission is 3% for mobile topup
        $commission = $response->json('data.transaction.commission');
        $this->assertEquals(0.300, $commission); // 3% of 10 TND

        $this->assertDatabaseHas('digital_service_transactions', [
            'user_id' => $this->user->id,
            'service_type' => 'mobile_topup',
            'provider' => 'ooredoo',
            'amount' => 10.000,
        ]);
    }

    /** @test */
    public function it_can_process_electricity_bill_payment()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'electricity_bill',
            'provider' => 'steg',
            'recipient_number' => '12345678901',
            'amount' => 100.000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify commission is 1.5% for electricity bills
        $commission = $response->json('data.transaction.commission');
        $this->assertEquals(1.500, $commission); // 1.5% of 100 TND

        $this->assertDatabaseHas('digital_service_transactions', [
            'user_id' => $this->user->id,
            'service_type' => 'electricity_bill',
            'provider' => 'steg',
        ]);
    }

    /** @test */
    public function it_can_process_water_bill_payment()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'water_bill',
            'provider' => 'sonede',
            'recipient_number' => '98765432',
            'amount' => 50.000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify commission is 1.5% for water bills
        $commission = $response->json('data.transaction.commission');
        $this->assertEquals(0.750, $commission); // 1.5% of 50 TND
    }

    /** @test */
    public function it_generates_unique_transaction_reference()
    {
        $response1 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'mobile_topup',
            'provider' => 'ooredoo',
            'recipient_number' => '98123456',
            'amount' => 10.000,
        ]);

        $response2 = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'mobile_topup',
            'provider' => 'orange',
            'recipient_number' => '23456789',
            'amount' => 20.000,
        ]);

        $ref1 = $response1->json('data.transaction.transaction_ref');
        $ref2 = $response2->json('data.transaction.transaction_ref');

        $this->assertNotEquals($ref1, $ref2);
        $this->assertStringStartsWith('DS-', $ref1);
        $this->assertStringStartsWith('DS-', $ref2);
    }

    /** @test */
    public function it_can_get_digital_service_transaction_history()
    {
        // Create some transactions
        DigitalServiceTransaction::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/digital-services/history');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.transactions')
            ->assertJsonStructure([
                'success',
                'data' => [
                    'transactions' => [
                        '*' => [
                            'id',
                            'transaction_ref',
                            'service_type',
                            'provider',
                            'amount',
                            'commission',
                            'status',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_filter_history_by_service_type()
    {
        DigitalServiceTransaction::factory()->create([
            'user_id' => $this->user->id,
            'service_type' => 'mobile_topup',
        ]);

        DigitalServiceTransaction::factory()->create([
            'user_id' => $this->user->id,
            'service_type' => 'electricity_bill',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/digital-services/history?service_type=mobile_topup');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data.transactions');

        $transaction = $response->json('data.transactions.0');
        $this->assertEquals('mobile_topup', $transaction['service_type']);
    }

    /** @test */
    public function it_can_get_digital_service_statistics()
    {
        // Create transactions with commissions
        DigitalServiceTransaction::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 100,
            'commission' => 3.00,
            'status' => 'completed',
        ]);

        DigitalServiceTransaction::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 200,
            'commission' => 6.00,
            'status' => 'completed',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/digital-services/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_transactions',
                    'total_amount',
                    'total_commission_earned',
                    'by_service_type',
                ]
            ]);

        $stats = $response->json('data');
        $this->assertEquals(2, $stats['total_transactions']);
        $this->assertEquals(300, $stats['total_amount']);
        $this->assertEquals(9.00, $stats['total_commission_earned']);
    }

    /** @test */
    public function it_requires_authentication()
    {
        $response = $this->getJson('/api/v1/digital-services/services');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/digital-services/process', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_for_processing()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['service_type', 'provider', 'recipient_number', 'amount']);
    }

    /** @test */
    public function it_validates_service_type()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'invalid_service',
            'provider' => 'ooredoo',
            'recipient_number' => '98123456',
            'amount' => 10.000,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_amount_is_positive()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/digital-services/process', [
            'service_type' => 'mobile_topup',
            'provider' => 'ooredoo',
            'recipient_number' => '98123456',
            'amount' => -10.000,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_only_shows_own_transactions_in_history()
    {
        $otherUser = User::factory()->create();

        DigitalServiceTransaction::factory()->create(['user_id' => $this->user->id]);
        DigitalServiceTransaction::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/digital-services/history');

        $response->assertStatus(200);
        $transactions = $response->json('data.transactions');

        $this->assertCount(1, $transactions);
        $this->assertEquals($this->user->id, $transactions[0]['user_id']);
    }

    /** @test */
    public function it_can_handle_multiple_providers_for_mobile_topup()
    {
        $providers = ['ooredoo', 'orange', 'tunisie_telecom'];

        foreach ($providers as $provider) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
            ])->postJson('/api/v1/digital-services/process', [
                'service_type' => 'mobile_topup',
                'provider' => $provider,
                'recipient_number' => '98123456',
                'amount' => 10.000,
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);
        }

        $this->assertDatabaseCount('digital_service_transactions', 3);
    }
}
