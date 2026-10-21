@extends('layouts.admin')

@section('title', $type->name)
@section('heading', $type->name)

@section('content')
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.collections.index') }}" class="tag hover:border-accent hover:text-accent">All collections</a>
        <span class="badge {{ $type->is_active ? 'badge--on' : 'badge--off' }}">
            {{ $type->is_active ? 'Active' : 'Off' }}
        </span>
    </div>

    <div class="grid gap-8 xl:grid-cols-2">
        <section class="admin-card">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Fields</h2>

            <div class="space-y-3">
                @forelse ($type->fields as $field)
                    <div class="border border-line p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-white">{{ $field->label }}</p>
                                <p class="font-mono text-xs text-muted">{{ $field->name }} · {{ $field->type }}</p>
                            </div>

                            <form action="{{ route('admin.collections.fields.destroy', [$type, $field]) }}" method="POST"
                                  onsubmit="return confirm('Remove this field?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                    Remove
                                </button>
                            </form>
                        </div>

                        <form action="{{ route('admin.collections.fields.update', [$type, $field]) }}" method="POST"
                              class="mt-3 grid gap-3 sm:grid-cols-2">
                            @csrf
                            @method('PUT')

                            <input class="input !py-2 text-sm" type="text" name="label" value="{{ $field->label }}" required>

                            <select class="input !py-2 text-sm" name="type">
                                @foreach (['text', 'textarea', 'number', 'email', 'url', 'date', 'select', 'radio', 'checkbox'] as $option)
                                    <option value="{{ $option }}" @selected($field->type === $option)>{{ $option }}</option>
                                @endforeach
                            </select>

                            <input class="input !py-2 text-sm" type="text" name="options"
                                   value="{{ is_array($field->options) ? implode(', ', $field->options) : '' }}"
                                   placeholder="one, two, three">

                            <input class="input !py-2 text-sm" type="text" name="validation"
                                   value="{{ implode(', ', $field->validationRules()) }}" placeholder="max:120">

                            <label class="flex items-center gap-3 text-sm text-body sm:col-span-2">
                                <input type="hidden" name="is_required" value="0">
                                <input type="checkbox" name="is_required" value="1" class="h-4 w-4 accent-[#d90429]"
                                       @checked($field->is_required)>
                                Required
                            </label>

                            <div class="sm:col-span-2">
                                <button type="submit" class="tag hover:border-accent hover:text-accent">Save Field</button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-muted">No fields yet — add one on the right.</p>
                @endforelse
            </div>
        </section>

        <div class="space-y-8">
            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Add a field</h2>

                <form action="{{ route('admin.collections.fields.store', $type) }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="field__label" for="field-label">Label *</label>
                        <input class="input" type="text" id="field-label" name="label" value="{{ old('label') }}"
                               placeholder="Client name" required>
                        @error('label') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="field-type">Type</label>
                        <select class="input" id="field-type" name="type">
                            @foreach (['text', 'textarea', 'number', 'email', 'url', 'date', 'select', 'radio', 'checkbox'] as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="field__label" for="field-options">Options</label>
                        <input class="input" type="text" id="field-options" name="options" value="{{ old('options') }}"
                               placeholder="one, two, three">
                        <p class="text-xs text-muted">For a dropdown or a choice field.</p>
                    </div>

                    <label class="flex items-center gap-3 text-body">
                        <input type="hidden" name="is_required" value="0">
                        <input type="checkbox" name="is_required" value="1" class="h-4 w-4 accent-[#d90429]"
                               @checked(old('is_required'))>
                        Required
                    </label>

                    <button type="submit" class="btn btn--accent w-full">
                        Add Field
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </section>

            <section class="admin-card">
                <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Collection details</h2>

                <form action="{{ route('admin.collections.update', $type) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="field__label" for="type-name">Name *</label>
                        <input class="input" type="text" id="type-name" name="name" value="{{ old('name', $type->name) }}" required>
                        @error('name') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="type-description">Description</label>
                        <input class="input" type="text" id="type-description" name="description"
                               value="{{ old('description', $type->description) }}">
                        @error('description') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="field__label" for="type-route">Public path pattern</label>
                        <input class="input font-mono text-sm" type="text" id="type-route" name="route_pattern"
                               value="{{ old('route_pattern', $type->route_pattern) }}" placeholder="/work/{slug}">
                        @error('route_pattern') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <label class="flex items-center gap-3 text-body">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 accent-[#d90429]"
                               @checked(old('is_active', $type->is_active))>
                        Active
                    </label>

                    <button type="submit" class="btn btn--accent w-full">
                        Save Collection
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </section>
        </div>
    </div>

    <section class="admin-card mt-8">
        <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">New record</h2>

        <form action="{{ route('admin.collections.records.store', $type) }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="field__label" for="record-title">Title *</label>
                <input class="input" type="text" id="record-title" name="title" value="{{ old('title') }}"
                       placeholder="Acme Corporation" required>
                @error('title') <span class="field__error">{{ $message }}</span> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($type->fields as $field)
                    <div>
                        <label class="field__label" for="record-{{ $field->id }}">
                            {{ $field->label }}@if ($field->is_required) <span class="text-accent">*</span> @endif
                        </label>

                        @if ($field->type === 'select' || $field->type === 'radio')
                            <select class="input" id="record-{{ $field->id }}" name="data[{{ $field->name }}]">
                                <option value="">Select…</option>
                                @foreach (is_array($field->options) ? $field->options : [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        @elseif ($field->type === 'textarea')
                            <textarea class="input" id="record-{{ $field->id }}" name="data[{{ $field->name }}]" rows="3"></textarea>
                        @else
                            <input class="input" type="{{ $field->type === 'number' ? 'number' : ($field->type === 'date' ? 'date' : 'text') }}"
                                   id="record-{{ $field->id }}" name="data[{{ $field->name }}]">
                        @endif

                        @error('data.'.$field->name) <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>

            <label class="flex items-center gap-3 text-body">
                <input type="hidden" name="is_published" value="0">
                <input type="checkbox" name="is_published" value="1" class="h-4 w-4 accent-[#d90429]"
                       @checked(old('is_published'))>
                Published
            </label>

            <button type="submit" class="btn btn--accent">
                Add Record
                <span class="btn__strip" aria-hidden="true"></span>
            </button>
        </form>
    </section>

    <section class="admin-card mt-8 !p-0">
        <div class="border-b border-line p-5">
            <h2 class="font-display text-lg tracking-[0.12em] text-white uppercase">Records</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td class="text-white">{{ $record->title }}</td>
                            <td class="font-mono text-xs text-muted">{{ $record->slug }}</td>
                            <td>
                                <span class="badge {{ $record->is_published ? 'badge--on' : 'badge--off' }}">
                                    {{ $record->is_published ? 'Live' : 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end">
                                    <form action="{{ route('admin.collections.records.destroy', [$type, $record]) }}" method="POST"
                                          onsubmit="return confirm('Delete this record?');">
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
                            <td colspan="4" class="py-10 text-center text-muted">No records yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links('partials.pagination') }}
    </section>
@endsection
