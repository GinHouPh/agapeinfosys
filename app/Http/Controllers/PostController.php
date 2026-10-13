<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersBuilderPages;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use RendersBuilderPages;

    /**
     * Paginated article listing.
     */
    public function index(Request $request): View
    {
        if ($builder = $this->builderPage('blog')) {
            return $builder;
        }

        return view('blog.index', [
            'posts' => Post::query()
                ->published()
                ->ordered()
                ->paginate(6)
                ->withQueryString(),
            'categories' => Post::query()
                ->published()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ]);
    }

    /**
     * Single article.
     */
    public function show(Request $request, Post $post): View
    {
        if ($builder = $this->builderPage('article-detail', ['record' => $post], [
            'pageTitle' => $post->title.' | Agape Infosys Blog',
            'pageDescription' => $post->excerpt ?: $post->title,
        ])) {
            return $builder;
        }

        return view('blog.show', [
            'post' => $post,
            'others' => Post::query()
                ->published()
                ->ordered()
                ->whereKeyNot($post->getKey())
                ->limit(3)
                ->get(),
        ]);
    }
}
