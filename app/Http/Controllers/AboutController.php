<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Client;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    use RendersBuilderPages;

    /**
     * Company story, capability overview and proof points.
     */
    public function index(): View
    {
        if ($builder = $this->builderPage('about')) {
            return $builder;
        }

        return view('about', [
            'services' => Service::query()->published()->ordered()->limit(3)->get(),
            'projects' => Project::query()->published()->featured()->ordered()->limit(3)->get(),
            'clients' => Client::query()->published()->ordered()->get(),
            'stats' => Setting::structured('stats', [
                ['value' => 12, 'label' => 'years of Experience'],
                ['value' => 250, 'label' => 'projects Delivered'],
                ['value' => 90, 'label' => 'happy Clients'],
                ['value' => 15, 'label' => 'Industry Awards'],
            ]),
            'values' => Setting::structured('values', [
                ['title' => 'Ownership', 'body' => 'We treat your product like our own — accountable for uptime, budgets and business results.'],
                ['title' => 'Transparency', 'body' => 'Open estimates, visible backlogs and weekly demos. No surprises at the end of the sprint.'],
                ['title' => 'Craft', 'body' => 'Clean architecture, automated tests and documentation that outlives the engagement.'],
                ['title' => 'Partnership', 'body' => 'Senior consultants who stay with you from discovery through to long term support.'],
            ]),
        ]);
    }
}
