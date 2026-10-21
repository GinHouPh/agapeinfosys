@extends('layouts.admin')

@section('title', 'Templates')
@section('heading', 'Templates & Components')

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <p class="max-w-2xl text-sm text-muted">
            Components are reusable sections saved from the builder canvas. Page templates are
            starter layouts a new page can begin from. Both stay editable after they land.
        </p>

        <a href="{{ route('admin.pages.index') }}" class="tag self-start">Pages</a>
    </div>

    <div class="space-y-8">
        <section class="admin-card">
            <h2 class="mb-4 font-display text-lg tracking-[0.12em] text-white uppercase">Components</h2>

            @forelse ($components as $category => $group)
                <div class="mb-6 last:mb-0">
                    <p class="mb-3 font-display text-[10px] tracking-[0.24em] text-muted uppercase">{{ $category }}</p>

                    <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($group as $component)
                            <div class="border border-line p-4">
                                <p class="text-white">{{ $component->name }}</p>
                                <p class="mt-1 text-xs text-muted">{{ $component->description }}</p>

                                @if ($component->slotDefinitions())
                                    <p class="mt-2 text-xs text-muted">
                                        Slots: {{ implode(', ', array_column($component->slotDefinitions(), 'label')) }}
                                    </p>
                                @endif

                                <div class="mt-3 flex items-center justify-between">
                                    <span class="text-xs text-muted">Used {{ $component->usage_count }}×</span>

                                    <form action="{{ route('admin.templates.components.destroy', $component) }}" method="POST"
                                          onsubmit="return confirm('Delete this component?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-muted">
                    No components yet — select a section in the builder and press
                    <span class="font-mono text-xs text-accent">☆</span> to save it.
                </p>
            @endforelse
        </section>

        <section class="admin-card !p-0">
            <div class="border-b border-line p-5">
                <h2 class="font-display text-lg tracking-[0.12em] text-white uppercase">Page templates</h2>
                <p class="mt-1 text-sm text-muted">
                    A new page can start from one of these instead of a blank canvas.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Template</th>
                            <th>Sections</th>
                            <th>Used</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pageTemplates as $template)
                            <tr>
                                <td>
                                    <span class="text-white">{{ $template->name }}</span>
                                    <span class="block text-xs text-muted">{{ $template->description }}</span>
                                </td>
                                <td class="text-muted">{{ count($template->blocks()) }}</td>
                                <td class="text-muted">{{ $template->usage_count }}×</td>
                                <td>
                                    <div class="flex justify-end">
                                        <form action="{{ route('admin.templates.pages.destroy', $template) }}" method="POST"
                                              onsubmit="return confirm('Delete this page template?');">
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
                                <td colspan="4" class="py-10 text-center text-muted">
                                    No page templates yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
