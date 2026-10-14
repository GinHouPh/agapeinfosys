<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Number;

/**
 * Client/user management: the accounts behind the client dashboard.
 *
 * Orders and subscriptions are matched on the account's email address —
 * the identity checkout records for guest purchases.
 */
class UserController extends Controller
{
    /**
     * Paginated client list with name/email search and a role filter.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');
        $role = (string) $request->query('role', '');

        $users = User::query()
            ->withCount(['orders', 'subscriptions'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($builder) => $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"));
            })
            ->when($role === 'admins', fn ($query) => $query->where('is_admin', true))
            ->when($role === 'clients', fn ($query) => $query->where('is_admin', false))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
        ]);
    }

    /**
     * Client detail with their purchase history and access.
     */
    public function show(User $user): View
    {
        $user->load([
            'orders' => fn ($query) => $query->with(['product', 'plan'])->orderByDesc('id'),
            'subscriptions' => fn ($query) => $query->with(['product', 'plan'])->orderByDesc('id'),
        ]);

        return view('admin.users.show', [
            'user' => $user,
            'recentOrders' => $user->orders->take(10)->values(),
            'recentSubscriptions' => $user->subscriptions->take(10)->values(),
            'paidOrderCount' => $user->orders->where('status', OrderStatus::Paid)->count(),
            'activeSubscriptionCount' => $user->subscriptions
                ->where('status', SubscriptionStatus::Active)
                ->count(),
            'lifetimeSpend' => $this->lifetimeSpend($user),
        ]);
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.users.create');
    }

    /**
     * Persist a new client account.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create($request->safe()->except('is_admin') + [
            'is_admin' => $request->boolean('is_admin'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Client account created.');
    }

    /**
     * Edit form.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Apply edits to an existing account.
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user) && ! $request->boolean('is_admin')) {
            return back()
                ->withInput()
                ->withErrors(['is_admin' => 'You cannot remove your own administrator access.']);
        }

        $attributes = $request->safe()->except(['password', 'is_admin'])
            + ['is_admin' => $request->boolean('is_admin')];

        if ($request->filled('password')) {
            $attributes['password'] = $request->validated('password');
        }

        $user->update($attributes);

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Client account updated.');
    }

    /**
     * Remove a client account. Their orders stay on record.
     */
    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', 'Client account deleted.');
    }

    /**
     * Total paid by the client, grouped per currency.
     *
     * @return array<string, string>
     */
    private function lifetimeSpend(User $user): array
    {
        return $user->orders()
            ->paid()
            ->selectRaw('currency, sum(amount) as total, count(*) as orders_count')
            ->groupBy('currency')
            ->get()
            ->mapWithKeys(fn ($row): array => [
                $row->currency => Number::currency(((int) $row->total) / 100, $row->currency)
                    .' across '.$row->orders_count.' '.str()->plural('order', (int) $row->orders_count),
            ])
            ->all();
    }
}
