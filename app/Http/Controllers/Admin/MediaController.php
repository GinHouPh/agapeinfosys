<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concerns\RecordsActivity;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    use RecordsActivity;

    /**
     * The media library: a grid of everything uploaded, with search.
     */
    public function index(Request $request, MediaService $media): View
    {
        $search = (string) $request->query('q', '');
        $kind = (string) $request->query('kind', '');

        $items = Media::query()
            ->with('creator')
            ->when($search !== '', fn ($query) => $query->where(fn ($builder) => $builder
                ->where('original_name', 'like', "%{$search}%")
                ->orWhere('alt_text', 'like', "%{$search}%")
                ->orWhere('title', 'like', "%{$search}%")))
            ->when($kind === 'images', fn ($query) => $query->images())
            ->when($kind === 'documents', fn ($query) => $query->documents())
            ->orderByDesc('id')
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', [
            'items' => $items,
            'search' => $search,
            'kind' => $kind,
        ]);
    }

    /**
     * The library, as JSON — what the builder's media picker reads.
     */
    public function library(Request $request): JsonResponse
    {
        $search = (string) $request->query('q', '');

        $items = Media::query()
            ->images()
            ->when($search !== '', fn ($query) => $query->where(fn ($builder) => $builder
                ->where('original_name', 'like', "%{$search}%")
                ->orWhere('alt_text', 'like', "%{$search}%")))
            ->orderByDesc('id')
            ->limit(120)
            ->get()
            ->map(fn (Media $item): array => [
                'id' => $item->id,
                'name' => $item->original_name,
                'path' => $item->path(),
                'url' => $item->url(),
                'alt' => $item->alt_text,
            ]);

        return response()->json(['items' => $items]);
    }

    /**
     * Upload a file into the library.
     */
    public function store(Request $request, MediaService $media): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $item = $media->store(
            $request->file('file'),
            'uploads/library',
            $request->user()?->getKey(),
        );

        $this->recordActivity('created', $item, 'Uploaded "'.$item->original_name.'".');

        return back()->with('status', 'File uploaded.');
    }

    /**
     * Edit a file's description.
     */
    public function update(Request $request, Media $media): RedirectResponse
    {
        $attributes = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:160'],
            'focal_x' => ['nullable', 'integer', 'min:0', 'max:100'],
            'focal_y' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $media->update($attributes + [
            'focal_x' => $request->integer('focal_x', 50),
            'focal_y' => $request->integer('focal_y', 50),
        ]);

        $this->recordActivity('updated', $media, 'Updated "'.$media->original_name.'".');

        return back()->with('status', 'File updated.');
    }

    /**
     * Delete a file and its record.
     */
    public function destroy(Media $media, MediaService $service): RedirectResponse
    {
        $name = $media->original_name;

        $service->delete($media);

        $this->recordActivity('deleted', null, 'Deleted the file "'.$name.'".');

        return back()->with('status', 'File deleted.');
    }
}
