<?php

namespace Tests\Feature;

use App\Models\ShowcaseProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminShowcaseTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_project_admin(): void
    {
        $this->get(route('admin.showcase-projects.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_projects_index_lists_existing_projects(): void
    {
        ShowcaseProject::factory()->create(['title' => 'Streamly']);

        $this->actingAs($this->admin)
            ->get(route('admin.showcase-projects.index'))
            ->assertOk()
            ->assertSee('Streamly');
    }

    public function test_the_index_status_filter_works(): void
    {
        ShowcaseProject::factory()->create(['title' => 'Ongoing Thing']);
        ShowcaseProject::factory()->upcoming()->create(['title' => 'Upcoming Thing']);

        $this->actingAs($this->admin)
            ->get(route('admin.showcase-projects.index', ['status' => 'upcoming']))
            ->assertOk()
            ->assertSee('Upcoming Thing')
            ->assertDontSee('Ongoing Thing');
    }

    public function test_a_project_can_be_created_with_a_poster(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('admin.showcase-projects.store'), [
                'title' => 'CartRocket',
                'slug' => '',
                'industry' => 'E-commerce',
                'status' => 'upcoming',
                'tagline' => 'Headless commerce for multi-brand retailers.',
                'stack' => 'Node.js, Vue, Redis',
                'expected_launch' => now()->addMonths(3)->toDateString(),
                'sort_order' => 0,
                'is_active' => 1,
                'poster' => UploadedFile::fake()->image('poster.png', 400, 600),
            ])
            ->assertRedirect(route('admin.showcase-projects.index'));

        $project = ShowcaseProject::query()->where('slug', 'cartrocket')->sole();

        $this->assertSame('E-commerce', $project->industry);
        $this->assertSame(['Node.js', 'Vue', 'Redis'], $project->stack);
        $this->assertTrue($project->is_active);
        Storage::disk('public')->assertExists($project->poster);
    }

    public function test_updating_a_project_replaces_its_poster_and_keeps_the_slug(): void
    {
        Storage::fake('public');

        $project = ShowcaseProject::factory()->create(['title' => 'Old Title']);

        $project->forceFill(['poster' => 'uploads/showcase/old.png'])->save();
        Storage::disk('public')->put('uploads/showcase/old.png', 'old-bytes');

        $this->actingAs($this->admin)
            ->put(route('admin.showcase-projects.update', $project), [
                'title' => 'Renamed Project',
                'industry' => 'Logistics & Fleet',
                'status' => 'ongoing',
                'tagline' => 'Updated tagline.',
                'stack' => 'Laravel',
                'expected_launch' => '',
                'sort_order' => 2,
                'is_active' => 1,
                'poster' => UploadedFile::fake()->image('new.png', 400, 600),
            ])
            ->assertRedirect(route('admin.showcase-projects.index'));

        $project->refresh();

        $this->assertSame('Renamed Project', $project->title);
        $this->assertSame('old-title', $project->slug, 'An untouched slug stays stable across edits.');
        Storage::disk('public')->assertMissing('uploads/showcase/old.png');
        Storage::disk('public')->assertExists($project->poster);
    }

    public function test_a_project_can_be_deleted_with_its_poster(): void
    {
        Storage::fake('public');

        $project = ShowcaseProject::factory()->create();
        $project->forceFill(['poster' => 'uploads/showcase/temp.png'])->save();
        Storage::disk('public')->put('uploads/showcase/temp.png', 'bytes');

        $this->actingAs($this->admin)
            ->delete(route('admin.showcase-projects.destroy', $project))
            ->assertRedirect(route('admin.showcase-projects.index'));

        $this->assertDatabaseMissing('showcase_projects', ['id' => $project->getKey()]);
        Storage::disk('public')->assertMissing('uploads/showcase/temp.png');
    }

    public function test_the_poster_must_be_an_image(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.showcase-projects.store'), [
                'title' => 'Bad Poster',
                'industry' => 'OTT & Media',
                'status' => 'ongoing',
                'poster' => UploadedFile::fake()->create('notes.txt', 10, 'text/plain'),
            ])
            ->assertSessionHasErrors('poster');

        $this->assertDatabaseMissing('showcase_projects', ['title' => 'Bad Poster']);
    }
}
