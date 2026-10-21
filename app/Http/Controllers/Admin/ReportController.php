<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * The revenue report for a period.
     */
    public function index(Request $request, DashboardService $dashboard): View
    {
        [$from, $to, $preset] = $dashboard->range($request->query('range'));

        return view('admin.reports.index', [
            'report' => $dashboard->report($from, $to),
            'preset' => $preset,
        ]);
    }

    /**
     * Download the revenue report as a CSV.
     */
    public function export(Request $request, DashboardService $dashboard): StreamedResponse
    {
        [$from, $to] = $dashboard->range($request->query('range'));

        $report = $dashboard->report($from, $to);

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Orders', 'Revenue (minor units)']);

            foreach ($report['revenue'] as $day) {
                fputcsv($handle, [$day['date'], $day['count'], $day['total']]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Plan', 'Orders', 'Revenue (minor units)']);

            foreach ($report['topPlans'] as $plan) {
                fputcsv($handle, [$plan['name'], $plan['count'], $plan['total']]);
            }

            fclose($handle);
        }, 'revenue-'.$from->toDateString().'-'.$to->toDateString().'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
