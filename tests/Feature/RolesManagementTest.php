<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function roles_page_requires_auth(): void
    {
        $this->get(route('roles.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function super_admin_can_view_roles_page(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Super Administrator')
            ->assertSee('Administrator')
            ->assertSee('Manager')
            ->assertSee('Viewer');
    }

    /** @test */
    public function roles_page_shows_permission_matrix(): void
    {
        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Permission Matrix')
            ->assertSee('View all modules')
            ->assertSee('Manage roles');
    }

    /** @test */
    public function roles_page_shows_assign_roles_table(): void
    {
        $target = User::factory()->viewer()->create(['name' => 'Jane Viewer']);

        $this->actingAs(User::factory()->superAdmin()->create())
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Assign Roles')
            ->assertSee('Jane Viewer');
    }

    /** @test */
    public function super_admin_can_assign_a_role(): void
    {
        $sa     = User::factory()->superAdmin()->create();
        $target = User::factory()->viewer()->create();

        $this->actingAs($sa)
            ->patch(route('roles.assign', $target), ['role' => 'manager'])
            ->assertRedirect(route('roles.index'));

        $this->assertDatabaseHas('users', ['id' => $target->id, 'role' => 'manager']);
    }

    /** @test */
    public function super_admin_cannot_change_own_role(): void
    {
        $sa = User::factory()->superAdmin()->create();

        $this->actingAs($sa)
            ->patch(route('roles.assign', $sa), ['role' => 'viewer'])
            ->assertRedirect();

        // Role must remain super_admin
        $this->assertDatabaseHas('users', ['id' => $sa->id, 'role' => 'super_admin']);
    }

    /** @test */
    public function admin_cannot_access_roles_page(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_cannot_access_roles_page(): void
    {
        $this->actingAs(User::factory()->manager()->create())
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    /** @test */
    public function viewer_cannot_access_roles_page(): void
    {
        $this->actingAs(User::factory()->viewer()->create())
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    /** @test */
    public function assign_role_requires_valid_role_value(): void
    {
        $sa     = User::factory()->superAdmin()->create();
        $target = User::factory()->viewer()->create();

        $this->actingAs($sa)
            ->patch(route('roles.assign', $target), ['role' => 'god_mode'])
            ->assertSessionHasErrors('role');
    }
}
