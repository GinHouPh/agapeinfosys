<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seed the three flagship marketing pages as builder-managed drafts.
 *
 * They mirror the current hand-built home, about and services views so the
 * team can review them in the builder and publish when ready. Until a page is
 * published the hand-built view keeps serving the route, so nothing breaks.
 */
class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stats = Setting::structured('stats', [
            ['value' => 12, 'label' => 'years of Experience'],
            ['value' => 250, 'label' => 'projects Delivered'],
            ['value' => 90, 'label' => 'happy Clients'],
            ['value' => 15, 'label' => 'Industry Awards'],
        ]);

        $process = Setting::structured('process', [
            ['step' => '01', 'title' => 'Discover, Strategy', 'body' => 'We map your business goals, audit the existing systems and define a delivery roadmap with measurable outcomes.'],
            ['step' => '02', 'title' => 'Design, Build', 'body' => 'Our engineers and designers ship in short iterations with continuous review, so you see working software every sprint.'],
            ['step' => '03', 'title' => 'Launch, Scale', 'body' => 'We deploy, monitor and harden the product, then stay on as your long term technology partner.'],
        ]);

        $values = Setting::structured('values', [
            ['title' => 'Ownership', 'body' => 'We treat your product like our own — accountable for uptime, budgets and business results.'],
            ['title' => 'Transparency', 'body' => 'Open estimates, visible backlogs and weekly demos. No surprises at the end of the sprint.'],
            ['title' => 'Craft', 'body' => 'Clean architecture, automated tests and documentation that outlives the engagement.'],
            ['title' => 'Partnership', 'body' => 'Senior consultants who stay with you from discovery through to long term support.'],
        ]);

        $this->seedPage($this->homeSchema($stats, $process), [
            'title' => 'Home',
            'slug' => 'home',
            'meta_description' => 'Agape Infosys designs, engineers and supports custom software, web platforms, mobile apps and cloud systems for growing enterprises.',
        ]);

        $this->seedPage($this->aboutSchema($stats, $values), [
            'title' => 'About',
            'slug' => 'about',
            'meta_description' => 'Meet Agape Infosys: a senior team of software engineers and IT consultants delivering web, mobile and cloud systems since 2013.',
        ]);

        $this->seedPage($this->servicesSchema(), [
            'title' => 'Services',
            'slug' => 'services',
            'meta_description' => 'Custom software development, web and mobile engineering, cloud and DevOps, digital transformation consulting and product design from Agape Infosys.',
        ]);
    }

    /**
     * Create or update a seeded page, always as a draft.
     *
     * @param  array{blocks: array<int, array{id?: string, widget: string, settings: array<string, mixed>}>}  $schema
     * @param  array{title: string, slug: string, meta_description: string}  $meta
     */
    private function seedPage(array $schema, array $meta): void
    {
        Page::query()->updateOrCreate(
            ['slug' => $meta['slug']],
            $meta + [
                'status' => 'draft',
                'schema' => $schema,
            ],
        );
    }

    /**
     * @param  array<int, array{value: int, label: string}>  $stats
     * @param  array<int, array{step: string, title: string, body: string}>  $process
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function homeSchema(array $stats, array $process): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'hero',
                    'settings' => [
                        'eyebrow' => 'Software solutions & IT consultancy',
                        'title_top' => "Design.\nEngineer.",
                        'title_accent' => 'Deliver.',
                        'lead' => 'We turn complex business problems into dependable software — from discovery workshops and product design through to cloud operations and long term support.',
                        'buttons' => [
                            ['label' => 'Explore Services', 'url' => '/services', 'variant' => 'accent'],
                            ['label' => 'See Our Work', 'url' => '/portfolio', 'variant' => 'ghost'],
                        ],
                        'show_card' => true,
                        'card_badge' => 'Since 2013',
                        'card_quote' => 'A senior engineering team you can plug into your roadmap.',
                        'card_stats' => [
                            ['value' => 250, 'suffix' => '+', 'label' => 'Products shipped'],
                            ['value' => 98, 'suffix' => '%', 'label' => 'Client retention'],
                        ],
                        'gradient' => 'right',
                        'show_scroll' => true,
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'marquee-band',
                    'settings' => [
                        'words' => [
                            ['text' => 'Custom Software'],
                            ['text' => 'Cloud & DevOps'],
                            ['text' => 'Web Platforms'],
                            ['text' => 'Mobile Apps'],
                            ['text' => 'Data & AI'],
                            ['text' => 'IT Consulting'],
                        ],
                        'size' => 'lg',
                        'reverse' => false,
                    ],
                ],
                [
                    'widget' => 'statement',
                    'settings' => [
                        'accent' => 'The technology partner',
                        'text' => 'enterprises trust to turn complex problems into working software.',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'media-text',
                    'settings' => [
                        'media_side' => 'left',
                        'subtitle' => 'Who we are',
                        'title' => 'Agape Infosys runs on engineering excellence',
                        'mark' => 'about',
                        'paragraphs' => [
                            ['text' => 'Agape Infosys is a software solutions and IT consultancy practice. We plan, build and operate the systems that businesses depend on — web platforms, mobile products, integrations and cloud infrastructure.'],
                            ['text' => 'Our consultants stay close to your team, so strategy never drifts away from delivery. Every engagement is run by senior engineers who write the code, own the outcome and hand over documentation you can actually maintain.'],
                        ],
                        'button_label' => 'More About Us',
                        'button_url' => '/about',
                        'images' => [
                            ['image' => '', 'label' => 'Agape Infosys Studio'],
                            ['image' => '', 'label' => 'Engineering'],
                            ['image' => '', 'label' => 'Consulting'],
                        ],
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'services-grid',
                    'settings' => [
                        'subtitle' => 'What we offer',
                        'title' => 'Services We Provide',
                        'mark' => 'services',
                        'align' => 'center',
                        'limit' => 6,
                        'featured_only' => false,
                        'show_button' => true,
                        'button_label' => 'All Services',
                        'button_url' => '/services',
                        'background' => 'ink',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'stats',
                    'settings' => [
                        'subtitle' => 'Why choose us',
                        'title' => 'Helping Build Software That Lasts',
                        'mark' => 'features',
                        'columns' => '2',
                        'stats' => array_map(fn ($s) => [
                            'value' => (int) ($s['value'] ?? 0),
                            'label' => (string) ($s['label'] ?? ''),
                        ], $stats),
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'clients-marquee',
                    'settings' => [
                        'subtitle' => 'Who we work with',
                        'title' => 'Valuable Clients',
                        'mark' => 'clients',
                        'align' => 'center',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'projects-grid',
                    'settings' => [
                        'subtitle' => 'Recent case studies',
                        'title' => 'Selected Work',
                        'mark' => 'projects',
                        'align' => 'left',
                        'limit' => 4,
                        'featured_only' => false,
                        'show_button' => true,
                        'button_label' => 'View All Work',
                        'button_url' => '/portfolio',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'process-steps',
                    'settings' => [
                        'subtitle' => 'How we work',
                        'title' => 'Process We Follow',
                        'mark' => 'process',
                        'align' => 'center',
                        'steps' => array_map(fn ($s) => [
                            'number' => (string) ($s['step'] ?? ''),
                            'title' => (string) ($s['title'] ?? ''),
                            'body' => (string) ($s['body'] ?? ''),
                        ], $process),
                        'background' => 'ink',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'testimonials',
                    'settings' => [
                        'subtitle' => 'Testimonials',
                        'title' => 'What They Say',
                        'mark' => 'reviews',
                        'align' => 'center',
                        'limit' => 0,
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'contact-section',
                    'settings' => [
                        'subtitle' => "Let's talk business",
                        'title' => "Let's Build Something Great Together",
                        'mark' => 'contact',
                        'lead' => 'Tell us what you are trying to achieve. We will come back within one business day with a point of view, a rough shape of the work and the next sensible step.',
                        'show_form' => true,
                        'background' => 'ink',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'posts-grid',
                    'settings' => [
                        'subtitle' => 'Insights & inspiration',
                        'title' => 'Read Our Latest Articles',
                        'mark' => 'blogs',
                        'align' => 'center',
                        'limit' => 3,
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param  array<int, array{value: int, label: string}>  $stats
     * @param  array<int, array{title: string, body: string}>  $values
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function aboutSchema(array $stats, array $values): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'hero',
                    'settings' => [
                        'eyebrow' => 'About us',
                        'title_top' => 'A consultancy',
                        'title_accent' => '',
                        'lead' => '',
                        'buttons' => [],
                        'show_card' => false,
                        'gradient' => 'left',
                        'show_scroll' => false,
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'media-text',
                    'settings' => [
                        'media_side' => 'right',
                        'subtitle' => 'Our story',
                        'title' => 'Agape Infosys runs on engineering excellence',
                        'mark' => 'about',
                        'paragraphs' => [
                            ['text' => 'Agape Infosys started as a small engineering studio helping businesses replace brittle spreadsheets and ageing legacy tools with software that could grow with them. Today we are a full service software solutions and IT consultancy practice covering product discovery, custom development, cloud operations and long term support.'],
                            ['text' => 'We keep teams deliberately small and senior. The consultant who scopes your engagement is the same person in your weekly stand-up, and the engineer who writes the code is the one who supports it after launch. That continuity is why clients stay with us for years rather than sprints.'],
                            ['text' => 'Our work spans regulated industries, logistics, education, healthcare and retail — sectors where uptime, data safety and clear communication are not negotiable.'],
                        ],
                        'button_label' => '',
                        'button_url' => '',
                        'images' => [
                            ['image' => '', 'label' => 'Studio'],
                            ['image' => '', 'label' => 'Workshops'],
                            ['image' => '', 'label' => 'Delivery'],
                        ],
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'values',
                    'settings' => [
                        'subtitle' => 'Our values',
                        'title' => 'What We Stand For',
                        'mark' => 'values',
                        'align' => 'center',
                        'columns' => '4',
                        'values' => array_map(fn ($v) => [
                            'title' => (string) ($v['title'] ?? ''),
                            'body' => (string) ($v['body'] ?? ''),
                        ], $values),
                        'background' => 'ink',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'stats',
                    'settings' => [
                        'subtitle' => 'By the numbers',
                        'title' => 'Numbers That Matter',
                        'mark' => 'features',
                        'columns' => '2',
                        'stats' => array_map(fn ($s) => [
                            'value' => (int) ($s['value'] ?? 0),
                            'label' => (string) ($s['label'] ?? ''),
                        ], $stats),
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'services-grid',
                    'settings' => [
                        'subtitle' => 'Core capabilities',
                        'title' => 'What We Do Best',
                        'mark' => 'services',
                        'align' => 'center',
                        'limit' => 3,
                        'featured_only' => false,
                        'show_button' => false,
                        'background' => 'ink',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'clients-marquee',
                    'settings' => [
                        'subtitle' => 'Who we work with',
                        'title' => 'Trusted By Teams Like Yours',
                        'mark' => 'clients',
                        'align' => 'center',
                        'background' => 'surface',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'cta-banner',
                    'settings' => [
                        'accent' => 'Ready',
                        'title' => 'to scope your next build?',
                        'lead' => '',
                        'buttons' => [
                            ['label' => 'Start A Project', 'url' => '/contact', 'variant' => 'accent'],
                            ['label' => 'Browse Services', 'url' => '/services', 'variant' => 'ghost'],
                        ],
                        'spacing' => 'tight',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function servicesSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'hero',
                    'settings' => [
                        'eyebrow' => 'Services',
                        'title_top' => 'Capabilities',
                        'title_accent' => 'that carry a product from idea to production.',
                        'lead' => 'Engage us for a single discipline or a full product team. Every service is delivered by senior practitioners with a documented, measurable process.',
                        'buttons' => [],
                        'show_card' => false,
                        'gradient' => 'right',
                        'show_scroll' => false,
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'services-list',
                    'settings' => [
                        'limit' => 0,
                        'show_button' => true,
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'cta-banner',
                    'settings' => [
                        'accent' => 'Not sure which service',
                        'title' => 'fits your problem?',
                        'lead' => 'Book a discovery call. We will help you shape the scope before you commit to anything.',
                        'buttons' => [
                            ['label' => 'Talk To A Consultant', 'url' => '/contact', 'variant' => 'accent'],
                        ],
                        'spacing' => 'tight',
                    ],
                ],
            ],
        ];
    }
}
