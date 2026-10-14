<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RendersBuilderPages;
use Illuminate\Contracts\View\View;

/**
 * The legal pages linked from the footer of every storefront page.
 *
 * Each route serves the hand-built policy view until a builder page with the
 * matching reserved slug is published, so the copy can be rewritten in the
 * page builder without a deploy — the same migration path every other
 * marketing route uses.
 */
class LegalController extends Controller
{
    use RendersBuilderPages;

    /**
     * Privacy Policy — what we collect, why, and the rights clients have.
     */
    public function privacy(): View
    {
        return $this->builderPage('privacy-policy') ?? view('legal.privacy');
    }

    /**
     * Terms and Conditions — the agreement behind using this site and buying from it.
     */
    public function terms(): View
    {
        return $this->builderPage('terms-and-conditions') ?? view('legal.terms');
    }

    /**
     * Cancellation, Refund & Subscription Cancellation Policy.
     */
    public function refunds(): View
    {
        return $this->builderPage('cancellation-and-refunds') ?? view('legal.refunds');
    }

    /**
     * Delivery Policy — how digital access is delivered and how fast.
     */
    public function delivery(): View
    {
        return $this->builderPage('delivery-policy') ?? view('legal.delivery');
    }
}
