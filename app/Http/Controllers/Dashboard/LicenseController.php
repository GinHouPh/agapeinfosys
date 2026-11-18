<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\DomainBinding;
use App\Models\License;
use App\Models\LicenseAuditLog;
use App\Services\Licensing\ReleaseManagerService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LicenseController extends Controller
{
    public function __construct(private readonly ReleaseManagerService $releaseManager) {}

    /**
     * Display client-owned licenses, bound domains, and download links.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $licenses = License::query()
            ->where('buyer_email', $user->email)
            ->with(['product.latestRelease', 'activeBindings'])
            ->orderByDesc('id')
            ->get();

        return view('dashboard.licenses.index', [
            'licenses' => $licenses,
            'title' => 'Software Licenses',
            'lead' => 'Manage your software license keys, domain bindings, and product updates.',
        ]);
    }

    /**
     * Self-service domain release/migration for customers.
     */
    public function unbindDomain(Request $request, License $license, DomainBinding $domainBinding): RedirectResponse
    {
        if ($license->buyer_email !== $request->user()->email || $domainBinding->license_id !== $license->id) {
            abort(403, 'Unauthorized access to this license.');
        }

        $domain = $domainBinding->domain;
        $domainBinding->update(['status' => 'deactivated']);

        LicenseAuditLog::create([
            'license_id' => $license->id,
            'event' => 'client_self_service_deactivation',
            'domain' => $domain,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'payload' => ['reason' => 'client_dashboard_unbound'],
            'response_code' => 200,
        ]);

        return back()->with('status', "Domain [{$domain}] unbound successfully. You can now activate your license on your new host.");
    }

    /**
     * Direct download of latest authorized release package for the customer.
     */
    public function downloadRelease(Request $request, License $license): BinaryFileResponse|RedirectResponse
    {
        if ($license->buyer_email !== $request->user()->email) {
            abort(403);
        }

        if (! $license->isValid()) {
            return back()->withErrors(['error' => 'Your license is currently inactive, suspended, or expired.']);
        }

        $latestRelease = $license->product?->latestRelease;
        if ($latestRelease === null) {
            return back()->withErrors(['error' => 'No active release packages found for this product.']);
        }

        return $this->releaseManager->serveDownload($latestRelease, $license, $request);
    }
}
