<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\HandlesImageUploads;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBusinessDetailsRequest;
use App\Models\Setting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class BusinessSettingsController extends Controller
{
    use HandlesImageUploads;

    /**
     * Colour themes a logo can be configured for.
     *
     * @var list<string>
     */
    private const LOGO_THEMES = ['dark', 'light'];

    /**
     * How a theme's logo is displayed: the built-in mark, a full logo image,
     * or a square icon rendered beside the brand text.
     *
     * @var list<string>
     */
    private const LOGO_MODES = ['default', 'full', 'icon'];

    /**
     * Business entity details shown across the frontend.
     */
    public function edit(): View
    {
        return view('admin.business.edit', [
            'business' => Setting::structured('business', $this->defaults()),
            'brand_logos' => $this->brandLogos(),
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
            'brand_logos' => $this->syncBrandLogos($request, $this->brandLogos()),
        ]);

        return redirect()
            ->route('admin.business.edit')
            ->with('status', 'Business details saved — frontend pages now use them.');
    }

    /**
     * Logo configuration for each colour theme, normalised against the defaults.
     *
     * @return array<string, array{mode: string, full: ?string, icon: ?string}>
     */
    private function brandLogos(): array
    {
        $stored = Setting::structured('brand_logos', []);
        $logos = [];

        foreach (self::LOGO_THEMES as $theme) {
            $config = $stored[$theme] ?? [];
            $mode = $config['mode'] ?? 'default';
            $mode = in_array($mode, self::LOGO_MODES, true) ? $mode : 'default';

            $logos[$theme] = [
                'mode' => $mode,
                'full' => $config['full'] ?? null,
                'icon' => $config['icon'] ?? null,
            ];
        }

        return $logos;
    }

    /**
     * Store any uploaded logo files and prune the ones they replace.
     *
     * @param  array<string, array{mode: string, full: ?string, icon: ?string}>  $current
     * @return array<string, array{mode: string, full: ?string, icon: ?string}>
     */
    private function syncBrandLogos(UpdateBusinessDetailsRequest $request, array $current): array
    {
        $logos = [];

        foreach (self::LOGO_THEMES as $theme) {
            $mode = $request->input("logos.{$theme}.mode", 'default');
            $mode = in_array($mode, self::LOGO_MODES, true) ? $mode : 'default';

            $logos[$theme] = [
                'mode' => $mode,
                'full' => $this->replaceUploadedImage(
                    $request->file("logos.{$theme}.full"),
                    $current[$theme]['full'] ?? null,
                    'uploads/brand',
                ),
                'icon' => $this->replaceUploadedImage(
                    $request->file("logos.{$theme}.icon"),
                    $current[$theme]['icon'] ?? null,
                    'uploads/brand',
                ),
            ];
        }

        return $logos;
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
