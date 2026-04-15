<?php

namespace Tests\Feature;

use App\Models\DailyProduction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    // ── helpers ──────────────────────────────────────────────────────────

    private function superAdmin(): User { return User::factory()->superAdmin()->create(); }
    private function admin(): User      { return User::factory()->admin()->create(); }
    private function manager(): User    { return User::factory()->manager()->create(); }
    private function viewer(): User     { return User::factory()->viewer()->create(); }

    private function productionPayload(): array
    {
        return [
            'date'              => '2026-04-14',
            'ore_hoisted'       => '100.00',
            'waste_hoisted'     => '10.00',
            'ore_crushed'       => '80.00',
            'ore_milled'        => '75.00',
            'gold_smelted'      => '2.50',
            'purity_percentage' => '85.00',
            'fidelity_price'    => '90000.00',
        ];
    }

    // ── Read access (all roles) ───────────────────────────────────────────

    /** @test */
    public function all_roles_can_view_production_index(): void
    {
        foreach ([$this->admin(), $this->manager(), $this->viewer()] as $user) {
            $this->actingAs($user)->get(route('production.index'))->assertOk();
        }
    }

    // ── Write access ─────────────────────────────────────────────────────

    /** @test */
    public function admin_can_create_production_record(): void
    {
        $this->actingAs($this->admin())
            ->post(route('production.store'), $this->productionPayload())
            ->assertRedirect(route('production.index'));
    }

    /** @test */
    public function manager_can_create_production_record(): void
    {
        $this->actingAs($this->manager())
            ->post(route('production.store'), $this->productionPayload())
            ->assertRedirect(route('production.index'));
    }

    /** @test */
    public function viewer_cannot_create_production_record(): void
    {
        $this->actingAs($this->viewer())
            ->post(route('production.store'), $this->productionPayload())
            ->assertForbidden();
    }

    /** @test */
    public function viewer_cannot_access_production_create_form(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('production.create'))
            ->assertForbidden();
    }

    /** @test */
    public function manager_can_delete_production_record(): void
    {
        $record = DailyProduction::factory()->create();

        $this->actingAs($this->manager())
            ->delete(route('production.destroy', $record))
            ->assertRedirect(route('production.index'));
    }

    /** @test */
    public function viewer_cannot_delete_production_record(): void
    {
        $record = DailyProduction::factory()->create();

        $this->actingAs($this->viewer())
            ->delete(route('production.destroy', $record))
            ->assertForbidden();
    }

    // ── Settings (admin + super_admin) ───────────────────────────────────

    /** @test */
    public function super_admin_can_access_settings(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('settings.index'))
            ->assertOk();
    }

    /** @test */
    public function admin_can_access_settings(): void
    {
        $this->actingAs($this->admin())
            ->get(route('settings.index'))
            ->assertOk();
    }

    /** @test */
    public function manager_cannot_access_settings(): void
    {
        $this->actingAs($this->manager())
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    /** @test */
    public function viewer_cannot_access_settings(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('settings.index'))
            ->assertForbidden();
    }

    // ── User management (admin + super_admin) ─────────────────────────────

    /** @test */
    public function super_admin_can_access_user_management(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('users.index'))
            ->assertOk();
    }

    /** @test */
    public function admin_can_access_user_management(): void
    {
        $this->actingAs($this->admin())
            ->get(route('users.index'))
            ->assertOk();
    }

    /** @test */
    public function manager_cannot_access_user_management(): void
    {
        $this->actingAs($this->manager())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    /** @test */
    public function viewer_cannot_access_user_management(): void
    {
        $this->actingAs($this->viewer())
            ->get(route('users.index'))
            ->assertForbidden();
    }

    // ── Roles page (super_admin only) ─────────────────────────────────────

    /** @test */
    public function super_admin_can_access_roles_page(): void
    {
        $this->actingAs($this->superAdmin())
            ->get(route('roles.index'))
            ->assertOk();
    }

    /** @test */
    public function admin_cannot_access_roles_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('roles.index'))
            ->assertForbidden();
    }

    // ── User model helpers ────────────────────────────────────────────────

    /** @test */
    public function user_role_helper_methods_work_correctly(): void
    {
        $superAdmin = $this->superAdmin();
        $admin      = $this->admin();
        $manager    = $this->manager();
        $viewer     = $this->viewer();

        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->isAdminOrAbove());
        $this->assertFalse($superAdmin->isAdmin());
        $this->assertTrue($superAdmin->canWrite());

        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->isAdminOrAbove());
        $this->assertFalse($admin->isSuperAdmin());
        $this->assertFalse($admin->isManager());
        $this->assertFalse($admin->isViewer());

        $this->assertFalse($manager->isAdmin());
        $this->assertTrue($manager->isManager());
        $this->assertFalse($manager->isViewer());

        $this->assertFalse($viewer->isAdmin());
        $this->assertFalse($viewer->isManager());
        $this->assertTrue($viewer->isViewer());

        $this->assertTrue($admin->canWrite());
        $this->assertTrue($manager->canWrite());
        $this->assertFalse($viewer->canWrite());
    }
}
