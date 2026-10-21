<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionField;
use App\Models\CollectionRecord;
use App\Models\CollectionType;
use App\Models\Concerns\RecordsActivity;
use App\Services\CollectionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CollectionController extends Controller
{
    use RecordsActivity;

    /**
     * Every collection type, with its fields and record counts.
     */
    public function index(): View
    {
        $types = CollectionType::query()
            ->withCount(['fields', 'records'])
            ->orderBy('name')
            ->get();

        return view('admin.collections.index', compact('types'));
    }

    /**
     * Create a collection type.
     */
    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:40'],
            'route_pattern' => ['nullable', 'string', 'max:200'],
        ]);

        $type = CollectionType::query()->create($attributes + [
            'created_by' => $request->user()?->getKey(),
        ]);

        $this->recordActivity('created', $type, 'Created the collection "'.$type->name.'".');

        return redirect()
            ->route('admin.collections.records', $type)
            ->with('status', 'Collection created — add its fields.');
    }

    /**
     * Update a collection type.
     */
    public function update(Request $request, CollectionType $type): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:300'],
            'icon' => ['nullable', 'string', 'max:40'],
            'route_pattern' => ['nullable', 'string', 'max:200'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $attributes['is_active'] = $request->boolean('is_active', $type->is_active);

        $type->update($attributes);

        $this->recordActivity('updated', $type, 'Updated the collection "'.$type->name.'".');

        return back()->with('status', 'Collection updated.');
    }

    /**
     * Delete a collection type and everything in it.
     */
    public function destroy(CollectionType $type): RedirectResponse
    {
        $name = $type->name;

        $type->delete();

        $this->recordActivity('deleted', null, 'Deleted the collection "'.$name.'".');

        return redirect()
            ->route('admin.collections.index')
            ->with('status', 'Collection deleted.');
    }

    /**
     * Add a field to a collection.
     */
    public function storeField(Request $request, CollectionType $type): RedirectResponse
    {
        $attributes = $request->validate([
            'label' => ['required', 'string', 'max:190'],
            'name' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'type' => ['required', 'string', 'max:30'],
            'options' => ['nullable', 'string', 'max:2000'],
            'validation' => ['nullable', 'string', 'max:400'],
            'is_required' => ['sometimes', 'boolean'],
        ]);

        $max = $type->fields()->max('sort_order');
        $next = $max === null ? 0 : ((int) $max) + 1;

        $type->fields()->create([
            'label' => $attributes['label'],
            'name' => ($attributes['name'] ?? '') ?: CollectionField::nameFromLabel($attributes['label']),
            'type' => $attributes['type'],
            'options' => $this->splitLines($attributes['options'] ?? null),
            'validation' => $this->splitLines($attributes['validation'] ?? null),
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $next,
        ]);

        return back()->with('status', 'Field added.');
    }

    /**
     * Update a field.
     */
    public function updateField(Request $request, CollectionType $type, CollectionField $field): RedirectResponse
    {
        abort_unless($field->collection_type_id === $type->id, 404);

        $attributes = $request->validate([
            'label' => ['required', 'string', 'max:190'],
            'type' => ['required', 'string', 'max:30'],
            'options' => ['nullable', 'string', 'max:2000'],
            'validation' => ['nullable', 'string', 'max:400'],
            'is_required' => ['sometimes', 'boolean'],
        ]);

        $field->update([
            'label' => $attributes['label'],
            'type' => $attributes['type'],
            'options' => $this->splitLines($attributes['options'] ?? null),
            'validation' => $this->splitLines($attributes['validation'] ?? null),
            'is_required' => $request->boolean('is_required', $field->is_required),
        ]);

        return back()->with('status', 'Field updated.');
    }

    /**
     * Remove a field.
     */
    public function destroyField(CollectionType $type, CollectionField $field): RedirectResponse
    {
        abort_unless($field->collection_type_id === $type->id, 404);

        $field->delete();

        return back()->with('status', 'Field removed.');
    }

    /**
     * The records of a collection.
     */
    public function records(CollectionType $type): View
    {
        $type->load('fields');

        $records = $type->records()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        return view('admin.collections.records', compact('type', 'records'));
    }

    /**
     * Create a record.
     */
    public function storeRecord(Request $request, CollectionType $type, CollectionService $collections): RedirectResponse
    {
        $type->load('fields');

        $validated = $request->validate($collections->validationRules($type) + [
            'title' => ['required', 'string', 'max:190'],
        ]);

        $type->records()->create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'data' => $collections->sanitise($type, $validated['data'] ?? []),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('status', 'Record created.');
    }

    /**
     * Update a record.
     */
    public function updateRecord(Request $request, CollectionType $type, CollectionRecord $record, CollectionService $collections): RedirectResponse
    {
        abort_unless($record->collection_type_id === $type->id, 404);

        $type->load('fields');

        $validated = $request->validate($collections->validationRules($type, $record) + [
            'title' => ['required', 'string', 'max:190'],
        ]);

        $record->update([
            'title' => $validated['title'],
            'data' => $collections->sanitise($type, $validated['data'] ?? []),
            'is_published' => $request->boolean('is_published', $record->is_published),
        ]);

        return back()->with('status', 'Record updated.');
    }

    /**
     * Remove a record.
     */
    public function destroyRecord(CollectionType $type, CollectionRecord $record): RedirectResponse
    {
        abort_unless($record->collection_type_id === $type->id, 404);

        $record->delete();

        return back()->with('status', 'Record deleted.');
    }

    /**
     * Split a textarea into a list of lines.
     *
     * @return array<int, string>|null
     */
    private function splitLines(?string $value): ?array
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $value)),
            fn (string $line): bool => $line !== '',
        ));
    }
}
