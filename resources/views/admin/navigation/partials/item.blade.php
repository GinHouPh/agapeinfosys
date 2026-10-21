<div class="px-4 py-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <p class="text-white">{{ $item->label }}</p>
            <p class="font-mono text-xs text-muted">
                {{ $item->link_type->value }} · {{ $item->link_value ?: '—' }}
                @if ($item->icon) · {{ $item->icon }} @endif
                @if ($item->badge) · {{ $item->badge }} @endif
            </p>
        </div>

        <div class="flex items-center gap-4">
            <span class="badge {{ $item->is_active ? 'badge--on' : 'badge--off' }}">
                {{ $item->is_active ? 'Visible' : 'Hidden' }}
            </span>

            <details>
                <summary class="cursor-pointer text-xs tracking-[0.16em] text-accent uppercase">Edit</summary>

                <form action="{{ route('admin.navigation.items.update', [$menu, $item]) }}" method="POST"
                      class="mt-4 space-y-4">
                    @csrf
                    @method('PUT')
                    @include('admin.navigation.partials.item-fields', ['parentOptions' => $parentOptions, 'item' => $item])

                    <button type="submit" class="btn btn--accent">
                        Save Item
                        <span class="btn__strip" aria-hidden="true"></span>
                    </button>
                </form>
            </details>

            <form action="{{ route('admin.navigation.items.destroy', [$menu, $item]) }}" method="POST"
                  onsubmit="return confirm('Delete this menu item?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-xs tracking-[0.16em] text-muted uppercase hover:text-accent">
                    Delete
                </button>
            </form>
        </div>
    </div>
</div>
