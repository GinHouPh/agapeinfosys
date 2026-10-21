<?php

namespace Tests\Feature;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\User;
use App\Notifications\NewFormSubmissionNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FormsUpgradeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();

        Storage::fake('public');
    }

    public function test_a_file_field_keeps_the_uploaded_file(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'brief',
            'type' => FormFieldType::File,
            'is_required' => true,
            'file_settings' => ['mimes' => ['pdf'], 'max_kilobytes' => 5120],
        ]);

        $this->post(route('forms.store', $form), [
            'answers' => ['brief' => UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf')],
        ])->assertRedirect();

        $submission = FormSubmission::query()->firstOrFail();

        $this->assertStringStartsWith('uploads/forms/'.$form->slug, $submission->answers['brief']);
        Storage::disk('public')->assertExists($submission->answers['brief']);
    }

    public function test_a_file_field_rejects_a_type_it_does_not_accept(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'brief',
            'type' => FormFieldType::File,
            'file_settings' => ['mimes' => ['pdf'], 'max_kilobytes' => 5120],
        ]);

        $this->post(route('forms.store', $form), [
            'answers' => ['brief' => UploadedFile::fake()->create('brief.exe', 10, 'application/octet-stream')],
        ])->assertSessionHasErrors('answers.brief');

        $this->assertDatabaseCount('form_submissions', 0);
    }

    public function test_a_conditional_field_is_not_required_when_its_condition_is_not_met(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'company',
            'type' => FormFieldType::Text,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'vat_number',
            'type' => FormFieldType::Text,
            'is_required' => true,
            'condition' => [['field' => 'company', 'operator' => 'equals', 'value' => 'Yes']],
        ]);

        $this->post(route('forms.store', $form), [
            'answers' => ['company' => 'No'],
        ])->assertSessionHasNoErrors();

        $submission = FormSubmission::query()->firstOrFail();

        $this->assertArrayNotHasKey('vat_number', $submission->answers);
    }

    public function test_a_conditional_field_is_required_when_its_condition_is_met(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'company',
            'type' => FormFieldType::Text,
        ]);
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'vat_number',
            'type' => FormFieldType::Text,
            'is_required' => true,
            'condition' => [['field' => 'company', 'operator' => 'equals', 'value' => 'Yes']],
        ]);

        $this->post(route('forms.store', $form), [
            'answers' => ['company' => 'Yes'],
        ])->assertSessionHasErrors('answers.vat_number');
    }

    public function test_a_filled_honeypot_records_the_submission_as_spam(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'name',
            'type' => FormFieldType::Text,
            'is_required' => true,
        ]);

        $this->post(route('forms.store', $form), [
            'hp' => 'https://spam.example.com',
            'answers' => [],
        ])->assertSessionHasNoErrors();

        $submission = FormSubmission::query()->firstOrFail();

        $this->assertTrue($submission->is_spam);
    }

    public function test_a_submission_records_where_it_came_from(): void
    {
        $form = Form::factory()->create();

        $this->post(route('forms.store', $form), [
            'answers' => [],
        ])->assertSessionHasNoErrors();

        $submission = FormSubmission::query()->firstOrFail();

        $this->assertNotNull($submission->page_url);
        $this->assertArrayHasKey('ip', $submission->meta);
    }

    public function test_a_form_stops_accepting_submissions_past_its_rate(): void
    {
        $form = Form::factory()->create(['throttle_per_minute' => 2]);

        for ($i = 0; $i < 2; $i++) {
            $this->post(route('forms.store', $form), ['answers' => []])->assertSessionHasNoErrors();
        }

        $this->post(route('forms.store', $form), ['answers' => []])->assertStatus(429);
    }

    public function test_a_submission_is_announced_when_the_form_asks_for_one(): void
    {
        Notification::fake();

        $form = Form::factory()->create([
            'notification_settings' => [
                'notify' => true,
                'email' => 'leads@example.com',
                'subject' => 'New {form} submission',
            ],
        ]);

        $this->post(route('forms.store', $form), ['answers' => []])->assertSessionHasNoErrors();

        Notification::assertSentOnDemand(
            NewFormSubmissionNotification::class,
            function (NewFormSubmissionNotification $notification): bool {
                return $notification->form->id === $notification->form->id;
            },
        );
    }

    public function test_no_submission_is_announced_when_the_form_asks_for_nothing(): void
    {
        Notification::fake();

        $form = Form::factory()->create();

        $this->post(route('forms.store', $form), ['answers' => []])->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_the_submissions_can_be_exported_as_csv(): void
    {
        $form = Form::factory()->create();
        FormField::factory()->create([
            'form_id' => $form->id,
            'name' => 'name',
            'label' => 'Full name',
            'type' => FormFieldType::Text,
        ]);

        FormSubmission::query()->create([
            'form_id' => $form->id,
            'answers' => ['name' => 'Ada Lovelace'],
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.submissions.export', ['form' => $form->id]));

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Reference', $csv);
        $this->assertStringContainsString('Full name', $csv);
        $this->assertStringContainsString('Ada Lovelace', $csv);
    }

    public function test_a_question_can_be_given_a_condition_and_a_width(): void
    {
        $form = Form::factory()->create();
        $field = FormField::factory()->create(['form_id' => $form->id, 'name' => 'company']);

        $this->actingAs($this->admin)
            ->post(route('admin.forms.fields.store', $form), [
                'label' => 'VAT number',
                'type' => FormFieldType::Text->value,
                'width' => 'half',
                'validation' => "min:3\nmax:20",
                'condition_field' => 'company',
                'condition_operator' => 'equals',
                'condition_value' => 'Yes',
            ])
            ->assertRedirect();

        $created = FormField::query()->where('name', 'vat_number')->firstOrFail();

        $this->assertSame('half', $created->width);
        $this->assertSame(['min:3', 'max:20'], $created->validationRules());
        $this->assertSame('company', $created->condition[0]['field']);
        $this->assertSame('equals', $created->condition[0]['operator']);
    }
}
