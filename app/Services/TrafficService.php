<?php

namespace App\Services;

use App\Models\PageView;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The traffic the site has seen.
 *
 * The numbers are read from the recorded page views. A visitor is a key
 * rather than a person: no address is kept, so the same visitor can be
 * recognised without holding anything that identifies them.
 */
class TrafficService
{
    /**
     * The report for a period.
     *
     * @return array<string, mixed>
     */
    public function report(Carbon $from, Carbon $to): array
    {
        $views = PageView::query()->between($from, $to)->get();

        return [
            'views' => $views->count(),
            'visitors' => $views->pluck('visitor')->unique()->count(),
            'daily' => $this->dailySeries($views, $from, $to),
            'paths' => $this->ranked($views, 'path'),
            'referrers' => $this->ranked($views->filter(fn (PageView $view): bool => $view->referrer !== null), 'referrer'),
            'statuses' => $this->ranked($views, 'status'),
        ];
    }

    /**
     * The views for each day of the period.
     *
     * @param  Collection<int, PageView>  $views
     * @return array<int, array{date: string, views: int, visitors: int}>
     */
    private function dailySeries($views, Carbon $from, Carbon $to): array
    {
        $buckets = [];

        foreach ($views as $view) {
            $day = $view->created_at?->toDateString();

            if ($day === null) {
                continue;
            }

            $buckets[$day]['views'] = ($buckets[$day]['views'] ?? 0) + 1;
            $buckets[$day]['visitors'][] = $view->visitor;
        }

        $series = [];

        for ($day = $from->copy()->startOfDay(); $day->lte($to); $day->addDay()) {
            $key = $day->toDateString();

            $series[] = [
                'date' => $key,
                'views' => $buckets[$key]['views'] ?? 0,
                'visitors' => count(array_unique($buckets[$key]['visitors'] ?? [])),
            ];
        }

        return $series;
    }

    /**
     * The most common values of a column.
     *
     * @param  Collection<int, PageView>  $views
     * @return array<int, array{value: string, count: int}>
     */
    private function ranked($views, string $column, int $limit = 10): array
    {
        $counts = [];

        foreach ($views as $view) {
            $value = (string) $view->{$column};

            if ($value === '') {
                continue;
            }

            $counts[$value] = ($counts[$value] ?? 0) + 1;
        }

        arsort($counts);

        $ranked = [];

        foreach ($counts as $value => $count) {
            $ranked[] = ['value' => $value, 'count' => $count];

            if (count($ranked) >= $limit) {
                break;
            }
        }

        return $ranked;
    }
}
