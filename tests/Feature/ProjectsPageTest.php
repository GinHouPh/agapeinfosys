<?php

namespace Tests\Feature;

use App\Models\ShowcaseProject;
use Database\Seeders\ShowcaseProjectSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_projects_page_lists_published_projects_only(): void
    {
        ShowcaseProject::factory()->create(['title' => 'Streamly']);
        ShowcaseProject::factory()->hidden()->create(['title' => 'Secret Projekt']);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Streamly')
            ->assertDontSee('Secret Projekt');
    }

    public function test_status_filter_separates_ongoing_from_upcoming(): void
    {
        ShowcaseProject::factory()->create(['title' => 'Ongoing Thing']);
        ShowcaseProject::factory()->upcoming()->create(['title' => 'Upcoming Thing']);

        $this->get(route('projects.index', ['status' => 'upcoming']))
            ->assertOk()
            ->assertSee('Upcoming Thing')
            ->assertDontSee('Ongoing Thing');

        $this->get(route('projects.index', ['status' => 'ongoing']))
            ->assertOk()
            ->assertSee('Ongoing Thing')
            ->assertDontSee('Upcoming Thing');
    }

    public function test_industry_filter_narrows_the_wall(): void
    {
        ShowcaseProject::factory()->create(['title' => 'OTT One', 'industry' => 'OTT & Media']);
        ShowcaseProject::factory()->create(['title' => 'POS One', 'industry' => 'POS & Retail']);

        $this->get(route('projects.index', ['industry' => 'POS & Retail']))
            ->assertOk()
            ->assertSee('POS One')
            ->assertDontSee('OTT One');
    }

    public function test_invalid_filter_values_fall_back_to_showing_all(): void
    {
        ShowcaseProject::factory()->create(['title' => 'Any Project']);

        $this->get(route('projects.index', ['status' => 'bogus', 'industry' => 'Nope']))
            ->assertOk()
            ->assertSee('Any Project');
    }

    public function test_filters_combine_status_and_industry(): void
    {
        ShowcaseProject::factory()->create(['title' => 'OTT Ongoing', 'industry' => 'OTT & Media']);
        ShowcaseProject::factory()->upcoming()->create(['title' => 'OTT Upcoming', 'industry' => 'OTT & Media']);
        ShowcaseProject::factory()->upcoming()->create(['title' => 'POS Upcoming', 'industry' => 'POS & Retail']);

        $this->get(route('projects.index', ['status' => 'upcoming', 'industry' => 'OTT & Media']))
            ->assertOk()
            ->assertSee('OTT Upcoming')
            ->assertDontSee('OTT Ongoing')
            ->assertDontSee('POS Upcoming');
    }

    public function test_seeded_showcase_projects_render(): void
    {
        $this->seed(ShowcaseProjectSeeder::class);

        $this->get(route('projects.index'))
            ->assertOk()
            ->assertSee('Streamly')
            ->assertSee('RentRadar');
    }
}
