<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed the capability catalogue shown on the site.
     */
    public function run(): void
    {
        $services = [
            [
                'title' => 'Custom Software Development',
                'slug' => 'custom-software-development',
                'eyebrow' => 'Engineering',
                'excerpt' => 'Bespoke business systems built around the way your organisation actually works.',
                'summary' => 'We design, build and maintain bespoke platforms — internal tools, customer portals, workflow engines and integrations — shaped around your processes instead of forcing your processes around off-the-shelf software.',
                'body' => <<<'HTML'
<p>Most off-the-shelf products solve the average case. When your business is not average, the gaps accumulate: manual workarounds, brittle spreadsheets and integrations held together with goodwill.</p>
<h2>How we work</h2>
<p>We start with a short discovery phase to map the real workflow, the systems it touches and the constraints that matter. From there we agree a roadmap with clear milestones, then ship in fortnightly increments you can review and steer.</p>
<h2>What you get</h2>
<p>Source code you own, automated test coverage on the critical paths, deployment pipelines, and documentation written for the team who will maintain it after we leave.</p>
HTML,
                'highlights' => [
                    'Discovery workshop and process mapping',
                    'Solution architecture and technical roadmap',
                    'Fortnightly delivery with demoable increments',
                    'Automated testing, CI/CD and handover documentation',
                ],
                'sort_order' => 1,
                'is_featured' => true,
            ],
            [
                'title' => 'Web Application Development',
                'slug' => 'web-application-development',
                'eyebrow' => 'Engineering',
                'excerpt' => 'Fast, accessible web platforms engineered to scale from first user to enterprise load.',
                'summary' => 'From marketing sites and dashboards to multi-tenant SaaS products — we build web applications that stay fast, secure and maintainable as the user base grows.',
                'body' => <<<'HTML'
<p>A web application is usually the front door to your business. Performance, accessibility and reliability are not polish — they are revenue.</p>
<h2>Our stack</h2>
<p>We work primarily with Laravel and modern JavaScript front ends, backed by PostgreSQL or MySQL, deployed on AWS, Azure or your own infrastructure. We choose boring, well-supported tools on purpose.</p>
<h2>Built to last</h2>
<p>Every build ships with monitoring, structured logging and a deployment path your own team can run without calling us.</p>
HTML,
                'highlights' => [
                    'Responsive, WCAG-aware interfaces',
                    'Role-based access and audit trails',
                    'Load-tested for peak traffic events',
                    'Analytics and observability wired in from day one',
                ],
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'title' => 'Mobile App Development',
                'slug' => 'mobile-app-development',
                'eyebrow' => 'Engineering',
                'excerpt' => 'Native-quality iOS and Android applications built on a single, maintainable codebase.',
                'summary' => 'We build consumer and enterprise mobile apps that feel native, work offline where it matters and share one codebase across iOS and Android.',
                'body' => <<<'HTML'
<p>Mobile projects fail when the app, the API and the release process are designed separately. We treat them as one system.</p>
<h2>Delivery</h2>
<p>Design, build and store submission are handled end to end, including beta distribution, crash reporting and a release calendar your marketing team can plan around.</p>
HTML,
                'highlights' => [
                    'Cross-platform build with native feel',
                    'Offline-first data synchronisation',
                    'Push notifications and deep linking',
                    'Store submission and release management',
                ],
                'sort_order' => 3,
                'is_featured' => false,
            ],
            [
                'title' => 'Cloud & DevOps Engineering',
                'slug' => 'cloud-devops-engineering',
                'eyebrow' => 'Platform',
                'excerpt' => 'Infrastructure as code, automated pipelines and observability that keeps uptime boring.',
                'summary' => 'Migrate, modernise and operate cloud infrastructure with infrastructure as code, automated deployments and monitoring that tells you what actually went wrong.',
                'body' => <<<'HTML'
<p>Cloud spend has a habit of drifting upward while reliability drifts down. We put structure around both.</p>
<h2>What we change</h2>
<p>Reproducible infrastructure definitions, containerised workloads, zero-downtime deployment strategies, alerting that maps to real user impact, and cost guardrails reviewed monthly.</p>
<h2>Migration</h2>
<p>Whether you are lifting and shifting or re-architecting, we run the migration in stages with a tested rollback path at every step.</p>
HTML,
                'highlights' => [
                    'Infrastructure as code (Terraform / IaC)',
                    'CI/CD pipelines and release automation',
                    'Centralised logging, metrics and alerting',
                    'Cost modelling and rightsizing reviews',
                ],
                'sort_order' => 4,
                'is_featured' => true,
            ],
            [
                'title' => 'IT Consulting & Digital Transformation',
                'slug' => 'it-consulting-digital-transformation',
                'eyebrow' => 'Consulting',
                'excerpt' => 'Independent advice on architecture, delivery practice and technology investment.',
                'summary' => 'Bring in a senior consultant when you need an honest assessment of your systems, your delivery process or a technology decision with real consequences.',
                'body' => <<<'HTML'
<p>Consulting only works when the recommendation is one we would be willing to implement ourselves — because usually, we are asked to.</p>
<h2>Typical engagements</h2>
<ul><li>Technical due diligence for acquisitions and funding rounds</li><li>Legacy system assessment and modernisation roadmaps</li><li>Delivery process audit and team topology design</li><li>Build-versus-buy evaluation and vendor selection</li></ul>
HTML,
                'highlights' => [
                    'Architecture and codebase review',
                    'Delivery health assessment',
                    'Build vs buy decision support',
                    'Prioritised, costed transformation roadmap',
                ],
                'sort_order' => 5,
                'is_featured' => false,
            ],
            [
                'title' => 'UI/UX & Product Design',
                'slug' => 'ui-ux-product-design',
                'eyebrow' => 'Design',
                'excerpt' => 'Research-led interface design that makes complex workflows feel obvious.',
                'summary' => 'We turn complicated processes into interfaces people can use without training — validated with real users before a line of production code is written.',
                'body' => <<<'HTML'
<p>Design debt costs more than technical debt, because every confused user pays it every day.</p>
<h2>Our approach</h2>
<p>Interviews and workflow mapping, information architecture, low-fidelity flows, a tested interactive prototype, and a documented component library your engineers can build straight from.</p>
HTML,
                'highlights' => [
                    'User interviews and journey mapping',
                    'Wireframes and interactive prototypes',
                    'Usability testing before build',
                    'Design system and component library',
                ],
                'sort_order' => 6,
                'is_featured' => false,
            ],
        ];

        foreach ($services as $index => $service) {
            Service::query()->updateOrCreate(
                ['slug' => $service['slug']],
                $service + [
                    'image' => null,
                    'is_published' => true,
                    'sort_order' => $index + 1,
                ],
            );
        }
    }
}
