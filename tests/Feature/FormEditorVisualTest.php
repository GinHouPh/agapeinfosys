<?php

namespace Tests\Feature;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormEditorVisualTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_visual_editor_renders_with_palette_templates_and_config(): void
    {
        $form = Form::factory()->create(['title' => 'Visual Lead Form']);

        $this->actingAs($this->admin)
            ->get(route('admin.forms.edit', $form))
            ->assertOk()
            ->assertSee('Field Palette')
            ->assertSee('Form Canvas')
            ->assertSee('Field Settings')
            ->assertSee('fe-config')
            ->assertSee('Contact Us');
    }

    public function test_builder_sync_creates_reorders_updates_and_deletes_fields(): void
    {
        $form = Form::factory()->create();
        $existing = FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Old Name',
            'name' => 'old_name',
            'type' => FormFieldType::Text,
            'sort_order' => 0,
        ]);
        $removed = FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Remove Me',
            'name' => 'remove_me',
            'type' => FormFieldType::Text,
            'sort_order' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->putJson(route('admin.forms.builder.sync', $form), [
                'fields' => [
                    [
                        'label' => 'Privacy Consent',
                        'name' => 'gdpr_consent',
                        'type' => FormFieldType::GdprConsent->value,
                        'width' => 'full',
                        'is_required' => true,
                    ],
                    [
                        'id' => $existing->id,
                        'label' => 'Full Name',
                        'name' => 'full_name',
                        'type' => FormFieldType::Text->value,
                        'width' => 'third',
                        'default_value' => '{url:name}',
                        'css_class' => 'highlight-field',
                        'is_required' => true,
                    ],
                ],
            ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertDatabaseMissing('form_fields', ['id' => $removed->id]);
        $this->assertDatabaseHas('form_fields', [
            'id' => $existing->id,
            'label' => 'Full Name',
            'name' => 'full_name',
            'width' => 'third',
            'default_value' => '{url:name}',
            'css_class' => 'highlight-field',
            'sort_order' => 1,
        ]);
        $this->assertDatabaseHas('form_fields', [
            'form_id' => $form->id,
            'name' => 'gdpr_consent',
            'type' => FormFieldType::GdprConsent->value,
            'sort_order' => 0,
        ]);
    }

    public function test_form_can_be_duplicated_as_draft_with_all_fields(): void
    {
        $form = Form::factory()->create([
            'title' => 'Customer Intake',
            'slug' => 'customer-intake',
            'is_active' => true,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Website',
            'name' => 'website',
            'type' => FormFieldType::Url,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.forms.duplicate', $form));

        $copy = Form::query()->where('id', '!=', $form->id)->firstOrFail();

        $response->assertRedirect(route('admin.forms.edit', $copy));
        $this->assertSame('Customer Intake (Copy)', $copy->title);
        $this->assertFalse($copy->is_active);
        $this->assertSame(1, $copy->fields()->count());
        $this->assertSame('website', $copy->fields()->first()->name);
    }

    public function test_new_field_types_and_any_condition_logic_work_on_submission(): void
    {
        $form = Form::factory()->create(['redirect_url' => '/thank-you']);
        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Section Info',
            'name' => 'section_info',
            'type' => FormFieldType::SectionBreak,
            'sort_order' => 0,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Website URL',
            'name' => 'website',
            'type' => FormFieldType::Url,
            'is_required' => true,
            'sort_order' => 1,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Team Size',
            'name' => 'team_size',
            'type' => FormFieldType::Number,
            'sort_order' => 2,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'Enterprise Note',
            'name' => 'enterprise_note',
            'type' => FormFieldType::Text,
            'is_required' => true,
            'condition_match' => 'any',
            'condition' => [
                ['field' => 'team_size', 'operator' => 'greater_than', 'value' => '50'],
                ['field' => 'website', 'operator' => 'contains', 'value' => 'enterprise'],
            ],
            'sort_order' => 3,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'label' => 'GDPR Consent',
            'name' => 'gdpr',
            'type' => FormFieldType::GdprConsent,
            'is_required' => true,
            'sort_order' => 4,
        ]);

        // Fails if GDPR is unchecked or enterprise_note is missing when team_size > 50
        $this->post(route('forms.store', $form), [
            'answers' => [
                'website' => 'https://example.com',
                'team_size' => '100',
            ],
        ])->assertSessionHasErrors(['answers.enterprise_note', 'answers.gdpr']);

        // Succeeds when valid and redirects to redirect_url
        $this->post(route('forms.store', $form), [
            'answers' => [
                'website' => 'https://example.com',
                'team_size' => '100',
                'enterprise_note' => 'Need SSO',
                'gdpr' => '1',
            ],
        ])->assertRedirect('/thank-you');

        $this->assertSame(1, FormSubmission::query()->count());
    }
}
