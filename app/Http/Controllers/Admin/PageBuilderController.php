<?php

namespace App\Http\Controllers\Admin;

use App\Builder\PageRenderer;
use App\Builder\RecordResolver;
use App\Builder\WidgetRegistry;
use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * AJAX surface for the visual page builder.
 *
 * The canvas renders server-side so the editor always previews exactly what
 * visitors will see; these endpoints drive that live preview.
 */
class PageBuilderController extends Controller
{
    use HandlesImageUploads;

    public function __construct(
        private readonly WidgetRegistry $registry,
        private readonly PageRenderer $renderer,
        private readonly RecordResolver $resolver,
    ) {}

    /**
     * Render a single widget's markup for the canvas preview.
     *
     * The page slug is echoed back so record templates preview against a live
     * record of their type instead of the placeholder visitors never see.
     */
    public function preview(Request $request): JsonResponse
    {
        $data = $request->validate([
            'widget' => ['required', 'string', 'max:60'],
            'settings' => ['nullable', 'array'],
            'page' => ['nullable', 'string', 'max:120'],
        ]);

        $widget = $this->registry->get($data['widget']);

        if ($widget === null) {
            return response()->json(['html' => ''], 422);
        }

        $context = [
            'record' => $this->resolver->forSlug((string) ($data['page'] ?? '')),
            'authoring' => true,
        ];

        return response()->json([
            'html' => $this->renderer->renderBlock($data['widget'], $data['settings'] ?? [], $context),
        ]);
    }

    /**
     * Return the control schema for one widget (drives the settings panel).
     */
    public function widgetSchema(Request $request): JsonResponse
    {
        $data = $request->validate([
            'widget' => ['required', 'string', 'max:60'],
        ]);

        $widget = $this->registry->get($data['widget']);

        if ($widget === null) {
            return response()->json(['controls' => []], 422);
        }

        return response()->json([
            'controls' => $widget->controls(),
            'defaults' => $widget->defaults(),
        ]);
    }

    /**
     * Upload an image chosen in a builder image control.
     */
    public function upload(Request $request): JsonResponse
    {
        $data = $request->validate([
            'image' => ['required', 'image', 'max:4096'],
        ]);

        $path = $this->storeUploadedImage($request->file('image'), 'uploads/pages');

        return response()->json([
            'path' => $path,
            'url' => $path ? Storage::disk('public')->url($path) : null,
        ]);
    }
}
