<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Self-service account creation for clients buying digital products.
 */
class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        if ($user !== null) {
            return redirect()->route($user->isAdmin() ? 'admin.dashboard' : 'dashboard.index');
        }

        return view('auth.register');
    }

    /**
     * Create the account and sign the client straight in.
     */
    public function store(StoreRegistrationRequest $request): RedirectResponse
    {
        $user = User::create(
            $request->safe()->only(['name', 'email', 'phone', 'password']) + ['is_admin' => false]
        );

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard.index')
            ->with('status', 'Your account is ready. Purchases made with this email address appear in your dashboard.');
    }
}
