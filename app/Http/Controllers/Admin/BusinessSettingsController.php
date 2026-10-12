<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBusinessDetailsRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BusinessSettingsController extends Controller
{
    /**
     * Business entity details shown across the frontend.
     */
    public function edit(): View
    {
        return view('admin.business.edit', [
            'business' => Setting::structured('business', $this->defaults()),
            'contact_email' => (string) Setting::value('contact_email', 'hello@agapeinfosys.com'),
            'contact_phone' => (string) Setting::value('contact_phone', '+91 00000 00000'),
            'office_address' => (string) Setting::value('office_address', ''),
            'mailing_address' => (string) Setting::value('mailing_address', ''),
        ]);
    }

    /**
     * Persist business details; every frontend page reads them from here.
     */
    public function update(UpdateBusinessDetailsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        Setting::store([
            'contact_email' => $data['contact_email'],
            'contact_phone' => $data['contact_phone'] ?? '',
            'office_address' => $data['office_address'] ?? '',
            'mailing_address' => $data['mailing_address'] ?? '',
            'business' => [
                'brand_name' => $data['brand_name'],
                'legal_name' => $data['legal_name'] ?? '',
                'tagline' => $data['tagline'] ?? '',
                'founded_year' => $data['founded_year'] !== null ? (string) $data['founded_year'] : '',
                'industry' => $data['industry'] ?? '',
                'registration_number' => $data['registration_number'] ?? '',
                'tax_number' => $data['tax_number'] ?? '',
                'website' => $data['website'] ?? '',
                'support_hours' => $data['support_hours'] ?? '',
                'socials' => $data['socials'] ?? [],
            ],
        ]);

        return redirect()
            ->route('admin.business.edit')
            ->with('status', 'Business details saved — frontend pages now use them.');
    }

    /**
     * Seed values mirrored from SettingSeeder for a fresh install.
     *
     * @return array<string, mixed>
     */
    private function defaults(): array
    {
        return [
            'brand_name' => 'Agape Infosys',
            'legal_name' => '',
            'tagline' => '',
            'founded_year' => '',
            'industry' => '',
            'registration_number' => '',
            'tax_number' => '',
            'website' => '',
            'support_hours' => '',
            'socials' => [],
        ];
    }
}
