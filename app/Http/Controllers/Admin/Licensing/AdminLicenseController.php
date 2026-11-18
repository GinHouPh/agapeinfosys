<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\DomainBinding;
use App\Models\License;
use App\Models\SoftwareProduct;
use App\Services\Licensing\AgapeLicenseService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminLicenseController extends Controller
{
    public function __construct(private readonly AgapeLicenseService $licenseService) {}

    /**
     * Display a paginated listing of licenses with filtering.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = (string) $request->query('status', '');
        $channel = (string) $request->query('channel', '');
        $productId = (string) $request->query('product_id', '');

        $licenses = License::query()
            ->with(['product'])
            ->withCount(['domainBindings', 'activeBindings'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($b) => $b
                    ->where('license_key', 'like', "%{$search}%")
                    ->orWhere('buyer_email', 'like', "%{$search}%")
                    ->orWhere('buyer_username', 'like', "%{$search}%"));
            })
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($channel !== '', fn ($query) => $query->where('channel', $channel))
            ->when($productId !== '', fn ($query) => $query->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $products = SoftwareProduct::query()->orderBy('name')->get();

        return view('admin.licensing.licenses.index', [
            'licenses' => $licenses,
            'products' => $products,
            'search' => $search,
            'status' => $status,
            'channel' => $channel,
            'productId' => $productId,
        ]);
    }

    /**
     * Show detailed license view with domain bindings and audit history.
     */
    public function show(License $license): View
    {
        $license->load(['product', 'domainBindings' => fn ($q) => $q->orderByDesc('activated_at')]);

        $auditLogs = $license->auditLogs()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('admin.licensing.licenses.show', [
            'license' => $license,
            'auditLogs' => $auditLogs,
        ]);
    }

    /**
     * Show form for manually creating / issuing a license.
     */
    public function create(): View
    {
        $products = SoftwareProduct::where('is_active', true)->orderBy('name')->get();

        return view('admin.licensing.licenses.create', [
            'products' => $products,
        ]);
    }

    /**
     * Store and issue a newly created manual license.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:software_products,id'],
            'tier' => ['required', 'in:regular,extended,developer,agency,enterprise'],
            'channel' => ['required', 'in:agape_direct,partner,envato'],
            'buyer_email' => ['required', 'email', 'max:191'],
            'buyer_username' => ['nullable', 'string', 'max:100'],
            'max_domains' => ['required', 'integer', 'min:1', 'max:1000'],
            'expires_at' => ['nullable', 'date'],
            'support_until' => ['nullable', 'date'],
        ]);

        $product = SoftwareProduct::findOrFail($validated['product_id']);
        $productPrefix = strtoupper(substr($product->slug, 0, 3));
        $licenseKey = $this->licenseService->generateLicenseKey($productPrefix);

        $license = License::create([
            'product_id' => $product->id,
            'license_key' => $licenseKey,
            'channel' => $validated['channel'],
            'buyer_email' => $validated['buyer_email'],
            'buyer_username' => $validated['buyer_username'] ?? Str::before($validated['buyer_email'], '@'),
            'tier' => $validated['tier'],
            'max_domains' => (int) $validated['max_domains'],
            'support_until' => ! empty($validated['support_until']) ? Carbon::parse($validated['support_until']) : now()->addMonths(6),
            'expires_at' => ! empty($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null,
            'status' => 'active',
            'client_secret_salt' => Str::random(32),
        ]);

        return redirect()
            ->route('admin.licensing.licenses.show', $license)
            ->with('status', "License [{$license->license_key}] successfully issued.");
    }

    /**
     * Update the status of a license (active, suspended, revoked, refunded).
     */
    public function updateStatus(Request $request, License $license): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,suspended,revoked,refunded'],
        ]);

        $license->update(['status' => $validated['status']]);

        if ($validated['status'] !== 'active') {
            $license->domainBindings()->update(['status' => 'deactivated']);
        }

        return back()->with('status', "License status updated to {$validated['status']}.");
    }

    /**
     * Manually unbind / release a domain from a license.
     */
    public function unbindDomain(License $license, DomainBinding $domainBinding): RedirectResponse
    {
        if ($domainBinding->license_id !== $license->id) {
            abort(403);
        }

        $domainBinding->update(['status' => 'deactivated']);

        return back()->with('status', "Domain [{$domainBinding->domain}] released successfully.");
    }

    /**
     * Extend customer support duration.
     */
    public function extendSupport(Request $request, License $license): RedirectResponse
    {
        $validated = $request->validate([
            'months' => ['required', 'integer', 'min:1', 'max:36'],
        ]);

        $baseDate = ($license->support_until && $license->support_until->isFuture())
            ? $license->support_until
            : now();

        $license->update([
            'support_until' => $baseDate->copy()->addMonths((int) $validated['months']),
        ]);

        return back()->with('status', "Support period extended by {$validated['months']} months.");
    }

    /**
     * Reissue client salt.
     */
    public function reissueSalt(License $license): RedirectResponse
    {
        $license->update(['client_secret_salt' => Str::random(32)]);

        return back()->with('status', 'Client secret salt reissued. Existing signed client tokens will require verification update.');
    }
}
