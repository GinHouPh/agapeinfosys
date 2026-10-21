<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        Storage::fake('public');
    }

    public function test_guests_are_redirected_from_the_media_library(): void
    {
        $this->get(route('admin.media.index'))->assertRedirect(route('admin.login'));
    }

    public function test_a_file_can_be_uploaded_into_the_library(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.media.store'), [
                'file' => UploadedFile::fake()->image('hero.jpg', 800, 600),
            ])
            ->assertRedirect();

        $item = Media::query()->firstOrFail();

        $this->assertSame('hero.jpg', $item->original_name);
        $this->assertSame('image/jpeg', $item->mime_type);
        $this->assertSame(800, $item->width);
        $this->assertSame(600, $item->height);
        Storage::disk('public')->assertExists($item->path());
    }

    public function test_the_library_lists_what_has_been_uploaded(): void
    {
        Media::factory()->create(['original_name' => 'brand-banner.png']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('brand-banner.png');
    }

    public function test_the_library_can_be_searched(): void
    {
        Media::factory()->create(['original_name' => 'brand-banner.png']);
        Media::factory()->create(['original_name' => 'team-photo.jpg']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.index', ['q' => 'banner']))
            ->assertOk()
            ->assertSee('brand-banner.png')
            ->assertDontSee('team-photo.jpg');
    }

    public function test_the_library_can_be_filtered_by_kind(): void
    {
        Media::factory()->create(['original_name' => 'a-picture.jpg', 'mime_type' => 'image/jpeg']);
        Media::factory()->create(['original_name' => 'a-brief.pdf', 'mime_type' => 'application/pdf']);

        $this->actingAs($this->admin)
            ->get(route('admin.media.index', ['kind' => 'documents']))
            ->assertOk()
            ->assertSee('a-brief.pdf')
            ->assertDontSee('a-picture.jpg');
    }

    public function test_a_file_can_be_described(): void
    {
        $item = Media::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.media.update', $item), [
                'alt_text' => 'The team at the office',
                'title' => 'Team',
                'focal_x' => 30,
                'focal_y' => 70,
            ])
            ->assertRedirect();

        $item->refresh();

        $this->assertSame('The team at the office', $item->alt_text);
        $this->assertSame(30, $item->focal_x);
        $this->assertSame(70, $item->focal_y);
    }

    public function test_deleting_a_file_removes_it_from_disk(): void
    {
        $item = Media::factory()->create();
        Storage::disk('public')->put($item->path(), 'bytes');

        $this->actingAs($this->admin)
            ->delete(route('admin.media.destroy', $item))
            ->assertRedirect();

        $this->assertDatabaseMissing('media', ['id' => $item->id]);
        Storage::disk('public')->assertMissing($item->path());
    }

    public function test_an_image_uploaded_in_the_builder_is_recorded_in_the_library(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.builder.upload'), [
                'image' => UploadedFile::fake()->image('builder.jpg', 400, 300),
            ])
            ->assertOk()
            ->assertJsonPath('path', $this->pathOfTheOnlyMedia());
    }

    public function test_the_service_records_a_file_that_is_already_on_disk(): void
    {
        Storage::disk('public')->put('uploads/library/existing.png', 'bytes');

        $item = app(MediaService::class)->record('uploads/library/existing.png');

        $this->assertNotNull($item);
        $this->assertSame('existing.png', $item->original_name);
        $this->assertSame('uploads/library', $item->directory);
    }

    /**
     * The stored path of the only media row.
     */
    private function pathOfTheOnlyMedia(): string
    {
        return Media::query()->firstOrFail()->path();
    }
}
