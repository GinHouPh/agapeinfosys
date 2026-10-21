<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Locale;
use App\Models\Page;
use App\Models\Post;
use App\Models\Project;
use App\Models\Service;
use App\Services\TranslationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TranslationController extends Controller
{
    /**
     * The record types that can be translated.
     *
     * @var array<string, class-string>
     */
    private const MODELS = [
        'page' => Page::class,
        'service' => Service::class,
        'project' => Project::class,
        'post' => Post::class,
    ];

    /**
     * The translation editor for one record in one language.
     */
    public function edit(string $type, int $id, string $locale, TranslationService $translations): View
    {
        $record = $this->find($type, $id);
        $language = $this->language($locale);

        $fields = $translations->fields($record);

        return view('admin.locales.edit', [
            'record' => $record,
            'type' => $type,
            'language' => $language,
            'languages' => Locale::query()->orderBy('code')->get(),
            'fields' => $fields,
            'values' => $translations->values($record, $fields, $language->code),
        ]);
    }

    /**
     * Save the translations of a record.
     */
    public function update(Request $request, string $type, int $id, string $locale, TranslationService $translations): RedirectResponse
    {
        $record = $this->find($type, $id);
        $language = $this->language($locale);

        $values = $request->validate([
            'values' => ['nullable', 'array'],
        ])['values'] ?? [];

        $allowed = array_keys($translations->fields($record));

        $translations->save(
            $record,
            $language->id,
            array_intersect_key($values, array_flip($allowed)),
        );

        return redirect()
            ->route('admin.locales.edit', ['type' => $type, 'id' => $id, 'locale' => $language->code])
            ->with('status', 'Translations saved.');
    }

    /**
     * Find the record a translation panel belongs to.
     */
    private function find(string $type, int $id): mixed
    {
        $model = self::MODELS[$type] ?? null;

        abort_if($model === null, 404);

        return $model::query()->findOrFail($id);
    }

    /**
     * The language a translation panel is editing.
     */
    private function language(string $code): Locale
    {
        return Locale::query()->where('code', $code)->firstOrFail();
    }
}
