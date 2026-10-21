{{--
    The enquiry/requirement form body.

    Shared by the hand-built /forms/{slug} view and the builder's form-embed
    widget so both render exactly the same markup.

    Expects: $form (App\Models\Form) and $groups (field groups from Form::groups()).
--}}
<div class="shell max-w-4xl">
    @if (session('status'))
        <p class="form-status" role="status">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div class="form-status" role="alert">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($form->fields->isEmpty())
        <p class="text-lg text-muted">
            This form has no questions yet — check back shortly.
        </p>
    @else
        @php
            $action = request()->routeIs('requirements.*')
                ? route('requirements.store')
                : route('forms.store', $form);
        @endphp

        <form action="{{ $action }}" method="POST" class="space-y-12" novalidate enctype="multipart/form-data">
            @csrf

            {{-- A bot fills this in; a person never sees it. --}}
            <div class="hidden" aria-hidden="true">
                <label for="{{ $honeypot ?? 'hp' }}">Leave this field empty</label>
                <input type="text" id="{{ $honeypot ?? 'hp' }}" name="{{ $honeypot ?? 'hp' }}" tabindex="-1" autocomplete="off">
            </div>

            @php $section = 0; @endphp

            @foreach ($groups as $group)
                <div class="space-y-6">
                    @if ($group['group'] !== null && $group['group'] !== '')
                        <div class="border-b border-line pb-4">
                            <h3 class="font-display text-sm tracking-[0.24em] text-white uppercase">
                                <span class="text-accent">{{ str_pad((string) ++$section, 2, '0', STR_PAD_LEFT) }}</span>
                                — {{ $group['group'] }}
                            </h3>
                        </div>
                    @endif

                    <div class="grid gap-7 sm:grid-cols-2">
                        @foreach ($group['fields'] as $field)
                            @include('forms.field', ['field' => $field])
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="flex flex-col items-start gap-4 border-t border-line pt-8 sm:flex-row sm:items-center">
                <button type="submit" class="btn btn--accent">
                    {{ $form->submit_label }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <p class="text-sm text-muted">
                    We usually respond within one business day. Prefer to talk?
                    <a href="{{ route('contact.create') }}" class="text-accent hover:underline">Contact us</a>.
                </p>
            </div>
        </form>
    @endif
</div>

@if ($honeypot ?? null)
    <script>
        // Conditional fields: a field with a condition stays out of the way
        // until the answers it depends on say it should be there.
        (function () {
            'use strict';

            function readValue(name) {
                var field = document.querySelector('[name="answers[' + name + ']"]');
                if (!field) return '';

                if (field.type === 'checkbox') {
                    var checked = document.querySelectorAll('[name="answers[' + name + '][]"]:checked');
                    return Array.prototype.map.call(checked, function (el) { return el.value; }).join(', ');
                }

                return field.value || '';
            }

            function matches(rule) {
                var actual = readValue(rule.field);
                var expected = rule.value === undefined || rule.value === null ? '' : String(rule.value);

                switch (rule.operator) {
                    case 'not_equals': return actual !== expected;
                    case 'contains': return actual !== '' && actual.indexOf(expected) !== -1;
                    case 'not_contains': return actual.indexOf(expected) === -1;
                    case 'is_filled': return actual !== '';
                    case 'is_empty': return actual === '';
                    default: return actual === expected;
                }
            }

            function apply() {
                document.querySelectorAll('[data-condition]').forEach(function (el) {
                    var rules;
                    try {
                        rules = JSON.parse(el.getAttribute('data-condition'));
                    } catch (e) {
                        return;
                    }

                    var show = (rules || []).every(matches);
                    el.classList.toggle('hidden', !show);

                    el.querySelectorAll('input, select, textarea').forEach(function (input) {
                        input.disabled = !show;
                    });
                });
            }

            document.addEventListener('input', apply);
            document.addEventListener('change', apply);
            apply();
        })();
    </script>
@endif
