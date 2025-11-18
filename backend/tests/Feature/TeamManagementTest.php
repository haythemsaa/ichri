<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TeamMember;
use App\Models\TeamInvitation;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $admin;
    protected $employee;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->owner = User::factory()->create([
            'store_name' => 'Test Store Owner',
            'is_verified' => true,
        ]);

        $this->admin = User::factory()->create([
            'store_name' => 'Test Admin',
            'is_verified' => true,
        ]);

        $this->employee = User::factory()->create([
            'store_name' => 'Test Employee',
            'is_verified' => true,
        ]);
    }

    /** @test */
    public function user_can_get_team_members()
    {
        // Create team members
        TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->admin->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->getJson('/api/v1/team/members');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'members' => [
                        '*' => [
                            'id',
                            'account_id',
                            'user_id',
                            'role',
                            'is_active',
                        ]
                    ]
                ]
            ]);
    }

    /** @test */
    public function user_can_invite_team_member_by_email()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->postJson('/api/v1/team/invite', [
                'email' => 'newmember@example.com',
                'role' => 'employee',
                'spending_limit' => 500,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'invitation' => [
                        'id',
                        'account_id',
                        'email',
                        'role',
                        'token',
                        'expires_at',
                    ]
                ]
            ]);

        $this->assertDatabaseHas('team_invitations', [
            'account_id' => $this->owner->id,
            'email' => 'newmember@example.com',
            'role' => 'employee',
        ]);
    }

    /** @test */
    public function user_can_invite_team_member_by_phone()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->postJson('/api/v1/team/invite', [
                'phone' => '+21612345678',
                'role' => 'manager',
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('team_invitations', [
            'account_id' => $this->owner->id,
            'phone' => '+21612345678',
            'role' => 'manager',
        ]);
    }

    /** @test */
    public function invitation_requires_email_or_phone()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->postJson('/api/v1/team/invite', [
                'role' => 'employee',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone']);
    }

    /** @test */
    public function invitation_requires_valid_role()
    {
        $response = $this->actingAs($this->owner, 'api')
            ->postJson('/api/v1/team/invite', [
                'email' => 'test@example.com',
                'role' => 'invalid_role',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    /** @test */
    public function user_can_update_team_member_role()
    {
        $teamMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->employee->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->putJson("/api/v1/team/members/{$teamMember->id}", [
                'role' => 'manager',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('team_members', [
            'id' => $teamMember->id,
            'role' => 'manager',
        ]);
    }

    /** @test */
    public function user_can_update_team_member_spending_limit()
    {
        $teamMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->employee->id,
            'role' => 'employee',
            'spending_limit' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->putJson("/api/v1/team/members/{$teamMember->id}", [
                'spending_limit' => 1000,
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('team_members', [
            'id' => $teamMember->id,
            'spending_limit' => 1000,
        ]);
    }

    /** @test */
    public function user_can_deactivate_team_member()
    {
        $teamMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->employee->id,
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->deleteJson("/api/v1/team/members/{$teamMember->id}");

        $response->assertStatus(200);

        $this->assertDatabaseHas('team_members', [
            'id' => $teamMember->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function cannot_remove_owner()
    {
        $ownerMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->owner, 'api')
            ->deleteJson("/api/v1/team/members/{$ownerMember->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Impossible de retirer le propriétaire',
            ]);

        $this->assertDatabaseHas('team_members', [
            'id' => $ownerMember->id,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function team_member_can_check_permissions()
    {
        $teamMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->employee->id,
            'role' => 'employee',
            'permissions' => ['can_order', 'can_view_products'],
            'is_active' => true,
        ]);

        $this->assertTrue($teamMember->hasPermission('can_order'));
        $this->assertTrue($teamMember->hasPermission('can_view_products'));
        $this->assertFalse($teamMember->hasPermission('can_manage_team'));
    }

    /** @test */
    public function owner_and_admin_have_all_permissions()
    {
        $ownerMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->owner->id,
            'role' => 'owner',
            'is_active' => true,
        ]);

        $adminMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->admin->id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertTrue($ownerMember->hasPermission('any_permission'));
        $this->assertTrue($adminMember->hasPermission('any_permission'));
    }

    /** @test */
    public function team_member_spending_limit_is_enforced()
    {
        $teamMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->employee->id,
            'role' => 'employee',
            'spending_limit' => 500,
            'is_active' => true,
        ]);

        $this->assertTrue($teamMember->isWithinSpendingLimit(400));
        $this->assertTrue($teamMember->isWithinSpendingLimit(500));
        $this->assertFalse($teamMember->isWithinSpendingLimit(600));
    }

    /** @test */
    public function team_member_without_limit_can_spend_any_amount()
    {
        $teamMember = TeamMember::create([
            'account_id' => $this->owner->id,
            'user_id' => $this->admin->id,
            'role' => 'admin',
            'spending_limit' => null,
            'is_active' => true,
        ]);

        $this->assertTrue($teamMember->isWithinSpendingLimit(10000));
    }
}
