<?php

namespace Tests\Feature;

use App\Models\KnowledgeBaseArticle;
use App\Models\KnowledgeBaseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    private function makeCategory(array $attrs = []): KnowledgeBaseCategory
    {
        return KnowledgeBaseCategory::create(array_merge([
            'title'      => 'Test Category',
            'slug'       => 'test-category',
            'icon'       => '📄',
            'sort_order' => 1,
        ], $attrs));
    }

    private function makeArticle(KnowledgeBaseCategory $cat, array $attrs = []): KnowledgeBaseArticle
    {
        return KnowledgeBaseArticle::create(array_merge([
            'knowledge_base_category_id' => $cat->id,
            'title'        => 'Test Article',
            'slug'         => 'test-article',
            'content'      => '<p>Hello world</p>',
            'sort_order'   => 1,
            'is_published' => true,
        ], $attrs));
    }

    // ─────────────────────────────────────────────────────────────────────
    // Access control
    // ─────────────────────────────────────────────────────────────────────

    public function test_unauthenticated_user_is_redirected_from_help(): void
    {
        $this->get('/help')->assertRedirect('/login');
    }

    public function test_viewer_can_read_kb(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $cat  = $this->makeCategory();
        $art  = $this->makeArticle($cat);

        $this->actingAs($user)
            ->get(route('kb.show', [$cat->slug, $art->slug]))
            ->assertOk()
            ->assertSee($art->title)
            ->assertSee('Hello world', false);
    }

    public function test_manager_can_read_kb(): void
    {
        $user = User::factory()->create(['role' => 'manager']);
        $cat  = $this->makeCategory();
        $art  = $this->makeArticle($cat);

        $this->actingAs($user)
            ->get(route('kb.show', [$cat->slug, $art->slug]))
            ->assertOk();
    }

    public function test_viewer_cannot_access_kb_admin(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)
            ->get(route('kb.admin.index'))
            ->assertForbidden();
    }

    public function test_manager_cannot_access_kb_admin(): void
    {
        $user = User::factory()->create(['role' => 'manager']);

        $this->actingAs($user)
            ->get(route('kb.admin.index'))
            ->assertForbidden();
    }

    public function test_admin_can_access_kb_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->get(route('kb.admin.index'))
            ->assertOk();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Public KB
    // ─────────────────────────────────────────────────────────────────────

    public function test_help_index_redirects_to_first_article(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $cat  = $this->makeCategory(['sort_order' => 0]); // sort before seeded data
        $art  = $this->makeArticle($cat);

        $this->actingAs($user)
            ->get(route('kb.index'))
            ->assertRedirect(route('kb.show', [$cat->slug, $art->slug]));
    }

    public function test_help_index_shows_empty_state_when_no_articles(): void
    {
        // Clear seeded KB data so we can test the empty state
        KnowledgeBaseArticle::query()->delete();
        KnowledgeBaseCategory::query()->delete();

        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)
            ->get(route('kb.index'))
            ->assertOk();
    }

    public function test_show_returns_404_for_missing_category(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)
            ->get(route('kb.show', ['no-such-cat', 'no-such-art']))
            ->assertNotFound();
    }

    public function test_show_returns_404_for_missing_article(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $cat  = $this->makeCategory();

        $this->actingAs($user)
            ->get(route('kb.show', [$cat->slug, 'nonexistent']))
            ->assertNotFound();
    }

    public function test_unpublished_article_is_not_shown(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $cat  = $this->makeCategory();
        $this->makeArticle($cat, ['is_published' => false, 'slug' => 'draft-art']);

        $this->actingAs($user)
            ->get(route('kb.show', [$cat->slug, 'draft-art']))
            ->assertNotFound();
    }

    // ─────────────────────────────────────────────────────────────────────
    // Search
    // ─────────────────────────────────────────────────────────────────────

    public function test_search_returns_matching_articles(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $cat  = $this->makeCategory();
        $this->makeArticle($cat, ['title' => 'Gold Production Guide XYZ', 'content' => '<p>Track gold daily</p>']);
        $this->makeArticle($cat, ['title' => 'Machine Uptime XYZ', 'slug' => 'machines', 'content' => '<p>Log hours</p>']);

        // The results section shows the article title for matches; non-matches appear only in sidebar
        // We use a unique marker that won't appear in seeded data
        $resp = $this->actingAs($user)->get(route('kb.search', ['q' => 'Gold Production Guide XYZ']));
        $resp->assertOk()->assertSee('Gold Production Guide XYZ');
    }

    public function test_search_with_short_query_returns_no_results(): void
    {
        $user = User::factory()->create(['role' => 'viewer']);
        $cat  = $this->makeCategory();
        $this->makeArticle($cat);

        // Short queries (< 2 chars) return an empty result set
        $this->actingAs($user)
            ->get(route('kb.search', ['q' => 'a']))
            ->assertOk()
            ->assertSee('0 articles found');
    }

    // ─────────────────────────────────────────────────────────────────────
    // Admin CRUD
    // ─────────────────────────────────────────────────────────────────────

    public function test_admin_can_create_category(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('kb.categories.store'), [
                'title'      => 'New Category',
                'icon'       => '🔧',
                'sort_order' => 5,
            ])
            ->assertRedirect(route('kb.admin.index'));

        $this->assertDatabaseHas('knowledge_base_categories', [
            'title' => 'New Category',
            'slug'  => 'new-category',
            'icon'  => '🔧',
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();

        $this->actingAs($user)
            ->put(route('kb.categories.update', $cat), [
                'title'      => 'Updated Title',
                'icon'       => '🆕',
                'sort_order' => 2,
            ])
            ->assertRedirect(route('kb.admin.index'));

        $this->assertDatabaseHas('knowledge_base_categories', ['id' => $cat->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_delete_category(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();

        $this->actingAs($user)
            ->delete(route('kb.categories.destroy', $cat))
            ->assertRedirect(route('kb.admin.index'));

        $this->assertDatabaseMissing('knowledge_base_categories', ['id' => $cat->id]);
    }

    public function test_deleting_category_cascades_to_articles(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();
        $art  = $this->makeArticle($cat);

        $this->actingAs($user)->delete(route('kb.categories.destroy', $cat));

        $this->assertDatabaseMissing('knowledge_base_articles', ['id' => $art->id]);
    }

    public function test_admin_can_create_article(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();

        $this->actingAs($user)
            ->post(route('kb.articles.store'), [
                'knowledge_base_category_id' => $cat->id,
                'title'        => 'New Article',
                'content'      => '<p>Content here</p>',
                'sort_order'   => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('kb.admin.index'));

        $this->assertDatabaseHas('knowledge_base_articles', [
            'title' => 'New Article',
            'slug'  => 'new-article',
        ]);
    }

    public function test_admin_can_update_article(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();
        $art  = $this->makeArticle($cat);

        $this->actingAs($user)
            ->put(route('kb.articles.update', $art), [
                'knowledge_base_category_id' => $cat->id,
                'title'        => 'Revised Title',
                'content'      => '<p>New content</p>',
                'sort_order'   => 2,
                'is_published' => 1,
            ])
            ->assertRedirect(route('kb.admin.index'));

        $this->assertDatabaseHas('knowledge_base_articles', ['id' => $art->id, 'title' => 'Revised Title']);
    }

    public function test_admin_can_toggle_article_publish_status(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();
        $art  = $this->makeArticle($cat);

        $this->assertTrue($art->is_published);

        $this->actingAs($user)
            ->post(route('kb.articles.toggle', $art))
            ->assertRedirect();

        $this->assertFalse($art->fresh()->is_published);

        $this->actingAs($user)
            ->post(route('kb.articles.toggle', $art))
            ->assertRedirect();

        $this->assertTrue($art->fresh()->is_published);
    }

    public function test_admin_can_delete_article(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();
        $art  = $this->makeArticle($cat);

        $this->actingAs($user)
            ->delete(route('kb.articles.destroy', $art))
            ->assertRedirect(route('kb.admin.index'));

        $this->assertDatabaseMissing('knowledge_base_articles', ['id' => $art->id]);
    }

    public function test_category_title_is_required(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('kb.categories.store'), ['title' => ''])
            ->assertSessionHasErrors('title');
    }

    public function test_article_content_is_required(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory();

        $this->actingAs($user)
            ->post(route('kb.articles.store'), [
                'knowledge_base_category_id' => $cat->id,
                'title'   => 'No Content',
                'content' => '',
            ])
            ->assertSessionHasErrors('content');
    }

    public function test_slug_auto_increments_to_avoid_duplicates(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $cat  = $this->makeCategory(['slug' => 'operations']);

        $this->actingAs($user)
            ->post(route('kb.categories.store'), ['title' => 'Operations', 'sort_order' => 2]);

        $this->assertDatabaseHas('knowledge_base_categories', ['slug' => 'operations-2']);
    }
}
