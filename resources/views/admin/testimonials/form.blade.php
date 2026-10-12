@php
    $testimonial = $testimonial ?? null;
    $method = $testimonial ? 'PUT' : 'POST';
    $action = $testimonial ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store');
@endphp

<form action="{{ $action }}" method="POST" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="name">Name *</label>
                        <input class="input" type="text" id="name" name="name" value="{{ old('name', $testimonial?->name) }}" required>
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="role">Role</label>
                        <input class="input" type="text" id="role" name="role" value="{{ old('role', $testimonial?->role) }}" placeholder="e.g. CTO">
                        @error('role') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="company">Company</label>
                        <input class="input" type="text" id="company" name="company" value="{{ old('company', $testimonial?->company) }}">
                        @error('company') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="linkedin_url">Profile URL</label>
                        <input class="input" type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $testimonial?->linkedin_url) }}" placeholder="https://">
                        @error('linkedin_url') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="quote">Quote *</label>
                    <textarea class="input" id="quote" name="quote" rows="5" required>{{ old('quote', $testimonial?->quote) }}</textarea>
                    @error('quote') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="rating">Rating</label>
                    <select class="input" id="rating" name="rating">
                        @for ($star = 5; $star >= 1; $star--)
                            <option value="{{ $star }}" @selected((int) old('rating', $testimonial?->rating ?? 5) === $star)>
                                {{ str_repeat('★', $star) }} ({{ $star }})
                            </option>
                        @endfor
                    </select>
                    @error('rating') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="input" type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}">
                    @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <label class="flex items-center gap-3 text-body">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" class="h-4 w-4 accent-[#d90429]" @checked(old('is_published', $testimonial?->is_published ?? true))>
                    Published
                </label>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn btn--accent w-full">
                    {{ $testimonial ? 'Save Changes' : 'Create Testimonial' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('admin.testimonials.index') }}" class="btn btn--ghost w-full text-center">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</form>
