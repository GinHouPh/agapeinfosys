<?php

namespace Tests\Feature;

use App\Enums\SubmissionStatus;
use App\Models\Form;
use App\Models\FormField;
use App\Models\FormSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Form $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
        $this->form = Form::factory()->create(['title' => 'Requirement Specification']);
    }

    public function test_guests_are_redirected_from_the_submissions_inbox(): void
    {
        $submission = FormSubmission::factory()->create(['form_id' => $this->form->id]);

        $this->get(route('admin.submissions.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.submissions.show', $submission))->assertRedirect(route('admin.login'));
        $this->patch(route('admin.submissions.status', $submission), [])->assertRedirect(route('admin.login'));
        $this->delete(route('admin.submissions.destroy', $submission))->assertRedirect(route('admin.login'));
    }

    public function test_the_inbox_lists_submissions_across_forms(): void
    {
        FormSubmission::factory()->create([
            'form_id' => $this->form->id,
            'answers' => ['name' => 'Priya Sharma'],
        ]);
        FormSubmission::factory()->create(['form_id' => Form::factory()->create()->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.index'))
            ->assertOk()
            ->assertSee('Priya Sharma')
            ->assertSee('Requirement Specification');
    }

    public function test_the_inbox_can_be_filtered_by_form(): void
    {
        $jobForm = Form::factory()->create(['title' => 'Careers']);

        FormSubmission::factory()->create(['form_id' => $this->form->id]);
        FormSubmission::factory()->create(['form_id' => $jobForm->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.index', ['form' => $jobForm->id]))
            ->assertOk()
            ->assertSee('Careers');
    }

    public function test_the_inbox_can_be_searched_by_reference(): void
    {
        FormSubmission::factory()->create([
            'form_id' => $this->form->id,
            'reference' => 'REQ-FINDME',
            'answers' => ['name' => 'Priya Sharma'],
        ]);
        FormSubmission::factory()->create([
            'form_id' => $this->form->id,
            'answers' => ['name' => 'Rahul Verma'],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.index', ['q' => 'FINDME']))
            ->assertOk()
            ->assertSee('REQ-FINDME')
            ->assertDontSee('Rahul Verma');
    }

    public function test_the_inbox_can_be_filtered_by_status(): void
    {
        FormSubmission::factory()->create(['form_id' => $this->form->id]);
        FormSubmission::factory()->create(['form_id' => $this->form->id, 'status' => SubmissionStatus::Quoted]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.submissions.index', ['status' => 'quoted']))
            ->assertOk();

        $this->assertCount(1, $response->viewData('submissions'));
    }

    public function test_a_submission_shows_every_answer_against_its_question(): void
    {
        FormField::factory()->create(['form_id' => $this->form->id, 'label' => 'Full Name', 'name' => 'name']);
        FormField::factory()->withOptions(['Red', 'Blue'])->create([
            'form_id' => $this->form->id,
            'label' => 'Favourite Colour',
            'name' => 'colour',
        ]);

        $submission = FormSubmission::factory()->create([
            'form_id' => $this->form->id,
            'answers' => ['name' => 'Priya Sharma', 'colour' => ['Red', 'Blue']],
        ]);

        $this->actingAs($this->admin)
            ->get(route('admin.submissions.show', $submission))
            ->assertOk()
            ->assertSee('Full Name')
            ->assertSee('Priya Sharma')
            ->assertSee('Favourite Colour')
            ->assertSee('Red')
            ->assertSee('Blue');
    }

    public function test_a_submission_status_can_be_updated(): void
    {
        $submission = FormSubmission::factory()->create(['form_id' => $this->form->id]);

        $this->actingAs($this->admin)
            ->patch(route('admin.submissions.status', $submission), ['status' => 'quoted'])
            ->assertRedirect(route('admin.submissions.show', $submission));

        $this->assertSame(SubmissionStatus::Quoted, $submission->refresh()->status);
    }

    public function test_an_invalid_status_is_rejected(): void
    {
        $submission = FormSubmission::factory()->create(['form_id' => $this->form->id]);

        $this->actingAs($this->admin)
            ->from(route('admin.submissions.show', $submission))
            ->patch(route('admin.submissions.status', $submission), ['status' => 'banana'])
            ->assertSessionHasErrors('status');

        $this->assertSame(SubmissionStatus::New, $submission->refresh()->status);
    }

    public function test_a_submission_can_be_deleted(): void
    {
        $submission = FormSubmission::factory()->create(['form_id' => $this->form->id]);

        $this->actingAs($this->admin)
            ->delete(route('admin.submissions.destroy', $submission))
            ->assertRedirect(route('admin.submissions.index'));

        $this->assertSame(0, FormSubmission::query()->count());
    }
}
