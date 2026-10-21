@extends('layouts.admin')

@section('title', 'Feature Flags')
@section('heading', 'Feature Flags')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            A flag that does not exist reads as off. A flag with a rollout below 100% applies
            to a share of visitors, decided by their address so they always see the same thing.
        </p>

        <a href="{{ route('admin.system.index') }}" class="tag self-start">System</a>
    </div>

    <div class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="space-y-6">
            @forelse ($flags as $flag)
                <section class="admin-card">
                    <form action="{{ route('admin.system.flags.update', $flag) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="field__label" for="flag-name-{{ $flag->id }}">Name *</label>
                                <input class="input" type="text" id="flag-name-{{ $flag->id }}" name="name"
                                       value="{{ old('name', $flag->name) }}" required>
                                @error('name') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="field__label" for="flag-key-{{ $flag->id }}">Key</label>
                                <input class="input font-mono text-sm" type="text" id="flag-key-{{ $flag->id }}"
                                       value="{{ $flag->key }}" disabled>
                                <p class="text-xs text-muted">Read in code as the flag's key.</p>
                            </div>

                            <div class="sm:col-span-2">
                                <label class="field__label" for="flag-description-{{ $flag->id }}">Description</label>
                                <input class="input" type="text" id="flag-description-{{ $flag->id }}" name="description"
                                       value="{{ old('description', $flag->description) }}">
                                @error('description') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="field__label" for="flag-rollout-{{ $flag->id }}">Rollout %</label>
                                <input class="input" type="number" id="flag-rollout-{{ $flag->id }}" name="rollout_percentage"
                                       value="{{ old('rollout_percentage', $flag->rollout_percentage) }}" min="0" max="100">
                                @error('rollout_percentage') <span class="field__error">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="field__label" for="flag-ends-{{ $flag->id }}">Ends at</label>
                                <input class="input" type="datetime-local" id="flag-ends-{{ $flag->id }}" name="ends_at"
                                       value="{{ old('ends_at', $flag->ends_at?->format('Y-m-d\TH:i')) }}">
                                @error('ends_at') <span class="field__error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <label class="flex items-center gap-3 text-body">
                            <input type="hidden" name="is_enabled" value="0">
                            <input type="checkbox" name="is_enabled" value="1" class="h-4 w-4 accent-[#d90429]"
                                   @checked(old('is_enabled', $flag->is_enabled))>
                            Switched on
                        </label>

                        <div class="flex flex-wrap items-center gap-4">
                            <button type="submit" class="btn btn--accent">
                                Save Flag
                                <span class="btn__strip" aria-hidden="true"></span>
                            </button>
                        </div>
                    </form>

                    <form action="{{ route('admin.system.flags.destroy', $flag) }}" method="POST" class="mt-4 border-t border-line pt-4"
                          onsubmit="return confirm('Delete this feature flag?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                            Delete Flag
                        </button>
                    </form>
                </section>
            @empty
                <section class="admin-card">
                    <p class="py-6 text-center text-sm text-muted">No feature flags yet.</p>
                </section>
            @endforelse
        </div>

        <section class="admin-card h-fit">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New flag</h2>

            <form action="{{ route('admin.system.flags.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="field__label" for="flag-name">Name *</label>
                    <input class="input" type="text" id="flag-name" name="name" value="{{ old('name') }}"
                           placeholder="New checkout" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="flag-key">Key *</label>
                    <input class="input font-mono text-sm" type="text" id="flag-key" name="key" value="{{ old('key') }}"
                           placeholder="new-checkout" required>
                    <p class="text-xs text-muted">Lowercase letters, digits and dashes.</p>
                    @error('key') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="flag-description">Description</label>
                    <input class="input" type="text" id="flag-description" name="description"
                           value="{{ old('description') }}" placeholder="What this switch turns on">
                    @error('description') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="field__label" for="flag-rollout">Rollout %</label>
                    <input class="input" type="number" id="flag-rollout" name="rollout_percentage"
                           value="{{ old('rollout_percentage', 100) }}" min="0" max="100">
                    @error('rollout_percentage') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="btn btn--accent w-full">
                    Create Flag
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>
            </form>
        </section>
    </div>
@endsection
