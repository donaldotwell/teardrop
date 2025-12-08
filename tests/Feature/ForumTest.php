<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\ForumPost;
use App\Models\ForumComment;
use App\Models\ForumReport;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ForumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    public function test_user_can_create_post()
    {
        $response = $this->post(route('forum.posts.store'), [
            'title' => 'Test Post',
            'body' => 'This is a test post content.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('forum_posts', [
            'title' => 'Test Post',
            'body' => 'This is a test post content.',
        ]);
    }

    public function test_post_with_links_is_rejected()
    {
        $response = $this->post(route('forum.posts.store'), [
            'title' => 'Test Post',
            'body' => 'Check out this site: https://example.com',
        ]);

        $response->assertSessionHasErrors('body');
        $this->assertDatabaseMissing('forum_posts', ['title' => 'Test Post']);
    }

    public function test_user_can_report_post()
    {
        $post = ForumPost::factory()->create();

        $response = $this->post(route('forum.posts.report', $post), [
            'reason' => 'This post violates community guidelines.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('forum_reports', [
            'reportable_id' => $post->id,
            'reportable_type' => ForumPost::class,
            'reason' => 'This post violates community guidelines.',
        ]);
    }

    public function test_banned_user_cannot_access_forum()
    {
        $bannedUser = User::factory()->create(['status' => 'banned']);

        $response = $this->actingAs($bannedUser)->get(route('forum.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_moderator_can_review_reports()
    {
        $moderator = User::factory()->create();
        $moderator->assignRoleByName('moderator');

        $report = ForumReport::factory()->create();

        $response = $this->actingAs($moderator)
            ->post(route('forum.moderate.reports.review', $report), [
                'action' => 'dismiss',
                'notes' => 'Report dismissed - no violation found.',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('forum_reports', [
            'id' => $report->id,
            'status' => 'reviewed',
        ]);
    }
}
