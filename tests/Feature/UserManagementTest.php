<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User { return User::factory()->admin()->create(); }

    // ── Index ─────────────────────────────────────────────────────────────

    /** @test */
    public function user_index_requires_auth(): void
    {
        $this->get(route('users.index'))->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_view_user_list(): void
    {
        $admin = $this->admin();
        User::factory()->manager()->create(['name' => 'Bob Manager']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('Bob Manager');
    }

    // ── Create ────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_create_a_new_user(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'      => 'Jane Doe',
                'email'     => 'jane@epoch.co.zw',
                'role'      => 'manager',
                'job_title' => 'Site Manager',
                'phone'     => '+263 77 111 2222',
                'password'  => 'secret12',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['email' => 'jane@epoch.co.zw', 'role' => 'manager']);
    }

    /** @test */
    public function user_email_must_be_unique(): void
    {
        $existing = User::factory()->create(['email' => 'dup@epoch.co.zw']);

        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'     => 'Duplicate',
                'email'    => 'dup@epoch.co.zw',
                'role'     => 'viewer',
                'password' => 'secret12',
            ])
            ->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_must_meet_requirements(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'     => 'Weak Pass',
                'email'    => 'weak@epoch.co.zw',
                'role'     => 'viewer',
                'password' => '1234',
            ])
            ->assertSessionHasErrors('password');
    }

    /** @test */
    public function role_must_be_valid(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name'     => 'Bad Role',
                'email'    => 'bad@epoch.co.zw',
                'role'     => 'superadmin',
                'password' => 'secret12',
            ])
            ->assertSessionHasErrors('role');
    }

    // ── Update ────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_update_a_user(): void
    {
        $user = User::factory()->viewer()->create(['name' => 'Original Name']);

        $this->actingAs($this->admin())
            ->put(route('users.update', $user), [
                'name'  => 'Updated Name',
                'email' => $user->email,
                'role'  => 'manager',
            ])
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'Updated Name', 'role' => 'manager']);
    }

    /** @test */
    public function password_is_not_required_on_update(): void
    {
        $user = User::factory()->viewer()->create();

        $this->actingAs($this->admin())
            ->put(route('users.update', $user), [
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ])
            ->assertRedirect(route('users.index'));
    }

    // ── Delete ────────────────────────────────────────────────────────────

    /** @test */
    public function admin_can_delete_another_user(): void
    {
        $admin  = $this->admin();
        $target = User::factory()->viewer()->create();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $target))
            ->assertRedirect(route('users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
    }

    /** @test */
    public function admin_cannot_delete_own_account_via_user_management(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->delete(route('users.destroy', $admin))
            ->assertRedirect(route('users.index'));

        // Account still exists
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
