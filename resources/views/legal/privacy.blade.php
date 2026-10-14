@extends('layouts.app')

@section('title', 'Privacy Policy — Agape Infosys')
@section('description', 'How Agape Infosys collects, uses, stores and protects your personal data when you use this site, contact us or buy from our shop.')

@section('content')
    @include('legal.partials.hero', [
        'title' => '<span class="text-accent">Privacy</span> Policy',
        'lead' => 'What we collect when you browse this site, contact us or buy from our shop — and the control you keep over it.',
        'updated' => '14 October 2026',
    ])

    <section class="pb-24">
        <div class="shell">
            <div class="prose-dark max-w-3xl">
                <h2>1. Who we are</h2>
                <p>
                    Agape Infosys is a software solutions and IT consultancy practice. This policy explains how we handle
                    the personal data of visitors to this website, people who contact us, and clients who buy our digital
                    products or services. Where this policy says "we", "us" or "our", it means Agape Infosys.
                </p>

                <h2>2. Information we collect</h2>
                <p>We collect only what the job needs:</p>
                <ul>
                    <li>
                        <strong>Information you give us</strong> — your name, email address, phone number and company when
                        you fill in a contact, requirement or enquiry form, or register a client account.
                    </li>
                    <li>
                        <strong>Order information</strong> — the name, email, phone, plan and payment status recorded
                        against an order when you buy a digital product or subscription from our shop.
                    </li>
                    <li>
                        <strong>Account information</strong> — the profile details of registered clients: name, email,
                        phone and password (stored only as a secure hash).
                    </li>
                    <li>
                        <strong>Technical information</strong> — standard server logs such as IP address, browser type and
                        the pages requested, used for security and reliability.
                    </li>
                </ul>

                <h2>3. Payment data</h2>
                <p>
                    Card numbers, bank credentials and wallet details never touch our servers. Payments are processed by
                    our payment gateway partners (such as Cashfree, PayU, PhonePe and PayPal) on their own secure
                    surfaces. We receive only the confirmation, the amount, the currency and a transaction reference —
                    enough to deliver what you bought and to answer disputes.
                </p>

                <h2>4. How we use your information</h2>
                <ul>
                    <li>To deliver digital products, activate subscriptions and grant access to the services you bought.</li>
                    <li>To send order confirmations, receipts and service messages about your purchase.</li>
                    <li>To reply to enquiries and prepare quotations and scopes of work.</li>
                    <li>To operate, secure and improve this website and our products.</li>
                    <li>To meet legal and accounting obligations, including tax records of transactions.</li>
                </ul>
                <p>
                    We do <strong>not</strong> sell your personal data, and we do not send marketing messages unless you
                    asked for them.
                </p>

                <h2>5. Who we share it with</h2>
                <p>We share data only with the parties that help us run the business, each bound to protect it:</p>
                <ul>
                    <li><strong>Payment gateways</strong> — to take and confirm your payment.</li>
                    <li><strong>Hosting and infrastructure providers</strong> — who store our application and databases.</li>
                    <li><strong>Email providers</strong> — who deliver receipts and replies on our behalf.</li>
                    <li><strong>Professional advisers</strong> — accountants or lawyers, when a matter requires it.</li>
                </ul>
                <p>
                    Connected product sites (for example a platform you subscribed to through us) receive your subscriber
                    key and entitlement status so your access works — nothing more.
                </p>

                <h2>6. Retention</h2>
                <p>
                    Enquiries and form submissions are kept for up to 24 months so we have context if you come back.
                    Order and subscription records are kept for as long as accounting law requires, typically eight
                    years. Client account data is deleted when you ask us to, unless a legal obligation requires us to
                    keep transaction records.
                </p>

                <h2>7. Security</h2>
                <p>
                    We use encryption in transit, hashed passwords, restricted administrative access and signed product
                    API requests. No system is perfectly secure, but we review our controls and will notify the affected
                    people and the relevant authority of any personal data breach as the law requires.
                </p>

                <h2>8. Your rights</h2>
                <p>Depending on where you live, you can ask us to:</p>
                <ul>
                    <li>give you a copy of the personal data we hold about you;</li>
                    <li>correct anything that is wrong;</li>
                    <li>delete your data, where we have no legal reason to keep it;</li>
                    <li>restrict or object to how we use it;</li>
                    <li>provide your data in a portable format.</li>
                </ul>
                <p>
                    Registered clients can see and edit the basics any time from the
                    <a href="{{ route('dashboard.profile.edit') }}">profile page</a>. For anything else, write to us — we
                    respond within 30 days.
                </p>

                <h2>9. Cookies</h2>
                <p>
                    We use only the cookies needed to keep you signed in and to run your shopping cart, plus standard
                    server logging. We do not run third-party advertising or cross-site tracking cookies.
                </p>

                <h2>10. Changes to this policy</h2>
                <p>
                    When we change how we handle data, we update this page and revise the date above. Significant
                    changes will also be announced on the site.
                </p>

                <h2>11. Contact</h2>
                <p>
                    Questions about this policy, or a request about your data, go to our
                    <a href="{{ route('contact.create') }}">contact page</a> or to the contact email published in the
                    footer of every page. Please put "Privacy" in the subject line so it reaches the right person.
                </p>
            </div>
        </div>
    </section>
@endsection
