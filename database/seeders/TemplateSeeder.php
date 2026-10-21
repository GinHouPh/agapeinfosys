<?php

namespace Database\Seeders;

use App\Models\ComponentTemplate;
use App\Models\PageTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TemplateSeeder extends Seeder
{
    /**
     * Seed the reusable components and starter layouts the site ships with.
     */
    public function run(): void
    {
        $this->seedComponent(
            'Call to action',
            'Marketing',
            'A closing banner with a heading and two buttons.',
            [
                ['name' => 'heading', 'label' => 'Heading', 'type' => 'text', 'default' => 'Ready to start?'],
                ['name' => 'lead', 'label' => 'Lead line', 'type' => 'textarea', 'default' => 'Tell us what you are building.'],
                ['name' => 'label', 'label' => 'Button label', 'type' => 'text', 'default' => "Let's Talk"],
            ],
            [
                [
                    'widget' => 'cta-banner',
                    'settings' => [
                        'title' => '{{heading}}',
                        'lead' => '{{lead}}',
                        'buttons' => [
                            ['label' => '{{label}}', 'url' => '/contact'],
                        ],
                    ],
                ],
            ],
        );

        $this->seedComponent(
            'Three feature cards',
            'Content',
            'A row of three cards describing what you offer.',
            [
                ['name' => 'first', 'label' => 'First card title', 'type' => 'text', 'default' => 'Discover'],
                ['name' => 'second', 'label' => 'Second card title', 'type' => 'text', 'default' => 'Design'],
                ['name' => 'third', 'label' => 'Third card title', 'type' => 'text', 'default' => 'Deliver'],
            ],
            [
                [
                    'widget' => 'feature-cards',
                    'settings' => [
                        'columns' => '3',
                        'cards' => [
                            ['title' => '{{first}}', 'body' => 'What this card is for.'],
                            ['title' => '{{second}}', 'body' => 'What this card is for.'],
                            ['title' => '{{third}}', 'body' => 'What this card is for.'],
                        ],
                    ],
                ],
            ],
        );

        $this->seedComponent(
            'Statement',
            'Content',
            'A single emphatic line.',
            [
                ['name' => 'text', 'label' => 'Statement', 'type' => 'textarea', 'default' => 'Built to last.'],
            ],
            [
                [
                    'widget' => 'statement',
                    'settings' => [
                        'accent' => 'Agape',
                        'text' => '{{text}}',
                    ],
                ],
            ],
        );

        $this->seedPageTemplate(
            'Landing page',
            'Hero, three features and a closing call to action.',
            [
                [
                    'widget' => 'hero',
                    'settings' => [
                        'eyebrow' => 'Software products & services',
                        'title_top' => 'We build',
                        'title_accent' => 'what lasts',
                        'lead' => 'Describe the offer in one sentence.',
                    ],
                ],
                [
                    'widget' => 'feature-cards',
                    'settings' => [
                        'columns' => '3',
                        'cards' => [
                            ['title' => 'First', 'body' => 'What it covers.'],
                            ['title' => 'Second', 'body' => 'What it covers.'],
                            ['title' => 'Third', 'body' => 'What it covers.'],
                        ],
                    ],
                ],
                [
                    'widget' => 'cta-banner',
                    'settings' => [
                        'title' => 'Ready to start?',
                        'lead' => 'Tell us what you are building.',
                        'buttons' => [
                            ['label' => "Let's Talk", 'url' => '/contact'],
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * Create — or refresh — one component.
     *
     * @param  array<int, array{name: string, label: string, type: string, default?: mixed}>  $slots
     * @param  array<int, array{widget: string, settings: array<string, mixed>}>  $blocks
     */
    private function seedComponent(string $name, string $category, string $description, array $slots, array $blocks): void
    {
        ComponentTemplate::query()->updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'category' => $category,
                'description' => $description,
                'schema' => ['blocks' => $blocks],
                'slots' => $slots,
                'is_active' => true,
            ],
        );
    }

    /**
     * Create — or refresh — one page template.
     *
     * @param  array<int, array{widget: string, settings: array<string, mixed>}>  $blocks
     */
    private function seedPageTemplate(string $name, string $description, array $blocks): void
    {
        PageTemplate::query()->updateOrCreate(
            ['slug' => Str::slug($name)],
            [
                'name' => $name,
                'description' => $description,
                'schema' => ['blocks' => $blocks],
                'is_active' => true,
            ],
        );
    }
}
