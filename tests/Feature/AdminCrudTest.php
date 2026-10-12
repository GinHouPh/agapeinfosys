<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingSeeder::class);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_dashboard_lists_content_counts(): void
    {
        Service::factory()->count(2)->create();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Services', false);
    }

    public function test_services_index_lists_seeded_entries(): void
    {
        Service::factory()->create(['title' => 'Legacy Migration']);

        $this->actingAs($this->admin)
            ->get(route('admin.services.index'))
            ->assertOk()
            ->assertSee('Legacy Migration', false);
    }

    public function test_a_service_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.services.store'), [
                'title' => 'Data Engineering',
                'slug' => '',
                'eyebrow' => 'Engineering',
                'excerpt' => 'Pipelines, warehouses and reporting you can trust.',
                'summary' => 'We build the data layer your business runs on.',
                'body' => '<p>Full service description.</p>',
                'highlights' => "Ingestion\nModelling\nDashboards",
                'sort_order' => 3,
                'is_featured' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('admin.services.index'))
            ->assertSessionHas('status');

        $service = Service::firstWhere('title', 'Data Engineering');

        $this->assertNotNull($service);
        $this->assertSame('data-engineering', $service->slug);
        $this->assertSame(['Ingestion', 'Modelling', 'Dashboards'], $service->highlights);
        $this->assertTrue((bool) $service->is_featured);
    }

    public function test_a_service_requires_a_title(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.services.create'))
            ->post(route('admin.services.store'), ['title' => ''])
            ->assertRedirect(route('admin.services.create'))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('services', 0);
    }

    public function test_a_service_can_be_updated_and_unpublished(): void
    {
        $service = Service::factory()->create(['title' => 'Old Title', 'is_published' => true]);

        $this->actingAs($this->admin)
            ->put(route('admin.services.update', $service), [
                'title' => 'New Title',
                'slug' => $service->slug,
                'excerpt' => 'Updated excerpt.',
                'highlights' => 'One highlight',
                'sort_order' => 0,
                'is_featured' => 0,
                'is_published' => 0,
            ])
            ->assertRedirect(route('admin.services.index'));

        $service->refresh();

        $this->assertSame('New Title', $service->title);
        $this->assertFalse((bool) $service->is_published);
    }

    public function test_a_service_can_be_deleted(): void
    {
        $service = Service::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.services.destroy', $service))
            ->assertRedirect(route('admin.services.index'));

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_an_image_can_be_uploaded_for_a_service(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->post(route('admin.services.store'), [
                'title' => 'With Artwork',
                'excerpt' => 'An entry that carries an image.',
                'highlights' => '',
                'sort_order' => 0,
                'is_published' => 1,
                'image' => UploadedFile::fake()->image('service.jpg', 800, 600),
            ])
            ->assertRedirect(route('admin.services.index'));

        $service = Service::firstWhere('title', 'With Artwork');

        $this->assertNotNull($service->image);
        Storage::disk('public')->assertExists($service->image);
    }

    public function test_a_case_study_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.projects.store'), [
                'title' => 'Payments Modernisation',
                'client' => 'Northwind Bank',
                'category' => 'Fintech',
                'service_type' => 'Modernisation',
                'excerpt' => 'Replaced a ageing core integration layer.',
                'body' => '<p>Case study body.</p>',
                'stack' => 'Laravel, RabbitMQ, AWS',
                'outcomes' => "40% faster settlement\n99.98% uptime",
                'year' => 2025,
                'sort_order' => 1,
                'is_published' => 1,
            ])
            ->assertRedirect(route('admin.projects.index'));

        $project = Project::firstWhere('title', 'Payments Modernisation');

        $this->assertNotNull($project);
        $this->assertSame('Northwind Bank', $project->client);
        $this->assertSame(['Laravel', 'RabbitMQ', 'AWS'], $project->stack);
        $this->assertSame(['40% faster settlement', '99.98% uptime'], $project->outcomes);
    }

    public function test_an_article_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.posts.store'), [
                'title' => 'Shipping Safer Releases',
                'slug' => '',
                'category' => 'Delivery',
                'excerpt' => 'How we cut regression risk on every deploy.',
                'body' => "First paragraph.\n\nSecond paragraph.",
                'reading_time' => '5 min read',
                'published_at' => '2026-04-01',
                'sort_order' => 0,
                'is_published' => 1,
            ])
            ->assertRedirect(route('admin.posts.index'));

        $post = Post::firstWhere('title', 'Shipping Safer Releases');

        $this->assertNotNull($post);
        $this->assertSame('shipping-safer-releases', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
    }

    public function test_a_testimonial_can_be_created_and_hidden(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.testimonials.store'), [
                'name' => 'Jordan Blake',
                'role' => 'CTO',
                'company' => 'Vertex Retail',
                'quote' => 'Agape delivered our commerce replatform two weeks early.',
                'rating' => 5,
                'sort_order' => 1,
                'is_published' => 0,
            ])
            ->assertRedirect(route('admin.testimonials.index'));

        $testimonial = Testimonial::firstWhere('name', 'Jordan Blake');

        $this->assertNotNull($testimonial);
        $this->assertFalse((bool) $testimonial->is_published);
    }

    public function test_contact_messages_can_be_reviewed_and_deleted(): void
    {
        $message = ContactMessage::create([
            'name' => 'Casey Morgan',
            'email' => 'casey@example.com',
            'message' => 'We would like a quote for a portal rebuild.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.messages.index'))
            ->assertOk()
            ->assertSee('Casey Morgan', false);

        $this->actingAs($this->admin)
            ->get(route('admin.messages.show', $message))
            ->assertOk()
            ->assertSee('portal rebuild', false);

        $this->actingAs($this->admin)
            ->delete(route('admin.messages.destroy', $message))
            ->assertRedirect(route('admin.messages.index'));

        $this->assertDatabaseMissing('contact_messages', ['id' => $message->id]);
    }
}
