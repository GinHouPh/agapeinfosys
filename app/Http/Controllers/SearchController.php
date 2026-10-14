<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Public site search across the published marketing catalogue: services,
 * case studies, blog articles and shop products.
 */
class SearchController extends Controller
{
    /**
     * Results for a query, grouped by content type.
     */
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $results = [
            'services' => collect(),
            'projects' => collect(),
            'posts' => collect(),
            'products' => collect(),
        ];

        if (mb_strlen($query) >= 2) {
            $like = '%'.$query.'%';

            $results['services'] = Service::query()
                ->published()
                ->where(fn ($builder) => $builder
                    ->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('summary', 'like', $like))
                ->orderBy('sort_order')
                ->limit(8)
                ->get();

            $results['projects'] = Project::query()
                ->published()
                ->where(fn ($builder) => $builder
                    ->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('client', 'like', $like))
                ->ordered()
                ->limit(8)
                ->get();

            $results['posts'] = Post::query()
                ->published()
                ->where(fn ($builder) => $builder
                    ->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                    ->orWhere('category', 'like', $like))
                ->ordered()
                ->limit(8)
                ->get();

            $results['products'] = Product::query()
                ->active()
                ->where(fn ($builder) => $builder
                    ->where('name', 'like', $like)
                    ->orWhere('description', 'like', $like))
                ->orderBy('sort_order')
                ->limit(8)
                ->get();
        }

        return view('search.index', [
            'query' => $query,
            'results' => $results,
            'total' => array_sum(array_map(fn ($collection) => $collection->count(), $results)),
        ]);
    }
}
