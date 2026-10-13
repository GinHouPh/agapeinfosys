<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use RendersBuilderPages;

    /**
     * Full capability catalogue.
     */
    public function index(): View
    {
        if ($builder = $this->builderPage('services')) {
            return $builder;
        }

        return view('services.index', [
            'services' => Service::query()->published()->ordered()->get(),
        ]);
    }

    /**
     * Detail page for a single capability.
     */
    public function show(Request $request, Service $service): View
    {
        return view('services.show', [
            'service' => $service,
            'others' => Service::query()
                ->published()
                ->ordered()
                ->whereKeyNot($service->getKey())
                ->limit(3)
                ->get(),
        ]);
    }
}
