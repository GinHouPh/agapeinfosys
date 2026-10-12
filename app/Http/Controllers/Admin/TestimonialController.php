<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTestimonialRequest;
use App\Models\Testimonial;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    /**
     * Paginated testimonial list.
     */
    public function index(Request $request): View
    {
        $search = (string) $request->query('q', '');

        $testimonials = Testimonial::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->ordered()
            ->paginate(10)
            ->withQueryString();

        return view('admin.testimonials.index', compact('testimonials', 'search'));
    }

    /**
     * Creation form.
     */
    public function create(): View
    {
        return view('admin.testimonials.create');
    }

    /**
     * Persist a new testimonial.
     */
    public function store(StoreTestimonialRequest $request): RedirectResponse
    {
        Testimonial::create($request->validated());

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimonial created.');
    }

    /**
     * Edit form.
     */
    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    /**
     * Apply edits to an existing testimonial.
     */
    public function update(StoreTestimonialRequest $request, Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update($request->validated());

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimonial updated.');
    }

    /**
     * Remove a testimonial.
     */
    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->delete();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimonial deleted.');
    }
}
