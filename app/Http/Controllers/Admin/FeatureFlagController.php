<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\FeatureFlag;
use App\Services\FeatureFlagService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeatureFlagController extends Controller
{
    use RecordsActivity;

    /**
     * Every switch on the platform.
     */
    public function index(FeatureFlagService $flags): View
    {
        return view('admin.system.flags', [
            'flags' => $flags->all(),
        ]);
    }

    /**
     * Create a switch.
     */
    public function store(Request $request, FeatureFlagService $flags): RedirectResponse
    {
        $attributes = $request->validate([
            'key' => ['required', 'string', 'max:120', 'alpha_dash', Rule::unique('feature_flags', 'key')],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:300'],
            'rollout_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        $flag = FeatureFlag::query()->create($attributes);

        $flags->flush();

        $this->recordActivity('created', $flag, 'Created the feature flag "'.$flag->name.'".');

        return redirect()
            ->route('admin.system.flags')
            ->with('status', 'Feature flag created.');
    }

    /**
     * Update a switch.
     */
    public function update(Request $request, FeatureFlag $flag, FeatureFlagService $flags): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:300'],
            'is_enabled' => ['sometimes', 'boolean'],
            'rollout_percentage' => ['required', 'integer', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        $attributes['is_enabled'] = $request->boolean('is_enabled');

        $flag->update($attributes);

        $flags->flush();

        $this->recordActivity('updated', $flag, 'Updated the feature flag "'.$flag->name.'".');

        return redirect()
            ->route('admin.system.flags')
            ->with('status', 'Feature flag updated.');
    }

    /**
     * Delete a switch.
     */
    public function destroy(FeatureFlag $flag, FeatureFlagService $flags): RedirectResponse
    {
        $name = $flag->name;

        $flag->delete();

        $flags->flush();

        $this->recordActivity('deleted', null, 'Deleted the feature flag "'.$name.'".');

        return redirect()
            ->route('admin.system.flags')
            ->with('status', 'Feature flag deleted.');
    }
}
