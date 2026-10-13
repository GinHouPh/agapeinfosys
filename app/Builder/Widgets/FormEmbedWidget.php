<?php

namespace App\Builder\Widgets;

use App\Models\Form;

/**
 * Embeds one of the dynamic forms (enquiry, requirement specification, ...).
 *
 * Either picks a form explicitly, or — when placed inside a form-detail
 * template — renders the record's own form so every /forms/{slug} page shows
 * its questions without the slug being wired up by hand.
 */
class FormEmbedWidget extends GenericWidget
{
    protected static function config(): array
    {
        return [
            'name' => 'form-embed',
            'title' => 'Form',
            'category' => 'Dynamic',
            'description' => 'Embed a dynamic form, or render the current form-detail record.',
            'icon' => 'M4 5h16v14H4z M8 10h8 M8 14h5',
            'view' => 'builder.widgets.form-embed',
            'controls' => [
                [
                    'key' => 'use_page_record',
                    'label' => "Render the page's record",
                    'type' => 'toggle',
                    'default' => false,
                    'help' => 'On a form-detail template, render the form of the page being visited.',
                ],
                [
                    'key' => 'form',
                    'label' => 'Form',
                    'type' => 'select',
                    'default' => '',
                    'options' => ['' => 'Choose a form…'],
                    'condition' => ['use_page_record' => false],
                ],
                [
                    'key' => 'empty_text',
                    'label' => 'Missing form message',
                    'type' => 'text',
                    'default' => 'No form selected yet.',
                ],
            ],
        ];
    }

    /**
     * Options for the form control, loaded from the database so the picker
     * always lists what actually exists.
     *
     * @return array<int, array<string, mixed>>
     */
    public function controls(): array
    {
        $controls = parent::controls();

        foreach ($controls as $index => $control) {
            if (($control['key'] ?? null) === 'form') {
                $controls[$index]['options'] = $this->formOptions();
            }
        }

        return $controls;
    }

    /**
     * @return array<string, string>
     */
    private function formOptions(): array
    {
        $options = ['' => 'Choose a form…'];

        foreach (Form::query()->active()->orderBy('title')->get() as $form) {
            $options[$form->slug] = $form->title;
        }

        return $options;
    }
}
