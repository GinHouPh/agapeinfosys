<?php

namespace Database\Seeders;

use App\Enums\FormFieldType;
use App\Models\Form;
use Illuminate\Database\Seeder;
use RuntimeException;

class SrsFormSeeder extends Seeder
{
    /**
     * Short titles for the 15-section wizard navigation ribbon, matching srs_genie.
     *
     * @var array<string, string>
     */
    private const SECTION_SHORT_TITLES = [
        'project_overview' => 'Overview',
        'system_type' => 'Architecture & Portals',
        'core_features' => 'Core Features',
        'user_roles' => 'User Roles',
        'design_ui_ux' => 'UI / UX',
        'technical_requirements' => 'Tech Stack',
        'integrations' => 'Integrations',
        'security' => 'Security',
        'performance' => 'Scalability',
        'deployment' => 'Deployment',
        'testing' => 'Testing & QA',
        'content_data_migration' => 'Migration',
        'constraints' => 'Constraints',
        'risks' => 'Risks',
        'additional' => 'Additional',
    ];

    /**
     * Seed the 15-section, 71-field IEEE 830 SRS Wizard form adopted from srs_genie.
     */
    public function run(): void
    {
        $schemaPath = database_path('data/srs_sections_schema.json');

        if (! file_exists($schemaPath)) {
            throw new RuntimeException("Missing SRS schema file at {$schemaPath}.");
        }

        $sections = json_decode((string) file_get_contents($schemaPath), true, 512, JSON_THROW_ON_ERROR);

        $sectionMeta = [];
        foreach ($sections as $section) {
            $id = $section['id'];
            $sectionMeta[$id] = [
                'icon' => $section['icon'] ?? '📋',
                'short_title' => self::SECTION_SHORT_TITLES[$id] ?? preg_replace('/^\d+\.\s*/', '', (string) ($section['title'] ?? $id)),
            ];
        }

        $form = Form::query()->updateOrCreate(
            ['slug' => 'srs-specification'],
            [
                'title' => 'Software Requirements Specification',
                'description' => 'Complete the 15-section IEEE 830 architectural questionnaire below. Your progress auto-saves locally, supports JSON import/export, and compiles into a structured SRS specification.',
                'submit_label' => 'Submit SRS Specification',
                'success_message' => 'Thank you — your Software Requirements Specification ({{reference}}) has been received. Our architecture team will review and follow up shortly.',
                'reference_prefix' => 'SRS',
                'is_active' => true,
                'is_wizard' => true,
                'wizard_settings' => [
                    'enable_draft' => true,
                    'enable_json_io' => true,
                    'enable_review' => true,
                    'section_meta' => $sectionMeta,
                ],
            ],
        );

        $sortOrder = 0;
        $activeFieldNames = [];

        foreach ($sections as $section) {
            $sectionId = (string) $section['id'];
            $sectionTitle = (string) $section['title'];

            foreach ($section['fields'] as $field) {
                $fieldId = (string) $field['id'];
                $activeFieldNames[] = $fieldId;

                $type = match ($field['type']) {
                    'text' => FormFieldType::Text,
                    'large_text_editor' => FormFieldType::LargeTextEditor,
                    'choice_with_text' => FormFieldType::Radio,
                    'multiple_choice_with_text' => FormFieldType::Checkbox,
                    'text_only' => FormFieldType::Textarea,
                    default => FormFieldType::Text,
                };

                $form->fields()->updateOrCreate(
                    ['name' => $fieldId],
                    [
                        'label' => (string) $field['label'],
                        'type' => $type,
                        'group' => $sectionTitle,
                        'section_id' => $sectionId,
                        'options' => $field['options'] ?? null,
                        'has_text_input' => (bool) ($field['hasTextInput'] ?? false),
                        'text_placeholder' => $field['textPlaceholder'] ?? null,
                        'placeholder' => $field['placeholder'] ?? null,
                        'is_required' => (bool) ($field['required'] ?? false),
                        'width' => 'full',
                        'sort_order' => $sortOrder++,
                    ],
                );
            }
        }

        $form->fields()->whereNotIn('name', $activeFieldNames)->delete();
    }
}
