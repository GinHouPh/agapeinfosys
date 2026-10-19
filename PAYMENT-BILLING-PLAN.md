# Payment & Billing — Implementation Plan

Concrete, prioritized plan to close the gaps between the shipped payment core
(see `ROADMAP.md` §5) and a launch-ready commerce experience. Written from the
code: every item names the files it touches and the tests that prove it.

- **Stack:** Laravel `^13.35`, PHP 8.3, Tailwind v4, PHPUnit 12.
- **Queue:** `QUEUE_CONNECTION=database` (workers already required for outbound
  entitlement webhooks).
- **Status snapshot:** payment core ✅ · billing/back-office ❌.

Legend: ✅ shipped · 🚧 in progress · ❌ not started.

---

## Priority summary

| # | Workstream | Why first | Effort | Sprint |
| --- | --- | --- | --- | --- |
| P0-1 | Transactional email foundation | No receipts, no password reset — the single biggest commerce gap | L | 1 |
| P0-2 | Prove the money path end-to-end | M4 goal; unverified gateways cannot take live money | M | 1 |
| P1-1 | Refund workflow | `OrderStatus::Refunded` exists but is unreachable | M | 2 |
| P1-2 | Dunning / expiry reminders | `renews_at` is written but nothing acts on it | S | 2 |
| P1-3 | Outbound webhook delivery log | Failures invisible outside queue logs | S | 2 |
| P2-1 | Invoice / receipt PDF | Compliance + repeat purchase | M | 3 |
| P2-2 | Coupons / discounts | Growth lever; not launch-critical | M | 3 |
| P2-3 | Customer self-service | Refund request, edit billing details | S | 3 |
| P2-4 | Admin revenue reporting + export | Ops visibility | M | 3 |
| P2-5 | Product API hardening | Rate limiting + test/live key pairs | S | 3 |

---

## Sprint 1 — Launch blockers (M4) 🚧

### P0-1 Transactional email foundation ✅

**Status:** shipped. `app/Mail/OrderReceipt.php`, `app/Mail/ShopOrderReceipt.php`,
`app/Notifications/NewOrderNotification.php`, `mail.*` views, password reset
controllers/routes/views, and wiring through both checkout completion paths.
Covered by `tests/Feature/OrderEmailTest.php` and
`tests/Feature/PasswordResetTest.php`.

**Goal:** buyers get a receipt, admins get an alert, users can reset a lost
password.

**Deliverables**
- Mailer config: real SMTP placeholders in `.env.example`, `MAIL_FROM_ADDRESS`
  set to the business address, `MAIL_FROM_NAME` from `APP_NAME`.
- `app/Mail/OrderReceipt.php` — queued `Mailable`, renders
  `resources/views/mail/order-receipt.blade.php` from `Order` + `Setting`
  (`business`) data.
- `app/Mail/ShopOrderReceipt.php` — queued `Mailable` for cart orders, lists
  the line items.
- `app/Notifications/NewOrderNotification.php` — queued mail notification to
  every admin user (`User::where('is_admin', true)`).
- Password reset: `Auth\PasswordResetLinkController` + `Auth\NewPasswordController`,
  routes (`password.request`, `password.email`, `password.reset`,
  `password.update`), views, and a "Forgot password?" link on the login page.

**Wire-up**
- `App\Services\Payments\CheckoutService::complete()` and
  `App\Services\Shop\CheckoutService::complete()` dispatch the customer receipt
  once and the admin alert once, via `afterCommit()`.
- Add a `bool $notifyCustomer = true` parameter to the single-order
  `complete()`; the shop fan-out passes `false` so a multi-line cart sends one
  receipt, not one per line.

**Acceptance tests** — `tests/Feature/OrderEmailTest.php`,
`tests/Feature/PasswordResetTest.php`
- Completing an order queues `OrderReceipt` to the order email.
- A cart completion queues exactly one `ShopOrderReceipt` (not per line).
- Admin users receive `NewOrderNotification`.
- Password reset sends the reset notification and resets the password.

### P0-2 Prove the money path end-to-end 🚧

**Status:** automated trust-model proof shipped in
`tests/Feature/GatewayTrustModelTest.php` (PhonePe, PayU, PayPal; Cashfree is
covered by `CashfreeDriverTest`). The manual sandbox pass below still needs
real provider keys.

**Goal:** one real sandbox purchase per gateway, plus automated proof that the
trust model holds.

**Deliverables**
- Automated: extend the `CashfreeDriverTest` pattern to assert, for every
  driver, that a signed-but-forged webhook cannot complete an order because
  `confirm()` is consulted (`tests/Feature/GatewayTrustModelTest.php`).
- Manual runbook (requires real provider keys; cannot be automated):
  1. Admin → Gateways: enter sandbox keys, keep `test` mode.
  2. Run a full purchase with `queue:work` running.
  3. Verify: order `paid`, `subscriptions` row, `payment_events` row, outbound
     webhook delivered.
  4. Flip to `live`, whitelist the domain, point the provider webhook at
     `https://<domain>/api/webhooks/<driver>`.

---

## Recurring billing - Cashfree Subscriptions (shipped)

Recurring plans now run through Cashfree's mandate API instead of a one-off
order, while one-time plans keep using the Orders API. The switch is driven by
`Plan.billing_type`:

- `Payable::paymentSubscriptionTerms(): ?SubscriptionTerms` - a payable returns
  mandate terms (plan id, cadence, recurring/max amount) or `null` for a
  one-time charge. `Order` derives them from its plan; `ShopOrder` returns null.
- `CashfreeDriver` branches: creates the provider plan (idempotent) +
  subscription and returns `subscription_session_id`; `confirm()` verifies the
  mandate status (`ACTIVE`) instead of a paid order.
- `checkout/pay.blade.php` opens `cashfree.subscriptionsCheckout()` when the
  payload carries a subscription session.
- `PaymentWebhookController` recognises subscription events (the subscription id
  is the original order reference); `CheckoutService::extendSubscriptionFromWebhook()`
  renews the entitlement on `SUBSCRIPTION_PAYMENT_SUCCESS`, de-duplicated by
  payment id.
- Tests: `tests/Feature/CashfreeSubscriptionTest.php`.

**Offers / no-cost EMI:** create the offer in the Cashfree dashboard; active
offers are surfaced at the hosted checkout automatically and the applied offer
is confirmed via `payment_offers[].offer_redemption.redemption_status` in the
webhook. Attaching a specific `offer_id` programmatically requires the Order Pay
(Elements/S2S) flow rather than hosted checkout - a follow-up if needed.

---

## Product integration model (centralised checkout)

agapeinfosys is the single payment + entitlement hub for every product
(Tongdon, SMT, MotionMix, Ekklesia) - like Google Play Billing: products never
talk to Cashfree.

- **Cashfree -> agapeinfosys (inbound):** one URL for all products,
  `POST https://<public-domain>/api/webhooks/cashfree`. HTTPS only; set
  `APP_URL` to the public domain and register it once under Cashfree →
  Developers → Webhooks (order + subscription events). The URL is shown on the
  admin gateway screen for copy-paste.
- **agapeinfosys -> product (outbound):** on order/subscription change,
  `Jobs\SendEntitlementWebhook` POSTs to the product's `callback_url` (set per
  product in admin) signed with that product's own key/secret:
  `X-Agape-Key`, `X-Agape-Timestamp`, and
  `X-Agape-Signature = HMAC-SHA256(timestamp + ".POST." + path, api_secret)`.
- **Product pull:** `GET /api/v1/entitlement?subscriber_key=...` (signed via
  `SignProductApiRequests` with the product key/secret) returns
  `{ entitled, subscription }` scoped to that product.

Entitlement is partitioned by `product_id` + `subscriber_key`, so one shared
checkout cleanly separates products.

---

## Sprint 2 — Money integrity (M5)

### P1-1 Refund workflow
- Add `refund(Payable, ?int $amount = null): void` to `PaymentGatewayDriver`;
  implement per driver (unsupported drivers throw so admins see "manual refund").
- Migration: `refunded_at`, `refund_amount`, `refund_reference` on `orders`.
- `Admin\RefundController` + `POST admin/orders/{order}/refund`.
- `Payments\CheckoutService::refund()`: transaction → `OrderStatus::Refunded`,
  refund `PaymentEvent`, revoke entitlement, dispatch entitlement webhook.
- Tests: `AdminRefundTest`.

### P1-2 Dunning / expiry reminders
- `app/Console/Commands/SendRenewalReminders.php`, scheduled daily.
- Migration: `last_reminded_at` on `subscriptions` for idempotency.
- Tests: window matching and no double-sends.

### P1-3 Outbound webhook delivery log
- Migration `webhook_deliveries`; persist each attempt in
  `Jobs\SendEntitlementWebhook` (success + `failed()` hook).
- Admin list on `admin.orders.show`.

---

## Sprint 3 — Experience & operations (M5/M6)

- **P2-1 Invoice PDF** — `barryvdh/laravel-dompdf` (needs dependency approval),
  `OrderInvoiceController`, client + admin download.
- **P2-2 Coupons** — `coupons` + `coupon_redemptions`; apply in
  `Payments\CheckoutService::createPendingOrder()` and
  `Shop\CheckoutService::startCheckout()`.
- **P2-3 Customer self-service** — refund request + editable billing details in
  the client dashboard.
- **P2-4 Admin reporting** — revenue by gateway/plan/period + CSV export.
- **P2-5 Product API hardening** — `throttle` on `/api/v1/*`, per-product
  test/live key pairs.

---

*Sprint 1 code complete: P0-1 shipped, P0-2 automated tests shipped. Remaining:
the manual per-gateway sandbox pass with real keys (P0-2 runbook). Keep this
file current as work lands.*
