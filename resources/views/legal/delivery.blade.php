@extends('layouts.app')

@section('title', 'Delivery Policy — Agape Infosys')
@section('description', 'How digital access is delivered after purchase: activation timelines, receipts, downloads and what happens if access is delayed.')

@section('content')
    @include('legal.partials.hero', [
        'title' => 'Delivery <span class="text-accent">Policy</span>',
        'lead' => 'Everything we sell here is digital. This page explains exactly how access reaches you, and how fast.',
        'updated' => '14 October 2026',
    ])

    <section class="pb-24">
        <div class="shell">
            <div class="prose-dark max-w-3xl">
                <h2>1. What we deliver</h2>
                <p>
                    Agape Infosys sells digital products, subscriptions and software services. Nothing is shipped
                    physically: there are no parcels, no couriers and no shipping charges. What you receive is digital
                    access — activation of a plan on the connected product, and the licence or service it unlocks.
                </p>

                <h2>2. How delivery works</h2>
                <ol>
                    <li>You complete checkout and pay through one of our payment gateways.</li>
                    <li>
                        Our system receives the payment confirmation and activates your plan — this is the moment of
                        "delivery".
                    </li>
                    <li>
                        You receive an order confirmation and receipt at the email address you checked out with, quoting
                        your order reference (for example <strong>AGP-XXXXXX</strong>).
                    </li>
                    <li>
                        Your access appears in your <a href="{{ route('dashboard.access.index') }}">client dashboard</a>
                        under <strong>My Access</strong>, with a direct link to the product you bought.
                    </li>
                </ol>

                <h2>3. Delivery timelines</h2>
                <ul>
                    <li>
                        <strong>Activation</strong> — typically within minutes of payment confirmation.
                    </li>
                    <li>
                        <strong>Guaranteed maximum</strong> — access is live within <strong>24 hours</strong> of a
                        confirmed payment, in every case.
                    </li>
                    <li>
                        <strong>Receipts and invoices</strong> — emailed immediately after payment; downloadable any
                        time from your <a href="{{ route('dashboard.orders.index') }}">order history</a>.
                    </li>
                    <li>
                        <strong>Bespoke software deliverables</strong> — delivered on the milestones agreed in your
                        statement of work, demonstrated at the end of each sprint.
                    </li>
                </ul>
                <p>
                    A payment is "confirmed" when our gateway reports it successful. Bank delays, 3-D Secure holds and
                    wallet settlements sit between your payment and that confirmation and are outside our control — but
                    the 24-hour guarantee starts the moment confirmation reaches us.
                </p>

                <h2>4. Where to find what you bought</h2>
                <ul>
                    <li><a href="{{ route('dashboard.orders.index') }}">My Orders</a> — every order with its receipt and status.</li>
                    <li><a href="{{ route('dashboard.subscriptions.index') }}">My Subscriptions</a> — renewal dates and access windows.</li>
                    <li><a href="{{ route('dashboard.access.index') }}">My Access</a> — the products you can open right now.</li>
                </ul>
                <p>
                    Not registered yet? Create a client account with the same email you checked out with and every
                    purchase appears automatically.
                </p>

                <h2>5. If access has not arrived</h2>
                <p>
                    Sometimes a payment is confirmed while you are still on the checkout page — refresh your dashboard
                    before anything else. If access is still missing after 24 hours:
                </p>
                <ul>
                    <li>check that the email you signed in with matches the one on the order;</li>
                    <li>open the order in your dashboard and confirm its status is <strong>Paid</strong>;</li>
                    <li>
                        <a href="{{ route('contact.create') }}">Contact us</a> with the order reference — we investigate
                        within one business day and either restore access immediately or refund the payment in full under
                        our <a href="{{ route('legal.refunds') }}">Refund Policy</a>.
                    </li>
                </ul>

                <h2>6. Wrong email at checkout</h2>
                <p>
                    Access is granted to the subscriber key recorded on the order — usually the email you checked out
                    with. If you used the wrong address, contact us with the order reference and the correct email; once
                    verified, we move the entitlement across.
                </p>

                <h2>7. Currency, taxes and receipts</h2>
                <ul>
                    <li>Prices are charged in the currency shown at checkout.</li>
                    <li>Taxes included or excluded are itemised on your receipt.</li>
                    <li>Every receipt carries the order reference, amount, date and payment status for your records.</li>
                </ul>

                <h2>8. Service availability</h2>
                <p>
                    The product sites we link to are operated by their own owners. We guarantee your entitlement is
                    granted on our side within the timelines above; availability of the destination platform is governed
                    by that platform's own terms.
                </p>

                <h2>9. Contact</h2>
                <p>
                    Delivery questions go through the <a href="{{ route('contact.create') }}">contact page</a> with your
                    order reference — we reply within one business day.
                </p>
            </div>
        </div>
    </section>
@endsection
