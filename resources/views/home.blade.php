@extends('layouts.app')

@section('title', 'Agape Infosys — Software Solutions & IT Consultancy')

@section('description', 'Agape Infosys designs, engineers and supports custom software, web platforms, mobile apps and cloud systems for growing enterprises.')

@section('content')
    {{-- ============================================================
         Hero
         ============================================================ --}}
    <section class="relative flex min-h-screen items-center overflow-hidden pt-28 pb-20">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_78%_18%,rgba(217,4,41,0.20),transparent_58%)]"
             aria-hidden="true"></div>
        <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(to_right,rgba(56,56,56,0.35)_1px,transparent_1px),linear-gradient(to_bottom,rgba(56,56,56,0.35)_1px,transparent_1px)] bg-[size:80px_80px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_78%)]"
             aria-hidden="true"></div>

        <div class="shell relative grid items-center gap-14 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <span class="eyebrow">Software solutions &amp; IT consultancy</span>

                <h1 class="mb-8">
                    <span class="block text-white">Design.</span>
                    <span class="block text-white">Engineer.</span>
                    <span class="block text-accent">Deliver.</span>
                </h1>

                <p class="mb-10 max-w-xl text-lg text-body">
                    We turn complex business problems into dependable software — from discovery
                    workshops and product design through to cloud operations and long term support.
                </p>

                <div class="flex flex-wrap gap-4">
                    <x-btn :href="route('services.index')" variant="accent" label="Explore Services" />
                    <x-btn :href="route('portfolio.index')" variant="ghost" label="See Our Work" />
                </div>
            </div>

            <div class="lg:col-span-5">
                <div class="relative border border-line bg-surface/70 p-8 backdrop-blur">
                    <span class="absolute -top-3 left-8 bg-ink px-3 font-display text-xs tracking-[0.3em] text-accent uppercase">
                        Since 2013
                    </span>

                    <p class="font-display text-2xl leading-snug tracking-[0.06em] text-white capitalize">
                        A senior engineering team you can plug into your roadmap.
                    </p>

                    <div class="mt-8 grid grid-cols-2 gap-6">
                        <div class="border-t border-line pt-4">
                            <span class="block font-display text-4xl text-white">
                                <span data-count="250">0</span>+
                            </span>
                            <span class="text-sm text-muted">Products shipped</span>
                        </div>
                        <div class="border-t border-line pt-4">
                            <span class="block font-display text-4xl text-white">
                                <span data-count="98">0</span>%
                            </span>
                            <span class="text-sm text-muted">Client retention</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-8 left-1/2 hidden -translate-x-1/2 flex-col items-center gap-3 lg:flex">
            <span class="font-display text-[11px] tracking-[0.3em] text-muted uppercase">Scroll</span>
            <span class="h-14 w-px bg-linear-to-b from-accent to-transparent" aria-hidden="true"></span>
        </div>
    </section>

    {{-- ============================================================
         Capability marquee band
         ============================================================ --}}
    <section class="border-y border-line bg-surface py-6" aria-label="Capabilities">
        <div class="marquee">
            <div class="marquee__track">
                @foreach (range(1, 2) as $pass)
                    @foreach (['Custom Software', 'Cloud & DevOps', 'Web Platforms', 'Mobile Apps', 'Data & AI', 'IT Consulting'] as $word)
                        <span class="marquee__item !text-3xl md:!text-5xl">
                            {{ $word }}
                            <span class="marquee__dot" aria-hidden="true"></span>
                        </span>
                    @endforeach
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         Statement
         ============================================================ --}}
    <section class="section section--tight">
        <div class="shell">
            <h2 class="mx-auto max-w-5xl text-center">
                <span class="text-accent">The technology partner</span>
                enterprises trust to turn complex problems into working software.
            </h2>
        </div>
    </section>

    {{-- ============================================================
         About
         ============================================================ --}}
    <section class="section section--surface overflow-hidden">
        <div class="shell grid-2 items-center gap-16">
            <div class="relative">
                <span class="section-mark" aria-hidden="true">About</span>

                <x-media
                    :label="'Agape Infosys Studio'"
                    ratio=""
                    aspect="749/457"
                    class="border border-line"
                />

                <div class="mt-6 grid grid-cols-2 gap-6">
                    <x-media :label="'Engineering'" ratio="" aspect="749/457" class="border border-line" />
                    <x-media :label="'Consulting'" ratio="" aspect="749/457" class="border border-line" />
                </div>
            </div>

            <div>
                <x-section-head
                    title="Agape Infosys runs on engineering excellence"
                    subtitle="Who we are"
                />

                <p class="mb-6 text-body">
                    Agape Infosys is a software solutions and IT consultancy practice. We plan,
                    build and operate the systems that businesses depend on — web platforms,
                    mobile products, integrations and cloud infrastructure.
                </p>

                <p class="mb-10 text-body">
                    Our consultants stay close to your team, so strategy never drifts away from
                    delivery. Every engagement is run by senior engineers who write the code,
                    own the outcome and hand over documentation you can actually maintain.
                </p>

                <x-btn :href="route('about')" label="More About Us" />
            </div>
        </div>
    </section>

    {{-- ============================================================
         Services
         ============================================================ --}}
    <section class="section">
        <div class="shell">
            <x-section-head
                title="Services We Provide"
                subtitle="What we offer"
                align="center"
                mark="services"
            />

            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($services as $service)
                    <a href="{{ route('services.show', $service) }}" class="group card">
                        <x-media
                            :src="$service->image ? Storage::url($service->image) : null"
                            :label="$service->title"
                            ratio=""
                            :aspect="$loop->index % 2 === 0 ? '475/590' : '475/520'"
                        />

                        <div class="mt-5 flex items-start justify-between gap-4">
                            <h4 class="card__title !text-2xl">
                                {{ $service->title }}
                            </h4>

                            <span class="mt-1 font-display text-xs tracking-[0.2em] text-muted uppercase">
                                {{ str_pad((string) ($loop->iteration), 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>

                        @if ($service->excerpt)
                            <p class="mt-3 text-sm text-muted">{{ $service->excerpt }}</p>
                        @endif
                    </a>
                @endforeach
            </div>

            <div class="mt-14 text-center">
                <x-btn :href="route('services.index')" variant="ghost" label="All Services" />
            </div>
        </div>
    </section>

    {{-- ============================================================
         Why choose us + counters
         ============================================================ --}}
    <section class="section section--surface">
        <div class="shell grid-2 items-start gap-16">
            <div>
                <x-section-head
                    title="Helping Build Software That Lasts"
                    subtitle="Why choose us"
                    mark="features"
                />

                <p class="mb-12 max-w-lg text-body">
                    We combine product thinking with disciplined engineering. That means clear
                    estimates, automated tests, observable systems and a team that answers the
                    phone after launch.
                </p>

                <div class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
                    @foreach ($stats as $stat)
                        <div class="stat">
                            <span class="stat__value">
                                <span data-count="{{ (int) ($stat['value'] ?? 0) }}">0</span>
                            </span>
                            <span class="stat__label">{{ $stat['label'] ?? '' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="relative">
                <x-media :label="'Delivery Team'" ratio="" aspect="1024/539" class="border border-line" />
                <div class="mt-6 grid grid-cols-2 gap-6">
                    <div class="border border-line p-6">
                        <h5 class="mb-2 !text-xl text-white">Senior only</h5>
                        <p class="text-sm text-muted">No junior hand-offs. The people who scope your work build it.</p>
                    </div>
                    <div class="border border-line p-6">
                        <h5 class="mb-2 !text-xl text-white">Fixed cadence</h5>
                        <p class="text-sm text-muted">Weekly demos, visible backlog and a demo you can click.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ============================================================
         Clients
         ============================================================ --}}
    <section class="section section--tight">
        <div class="shell mb-12">
            <x-section-head
                title="Valuable Clients"
                subtitle="Who we work with"
                align="center"
                mark="clients"
            />
        </div>

        @if ($clients->isNotEmpty())
            <div class="marquee logo-marquee border-y border-line py-8">
                <div class="marquee__track">
                    @foreach (range(1, 2) as $pass)
                        @foreach ($clients as $client)
                            <span class="marquee__item">
                                @if ($client->logo)
                                    <img src="{{ Storage::url($client->logo) }}" alt="{{ $client->name }}" class="h-10 w-auto opacity-60 grayscale transition hover:opacity-100 hover:grayscale-0">
                                @else
                                    {{ $client->name }}
                                @endif
                            </span>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- ============================================================
         Selected work
         ============================================================ --}}
    <section class="section section--surface">
        <div class="shell">
            <x-section-head
                title="Selected Work"
                subtitle="Recent case studies"
                mark="projects"
            />

            <div class="grid gap-10 md:grid-cols-2">
                @forelse ($projects as $project)
                    <a href="{{ route('portfolio.show', $project) }}" class="group card">
                        <x-media
                            :src="$project->cover_image ? Storage::url($project->cover_image) : null"
                            :label="$project->title"
                            ratio=""
                            aspect="666/530"
                        />

                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($project->category)
                                <span class="tag">{{ $project->category }}</span>
                            @endif
                            @if ($project->year)
                                <span class="tag">{{ $project->year }}</span>
                            @endif
                        </div>

                        <h5 class="card__title mt-5 !text-2xl">{{ $project->title }}</h5>

                        @if ($project->excerpt)
                            <p class="mt-3 text-body">{{ $project->excerpt }}</p>
                        @endif
                    </a>
                @empty
                    <p class="text-muted md:col-span-2">Case studies are on the way — check back shortly.</p>
                @endforelse
            </div>

            @if ($projects->isNotEmpty())
                <div class="mt-14">
                    <x-btn :href="route('portfolio.index')" variant="ghost" label="View All Work" />
                </div>
            @endif
        </div>
    </section>

    {{-- ============================================================
         Process
         ============================================================ --}}
    <section class="section">
        <div class="shell">
            <x-section-head
                title="Process We Follow"
                subtitle="How we work"
                align="center"
                mark="process"
            />

            <div class="grid gap-10 md:grid-cols-3">
                @foreach ($process as $step)
                    <article class="relative border-t border-line pt-8">
                        <span class="mb-6 block font-display text-6xl leading-none text-accent">
                            {{ $step['step'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </span>

                        <h5 class="mb-4 !text-2xl text-white">{{ $step['title'] ?? '' }}</h5>
                        <p class="text-body">{{ $step['body'] ?? '' }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         Testimonials
         ============================================================ --}}
    <section class="section section--surface">
        <div class="shell">
            <x-section-head
                title="What They Say"
                subtitle="Testimonials"
                align="center"
                mark="reviews"
            />

            <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-3">
                @forelse ($testimonials as $testimonial)
                    <figure class="quote-card">
                        <span class="quote-card__mark" aria-hidden="true">&ldquo;</span>

                        <div class="flex items-center gap-4 pt-4">
                            @if ($testimonial->avatar)
                                <img src="{{ Storage::url($testimonial->avatar) }}" alt="{{ $testimonial->name }}" class="h-14 w-14 rounded-full object-cover" loading="lazy">
                            @else
                                <span class="grid h-14 w-14 place-items-center rounded-full border border-line font-display text-lg text-accent">
                                    {{ mb_strtoupper(mb_substr($testimonial->name, 0, 1)) }}
                                </span>
                            @endif

                            <figcaption>
                                <span class="block font-display text-lg tracking-[0.06em] text-white capitalize">
                                    {{ $testimonial->name }}
                                </span>
                                <span class="text-sm text-muted">
                                    {{ trim(($testimonial->role ?? '').($testimonial->company ? ', '.$testimonial->company : ''), ', ') }}
                                </span>
                            </figcaption>
                        </div>

                        <blockquote class="text-body">
                            {{ $testimonial->quote }}
                        </blockquote>

                        <div class="mt-auto flex gap-1 text-accent" aria-label="{{ $testimonial->rating }} out of 5">
                            @foreach (range(1, 5) as $star)
                                <span aria-hidden="true">{{ $star <= $testimonial->rating ? '★' : '☆' }}</span>
                            @endforeach
                        </div>
                    </figure>
                @empty
                    <p class="text-muted md:col-span-2 lg:col-span-3">Testimonials are being collected.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ============================================================
         Contact
         ============================================================ --}}
    <section class="section">
        <div class="shell grid-2 gap-16">
            <div>
                <x-section-head
                    title="Let's Build Something Great Together"
                    subtitle="Let's talk business"
                    mark="contact"
                />

                <p class="mb-10 max-w-lg text-body">
                    Tell us what you are trying to achieve. We will come back within one business
                    day with a point of view, a rough shape of the work and the next sensible step.
                </p>

                <ul class="space-y-5">
                    <li class="flex items-start gap-4 border-b border-line pb-5">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        <div>
                            <span class="block font-display text-sm tracking-[0.2em] text-white uppercase">Email</span>
                            <a href="mailto:{{ \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com') }}" class="hover:text-accent">
                                {{ \App\Models\Setting::value('contact_email', 'hello@agapeinfosys.com') }}
                            </a>
                        </div>
                    </li>
                    <li class="flex items-start gap-4 border-b border-line pb-5">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        <div>
                            <span class="block font-display text-sm tracking-[0.2em] text-white uppercase">Phone</span>
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', \App\Models\Setting::value('contact_phone', '+91 00000 00000')) }}" class="hover:text-accent">
                                {{ \App\Models\Setting::value('contact_phone', '+91 00000 00000') }}
                            </a>
                        </div>
                    </li>
                    <li class="flex items-start gap-4">
                        <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-accent" aria-hidden="true"></span>
                        <div>
                            <span class="block font-display text-sm tracking-[0.2em] text-white uppercase">Studio</span>
                            <p class="whitespace-pre-line">{{ \App\Models\Setting::value('office_address', "Level 4, Tech Park, Innovation Drive\nBengaluru, Karnataka 560103") }}</p>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="border border-line bg-surface p-8 sm:p-10">
                @include('partials.enquiry-form', ['compact' => true])
            </div>
        </div>
    </section>

    {{-- ============================================================
         Blog
         ============================================================ --}}
    <section class="section section--surface">
        <div class="shell">
            <x-section-head
                title="Read Our Latest Articles"
                subtitle="Insights & inspiration"
                align="center"
                mark="blogs"
            />

            <div class="grid gap-8 md:grid-cols-3">
                @forelse ($posts as $post)
                    <article class="group card">
                        <a href="{{ route('blog.show', $post) }}">
                            <x-media
                                :src="$post->cover_image ? Storage::url($post->cover_image) : null"
                                :label="$post->title"
                                ratio=""
                                aspect="666/530"
                            />
                        </a>

                        <div class="mt-6 flex flex-wrap items-center gap-4 text-xs text-muted">
                            @if ($post->category)
                                <span class="tag">{{ $post->category }}</span>
                            @endif
                            <span>
                                {{ $post->published_at?->format('d/m/Y') ?? '' }}
                            </span>
                        </div>

                        <h5 class="card__title mt-4 !text-xl">
                            <a href="{{ route('blog.show', $post) }}">{{ $post->title }}</a>
                        </h5>

                        @if ($post->excerpt)
                            <p class="mt-3 text-sm text-body">{{ $post->excerpt }}</p>
                        @endif
                    </article>
                @empty
                    <p class="text-muted md:col-span-3">No articles published yet.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection
