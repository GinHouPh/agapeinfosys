<?php

namespace App\Http\Controllers;

use App\Models\CollectionType;
use Illuminate\Contracts\View\View;

/**
 * Public renderer for the records of a collection.
 */
class CollectionController extends Controller
{
    /**
     * Show one record of a collection.
     */
    public function show(string $collection, string $record): View
    {
        $type = CollectionType::query()
            ->active()
            ->where('slug', $collection)
            ->firstOrFail();

        $item = $type->records()
            ->published()
            ->where('slug', $record)
            ->firstOrFail();

        return view('collections.show', [
            'type' => $type,
            'record' => $item,
        ]);
    }
}
