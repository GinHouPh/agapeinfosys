<?php

namespace Tests\Feature;

use App\Enums\SubmissionStatus;
use App\Models\Form;
use App\Models\FormSubmission;
use Database\Seeders\RequirementFormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequirementFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RequirementFormSeeder::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'answers' => [
                'name' => 'Priya Sharma',
                'email' => 'priya@example.com',
                'phone' => '+91 98765 43210',
                'company' => 'Acme Clinics',
                'industry' => 'Hospital & healthcare',
                'project_name' => 'Clinic Companion',
                'brief' => 'A patient companion app for our chain of clinics with booking and reports.',
                'solutions' => ['Web application', 'Mobile app (iOS)'],
                'features' => 'Registration, appointment booking, payments, reports and an admin console.',
                'integrations' => ['Payment gateway', 'SMS / WhatsApp'],
                'user_scale' => '1,000 – 10,000 users',
                'budget' => '₹5,00,000 – ₹15,00,000',
                'timeline' => '3 – 6 months',
                'notes' => 'We already use a CRM for leads.',
            ],
        ];
    }

    public function test_requirement_page_renders_with_all_sections(): void
    {
        $this->get(route('requirements.create'))
            ->assertOk()
            ->assertSee('Requirement Specification')
            ->assertSee('Solutions needed')
            ->assertSee('Scale, budget');
    }

    public function test_a_requirement_brief_can_be_submitted(): void
    {
        $response = $this->post(route('requirements.store'), $this->validPayload());

        $response->assertRedirect(route('requirements.create'));
        $response->assertSessionHas('status');

        $submission = FormSubmission::sole();

        $this->assertSame('REQ-', substr($submission->reference, 0, 4));
        $this->assertStringContainsString($submission->reference, (string) $response->getSession()->get('status'));
        $this->assertSame('Priya Sharma', $submission->answers['name']);
        $this->assertSame(SubmissionStatus::New->value, $submission->status->value);
        $this->assertSame(['Web application', 'Mobile app (iOS)'], $submission->answers['solutions']);
        $this->assertSame('Hospital & healthcare', $submission->answers['industry']);
    }

    public function test_required_sections_are_enforced(): void
    {
        $response = $this->from(route('requirements.create'))
            ->post(route('requirements.store'), ['name' => 'No Brief']);

        $response->assertSessionHasErrors([
            'answers.email',
            'answers.industry',
            'answers.project_name',
            'answers.brief',
            'answers.solutions',
            'answers.features',
            'answers.user_scale',
            'answers.budget',
            'answers.timeline',
        ]);

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_validation_errors_use_the_question_labels(): void
    {
        $this->from(route('requirements.create'))
            ->post(route('requirements.store'), [])
            ->assertSessionHasErrors(['answers.email' => 'The Email field is required.']);
    }

    public function test_unknown_options_are_rejected(): void
    {
        $payload = array_merge($this->validPayload(), [
            'answers' => array_merge($this->validPayload()['answers'], ['solutions' => ['Quantum blockchain']]),
        ]);

        $this->post(route('requirements.store'), $payload)
            ->assertSessionHasErrors('answers.solutions.0');

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_unknown_dropdown_options_are_rejected(): void
    {
        $payload = array_merge($this->validPayload(), [
            'answers' => array_merge($this->validPayload()['answers'], ['industry' => 'Time travel']),
        ]);

        $this->post(route('requirements.store'), $payload)
            ->assertSessionHasErrors('answers.industry');

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_optional_integrations_may_be_omitted(): void
    {
        $payload = array_merge($this->validPayload(), [
            'answers' => array_merge($this->validPayload()['answers'], ['integrations' => null]),
        ]);

        $this->post(route('requirements.store'), $payload)
            ->assertSessionDoesntHaveErrors();

        $this->assertNull(FormSubmission::sole()->answers['integrations'] ?? null);
    }

    public function test_the_form_re_renders_when_scalars_are_sent_for_multi_selects(): void
    {
        $payload = array_merge($this->validPayload(), [
            'answers' => array_merge($this->validPayload()['answers'], ['solutions' => 'Web application']),
        ]);

        $this->from(route('requirements.create'))
            ->post(route('requirements.store'), $payload)
            ->assertSessionHasErrors('answers.solutions');

        $this->get(route('requirements.create'))
            ->assertOk()
            ->assertSee('Web application');

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_the_requirement_form_is_also_live_at_its_slug_url(): void
    {
        $form = Form::query()->where('slug', 'requirement-specification')->sole();

        $this->get(route('forms.show', $form))
            ->assertOk()
            ->assertSee('Requirement Specification');
    }
}
