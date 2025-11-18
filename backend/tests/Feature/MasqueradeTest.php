<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\MasqueradeSession;
use Spatie\Permission\Models\Role;

class MasqueradeTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $salesRep;
    protected $customer;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'admin', 'guard_name' => 'api']);
        Role::create(['name' => 'sales_rep', 'guard_name' => 'api']);
        Role::create(['name' => 'customer', 'guard_name' => 'api']);

        // Create users
        $this->admin = User::factory()->create(['store_name' => 'Admin User']);
        $this->admin->assignRole('admin');

        $this->salesRep = User::factory()->create(['store_name' => 'Sales Rep']);
        $this->salesRep->assignRole('sales_rep');

        $this->customer = User::factory()->create(['store_name' => 'Customer Store']);
        $this->customer->assignRole('customer');

        $this->regularUser = User::factory()->create(['store_name' => 'Regular User']);
    }

    /** @test */
    public function admin_can_start_masquerade_session()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => $this->customer->id,
                'reason' => 'Help customer place order',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'session_id',
                    'target_user',
                    'token',
                    'token_type',
                    'warning',
                ]
            ]);

        $this->assertDatabaseHas('masquerade_sessions', [
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Help customer place order',
        ]);
    }

    /** @test */
    public function sales_rep_can_start_masquerade_session()
    {
        $response = $this->actingAs($this->salesRep, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => $this->customer->id,
                'reason' => 'Onboarding new customer',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('masquerade_sessions', [
            'admin_user_id' => $this->salesRep->id,
            'target_user_id' => $this->customer->id,
        ]);
    }

    /** @test */
    public function regular_user_cannot_start_masquerade_session()
    {
        $response = $this->actingAs($this->regularUser, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => $this->customer->id,
                'reason' => 'Testing',
            ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Permission non autorisée',
            ]);
    }

    /** @test */
    public function masquerade_session_requires_user_id()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'reason' => 'Testing',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function masquerade_session_requires_reason()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => $this->customer->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    /** @test */
    public function masquerade_session_requires_valid_user_id()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => 999999,
                'reason' => 'Testing',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['user_id']);
    }

    /** @test */
    public function masquerade_session_returns_target_user_token()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => $this->customer->id,
                'reason' => 'Testing token generation',
            ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertNotNull($data['token']);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertEquals($this->customer->id, $data['target_user']['id']);
    }

    /** @test */
    public function admin_can_end_masquerade_session()
    {
        $session = MasqueradeSession::create([
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Testing',
            'started_at' => now(),
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->postJson('/api/v1/masquerade/end', [
                'session_id' => $session->id,
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'token',
                    'user',
                ]
            ]);

        $session->refresh();
        $this->assertNotNull($session->ended_at);
    }

    /** @test */
    public function masquerade_session_logs_ip_and_user_agent()
    {
        $response = $this->actingAs($this->admin, 'api')
            ->withHeaders([
                'User-Agent' => 'TestBrowser/1.0',
            ])
            ->postJson('/api/v1/masquerade/start', [
                'user_id' => $this->customer->id,
                'reason' => 'Testing logging',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('masquerade_sessions', [
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'user_agent' => 'TestBrowser/1.0',
        ]);
    }

    /** @test */
    public function admin_can_view_masquerade_history()
    {
        // Create some sessions
        MasqueradeSession::create([
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Session 1',
            'started_at' => now()->subHours(2),
            'ended_at' => now()->subHours(1),
        ]);

        MasqueradeSession::create([
            'admin_user_id' => $this->salesRep->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Session 2',
            'started_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($this->admin, 'api')
            ->getJson('/api/v1/masquerade/history');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'data' => [
                        '*' => [
                            'id',
                            'admin_user_id',
                            'target_user_id',
                            'reason',
                            'started_at',
                            'ended_at',
                            'admin',
                            'target',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function non_admin_cannot_view_masquerade_history()
    {
        $response = $this->actingAs($this->salesRep, 'api')
            ->getJson('/api/v1/masquerade/history');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Permission non autorisée',
            ]);
    }

    /** @test */
    public function masquerade_session_can_log_actions()
    {
        $session = MasqueradeSession::create([
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Testing action logging',
            'started_at' => now(),
        ]);

        $session->logAction('order_created', ['order_id' => 123, 'amount' => 100]);
        $session->logAction('product_viewed', ['product_id' => 456]);

        $session->refresh();

        $this->assertIsArray($session->actions_log);
        $this->assertCount(2, $session->actions_log);
        $this->assertEquals('order_created', $session->actions_log[0]['action']);
        $this->assertEquals(123, $session->actions_log[0]['details']['order_id']);
    }

    /** @test */
    public function masquerade_session_end_method_sets_ended_at()
    {
        $session = MasqueradeSession::create([
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Testing end method',
            'started_at' => now(),
        ]);

        $this->assertNull($session->ended_at);

        $session->end();
        $session->refresh();

        $this->assertNotNull($session->ended_at);
    }

    /** @test */
    public function masquerade_session_duration_is_calculated()
    {
        $session = MasqueradeSession::create([
            'admin_user_id' => $this->admin->id,
            'target_user_id' => $this->customer->id,
            'reason' => 'Testing duration',
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
        ]);

        $this->assertGreaterThanOrEqual(29, $session->duration());
        $this->assertLessThanOrEqual(31, $session->duration());
    }
}
