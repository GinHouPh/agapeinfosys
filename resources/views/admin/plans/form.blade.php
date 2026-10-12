@php
    $plan = $plan ?? null;
    $isEdit = $plan !== null;
    $method = $isEdit ? 'PUT' : 'POST';
    $action = $isEdit ? route('admin.plans.update', $plan) : route('admin.plans.store');
    $selectedProduct = old('product_id', $plan?->product_id ?? $product?->id);
    $billingType = old('billing_type', $plan?->billing_type?->value ?? 'one_time');
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
                        <label class="field__label" for="price">Price (minor units) *</label>
                        <input class="input" type="number" id="price" name="price" value="{{ old('price', $plan?->price) }}" min="0" required>
                        @error('price') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Paise/cents — 49900 = ₹499.00</span>
                    </div>

                    <div class="field">
                        <label class="field__label" for="currency">Currency *</label>
                        <input class="input" type="text" id="currency" name="currency" value="{{ old('currency', $plan?->currency ?? 'INR') }}" maxlength="3" required>
                        @error('currency') <span class="field__error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid gap-6 sm:grid-cols-2">
                    <div class="field" data-validity-field>
                        <label class="field__label" for="validity_days">Validity (days)</label>
                        <input class="input" type="number" id="validity_days" name="validity_days" value="{{ old('validity_days', $plan?->validity_days ?? 365) }}" min="1">
                        @error('validity_days') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Access granted for one-time purchases.</span>
                    </div>

                    <div class="field" data-interval-field>
                        <label class="field__label" for="interval_days">Interval (days)</label>
                        <input class="input" type="number" id="interval_days" name="interval_days" value="{{ old('interval_days', $plan?->interval_days ?? 30) }}" min="1">
                        @error('interval_days') <span class="field__error">{{ $message }}</span> @enderror
                        <span class="mt-1 block text-xs text-muted">Renewal period for recurring subscriptions.</span>
                    </div>
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
            var validity = document.querySelector('[data-validity-field]');
            var interval = document.querySelector('[data-interval-field]');

            function sync() {
                var recurring = select.value === 'recurring';
                validity.style.display = recurring ? 'none' : '';
                interval.style.display = recurring ? '' : 'none';
            }

            select.addEventListener('change', sync);
            sync();
        })();
    </script>
@endpush
