<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Seed every site page as a builder-managed draft.
 *
 * Each schema mirrors the current hand-built view of that route so the team can
 * review it in the builder and publish when ready. Until a page is published
 * the hand-built view keeps serving the route, so nothing breaks.
 *
 * Slugs in config/builder.php are reserved: 'home', 'about', ... take over a
 * marketing route, and 'service-detail', 'case-study-detail', ... take over
 * every detail page of that record type, rendering with the current record.
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
            'title' => 'Agape Infosys — Software Solutions & IT Consultancy',
            'slug' => 'home',
            'meta_description' => 'Agape Infosys designs, engineers and supports custom software, web platforms, mobile apps and cloud systems for growing enterprises.',
        ]);

        $this->seedPage($this->aboutSchema($stats, $values), [
            'title' => 'About Agape Infosys — Engineering Excellence, Consultancy & Delivery',
            'slug' => 'about',
            'meta_description' => 'Meet Agape Infosys: a senior team of software engineers and IT consultants delivering web, mobile and cloud systems since 2013.',
        ]);

        $this->seedPage($this->servicesSchema(), [
            'title' => 'Services — Software Development & IT Consultancy | Agape Infosys',
            'slug' => 'services',
            'meta_description' => 'Custom software development, web and mobile engineering, cloud and DevOps, digital transformation consulting and product design from Agape Infosys.',
        ]);

        $this->seedPage($this->portfolioSchema(), [
            'title' => 'Case Studies — Selected Work | Agape Infosys',
            'slug' => 'portfolio',
            'meta_description' => 'Selected software engineering case studies from Agape Infosys: web platforms, mobile products, cloud migrations and enterprise integrations.',
        ]);

        $this->seedPage($this->showcaseSchema(), [
            'title' => 'Projects — Ongoing & Upcoming | Agape Infosys',
            'slug' => 'projects',
            'meta_description' => 'The products Agape Infosys is building now and proposing next — spanning OTT, appointment booking, hospital management, POS, logistics and more.',
        ]);

        $this->seedPage($this->blogSchema(), [
            'title' => 'Blog — Insights on Software, Cloud & Digital Transformation | Agape Infosys',
            'slug' => 'blog',
            'meta_description' => 'Articles from the Agape Infosys team on software engineering, cloud operations, product delivery and digital transformation.',
        ]);

        $this->seedPage($this->contactSchema(), [
            'title' => 'Contact Agape Infosys — Start A Project',
            'slug' => 'contact',
            'meta_description' => 'Talk to the Agape Infosys team about custom software development, IT consultancy, cloud and digital transformation engagements.',
        ]);

        $this->seedPage($this->requirementSchema(), [
            'title' => 'Requirement Specification | Agape Infosys',
            'slug' => 'requirements',
            'meta_description' => 'The more detail you share, the sharper our quotation. Fill in the brief below — it takes about five minutes.',
        ]);

        $this->seedPage($this->serviceDetailSchema(), [
            'title' => 'Service | Agape Infosys Services',
            'slug' => 'service-detail',
            'meta_description' => 'A capability delivered by Agape Infosys.',
        ]);

        $this->seedPage($this->caseStudyDetailSchema(), [
            'title' => 'Case Study | Agape Infosys',
            'slug' => 'case-study-detail',
            'meta_description' => 'A case study by Agape Infosys.',
        ]);

        $this->seedPage($this->articleDetailSchema(), [
            'title' => 'Article | Agape Infosys Blog',
            'slug' => 'article-detail',
            'meta_description' => 'Practical notes from the Agape Infosys team on software engineering, cloud, delivery and digital transformation.',
        ]);

        $this->seedPage($this->formDetailSchema(), [
            'title' => 'Form | Agape Infosys',
            'slug' => 'form-detail',
            'meta_description' => 'Tell us about your project — Agape Infosys replies within one business day.',
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

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function portfolioSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'static',
                        'eyebrow' => 'Portfolio',
                        'title_before' => '',
                        'accent' => 'Work',
                        'title_after' => 'that shipped, scaled and stayed shipped.',
                        'lead' => 'A selection of the platforms, products and systems we have designed, built and operated for our clients.',
                        'show_breadcrumb' => false,
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'left',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'portfolio-grid',
                    'settings' => [
                        'show_filters' => true,
                        'title' => '',
                        'subtitle' => '',
                        'mark' => '',
                        'align' => 'left',
                        'empty_text' => 'No case studies published yet.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function showcaseSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'static',
                        'eyebrow' => 'Showcase',
                        'title_before' => '',
                        'accent' => 'Projects',
                        'title_after' => 'in the making — and coming soon.',
                        'lead' => 'A look at the products we are building right now and the ones we are gearing up to launch — across the industries we know best.',
                        'show_breadcrumb' => false,
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'left',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'showcase-grid',
                    'settings' => [
                        'show_stage_filter' => true,
                        'show_industry_filter' => true,
                        'title' => '',
                        'subtitle' => '',
                        'mark' => '',
                        'align' => 'left',
                        'empty_text' => 'No projects match this filter — try another stage or industry.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'cta-card',
                    'settings' => [
                        'heading' => 'Have a project in mind?',
                        'lead' => 'Share your requirement specification and we will come back with scope, timelines and a quotation.',
                        'button_label' => 'Start a Requirement Brief',
                        'button_url' => '/requirements',
                        'variant' => 'accent',
                        'background' => 'ink',
                        'spacing' => 'tight',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function blogSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'static',
                        'eyebrow' => 'Blog',
                        'title_before' => '',
                        'accent' => 'Read',
                        'title_after' => 'our latest articles',
                        'lead' => 'Practical notes from the people doing the work — delivery, architecture, cloud economics and everything in between.',
                        'show_breadcrumb' => false,
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'right',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'posts-list',
                    'settings' => [
                        'per_page' => 6,
                        'show_filters' => true,
                        'show_pagination' => true,
                        'title' => '',
                        'subtitle' => '',
                        'mark' => '',
                        'align' => 'left',
                        'empty_text' => 'No articles published yet.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function contactSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'static',
                        'eyebrow' => 'Contact',
                        'title_before' => '',
                        'accent' => 'Connect',
                        'title_after' => 'with customers to your business — starting with ours.',
                        'lead' => 'Send us the outline of your project, or just the problem you are stuck on. We reply to every enquiry within one business day.',
                        'show_breadcrumb' => false,
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'left',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'contact-section',
                    'settings' => [
                        'subtitle' => 'Studio & mailing details',
                        'title' => 'Where To Find Us',
                        'mark' => 'contact',
                        'lead' => '',
                        'show_form' => true,
                        'swap_sides' => true,
                        'form_heading' => "Let's talk business",
                        'form_compact' => false,
                        'show_office' => true,
                        'show_mailing' => true,
                        'show_studio' => false,
                        'show_hours' => true,
                        'hours_from_business' => true,
                        'hours_label' => 'Working Hours',
                        'hours_body' => "Monday – Friday, 9:30 – 18:30 IST.\nCritical support is staffed around the clock.",
                        'show_business' => true,
                        'business_heading' => 'Business Details',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function requirementSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'static',
                        'eyebrow' => 'Requirement Specification',
                        'title_before' => 'Tell us what to ',
                        'accent' => 'build',
                        'title_after' => '.',
                        'lead' => 'The more detail you share, the sharper our quotation. Fill in the brief below — it takes about five minutes, and every field helps us scope honestly.',
                        'show_breadcrumb' => false,
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'left',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'form-embed',
                    'settings' => [
                        'use_page_record' => false,
                        'form' => 'requirement-specification',
                        'empty_text' => 'No form selected yet.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function serviceDetailSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'record',
                        'eyebrow' => '',
                        'title_before' => '',
                        'accent' => '',
                        'title_after' => '',
                        'lead' => '',
                        'show_breadcrumb' => true,
                        'breadcrumb_parent_label' => 'Services',
                        'breadcrumb_parent_url' => '/services',
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'right',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'record-body',
                    'settings' => [
                        'layout' => 'media-cta',
                        'show_pullquote' => false,
                        'show_back' => false,
                        'back_label' => 'Back To Articles',
                        'back_url' => '/blog',
                        'show_list' => true,
                        'list_title' => '',
                        'cta_heading' => 'Engage us',
                        'cta_body' => 'Need this capability embedded in your team? We can start with a two week discovery sprint.',
                        'cta_label' => 'Request A Quote',
                        'cta_url' => '/contact',
                        'facts_cta_label' => 'Build Something Like This',
                        'facts_visit_label' => 'Visit project →',
                        'background' => 'ink',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'record-related',
                    'settings' => [
                        'limit' => 3,
                        'title' => '',
                        'subtitle' => '',
                        'align' => 'center',
                        'empty_text' => 'More capabilities will be published shortly.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function caseStudyDetailSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'record',
                        'eyebrow' => '',
                        'title_before' => '',
                        'accent' => '',
                        'title_after' => '',
                        'lead' => '',
                        'show_breadcrumb' => true,
                        'breadcrumb_parent_label' => 'Portfolio',
                        'breadcrumb_parent_url' => '/portfolio',
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'right',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'record-cover',
                    'settings' => [
                        'aspect' => '1300/560',
                        'decorative' => true,
                        'background' => 'ink',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'record-body',
                    'settings' => [
                        'layout' => 'facts',
                        'show_pullquote' => false,
                        'show_back' => false,
                        'back_label' => 'Back To Articles',
                        'back_url' => '/portfolio',
                        'show_list' => true,
                        'list_title' => '',
                        'cta_heading' => 'Engage us',
                        'cta_body' => '',
                        'cta_label' => 'Request A Quote',
                        'cta_url' => '/contact',
                        'facts_cta_label' => 'Build Something Like This',
                        'facts_visit_label' => 'Visit project →',
                        'background' => 'ink',
                        'spacing' => 'flush',
                    ],
                ],
                [
                    'widget' => 'record-related',
                    'settings' => [
                        'limit' => 3,
                        'title' => '',
                        'subtitle' => '',
                        'align' => 'center',
                        'empty_text' => 'More case studies will be published shortly.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function articleDetailSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'record',
                        'eyebrow' => '',
                        'title_before' => '',
                        'accent' => '',
                        'title_after' => '',
                        'lead' => '',
                        'show_breadcrumb' => true,
                        'breadcrumb_parent_label' => 'Blog',
                        'breadcrumb_parent_url' => '/blog',
                        'show_meta' => true,
                        'show_author' => true,
                        'gradient' => 'right',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'record-cover',
                    'settings' => [
                        'aspect' => '1300/560',
                        'decorative' => false,
                        'background' => 'ink',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'record-body',
                    'settings' => [
                        'layout' => 'single',
                        'show_pullquote' => true,
                        'show_back' => true,
                        'back_label' => 'Back To Articles',
                        'back_url' => '/blog',
                        'show_list' => true,
                        'list_title' => '',
                        'cta_heading' => 'Engage us',
                        'cta_body' => '',
                        'cta_label' => 'Request A Quote',
                        'cta_url' => '/contact',
                        'facts_cta_label' => 'Build Something Like This',
                        'facts_visit_label' => 'Visit project →',
                        'background' => 'ink',
                        'spacing' => 'tight',
                    ],
                ],
                [
                    'widget' => 'record-related',
                    'settings' => [
                        'limit' => 3,
                        'title' => '',
                        'subtitle' => '',
                        'align' => 'center',
                        'empty_text' => 'More articles will be published shortly.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array{blocks: array<int, array{widget: string, settings: array<string, mixed>}>}
     */
    private function formDetailSchema(): array
    {
        return [
            'blocks' => [
                [
                    'widget' => 'page-hero',
                    'settings' => [
                        'source' => 'record',
                        'eyebrow' => '',
                        'title_before' => '',
                        'accent' => '',
                        'title_after' => '',
                        'lead' => '',
                        'show_breadcrumb' => false,
                        'show_meta' => false,
                        'show_author' => false,
                        'gradient' => 'left',
                        'spacing' => 'default',
                    ],
                ],
                [
                    'widget' => 'form-embed',
                    'settings' => [
                        'use_page_record' => true,
                        'form' => '',
                        'empty_text' => 'This form is not active yet.',
                        'background' => 'surface',
                        'spacing' => 'default',
                    ],
                ],
            ],
        ];
    }
}
