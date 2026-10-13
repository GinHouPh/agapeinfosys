<?php

namespace Tests\Feature;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_form_builder(): void
    {
        $form = Form::factory()->create();

        $this->get(route('admin.forms.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.forms.create'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.forms.edit', $form))->assertRedirect(route('admin.login'));
        $this->post(route('admin.forms.store'), [])->assertRedirect(route('admin.login'));
        $this->put(route('admin.forms.update', $form), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.forms.destroy', $form))->assertRedirect(route('admin.login'));
        $this->post(route('admin.forms.fields.store', $form), [])->assertRedirect(route('admin.login'));
    }

    public function test_forms_index_lists_existing_forms(): void
    {
        Form::factory()->create(['title' => 'Partner Application']);

        $this->actingAs($this->admin)
            ->get(route('admin.forms.index'))
            ->assertOk()
            ->assertSee('Partner Application');
    }

    public function test_a_form_can_be_created_with_a_slug_derived_from_the_title(): void
    {
        $response = $this->actingAs($this->admin)->post(route('admin.forms.store'), [
            'title' => 'Partner Application',
            'submit_label' => 'Apply Now',
            'reference_prefix' => 'APP',
            'is_active' => 1,
        ]);

        $form = Form::sole();

        $response->assertRedirect(route('admin.forms.edit', $form));
        $this->assertSame('partner-application', $form->slug);
        $this->assertSame('APP', $form->reference_prefix);
        $this->assertTrue($form->is_active);
    }

    public function test_form_title_is_required(): void
    {
        $this->actingAs($this->admin)
            ->from(route('admin.forms.create'))
            ->post(route('admin.forms.store'), [
                'submit_label' => 'Submit',
                'reference_prefix' => 'REQ',
            ])
            ->assertRedirect(route('admin.forms.create'))
            ->assertSessionHasErrors('title');

        $this->assertSame(0, Form::query()->count());
    }

    public function test_duplicate_slugs_are_rejected(): void
    {
        Form::factory()->create(['slug' => 'partner-application']);

        $this->actingAs($this->admin)
            ->post(route('admin.forms.store'), [
                'title' => 'Another',
                'slug' => 'partner-application',
                'submit_label' => 'Submit',
                'reference_prefix' => 'REQ',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_form_meta_can_be_updated(): void
    {
        $form = Form::factory()->create();

        $this->actingAs($this->admin)
            ->put(route('admin.forms.update', $form), [
                'title' => 'Vendor Onboarding',
                'slug' => 'vendor-onboarding',
                'submit_label' => 'Send',
                'reference_prefix' => 'VEN',
            ])
            ->assertRedirect(route('admin.forms.edit', $form));

        $this->assertSame('Vendor Onboarding', $form->refresh()->title);
        $this->assertSame('vendor-onboarding', $form->slug);
    }

    public function test_a_question_can_be_added_with_options_and_an_auto_generated_key(): void
    {
        $form = Form::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'Budget Range',
                'type' => FormFieldType::Select->value,
                'group' => 'Commercial',
                'options' => "Under 2L\nAbove 2L",
                'is_required' => 1,
            ])
            ->assertRedirect(route('admin.forms.edit', $form));

        $field = FormField::sole();

        $this->assertSame('budget_range', $field->name);
        $this->assertSame(['Under 2L', 'Above 2L'], $field->options);
        $this->assertSame('Commercial', $field->group);
        $this->assertTrue($field->is_required);
        $this->assertSame(0, $field->sort_order);
    }

    public function test_questions_are_appended_in_order(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'sort_order' => 4]);

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'Email',
                'type' => FormFieldType::Email->value,
            ]);

        $this->assertSame(5, FormField::query()->where('form_id', $form->id)->latest('id')->first()->sort_order);
    }

    public function test_choice_types_require_at_least_one_option(): void
    {
        $form = Form::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'Favourite Colour',
                'type' => FormFieldType::Select->value,
                'options' => "  \n ",
            ])
            ->assertSessionHasErrors('options');

        $this->assertSame(0, FormField::query()->count());
    }

    public function test_options_are_dropped_for_plain_text_questions(): void
    {
        $form = Form::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'Anything Else',
                'type' => FormFieldType::Text->value,
                'options' => 'Not used',
            ]);

        $this->assertNull(FormField::sole()->options);
    }

    public function test_duplicate_question_keys_within_a_form_are_rejected(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'name' => 'email']);

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'Email Again',
                'name' => 'email',
                'type' => FormFieldType::Email->value,
            ])
            ->assertSessionHasErrors('name');
    }

    public function test_auto_generated_keys_are_suffixed_when_the_label_collides(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create(['form_id' => $form->id, 'label' => 'Email', 'name' => 'email']);

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'Email',
                'type' => FormFieldType::Email->value,
            ])
            ->assertRedirect(route('admin.forms.edit', $form));

        $this->assertSame('email_2', FormField::query()->latest('id')->first()->name);
    }

    public function test_a_question_can_be_updated(): void
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create(['form_id' => $form->id, 'type' => FormFieldType::Text]);

        $this->actingAs($this->admin)
            ->put(route('admin.forms.fields.update', [$form, $field]), [
                'label' => 'Preferred Contact Time',
                'type' => FormFieldType::Text->value,
                'is_required' => 1,
            ])
            ->assertRedirect(route('admin.forms.edit', $form));

        $field->refresh();
        $this->assertSame('Preferred Contact Time', $field->label);
        $this->assertTrue($field->is_required);
    }

    public function test_a_question_can_be_deleted(): void
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create(['form_id' => $form->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.forms.fields.destroy', [$form, $field]))
            ->assertRedirect(route('admin.forms.edit', $form));

        $this->assertSame(0, FormField::query()->count());
    }

    public function test_questions_cannot_be_touched_through_another_form(): void
    {
        $form = Form::factory()->create();
        $other = Form::factory()->create();
        $field = FormField::factory()->create(['form_id' => $other->id]);

        $this->actingAs($this->admin)
            ->put(route('admin.forms.fields.update', [$form, $field]), [
                'label' => 'Hijacked',
                'type' => FormFieldType::Text->value,
            ])
            ->assertNotFound();
    }

    public function test_deleting_a_form_removes_its_questions_and_submissions(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->count(2)->create(['form_id' => $form->id]);
        FormSubmission::factory()->count(2)->create(['form_id' => $form->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.forms.destroy', $form))
            ->assertRedirect(route('admin.forms.index'));

        $this->assertSame(0, FormField::query()->count());
        $this->assertSame(0, FormSubmission::query()->count());
    }
}
