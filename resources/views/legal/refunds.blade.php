@extends('layouts.app')

@section('title', 'Cancellation, Refund & Subscription Cancellation Policy — Agape Infosys')
@section('description', 'How to cancel a subscription, when refunds apply and how long they take — for Agape Infosys digital products and services.')

@section('content')
    @include('legal.partials.hero', [
        'title' => 'Cancellation <span class="text-accent">&amp;</span> Refunds',
        'lead' => 'How to cancel a subscription, when a refund applies, and how quickly the money comes back.',
        'updated' => '14 October 2026',
    ])

    <section class="pb-24">
        <div class="shell">
            <div class="prose-dark max-w-3xl">
                <h2>1. The short version</h2>
                <blockquote>
                    Subscriptions can be cancelled at any time from your dashboard, with one click and no questions
                    asked. Digital purchases are refundable within 14 days of payment if the access has not been used.
                    Approved refunds reach your original payment method within 7–10 business days.
                </blockquote>

                <h2>2. Cancelling a subscription</h2>
                <p>
                    You can cancel a recurring plan yourself, at any time, without contacting support:
                </p>
                <ul>
                    <li>Sign in to your <a href="{{ route('login') }}">client account</a>.</li>
                    <li>Open <a href="{{ route('dashboard.subscriptions.index') }}">My Subscriptions</a>.</li>
                    <li>Press <strong>Cancel</strong> next to the plan you want to stop.</li>
                </ul>
                <p>What cancellation does:</p>
                <ul>
                    <li>It stops all future renewals immediately — no further charges are taken.</li>
                    <li>Your access continues until the end of the period you have already paid for.</li>
                    <li>It is reflected in our entitlement system straight away, so connected product sites stop renewing you.</li>
                </ul>
                <p>
                    What cancellation does not do: it does not refund the current period by default — see section 3 for
                    when a refund applies.
                </p>

                <h2>3. Refunds for digital products and subscriptions</h2>
                <p>Digital goods are delivered instantly, so refunds are offered on these terms:</p>
                <ul>
                    <li>
                        <strong>Within 14 days of payment, access not yet used</strong> — full refund on request. "Not
                        used" means the entitlement has not been exercised on the connected product.
                    </li>
                    <li>
                        <strong>Technical fault on our side</strong> — if we cannot deliver access and cannot fix it
                        within the window promised by the
                        <a href="{{ route('legal.delivery') }}">Delivery Policy</a>, you may cancel for a full refund.
                    </li>
                    <li>
                        <strong>Duplicate payment</strong> — refunded in full as soon as we confirm it.
                    </li>
                    <li>
                        <strong>Billing error by us</strong> — refunded in full, plus any gateway charges caused by the error.
                    </li>
                </ul>

                <h2>4. Situations that are not refundable</h2>
                <ul>
                    <li>Digital access that has already been exercised or downloaded.</li>
                    <li>Subscription time already consumed after the 14-day window.</li>
                    <li>Change of mind after the first 30 days of a subscription.</li>
                    <li>Currency fluctuations or bank and gateway fees charged to you by your own bank.</li>
                    <li>
                        Bespoke development work, to the extent it has already been delivered or a contracted milestone
                        has been met — the statement of work for that engagement governs.
                    </li>
                </ul>

                <h2>5. How to request a refund</h2>
                <ul>
                    <li>
                        Send the request through the <a href="{{ route('contact.create') }}">contact page</a>, quoting
                        your order reference (it looks like <strong>AGP-XXXXXX</strong> and appears in your
                        <a href="{{ route('dashboard.orders.index') }}">order history</a>), or
                    </li>
                    <li>reply to your order confirmation email with "Refund request" in the subject line.</li>
                </ul>
                <p>
                    We acknowledge refund requests within one business day and decide within three business days. If
                    approved, the refund is issued to the original payment method.
                </p>

                <h2>6. How long refunds take</h2>
                <ul>
                    <li><strong>Processing by us</strong> — up to 3 business days from approval.</li>
                    <li><strong>Gateway settlement</strong> — typically 7–10 business days for cards and UPI, up to 15 business days for some wallets and international methods.</li>
                    <li>You receive a refund confirmation email with the transaction reference when we release the money.</li>
                </ul>

                <h2>7. Cancelling an order before payment</h2>
                <p>
                    An order that has not been paid simply expires — checkout sessions are held for a limited window
                    and then lapse automatically. Nothing is charged, and there is nothing to cancel.
                </p>

                <h2>8. Failed and expired payments</h2>
                <p>
                    If a payment fails or a session expires, no money is captured and your access is not activated. Any
                    temporary hold your bank placed is released by them, usually within 48 hours. You are welcome to
                    start the purchase again.
                </p>

                <h2>9. Chargebacks</h2>
                <p>
                    Please contact us before raising a chargeback — most issues (missing access, double charges,
                    confusion about a renewal) are solved faster directly, and we can often settle within a day. A
                    chargeback while a request is open with us may pause the refund process until the bank process
                    concludes.
                </p>

                <h2>10. Enterprise and contractual engagements</h2>
                <p>
                    Cancellation and refund terms for bespoke software or consultancy work are set out in the signed
                    statement of work for that engagement. Where it is silent, the milestones already delivered are
                    non-refundable and remaining work can be cancelled with 30 days' written notice.
                </p>

                <h2>11. Changes to this policy</h2>
                <p>
                    We may update this policy; the version published on this page with its date always applies to
                    purchases made after that date.
                </p>

                <h2>12. Contact</h2>
                <p>
                    Cancellations, refunds and billing questions all go through the
                    <a href="{{ route('contact.create') }}">contact page</a>. We reply within one business day.
                </p>
            </div>
        </div>
    </section>
@endsection
