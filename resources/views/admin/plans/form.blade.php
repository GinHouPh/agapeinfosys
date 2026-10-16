@php
    $plan = $plan ?? null;
    $isEdit = $plan !== null;
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.plans.update', $plan) : route('admin.plans.store');
    $selectedProduct = old('product_id', $plan?->product_id ?? $product?->id);
    $billingType = old('billing_type', $plan?->billing_type?->value ?? 'one_time');
    $priceMajor = old('price', $plan !== null ? $plan->price / 100 : null);
    if ($priceMajor !== null && $priceMajor !== '') {
        $priceMajor = rtrim(rtrim(number_format((float) $priceMajor, 2, '.', ''), '0'), '.');
    }
@endphp

<form action="{{ $action }}" method="POST" class="space-y-8">
    @csrf
    @method($method)

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="admin-card space-y-6">
                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field">
                        <label class="field__label" for="product_id">Product *</label>
                        <select class="input" id="product_id" name="product_id" required>
                            @foreach ($products as $productOption)
                                <option value="{{ $productOption->id }}" @selected($selectedProduct == $productOption->id)>
                                    {{ $productOption->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="code">Code *</label>
                        <input class="input" type="text" id="code" name="code" value="{{ old('code', $plan?->code) }}" placeholder="pro_monthly" required>
                        @error('code') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="field__label" for="name">Name *</label>
                    <input class="input" type="text" id="name" name="name" value="{{ old('name', $plan?->name) }}" placeholder="Pro" required>
                    @error('name') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label class="field__label" for="description">Description</label>
                    <textarea class="input" id="description" name="description" rows="3">{{ old('description', $plan?->description) }}</textarea>
                    @error('description') <span class="field__error">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="admin-card space-y-6">
                <div class="grid gap-6 sm:grid-cols-3">
                    <div class="field">
                        <label class="field__label" for="billing_type">Billing type *</label>
                        <select class="input" id="billing_type" name="billing_type" data-billing-select required>
                            @foreach (\App\Enums\BillingType::cases() as $type)
                                <option value="{{ $type->value }}" @selected($billingType === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('billing_type') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="price">Full amount *</label>
                        <input class="input" type="number" id="price" name="price" value="{{ $priceMajor }}" min="0" step="0.01" required>
                        @error('price') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Enter the total price (e.g., 1499.00)</span>
                    </div>

                    <div class="field">
                        <label class="field__label" for="currency">Currency *</label>
                        <input class="input" type="text" id="currency" name="currency" value="{{ old('currency', $plan?->currency ?? 'INR') }}" maxlength="3" required>
                        @error('currency') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2" data-validity-group>
                    <div class="field">
                        <label class="field__label" for="validity_value">Validity value *</label>
                        <input class="input" type="number" id="validity_value" name="validity_value" value="{{ old('validity_value') }}" min="0" placeholder="0, 1, 6, 12...">
                        @error('validity_value') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="validity_unit">Validity unit *</label>
                        <select class="input" id="validity_unit" name="validity_unit">
                            <option value="days" @selected(old('validity_unit') === 'days')>Days</option>
                            <option value="months" @selected(old('validity_unit') === 'months')>Months</option>
                            <option value="years" @selected(old('validity_unit') === 'years')>Years</option>
                            <option value="lifetime" @selected(old('validity_unit') === 'lifetime')>Lifetime (0)</option>
                        </select>
                        @error('validity_unit') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Use value 0 with Lifetime for unlimited access.</span>
                    </div>
                    <input type="hidden" id="validity_days" name="validity_days" value="{{ old('validity_days', $plan?->validity_days) }}">
                </div>

                <div class="grid gap-6 sm:grid-cols-2" data-interval-group>
                    <div class="field">
                        <label class="field__label" for="interval_value">Interval value *</label>
                        <input class="input" type="number" id="interval_value" name="interval_value" value="{{ old('interval_value') }}" min="1" placeholder="1, 6, 12...">
                        @error('interval_value') <span class="field__error">{{ $message }}</span> @enderror
                    </div>

                    <div class="field">
                        <label class="field__label" for="interval_unit">Interval unit *</label>
                        <select class="input" id="interval_unit" name="interval_unit">
                            <option value="days" @selected(old('interval_unit') === 'days')>Days</option>
                            <option value="months" @selected(old('interval_unit') === 'months')>Months</option>
                            <option value="years" @selected(old('interval_unit') === 'years')>Years</option>
                        </select>
                        @error('interval_unit') <span class="field__error">{{ $message }}</span> @enderror>
                        <span class="mt-1 block text-xs text-muted">Renewal period for recurring subscriptions.</span>
                    </div>
                    <input type="hidden" id="interval_days" name="interval_days" value="{{ old('interval_days', $plan?->interval_days) }}">
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="admin-card space-y-6">
                <div class="field">
                    <label class="field__label" for="sort_order">Sort order</label>
                    <input class="input" type="number" id="sort_order" name="sort_order" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}" min="0">
                    @error('sort_order') <span class="field__error">{{ $message }}</span> @enderror
                </div>

                <label class="flex items-center gap-3 text-sm text-white">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan?->is_active ?? true))>
                    Active (shown at checkout)
                </label>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="btn btn--accent">
                    {{ $isEdit ? 'Save changes' : 'Create plan' }}
                    <span class="btn__strip" aria-hidden="true"></span>
                </button>

                <a href="{{ route('admin.plans.index') }}" class="tag self-center hover:border-accent hover:text-accent">Cancel</a>
            </div>
        </div>
    </div>
</form>

@push('scripts')
    <script>
        (function () {
            var select = document.querySelector('[data-billing-select]');
            var validityGroup = document.querySelector('[data-validity-group]');
            var intervalGroup = document.querySelector('[data-interval-group]');
            var validityDays = document.getElementById('validity_days');
            var validityValue = document.getElementById('validity_value');
            var validityUnit = document.getElementById('validity_unit');
            var intervalDays = document.getElementById('interval_days');
            var intervalValue = document.getElementById('interval_value');
            var intervalUnit = document.getElementById('interval_unit');

            var initialValidity = parseInt(validityDays.value) || 0;
            if (initialValidity === 0) {
                validityValue.value = 0;
                validityUnit.value = 'lifetime';
            } else if (initialValidity % 365 === 0 && initialValidity !== 0) {
                validityValue.value = initialValidity / 365;
                validityUnit.value = 'years';
            } else if (initialValidity % 30 === 0) {
                validityValue.value = initialValidity / 30;
                validityUnit.value = 'months';
            } else {
                validityValue.value = initialValidity;
                validityUnit.value = 'days';
            }

            var initialInterval = parseInt(intervalDays.value) || 30;
            if (initialInterval % 365 === 0) {
                intervalValue.value = initialInterval / 365;
                intervalUnit.value = 'years';
            } else if (initialInterval % 30 === 0) {
                intervalValue.value = initialInterval / 30;
                intervalUnit.value = 'months';
            } else {
                intervalValue.value = initialInterval;
                intervalUnit.value = 'days';
            }

            function sync() {
                var recurring = select.value === 'recurring';
                if (validityGroup) validityGroup.style.display = recurring ? 'none' : '';
                if (intervalGroup) intervalGroup.style.display = recurring ? '' : 'none';
            }

            select.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
