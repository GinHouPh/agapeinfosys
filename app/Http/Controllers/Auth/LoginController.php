<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Sign-in for the client dashboard. Administrators keep using the control
 * panel login at /admin/login, so this form only ever admits non-admins.
 */
class LoginController extends Controller
{
    /**
     * Show the client sign-in form.
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null) {
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard.index');
        }

        return view('auth.login');
    }

    /**
     * Authenticate a client.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials + ['is_admin' => false], $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Those credentials do not match a client account.']);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('dashboard.index'))
            ->with('status', 'Welcome back.');
    }

    /**
     * Sign out of the client dashboard.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
