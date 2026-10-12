<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the control panel sign-in form.
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user !== null) {
            Auth::logout();
        }

        return view('admin.login');
    }

    /**
     * Authenticate an administrator.
     */
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:190'],
            'password' => ['required', 'string', 'max:255'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials + ['is_admin' => true], $remember)) {
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'Those credentials do not match an administrator account.']);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('admin.dashboard'))
            ->with('status', 'Welcome back.');
    }

    /**
     * Sign out of the control panel.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
