<?php

namespace App\Http\Controllers\Admin\Licensing;

use App\Http\Controllers\Controller;
use App\Models\LicenseAuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminLicenseAuditLogController extends Controller
{
    /**
     * Display a searchable, filterable log of licensing audit events.
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $event = (string) $request->query('event', '');
        $code = (string) $request->query('code', '');

        $logs = LicenseAuditLog::query()
            ->with(['license.product'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(fn ($b) => $b
                    ->where('domain', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('event', 'like', "%{$search}%")
                    ->orWhereHas('license', fn ($l) => $l->where('license_key', 'like', "%{$search}%")));
            })
            ->when($event !== '', fn ($query) => $query->where('event', $event))
            ->when($code !== '', fn ($query) => $query->where('response_code', (int) $code))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $events = LicenseAuditLog::query()
            ->select('event')
            ->distinct()
            ->pluck('event')
            ->filter()
            ->values();

        return view('admin.licensing.audit-logs.index', [
            'logs' => $logs,
            'events' => $events,
            'search' => $search,
            'event' => $event,
            'code' => $code,
        ]);
    }
}
