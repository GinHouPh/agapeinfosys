<?php

namespace Tests\Feature;

use App\Enums\RequirementStatus;
use App\Models\RequirementSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRequirementsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    public function test_guests_are_redirected_from_the_requirements_inbox(): void
    {
        $submission = RequirementSubmission::factory()->create();

        $this->get(route('admin.requirements.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.requirements.show', $submission))->assertRedirect(route('admin.login'));
    }

    public function test_the_inbox_lists_submissions_with_search_and_filter(): void
    {
        RequirementSubmission::factory()->create(['project_name' => 'Clinic Companion']);
        RequirementSubmission::factory()->reviewed()->create(['project_name' => 'Hidden Gem']);

        $this->actingAs($this->admin)
            ->get(route('admin.requirements.index'))
            ->assertOk()
            ->assertSee('Clinic Companion')
            ->assertSee('Hidden Gem');

        $this->actingAs($this->admin)
            ->get(route('admin.requirements.index', ['status' => 'reviewing']))
            ->assertOk()
            ->assertSee('Hidden Gem')
            ->assertDontSee('Clinic Companion');

        $this->actingAs($this->admin)
            ->get(route('admin.requirements.index', ['q' => 'Clinic']))
            ->assertOk()
            ->assertSee('Clinic Companion')
            ->assertDontSee('Hidden Gem');
    }

    public function test_the_detail_page_shows_the_full_brief(): void
    {
        $submission = RequirementSubmission::factory()->create([
            'brief' => 'We need a hospital management suite for two branches.',
            'features' => 'OPD, IPD, pharmacy, lab and billing modules.',
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.requirements.show', $submission))
            ->assertOk()
            ->assertSee($submission->reference)
            ->assertSee('hospital management suite')
            ->assertSee('OPD, IPD, pharmacy');
    }

    public function test_the_pipeline_status_can_be_updated(): void
    {
        $submission = RequirementSubmission::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.requirements.status', $submission), ['status' => 'quoted'])
            ->assertRedirect(route('admin.requirements.show', $submission));

        $this->assertSame(RequirementStatus::Quoted, $submission->refresh()->status);
    }

    public function test_an_invalid_status_is_rejected(): void
    {
        $submission = RequirementSubmission::factory()->create();

        $this->actingAs($this->admin)
            ->patch(route('admin.requirements.status', $submission), ['status' => 'banana'])
            ->assertSessionHasErrors('status');

        $this->assertSame(RequirementStatus::New, $submission->refresh()->status);
    }

    public function test_a_submission_can_be_deleted(): void
    {
        $submission = RequirementSubmission::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('admin.requirements.destroy', $submission))
            ->assertRedirect(route('admin.requirements.index'));

        $this->assertDatabaseMissing('requirement_submissions', ['id' => $submission->getKey()]);
    }
}
