<?php

namespace Tests\Feature;

use App\Enums\RequirementStatus;
use App\Models\RequirementSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequirementFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
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

        $submission = RequirementSubmission::sole();

        $this->assertSame('REQ-', substr($submission->reference, 0, 4));
        $this->assertStringContainsString($submission->reference, (string) $response->getSession()->get('status'));
        $this->assertSame('Priya Sharma', $submission->name);
        $this->assertSame(RequirementStatus::New, $submission->status);
        $this->assertSame(['Web application', 'Mobile app (iOS)'], $submission->solutions);
        $this->assertSame('Hospital & healthcare', $submission->industry);
    }

    public function test_required_sections_are_enforced(): void
    {
        $response = $this->from(route('requirements.create'))
            ->post(route('requirements.store'), ['name' => 'No Brief']);

        $response->assertSessionHasErrors([
            'email',
            'industry',
            'project_name',
            'brief',
            'solutions',
            'features',
            'user_scale',
            'budget',
            'timeline',
        ]);

        $this->assertSame(0, RequirementSubmission::query()->count());
    }

    public function test_unknown_options_are_rejected(): void
    {
        $payload = array_merge($this->validPayload(), ['solutions' => ['Quantum blockchain']]);

        $this->post(route('requirements.store'), $payload)
            ->assertSessionHasErrors('solutions.0');

        $this->assertSame(0, RequirementSubmission::query()->count());
    }

    public function test_optional_integrations_may_be_omitted(): void
    {
        $payload = array_merge($this->validPayload(), ['integrations' => null]);

        $this->post(route('requirements.store'), $payload)
            ->assertSessionDoesntHaveErrors();

        $this->assertNull(RequirementSubmission::sole()->integrations);
    }

    public function test_the_form_re_renders_when_scalars_are_sent_for_multi_selects(): void
    {
        $payload = array_merge($this->validPayload(), ['solutions' => 'Web application']);

        $this->from(route('requirements.create'))
            ->post(route('requirements.store'), $payload)
            ->assertSessionHasErrors('solutions');

        $this->get(route('requirements.create'))
            ->assertOk()
            ->assertSee('Web application');

        $this->assertSame(0, RequirementSubmission::query()->count());
    }
}
