<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Redirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RedirectController extends Controller
{
    use RecordsActivity;

    /**
     * Every redirect, and the form to add one.
     */
    public function index(): View
    {
        $redirects = Redirect::query()->orderBy('from_path')->get();

        return view('admin.seo.redirects', compact('redirects'));
    }

    /**
     * Create a redirect.
     */
    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'from_path' => ['required', 'string', 'max:255', 'starts_with:/', Rule::unique('redirects', 'from_path')],
            'to_path' => ['required', 'string', 'max:255', 'starts_with:/'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 307, 308])],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $attributes['is_active'] = $request->boolean('is_active', true);

        $redirect = Redirect::query()->create($attributes);

        $this->recordActivity('created', $redirect, 'Created the redirect from '.$redirect->from_path.'.');

        return redirect()
            ->route('admin.seo.redirects')
            ->with('status', 'Redirect created.');
    }

    /**
     * Delete a redirect.
     */
    public function destroy(Redirect $redirect): RedirectResponse
    {
        $from = $redirect->from_path;

        $redirect->delete();

        $this->recordActivity('deleted', null, 'Deleted the redirect from '.$from.'.');

        return redirect()
            ->route('admin.seo.redirects')
            ->with('status', 'Redirect deleted.');
    }
}
