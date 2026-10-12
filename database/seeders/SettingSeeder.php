<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Seed the editable site-wide content values.
     */
    public function run(): void
    {
        Setting::store([
            'contact_email' => 'hello@agapeinfosys.com',
            'contact_phone' => '+91 00000 00000',
            'office_address' => "Level 4, Tech Park, Innovation Drive\nBengaluru, Karnataka 560103",
            'mailing_address' => "PO Box 4021, Knowledge City\nBengaluru, Karnataka 560001",

            'stats' => [
                ['value' => 12, 'label' => 'years of Experience'],
                ['value' => 250, 'label' => 'projects Delivered'],
                ['value' => 90, 'label' => 'happy Clients'],
                ['value' => 15, 'label' => 'Industry Awards'],
            ],

            'process' => [
                [
                    'step' => '01',
                    'title' => 'Discover, Strategy',
                    'body' => 'We map the workflow, audit the existing systems and agree a roadmap with measurable outcomes before anyone writes code.',
                ],
                [
                    'step' => '02',
                    'title' => 'Design, Build',
                    'body' => 'Design and engineering run together in fortnightly increments, so you are reviewing working software rather than slide decks.',
                ],
                [
                    'step' => '03',
                    'title' => 'Launch, Scale',
                    'body' => 'We deploy, monitor and harden the system, then stay on as your long term technology partner.',
                ],
            ],

            'values' => [
                ['title' => 'Ownership', 'body' => 'We treat your product like our own — accountable for uptime, budgets and business results.'],
                ['title' => 'Transparency', 'body' => 'Open estimates, visible backlogs and weekly demos. No surprises at the end of the sprint.'],
                ['title' => 'Craft', 'body' => 'Clean architecture, automated tests and documentation that outlives the engagement.'],
                ['title' => 'Partnership', 'body' => 'Senior consultants who stay with you from discovery through to long term support.'],
            ],
        ]);
    }
}
