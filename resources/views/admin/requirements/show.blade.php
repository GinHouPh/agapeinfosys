@extends('layouts.admin')

@section('title', 'Requirement '.$requirement->reference)
@section('heading', 'Requirement')

@section('content')
    <div class="grid gap-6 xl:grid-cols-3">
        <div class="admin-card xl:col-span-2">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4 border-b border-line pb-6">
                <div>
                    <p class="font-mono text-sm text-accent">{{ $requirement->reference }}</p>
                    <h2 class="mt-2 !text-2xl text-white">{{ $requirement->project_name }}</h2>
                    <p class="mt-2 text-sm text-muted">
                        Received {{ $requirement->created_at->format('d M Y, H:i') }} · {{ $requirement->industry }}
                    </p>
                </div>

                <form action="{{ route('admin.requirements.status', $requirement) }}" method="POST" class="flex items-center gap-3">
                    @csrf
                    @method('PATCH')

                    <select name="status" class="input !border-b-0 !bg-ink !px-4 !py-2.5" onchange="this.form.submit()">
                        @foreach (\App\Enums\RequirementStatus::cases() as $case)
                            <option value="{{ $case->value }}" @selected($requirement->status === $case)>{{ $case->label() }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="space-y-8">
                <div>
                    <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Project Overview</span>
                    <p class="mt-3 whitespace-pre-line text-body">{{ $requirement->brief }}</p>
                </div>

                <div>
                    <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Features Checklist</span>
                    <p class="mt-3 whitespace-pre-line text-body">{{ $requirement->features }}</p>
                </div>

                <div>
                    <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Solutions Needed</span>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($requirement->solutions ?? [] as $solution)
                            <span class="tag">{{ $solution }}</span>
                        @endforeach
                    </div>
                </div>

                <div>
                    <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Integrations</span>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @forelse ($requirement->integrations ?? [] as $integration)
                            <span class="tag">{{ $integration }}</span>
                        @empty
                            <span class="text-sm text-muted">None selected.</span>
                        @endforelse
                    </div>
                </div>

                @if ($requirement->notes)
                    <div>
                        <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Additional Notes</span>
                        <p class="mt-3 whitespace-pre-line text-body">{{ $requirement->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="mt-8 flex flex-wrap gap-3 border-t border-line pt-6">
                <a href="mailto:{{ $requirement->email }}?subject={{ rawurlencode('Re: Your requirement brief '.$requirement->reference) }}"
                   class="btn btn--accent">
                    Reply By Email
                    <span class="btn__strip" aria-hidden="true"></span>
                </a>

                <form action="{{ route('admin.requirements.destroy', $requirement) }}" method="POST"
                      onsubmit="return confirm('Delete this requirement?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn--ghost">Delete</button>
                </form>
            </div>
        </div>

        <aside class="admin-card h-fit space-y-6">
            <div>
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Contact</span>
                <p class="mt-3 text-lg text-white">{{ $requirement->name }}</p>
                <a href="mailto:{{ $requirement->email }}" class="block text-body hover:text-accent">{{ $requirement->email }}</a>
                @if ($requirement->phone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $requirement->phone) }}" class="block text-body hover:text-accent">
                        {{ $requirement->phone }}
                    </a>
                @endif
                @if ($requirement->company)
                    <p class="mt-2 text-body">{{ $requirement->company }}</p>
                @endif
            </div>

            <div class="border-t border-line pt-6 space-y-4">
                <span class="block font-display text-xs tracking-[0.24em] text-muted uppercase">Commercial</span>

                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Expected users</span>
                    <span class="text-white">{{ $requirement->user_scale }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Budget</span>
                    <span class="text-white">{{ $requirement->budget }}</span>
                </div>
                <div class="flex items-center justify-between gap-4 text-sm">
                    <span class="text-muted">Timeline</span>
                    <span class="text-white">{{ $requirement->timeline }}</span>
                </div>
            </div>

            <div class="border-t border-line pt-6">
                <a href="{{ route('admin.requirements.index') }}" class="font-display text-xs tracking-[0.24em] text-accent uppercase">
                    &larr; Back to requirements
                </a>
            </div>
        </aside>
    </div>
@endsection
