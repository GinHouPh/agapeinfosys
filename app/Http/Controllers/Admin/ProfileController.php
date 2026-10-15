<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The administrator's own account: details, password, and deletion.
 */
class ProfileController extends Controller
{
    /**
     * Profile, password, and danger-zone forms.
     */
    public function edit(Request $request): View
    {
        return view('admin.profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Apply the profile edits. Role access is never changed from here.
     */
    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()
            ->route('admin.profile.edit')
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
            ->route('admin.profile.edit')
            ->with('status', 'Password changed.');
    }

    /**
     * Delete the administrator's own account and end the session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()
            ->route('admin.login')
            ->with('status', 'Your account has been deleted.');
    }
}
