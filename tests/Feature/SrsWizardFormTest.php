<?php

namespace Tests\Feature;

use App\Enums\FormFieldType;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use Database\Seeders\SrsFormSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SrsWizardFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SrsFormSeeder::class);
    }

    /**
     * @return array<string, mixed>
     */
    private function validSrsPayload(): array
    {
        return [
            'answers' => [
                'project_name' => 'SkyCourier Logistics',
                'project_summary' => "# SkyCourier\n\nReal-time fleet dispatch and last-mile delivery platform.",
                'project_summary_file_name' => 'skycourier-brief.md',
                'industry' => 'Logistics & Transportation',
                'industry_text' => 'Cold-chain medical courier focus',
                'project_archetype' => 'Multi-Tenant B2B SaaS Platform (Customer App + Tenant Admin Console + Platform SuperAdmin)',
                'project_type' => 'Minimum Viable Product (MVP)',
                'target_users' => ['Business Professionals', 'Administrators'],
                'target_users_text' => 'Dispatch coordinators and fleet managers',
                'application_type' => ['Web Application (Browser-based)', 'Mobile App - Cross-platform (iOS + Android)'],
                'admin_panel_architecture' => 'No Admin Panel (Not needed / Zero admin surfaces)',
                'subsystem_portals' => ['Public Marketing / Landing Page (e.g., example.com, SEO & pricing)', 'Client / End-User Web Application (e.g., app.example.com)'],
                'role_types' => ['Guest / Anonymous Visitor (No login required)', 'Registered Customer / Client (Primary User)'],
                'device_support' => ['Desktop (Large screens)', 'Mobile Phone (Portrait)'],
                'authentication' => ['Email & Password'],
                'data_security' => ['Data Encryption at Rest', 'Data Encryption in Transit (SSL/TLS)'],
                'hosting_model' => 'Cloud Hosting (AWS, Azure, Google Cloud)',
                'testing_scope' => ['Unit Testing', 'Integration Testing'],
            ],
        ];
    }

    public function test_srs_seeder_creates_15_sections_and_76_fields(): void
    {
        $form = Form::query()->where('slug', 'srs-specification')->with('fields')->firstOrFail();

        $this->assertTrue($form->isWizard());
        $this->assertSame('SRS', $form->reference_prefix);
        $this->assertCount(76, $form->fields);

        $sections = $form->fields->pluck('section_id')->unique()->values()->all();
        $this->assertCount(15, $sections);
        $this->assertSame('project_overview', $sections[0]);
        $this->assertSame('additional', $sections[14]);

        $summaryField = $form->fields->firstWhere('name', 'project_summary');
        $this->assertNotNull($summaryField);
        $this->assertSame(FormFieldType::LargeTextEditor, $summaryField->type);
    }

    public function test_srs_page_renders_the_15_section_wizard(): void
    {
        $this->get(route('srs.create'))
            ->assertOk()
            ->assertSee('data-form-wizard', false)
            ->assertSee('data-wizard-review-view', false)
            ->assertSee('data-large-editor', false)
            ->assertSee('1. Overview')
            ->assertSee('2. Architecture &amp; Portals', false)
            ->assertSee('15. Additional');
    }

    public function test_srs_wizard_submission_stores_answers_and_serializes_to_srs_format(): void
    {
        $response = $this->post(route('srs.store'), $this->validSrsPayload());

        $response->assertRedirect(route('srs.create'));
        $response->assertSessionHas('status');

        $submission = FormSubmission::sole();
        $this->assertStringStartsWith('SRS-', $submission->reference);
        $this->assertSame('Cold-chain medical courier focus', $submission->answers['industry_text']);
        $this->assertSame('skycourier-brief.md', $submission->answers['project_summary_file_name']);

        $srsData = $submission->toSrsFormData();
        $this->assertSame('SkyCourier Logistics', $srsData['project_overview']['project_name']);
        $this->assertSame('Cold-chain medical courier focus', $srsData['project_overview']['industry_text']);
        $this->assertSame(['Guest / Anonymous Visitor (No login required)', 'Registered Customer / Client (Primary User)'], $srsData['user_roles']['role_types']);

        $prompt = $submission->toSrsPrompt();
        $this->assertStringContainsString('## PROJECT OVERVIEW', $prompt);
        $this->assertStringContainsString('- project name: SkyCourier Logistics', $prompt);
        $this->assertStringContainsString('## USER ROLES', $prompt);
    }

    public function test_srs_required_fields_are_enforced(): void
    {
        $this->from(route('srs.create'))
            ->post(route('srs.store'), ['answers' => []])
            ->assertSessionHasErrors([
                'answers.project_name',
                'answers.industry',
                'answers.project_archetype',
                'answers.project_type',
                'answers.target_users',
                'answers.application_type',
                'answers.admin_panel_architecture',
                'answers.subsystem_portals',
                'answers.role_types',
                'answers.device_support',
                'answers.authentication',
                'answers.data_security',
                'answers.hosting_model',
                'answers.testing_scope',
            ]);

        $this->assertSame(0, FormSubmission::query()->count());
    }

    public function test_admin_can_enable_wizard_mode_and_additional_details_on_custom_form(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post(route('admin.forms.store'), [
            'title' => 'Discovery Wizard',
            'submit_label' => 'Send',
            'reference_prefix' => 'DSC',
            'is_active' => 1,
            'is_wizard' => 1,
        ])->assertRedirect();

        $form = Form::query()->where('slug', 'discovery-wizard')->firstOrFail();
        $this->assertTrue($form->isWizard());

        $this->actingAs($admin)->post(route('admin.forms.fields.store', $form), [
            'label' => 'Platform',
            'type' => FormFieldType::Radio->value,
            'group' => '1. Basics',
            'section_id' => 'basics',
            'options' => "Web\nMobile\nOther",
            'has_text_input' => 1,
            'text_placeholder' => 'Specify platform details...',
            'is_required' => 1,
        ])->assertRedirect();

        $field = $form->fields()->firstOrFail();
        $this->assertTrue($field->has_text_input);
        $this->assertSame('basics', $field->section_id);
        $this->assertSame('Specify platform details...', $field->text_placeholder);
    }
}
