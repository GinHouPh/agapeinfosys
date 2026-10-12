<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Seed the public case study grid.
     */
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Freight Operations Platform',
                'slug' => 'freight-operations-platform',
                'client' => 'Meridian Logistics',
                'category' => 'Logistics',
                'service_type' => 'End-to-end build',
                'year' => 2025,
                'excerpt' => 'A single operations platform replacing seven spreadsheets and three legacy tools for a regional freight network.',
                'body' => <<<'HTML'
<p>Meridian's dispatch team ran the business from a patchwork of spreadsheets, a twenty-year-old desktop application and a lot of institutional memory. Growth had stopped being a staffing problem and started being a data problem.</p>
<h2>What we built</h2>
<p>A web platform covering booking, route planning, driver assignment, proof of delivery and customer tracking, with an offline-capable mobile app for drivers in low-signal areas.</p>
<h2>Delivery</h2>
<p>Eight months, four engineers, one designer, shipped region by region so the dispatch team never had to switch everything on at once.</p>
HTML,
                'stack' => ['Laravel', 'Vue', 'PostgreSQL', 'Flutter', 'AWS'],
                'outcomes' => [
                    'Dispatch planning time reduced by 62%',
                    'Eliminated 7 parallel spreadsheets',
                    'On-time delivery improved from 87% to 96%',
                ],
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Patient Portal Modernisation',
                'slug' => 'patient-portal-modernisation',
                'client' => 'Northgate Health Group',
                'category' => 'Healthcare',
                'service_type' => 'Modernisation',
                'year' => 2024,
                'excerpt' => 'Re-platforming a clinician-facing portal used by 1,400 staff, without a single day of downtime.',
                'body' => <<<'HTML'
<p>The existing portal was fragile, expensive to change and structurally incapable of meeting new accessibility requirements. A big-bang rewrite was ruled out on day one.</p>
<h2>The approach</h2>
<p>We strangler-figged the system: a new API layer in front of the legacy database, feature by feature migration, and a shadow-run period where both systems were compared before traffic moved.</p>
HTML,
                'stack' => ['Laravel', 'Blade', 'MySQL', 'Redis', 'Azure'],
                'outcomes' => [
                    'Zero downtime across the migration',
                    'WCAG 2.2 AA conformance achieved',
                    'Feature lead time cut from weeks to days',
                ],
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Retail Loyalty & Commerce App',
                'slug' => 'retail-loyalty-commerce-app',
                'client' => 'Brightside Retail',
                'category' => 'Retail',
                'service_type' => 'End-to-end build',
                'year' => 2024,
                'excerpt' => 'A cross-platform loyalty app tying 180 stores and e-commerce into one customer profile.',
                'body' => <<<'HTML'
<p>Brightside had loyalty data in-store, separate profiles online and no way to recognise a customer who shopped both ways.</p>
<h2>The result</h2>
<p>One customer profile, one wallet, and a store associate view that shows the same information the app does.</p>
HTML,
                'stack' => ['Laravel', 'React Native', 'Elasticsearch', 'AWS'],
                'outcomes' => [
                    '240,000 app installs in the first quarter',
                    'Repeat purchase rate up 18%',
                    'Store associate lookup under 200ms',
                ],
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'title' => 'Cloud Migration & Cost Recovery',
                'slug' => 'cloud-migration-cost-recovery',
                'client' => 'Halden Financial',
                'category' => 'Fintech',
                'service_type' => 'Consulting',
                'year' => 2023,
                'excerpt' => 'Moving a regulated workload to cloud with audit evidence, then taking 34% back out of the bill.',
                'body' => <<<'HTML'
<p>Halden needed to leave a data centre lease but could not compromise the audit trail their regulator expects.</p>
<h2>What we did</h2>
<p>Terraform-defined infrastructure, staged cutover with rehearsed rollback, and a rightsizing programme reviewed monthly against actual load rather than peak assumptions.</p>
HTML,
                'stack' => ['Terraform', 'Kubernetes', 'AWS', 'Datadog'],
                'outcomes' => [
                    '34% reduction in monthly cloud spend',
                    'Audit evidence automated end to end',
                    'Recovery time objective cut to 15 minutes',
                ],
                'is_featured' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($projects as $project) {
            Project::query()->updateOrCreate(
                ['slug' => $project['slug']],
                $project + [
                    'cover_image' => null,
                    'gallery' => null,
                    'project_url' => null,
                    'is_published' => true,
                ],
            );
        }
    }
}
