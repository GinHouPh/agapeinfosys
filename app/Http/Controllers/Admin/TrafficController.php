<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TrafficService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TrafficController extends Controller
{
    /**
     * The traffic the site has seen over a period.
     */
    public function index(Request $request, TrafficService $traffic): View
    {
        $days = (int) $request->query('days', 30);
        $days = in_array($days, [7, 30, 90, 365], true) ? $days : 30;

        $to = now()->endOfDay();
        $from = now()->subDays($days - 1)->startOfDay();

        return view('admin.traffic.index', [
            'report' => $traffic->report($from, $to),
            'days' => $days,
        ]);
    }
}
