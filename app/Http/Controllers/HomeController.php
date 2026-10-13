<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Client;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    use RendersBuilderPages;

    /**
     * Compose every block the landing page renders.
     */
    public function index(): View
    {
        if ($builder = $this->builderPage('home')) {
            return $builder;
        }

        return view('home', [
            'services' => Service::query()->published()->ordered()->limit(6)->get(),
            'projects' => Project::query()->published()->ordered()->get(),
            'posts' => Post::query()->published()->ordered()->limit(3)->get(),
            'testimonials' => Testimonial::query()->published()->ordered()->get(),
            'clients' => Client::query()->published()->ordered()->get(),
            'stats' => Setting::structured('stats', [
                ['value' => 12, 'label' => 'years of Experience'],
                ['value' => 250, 'label' => 'projects Delivered'],
                ['value' => 90, 'label' => 'happy Clients'],
                ['value' => 15, 'label' => 'Industry Awards'],
            ]),
            'process' => Setting::structured('process', [
                ['step' => '01', 'title' => 'Discover, Strategy', 'body' => 'We map your business goals, audit the existing systems and define a delivery roadmap with measurable outcomes.'],
                ['step' => '02', 'title' => 'Design, Build', 'body' => 'Our engineers and designers ship in short iterations with continuous review, so you see working software every sprint.'],
                ['step' => '03', 'title' => 'Launch, Scale', 'body' => 'We deploy, monitor and harden the product, then stay on as your long term technology partner.'],
            ]),
        ]);
    }
}
