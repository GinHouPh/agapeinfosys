@php
    use App\Models\Form;

    $form = null;

    if (($settings['use_page_record'] ?? false) && ($record ?? null) instanceof Form) {
        $form = $record;
    } elseif (! empty($settings['form'])) {
        $form = Form::query()->active()->where('slug', $settings['form'])->first();
    }

    if ($form === null) {
        $message = $settings['empty_text'] ?? 'No form selected yet.';
    } else {
        $form->loadMissing('fields');
        $groups = \App\Models\FormField::groupForRenderer($form->fields);
    }
@endphp

<x-builder.partials.section :settings="$settings">
    @if ($form === null)
        <div class="shell max-w-4xl">
            <div class="border border-dashed border-line p-8 text-sm text-muted">
                {{ $message }}
            </div>
        </div>
    @else
        @include('partials.dynamic-form')
    @endif
</x-builder.partials.section>
