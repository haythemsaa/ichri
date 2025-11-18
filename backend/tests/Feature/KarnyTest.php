<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\KarnyCustomer;
use App\Models\KarnyTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class KarnyTest extends TestCase
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
    public function it_can_create_a_karny_customer()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/v1/karny/customers', [
            'name' => 'Ahmed Ben Ali',
            'phone' => '+21698765432',
            'credit_limit' => 500.000,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'customer' => [
                        'id',
                        'name',
                        'phone',
                        'qr_code',
                        'credit_limit',
                        'current_balance',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('karny_customers', [
            'user_id' => $this->user->id,
            'name' => 'Ahmed Ben Ali',
            'phone' => '+21698765432',
        ]);
    }

    /** @test */
    public function it_generates_unique_qr_code_for_customer()
    {
        $customer1 = KarnyCustomer::factory()->create(['user_id' => $this->user->id]);
        $customer2 = KarnyCustomer::factory()->create(['user_id' => $this->user->id]);

        $this->assertNotEquals($customer1->qr_code, $customer2->qr_code);
    }

    /** @test */
    public function it_can_list_all_karny_customers()
    {
        KarnyCustomer::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/karny/customers');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data.customers');
    }

    /** @test */
    public function it_can_show_karny_customer_details()
    {
        $customer = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Mohamed Trabelsi',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/v1/karny/customers/{$customer->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'customer' => [
                        'name' => 'Mohamed Trabelsi',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_can_add_credit_to_customer()
    {
        $customer = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'current_balance' => 0,
            'credit_limit' => 500,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/karny/customers/{$customer->id}/credit", [
            'amount' => 50.000,
            'description' => 'Pain, lait, cigarettes',
            'due_date' => now()->addDays(7)->format('Y-m-d'),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('karny_transactions', [
            'karny_customer_id' => $customer->id,
            'type' => 'credit',
            'amount' => 50.000,
            'status' => 'pending',
        ]);

        $customer->refresh();
        $this->assertEquals(50.000, $customer->current_balance);
    }

    /** @test */
    public function it_prevents_exceeding_credit_limit()
    {
        $customer = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'current_balance' => 450,
            'credit_limit' => 500,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/karny/customers/{$customer->id}/credit", [
            'amount' => 100.000,
            'description' => 'Test',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Credit limit exceeded',
            ]);
    }

    /** @test */
    public function it_can_add_payment_to_customer()
    {
        $customer = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'current_balance' => 100,
        ]);

        $transaction = KarnyTransaction::factory()->create([
            'karny_customer_id' => $customer->id,
            'user_id' => $this->user->id,
            'type' => 'credit',
            'amount' => 100,
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/karny/customers/{$customer->id}/payment", [
            'amount' => 50.000,
            'transaction_id' => $transaction->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('karny_transactions', [
            'karny_customer_id' => $customer->id,
            'type' => 'payment',
            'amount' => 50.000,
            'status' => 'completed',
        ]);

        $customer->refresh();
        $this->assertEquals(50.000, $customer->current_balance);
    }

    /** @test */
    public function it_can_get_karny_statistics()
    {
        $customer1 = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'current_balance' => 100,
        ]);

        $customer2 = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'current_balance' => 200,
        ]);

        KarnyTransaction::factory()->create([
            'karny_customer_id' => $customer1->id,
            'user_id' => $this->user->id,
            'type' => 'credit',
            'due_date' => now()->subDays(1),
            'status' => 'pending',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/karny/statistics');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_customers',
                    'total_credit_outstanding',
                    'total_overdue',
                    'overdue_count',
                ]
            ]);
    }

    /** @test */
    public function it_can_search_customer_by_qr_code()
    {
        $customer = KarnyCustomer::factory()->create([
            'user_id' => $this->user->id,
            'qr_code' => 'KARNY-TEST-12345',
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/karny/qr/KARNY-TEST-12345');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'customer' => [
                        'qr_code' => 'KARNY-TEST-12345',
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_requires_authentication_for_karny_endpoints()
    {
        $response = $this->getJson('/api/v1/karny/customers');
        $response->assertStatus(401);

        $response = $this->postJson('/api/v1/karny/customers', []);
        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_credit_amount_is_positive()
    {
        $customer = KarnyCustomer::factory()->create(['user_id' => $this->user->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson("/api/v1/karny/customers/{$customer->id}/credit", [
            'amount' => -50.000,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_only_shows_own_customers()
    {
        $otherUser = User::factory()->create();
        $ownCustomer = KarnyCustomer::factory()->create(['user_id' => $this->user->id]);
        $otherCustomer = KarnyCustomer::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/v1/karny/customers');

        $response->assertStatus(200);
        $customers = $response->json('data.customers');

        $this->assertCount(1, $customers);
        $this->assertEquals($ownCustomer->id, $customers[0]['id']);
    }
}
