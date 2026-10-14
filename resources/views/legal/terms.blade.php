@extends('layouts.app')

@section('title', 'Terms & Conditions — Agape Infosys')
@section('description', 'The terms governing your use of the Agape Infosys website, services and digital shop.')

@section('content')
    @include('legal.partials.hero', [
        'title' => 'Terms <span class="text-accent">&amp;</span> Conditions',
        'lead' => 'The agreement behind using this website and buying from us. Plain language, no tricks.',
        'updated' => '14 October 2026',
    ])

    <section class="pb-24">
        <div class="shell">
            <div class="prose-dark max-w-3xl">
                <h2>1. Agreement to these terms</h2>
                <p>
                    By using this website, contacting us, registering an account or buying a product or service, you
                    agree to these Terms &amp; Conditions and to the policies they link to — including our
                    <a href="{{ route('legal.privacy') }}">Privacy Policy</a>,
                    <a href="{{ route('legal.refunds') }}">Cancellation &amp; Refund Policy</a> and
                    <a href="{{ route('legal.delivery') }}">Delivery Policy</a>. If you do not agree, please do not use
                    the site.
                </p>
                <p>
                    If you engage us for bespoke work, the signed statement of work, proposal or order form for that
                    engagement also applies. Where it conflicts with these terms, the signed document wins for that
                    engagement.
                </p>

                <h2>2. Our services</h2>
                <p>
                    Agape Infosys provides software development, IT consultancy, cloud and related digital services, and
                    sells digital products and subscriptions through this website. Descriptions, timelines and prices on
                    this site are an invitation to buy, not a binding offer; an engagement begins when we confirm it in
                    writing.
                </p>

                <h2>3. Accounts</h2>
                <ul>
                    <li>You must be at least 18 years old, or acting for an organisation that can contract, to buy from us.</li>
                    <li>Keep your login credentials confidential — you are responsible for activity under your account.</li>
                    <li>Give us accurate details. Orders are matched to the email address you checkout with, so use one you control.</li>
                    <li>Tell us promptly if you believe your account has been compromised.</li>
                </ul>

                <h2>4. Orders, pricing and payment</h2>
                <ul>
                    <li>Prices are shown in the currency displayed at checkout and include taxes unless stated otherwise.</li>
                    <li>Payment is due in full at checkout unless a written engagement says otherwise.</li>
                    <li>A contract of sale forms when we confirm your payment; until then we may decline an order.</li>
                    <li>We may correct obvious pricing errors and cancel affected orders with a full refund.</li>
                    <li>Payment is taken by our third-party gateways; their terms also apply to the transaction.</li>
                </ul>

                <h2>5. Subscriptions and renewals</h2>
                <p>
                    Subscription plans renew automatically at the interval shown at checkout until cancelled. You can
                    cancel any time from your <a href="{{ route('dashboard.subscriptions.index') }}">dashboard</a>;
                    cancellation stops future renewals and your access continues until the end of the period already paid
                    for. Full details are in our
                    <a href="{{ route('legal.refunds') }}">Cancellation &amp; Refund Policy</a>.
                </p>

                <h2>6. Intellectual property</h2>
                <ul>
                    <li>The website, its design, text, logos and code belong to Agape Infosys or its licensors.</li>
                    <li>You may not copy, adapt or redistribute site content without written permission, except for personal, non-commercial reference.</li>
                    <li>For bespoke work, ownership of the deliverables transfers to you on full payment, as set out in the engagement contract.</li>
                    <li>We retain ownership of pre-existing tools, libraries and know-how, and the right to show non-confidential work in our portfolio.</li>
                </ul>

                <h2>7. Acceptable use</h2>
                <p>You agree not to:</p>
                <ul>
                    <li>use the site for any unlawful, fraudulent or abusive purpose;</li>
                    <li>attempt to probe, breach or overload our systems, or resell access to our products without permission;</li>
                    <li>upload malicious code, or scrape the site in a way that degrades it for others;</li>
                    <li>impersonate another person or misrepresent your affiliation with us.</li>
                </ul>
                <p>We may suspend accounts that break these rules.</p>

                <h2>8. Third-party services and links</h2>
                <p>
                    The site links to and integrates with third-party platforms — payment providers and the product sites
                    our digital goods unlock. Those services run under their own terms and we are not responsible for
                    their content or availability.
                </p>

                <h2>9. Disclaimers</h2>
                <p>
                    The website and its content are provided "as is". We work hard to keep it accurate and available, but
                    we do not warrant that it will be uninterrupted, error-free or suitable for every purpose. Digital
                    access is subject to the
                    <a href="{{ route('legal.delivery') }}">Delivery Policy</a> timelines.
                </p>

                <h2>10. Limitation of liability</h2>
                <p>
                    To the maximum extent permitted by law, Agape Infosys is not liable for indirect, incidental or
                    consequential losses, or for loss of profit, revenue, data or goodwill arising from your use of the
                    site or our products. Our total liability for any claim arising from a purchase is limited to the
                    amount you paid for the product or service that gave rise to it. Nothing in these terms limits
                    liability that cannot be limited by law.
                </p>

                <h2>11. Indemnity</h2>
                <p>
                    You agree to indemnify Agape Infosys against claims arising from your misuse of the site, your
                    violation of these terms, or your infringement of another party's rights.
                </p>

                <h2>12. Termination</h2>
                <p>
                    We may restrict or terminate access to the site or an account where these terms are breached.
                    Sections concerning intellectual property, disclaimers, liability and indemnity survive termination.
                </p>

                <h2>13. Governing law</h2>
                <p>
                    These terms are governed by the laws of India, and the courts of Bengaluru, Karnataka have
                    jurisdiction over any dispute. Consumers retain any mandatory protections of the law of their place
                    of residence.
                </p>

                <h2>14. Changes to these terms</h2>
                <p>
                    We may update these terms from time to time. The current version always lives on this page with its
                    revision date; continued use of the site after a change means you accept it.
                </p>

                <h2>15. Contact</h2>
                <p>
                    Anything unclear? Ask us through the
                    <a href="{{ route('contact.create') }}">contact page</a> — we answer within one business day.
                </p>
            </div>
        </div>
    </section>
@endsection
