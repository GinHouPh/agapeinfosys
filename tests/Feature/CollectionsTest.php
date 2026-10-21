<?php

namespace Tests\Feature;

use App\Models\CollectionField;
use App\Models\CollectionRecord;
use App\Models\CollectionType;
use App\Models\User;
use App\Services\CollectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_collection_editor(): void
    {
        $this->get(route('admin.collections.index'))->assertRedirect(route('admin.login'));
    }

    public function test_a_collection_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.collections.store'), [
                'name' => 'Case studies',
                'description' => 'Work we have done.',
            ])
            ->assertRedirect();

        $type = CollectionType::query()->where('name', 'Case studies')->firstOrFail();

        $this->assertSame('case-studies', $type->slug);
    }

    public function test_a_field_can_be_added_to_a_collection(): void
    {
        $type = CollectionType::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.collections.fields.store', $type), [
                'label' => 'Client name',
                'type' => 'text',
                'is_required' => '1',
            ])
            ->assertRedirect();

        $field = $type->fields()->firstOrFail();

        $this->assertSame('client_name', $field->name);
        $this->assertTrue($field->is_required);
    }

    public function test_a_record_can_be_created(): void
    {
        $type = CollectionType::factory()->create();
        CollectionField::factory()->create([
            'collection_type_id' => $type->id,
            'name' => 'client',
            'label' => 'Client',
            'type' => 'text',
            'is_required' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.collections.records.store', $type), [
                'title' => 'Acme Corporation',
                'data' => ['client' => 'Acme'],
                'is_published' => '1',
            ])
            ->assertRedirect();

        $record = CollectionRecord::query()->firstOrFail();

        $this->assertSame('Acme Corporation', $record->title);
        $this->assertSame('Acme', $record->value('client'));
        $this->assertTrue($record->is_published);
    }

    public function test_a_required_field_must_be_filled(): void
    {
        $type = CollectionType::factory()->create();
        CollectionField::factory()->create([
            'collection_type_id' => $type->id,
            'name' => 'client',
            'label' => 'Client',
            'type' => 'text',
            'is_required' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('admin.collections.records.store', $type), [
                'title' => 'No client',
                'data' => [],
            ])
            ->assertSessionHasErrors('data.client');
    }

    public function test_a_value_the_type_does_not_declare_is_dropped(): void
    {
        $type = CollectionType::factory()->create();
        CollectionField::factory()->create([
            'collection_type_id' => $type->id,
            'name' => 'client',
            'label' => 'Client',
            'type' => 'text',
        ]);

        $clean = app(CollectionService::class)->sanitise($type, [
            'client' => 'Acme',
            'smuggled' => 'not a field',
        ]);

        $this->assertSame(['client' => 'Acme'], $clean);
    }

    public function test_a_published_record_is_served_at_its_path(): void
    {
        $type = CollectionType::factory()->create(['slug' => 'work', 'name' => 'Work']);
        CollectionRecord::factory()->create([
            'collection_type_id' => $type->id,
            'slug' => 'acme',
            'title' => 'Acme Corporation',
            'is_published' => true,
        ]);

        $this->get('/work/acme')
            ->assertOk()
            ->assertSee('Acme Corporation', false);
    }

    public function test_an_unpublished_record_is_not_served(): void
    {
        $type = CollectionType::factory()->create(['slug' => 'work']);
        CollectionRecord::factory()->create([
            'collection_type_id' => $type->id,
            'slug' => 'acme',
            'is_published' => false,
        ]);

        $this->get('/work/acme')->assertNotFound();
    }

    public function test_a_collection_that_does_not_exist_is_a_404(): void
    {
        $this->get('/nothing/here')->assertNotFound();
    }

    public function test_a_record_can_be_deleted(): void
    {
        $type = CollectionType::factory()->create();
        $record = CollectionRecord::factory()->create(['collection_type_id' => $type->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.collections.records.destroy', [$type, $record]))
            ->assertRedirect();

        $this->assertDatabaseMissing('collection_records', ['id' => $record->id]);
    }

    public function test_the_records_screen_renders(): void
    {
        $type = CollectionType::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('admin.collections.records', $type))
            ->assertOk();
    }
}
