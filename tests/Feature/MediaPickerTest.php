<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaPickerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        Storage::fake('public');
    }

    public function test_the_library_endpoint_lists_the_images(): void
    {
        Media::factory()->create(['original_name' => 'hero.jpg', 'mime_type' => 'image/jpeg']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.library'))
            ->assertOk()
            ->assertJsonPath('items.0.name', 'hero.jpg')
            ->assertJsonPath('items.0.path', $this->onlyMediaPath());
    }

    public function test_the_library_endpoint_leaves_out_documents(): void
    {
        Media::factory()->create(['original_name' => 'brief.pdf', 'mime_type' => 'application/pdf']);
        Media::factory()->create(['original_name' => 'photo.jpg', 'mime_type' => 'image/jpeg']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.library'))
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.name', 'photo.jpg');
    }

    public function test_the_library_endpoint_can_be_searched(): void
    {
        Media::factory()->create(['original_name' => 'hero.jpg', 'alt_text' => 'A hero shot']);
        Media::factory()->create(['original_name' => 'team.jpg', 'alt_text' => 'The team']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.library', ['q' => 'hero']))
            ->assertOk()
            ->assertJsonCount(1, 'items')
            ->assertJsonPath('items.0.name', 'hero.jpg');
    }

    public function test_guests_cannot_read_the_library(): void
    {
        $this->get(route('admin.media.library'))->assertRedirect(route('admin.login'));
    }

    public function test_the_builder_is_handed_the_library_endpoint(): void
    {
        $page = Page::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.pages.edit', $page))
            ->assertOk()
            ->assertSee(json_encode(route('admin.media.library')), false);
    }

    public function test_the_canvas_offers_the_picker_on_an_image_control(): void
    {
        // The panel is drawn by the canvas script, so the marker is checked
        // where it lives: the script the builder loads.
        $canvas = file_get_contents(resource_path('js/builder/canvas.js'));

        $this->assertStringContainsString('data-pick-media', $canvas);
        $this->assertStringContainsString('urls.media', $canvas);
    }

    /**
     * The path of the only media row.
     */
    private function onlyMediaPath(): string
    {
        return Media::query()->firstOrFail()->path();
    }
}
