<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Setting;
use App\Services\FeatureFlagService;
use App\Services\SystemHealthService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    use RecordsActivity;

    /**
     * The platform overview: health, flags and the maintenance switch.
     */
    public function index(SystemHealthService $health, FeatureFlagService $flags): View
    {
        return view('admin.system.index', [
            'report' => $health->report(),
            'flags' => $flags->all(),
            'maintenance' => Setting::structured('maintenance', []),
        ]);
    }

    /**
     * Turn maintenance mode on or off.
     */
    public function maintenance(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'enabled' => ['sometimes', 'boolean'],
            'message' => ['nullable', 'string', 'max:400'],
        ]);

        $maintenance = [
            'enabled' => $request->boolean('enabled'),
            'message' => $attributes['message'] ?? 'We are carrying out scheduled work. We will be back shortly.',
        ];

        Setting::store(['maintenance' => $maintenance]);

        $this->recordActivity(
            'updated',
            null,
            $maintenance['enabled'] ? 'Turned maintenance mode on.' : 'Turned maintenance mode off.',
        );

        return redirect()
            ->route('admin.system.index')
            ->with('status', $maintenance['enabled']
                ? 'Maintenance mode is on — visitors see the notice.'
                : 'Maintenance mode is off.');
    }
}
