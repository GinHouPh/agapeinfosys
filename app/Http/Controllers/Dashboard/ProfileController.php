<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The client's own account details.
 */
class ProfileController extends Controller
{
    /**
     * Profile and password forms.
     */
    public function edit(Request $request): View
    {
        return view('dashboard.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Apply the profile edits.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()
            ->route('dashboard.profile.edit')
            ->with('status', 'Profile updated.');
    }

    /**
     * Change the account password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        return redirect()
            ->route('dashboard.profile.edit')
            ->with('status', 'Password changed.');
    }
}
