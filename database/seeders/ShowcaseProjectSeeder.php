<?php

namespace Database\Seeders;

use App\Enums\ProjectStatus;
use App\Models\ShowcaseProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShowcaseProjectSeeder extends Seeder
{
    /**
     * Seed the ongoing and upcoming projects shown on the poster wall.
     */
    public function run(): void
    {
        $projects = [
            [
                'title' => 'Streamly',
                'industry' => 'OTT & Media',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Ad-free OTT platform with adaptive streaming and regional subtitles.',
                'description' => 'A full OTT product: content library, DRM-ready delivery, subscriptions, recommendations and a creator dashboard for regional publishers.',
                'stack' => ['Laravel', 'Vue', 'AWS', 'FFmpeg'],
                'expected_launch' => null,
            ],
            [
                'title' => 'CareSlot',
                'industry' => 'Appointment Booking',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Doctor and clinic booking with reminders and waitlist automation.',
                'description' => 'Patients book, reschedule and pay for consultations; clinics manage slots, staff rosters and automated SMS/WhatsApp reminders.',
                'stack' => ['Laravel', 'React Native', 'MySQL'],
                'expected_launch' => null,
            ],
            [
                'title' => 'MedTrack HIS',
                'industry' => 'Hospital Management',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Hospital management suite covering OPD, IPD, pharmacy and lab.',
                'description' => 'Electronic medical records, billing, inventory, lab workflows and MIS reporting for multi-speciality hospitals.',
                'stack' => ['Laravel', 'Vue', 'PostgreSQL'],
                'expected_launch' => null,
            ],
            [
                'title' => 'BazaarPOS',
                'industry' => 'POS & Retail',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Offline-first POS with inventory sync and GST invoicing.',
                'description' => 'Retail counter software that keeps selling through network drops, then reconciles stock, invoices and shift summaries.',
                'stack' => ['Flutter', 'Node.js', 'MySQL'],
                'expected_launch' => null,
            ],
            [
                'title' => 'FleetPulse',
                'industry' => 'Logistics & Fleet',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Live fleet tracking with route optimisation and driver analytics.',
                'description' => 'GPS telemetry, geofence alerts, trip planning and driver scorecards for transport operators.',
                'stack' => ['Laravel', 'Vue', 'Redis', 'AWS'],
                'expected_launch' => null,
            ],
            [
                'title' => 'LearnNest',
                'industry' => 'Education & E-learning',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Learning platform with live classes, assessments and certification.',
                'description' => 'Course authoring, live cohorts, proctored assessments and verifiable certificates for institutes and corporate training.',
                'stack' => ['Laravel', 'Vue', 'WebRTC'],
                'expected_launch' => null,
            ],
            [
                'title' => 'PayLedger',
                'industry' => 'FinTech & Banking',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Reconciliation and ledger platform for payment aggregators.',
                'description' => 'Automated settlement reconciliation, dispute tracking and audit-ready ledgers across multiple payment rails.',
                'stack' => ['Laravel', 'PostgreSQL', 'AWS'],
                'expected_launch' => null,
            ],
            [
                'title' => 'FitLoop',
                'industry' => 'Health & Fitness',
                'status' => ProjectStatus::Ongoing,
                'tagline' => 'Gym and studio management with class booking and CRM.',
                'description' => 'Memberships, trainer schedules, class capacity, lead pipelines and branded member apps for fitness businesses.',
                'stack' => ['Laravel', 'React Native', 'MySQL'],
                'expected_launch' => null,
            ],
            [
                'title' => 'StayVista',
                'industry' => 'Travel & Hospitality',
                'status' => ProjectStatus::Upcoming,
                'tagline' => 'Boutique hotel booking engine with channel manager.',
                'description' => 'Direct bookings for independent hotels, with rate parity across OTAs, housekeeping workflows and guest messaging.',
                'stack' => ['Laravel', 'Vue', 'MySQL'],
                'expected_launch' => now()->addMonths(4),
            ],
            [
                'title' => 'CartRocket',
                'industry' => 'E-commerce',
                'status' => ProjectStatus::Upcoming,
                'tagline' => 'Headless commerce backend for multi-brand retailers.',
                'description' => 'Composable catalogue, cart and checkout APIs with a merchant console for brands that outgrew off-the-shelf platforms.',
                'stack' => ['Node.js', 'Vue', 'Redis'],
                'expected_launch' => now()->addMonths(3),
            ],
            [
                'title' => 'HROne Suite',
                'industry' => 'HR & Payroll',
                'status' => ProjectStatus::Upcoming,
                'tagline' => 'Payroll, attendance and leave automation for growing SMEs.',
                'description' => 'Attendance capture, leave workflows, statutory payroll and employee self-service for small and mid-size companies.',
                'stack' => ['Laravel', 'Vue', 'MySQL'],
                'expected_launch' => now()->addMonths(5),
            ],
            [
                'title' => 'TableTreat',
                'industry' => 'POS & Restaurant',
                'status' => ProjectStatus::Upcoming,
                'tagline' => 'Restaurant POS with kitchen display and table management.',
                'description' => 'Order taking, KDS routing, table layouts and shift accounting for quick-service and fine-dining chains.',
                'stack' => ['Flutter', 'Node.js', 'MySQL'],
                'expected_launch' => now()->addMonths(2),
            ],
            [
                'title' => 'RentRadar',
                'industry' => 'Real Estate',
                'status' => ProjectStatus::Upcoming,
                'tagline' => 'Rental marketplace with tenant verification and e-signatures.',
                'description' => 'Listings, verified owner/tenant profiles, rental agreements and deposit tracking in one flow.',
                'stack' => ['Laravel', 'React Native', 'AWS'],
                'expected_launch' => now()->addMonths(6),
            ],
        ];

        foreach ($projects as $index => $project) {
            ShowcaseProject::query()->updateOrCreate(
                ['slug' => Str::slug($project['title'])],
                array_merge($project, ['sort_order' => $index]),
            );
        }
    }
}
