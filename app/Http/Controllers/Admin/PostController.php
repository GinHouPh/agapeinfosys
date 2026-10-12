<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use HandlesImageUploads;

    /**
     * Paginated article list.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');

        $posts = Post::query()
            ->with('author')
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.posts.index', compact('posts', 'search'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.posts.create');
    }

    /**
     * Persist a new article.
     */
    public function store(StorePostRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['author_id'] = $request->user()?->getKey();
        $attributes['cover_image'] = $this->storeUploadedImage($request->file('cover_image'), 'uploads/posts');

        Post::create($attributes);

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Article created.');
    }

    /**
     * Edit form.
     */
    public function edit(Post $post): View
    {
        return view('admin.posts.edit', compact('post'));
    }

    /**
     * Apply edits to an existing article.
     */
    public function update(StorePostRequest $request, Post $post): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['cover_image'] = $this->replaceUploadedImage(
            $request->file('cover_image'),
            $post->cover_image,
            'uploads/posts',
        );

        $post->update($attributes);

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Article updated.');
    }

    /**
     * Remove an article and its media.
     */
    public function destroy(Post $post): RedirectResponse
    {
        $this->forgetUploadedImage($post->cover_image);
        $post->delete();

        return redirect()
            ->route('admin.posts.index')
            ->with('status', 'Article deleted.');
    }
}
