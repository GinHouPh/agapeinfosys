<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * The audit trail, newest first, filtered by action and admin.
     */
    public function index(Request $request): View
    {
        $action = (string) $request->query('action', '');
        $userId = (string) $request->query('user', '');

        $entries = ActivityLog::query()
            ->with('user')
            ->when($action !== '', fn ($query) => $query->where('action', $action))
            ->when($userId !== '', fn ($query) => $query->where('user_id', $userId))
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        $actions = ActivityLog::query()
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.activity.index', [
            'entries' => $entries,
            'actions' => $actions,
            'action' => $action,
            'userId' => $userId,
        ]);
    }
}
