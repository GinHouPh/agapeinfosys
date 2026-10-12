<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Concerns\SplitsListInput;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Models\Service;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use HandlesImageUploads, SplitsListInput;

    /**
     * Paginated capability list.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');

        $services = Service::query()
            ->when($search !== '', fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.services.index', compact('services', 'search'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.services.create');
    }

    /**
     * Persist a new capability.
     */
    public function store(StoreServiceRequest $request): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['highlights'] = $this->splitList($request->input('highlights'));
        $attributes['image'] = $this->storeUploadedImage($request->file('image'));

        Service::create($attributes);

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service created.');
    }

    /**
     * Edit form.
     */
    public function edit(Service $service): View
    {
        return view('admin.services.edit', compact('service'));
    }

    /**
     * Apply edits to an existing capability.
     */
    public function update(StoreServiceRequest $request, Service $service): RedirectResponse
    {
        $attributes = $request->validated();
        $attributes['highlights'] = $this->splitList($request->input('highlights'));
        $attributes['image'] = $this->replaceUploadedImage($request->file('image'), $service->image);

        $service->update($attributes);

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service updated.');
    }

    /**
     * Remove a capability and its media.
     */
    public function destroy(Service $service): RedirectResponse
    {
        $this->forgetUploadedImage($service->image);
        $service->delete();

        return redirect()
            ->route('admin.services.index')
            ->with('status', 'Service deleted.');
    }
}
