@extends('layouts.admin')

@section('title', 'Testimonials')
@section('heading', 'Testimonials')

@section('content')
    <div class="mb-6 flex items-center justify-between gap-4">
        <p class="text-sm text-muted">Client quotes shown on the homepage.</p>

        <a href="{{ route('admin.testimonials.create') }}" class="btn btn--accent self-start">
            New Testimonial
            <span class="btn__strip" aria-hidden="true"></span>
        </a>
    </div>

    <div class="admin-card !p-0">
        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($testimonials as $testimonial)
                        <tr>
                            <td>
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-white hover:text-accent">
                                    {{ $testimonial->name }}
                                </a>
                                <span class="mt-1 block max-w-md text-xs text-muted">
                                    {{ \Illuminate\Support\Str::limit($testimonial->quote, 90) }}
                                </span>
                            </td>
                            <td class="text-muted">
                                {{ trim(($testimonial->role ?: '').($testimonial->company ? ', '.$testimonial->company : ''), ', ') ?: '—' }}
                            </td>
                            <td class="text-accent">{{ str_repeat('★', $testimonial->rating) }}</td>
                            <td>
                                <span class="badge {{ $testimonial->is_published ? 'badge--on' : 'badge--off' }}">
                                    {{ $testimonial->is_published ? 'Published' : 'Hidden' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-xs tracking-[0.16em] text-accent uppercase">Edit</a>
                                    <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST"
                                          onsubmit="return confirm('Delete this testimonial?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-10 text-center text-muted">No testimonials yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $testimonials->links('partials.pagination') }}
@endsection
