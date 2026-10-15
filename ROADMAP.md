# Roadmap — Agape Infosys

Where the project is, what is finished, what is left, and in what order it should
land. Written from the code and git history, not from a wish list.

- **Stack:** Laravel `^13.17` on PHP 8.3, Tailwind CSS v4, Vite 8, PHPUnit 12.
- **Data:** SQLite by default (`DB_CONNECTION=sqlite`); MySQL/Postgres via `.env`.
- **Snapshot:** 27 commits, `2026-10-12 → 2026-10-14`; HEAD `a021b3c`.
- **Size:** 23 models, 49 controllers, 139 Blade views, 27 migrations,
  28 page-builder widgets, 310 test methods across 30 feature files.

This document is the source of truth for "what's next". Code is the source of
truth for "what exists".

---

## 1. Where we are

The app is a single Laravel codebase that does four jobs:

1. **Marketing site + CMS** — a public site whose every page is authored in an
   admin CMS and a visual page builder.
2. **Shop + payments** — visitors buy plans for four digital products; five
   gateway drivers, with a built-in sandbox for keyless testing.
3. **Product API** — the purchased products (Tongdon, SMT, MotionMix, Ekklesia)
   query entitlements and receive signed webhooks.
4. **Client accounts** — a customer dashboard over orders, subscriptions and
   access.

### Status by area

| Area | Status | Notes |
| --- | --- | --- |
| Marketing site (Home, About, Services, Portfolio, Projects, Blog, Contact, Legal, Search) | ✅ Shipped | All pages are builder-managed |
| Admin CMS (content, inbox, commerce, users, settings) | ✅ Shipped | `routes/web.php` admin group |
| Dynamic form builder + submissions inbox | ✅ Shipped | Replaced the old static requirement form |
| Visual page builder (drag-drop, nesting, undo/redo, clipboard, device preview, design tokens) | ✅ Shipped | 28 widgets; flagship subsystem |
| Products & plans | ✅ Shipped | Plans priced in minor units (paise) |
| Shop + cart + cart checkout | ✅ Shipped | `App\Services\Shop` |
| Direct product checkout (`/{product}`) | ✅ Shipped | `App\Services\Payments\CheckoutService` |
| Sandbox gateway (keyless simulator) | ✅ Shipped | Seeded **enabled + default** |
| Cashfree driver (orders, webhooks, status confirm) | ✅ Shipped | Needs one live/sandbox end-to-end pass |
| PhonePe / PayU / PayPal drivers | 🟡 Code complete, unverified | Seeded **disabled**; need provider-sandbox verification before live |
| Inbound webhooks + server-to-server `confirm()` | ✅ Shipped | `PaymentWebhookController` |
| Outbound entitlement webhooks (queued, retried) | ✅ Shipped | `SendEntitlementWebhook` |
| Product API v1 (HMAC-signed entitlement + order lookup) | ✅ Shipped | `SignProductApiRequests` |
| Client accounts + dashboard (orders, subs, access, profile) | ✅ Shipped | Orders link to checkout email |
| Transactional email (order receipt, password reset, invoices) | ❌ Not started | No `Mailable`/`Notification` classes; `MAIL_MAILER=log` |
| Refund workflow (admin-initiated) | ❌ Not started | Cancellation of subscriptions exists; refunds do not |
| CI (tests + Pint on push) | ❌ Not started | No `.github` |
| Production deploy + backups + monitoring | ❌ Not started | See `DEPLOY.md` (shared-host guide) |
| SEO (sitemap, structured data) | 🟡 Partial | `robots.txt` present; no sitemap |

Legend: ✅ shipped · 🟡 partial / needs verification · ❌ not started.

---

## 2. Milestones

### M1 — Foundation ✅ (2026-10-12)
Marketing site, admin CMS, product checkout, multi-gateway payments skeleton,
shop with cart, Cashfree rebuild, Quick Start docs.

### M2 — Content tooling ✅ (2026-10-13)
Dynamic form builder; visual page builder with drag-and-drop canvas; every page
migrated into the builder; nested 1–4 column sections; undo/redo; shortcut
layer.

### M3 — Builder & commerce maturity ✅ (2026-10-14)
Builder clipboard, right-click menu, structure drawer, device preview, colour
controls bound to design tokens, unsafe-link filtering. Site chrome: three-way
theme (dark/light/screen), top bar, bottom icon dock.

### M4 — Launch readiness 🚧 *(current)*
Get a real deployment online and prove the money path end to end. See §4.
Deliverables:
- Deploy to shared hosting with Git-based deploy (`DEPLOY.md`).
- Switch `APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL`, HTTPS.
- Run the Cashfree **sandbox** checkout end to end (create → pay → webhook →
  entitlement → outbound webhook) with a queue worker.
- Configure queue worker + scheduler via cron.
- Set up database + uploaded-media backups.

### M5 — Trust & polish
- Transactional email: order confirmation/receipt, password reset, admin new
  order/submission alerts (currently nothing is emailed).
- Refund workflow (admin action + gateway refund call + entitlement revoke).
- Verify and enable the remaining gateways (PhonePe, PayU, PayPal) or remove
  the unused ones to shrink the attack surface.
- Verify PhonePe/PayU/PayPal drivers against each provider's sandbox.

### M6 — Hardening
- CI: run `php artisan test` + `vendor/bin/pint --test` on push.
- Monitoring/error reporting (e.g. Sentry) and log rotation.
- Sitemap + Open Graph metadata; analytics.
- Security pass: review throttles, headers, upload validation, secrets.

> Follow-up milestones are intentionally short. Promote items from §3 into M5/M6
> as priorities firm up.

---

## 3. Backlog (unscheduled)

**Housekeeping / correctness**
- Rebrand defaults: `.env.example` `APP_NAME=Laravel` → `Agape Infosys`;
  `config/app.php` default still `Laravel`.
- Delete the unused stock `resources/views/welcome.blade.php` (dead 70 KB file,
  no route references it).
- Refresh docs: `README.md` is still the stock Laravel readme; `QUICKSTART.md`
  says "118 tests" but the suite now has 310 test methods.
- Add `.ai/rules` (the AGENTS.md references it but the directory does not exist).

**Features**
- Invoice/receipt generation (PDF) tied to an order.
- Customer self-service: request refund, edit billing details.
- Admin reporting: revenue by gateway/plan/period; export orders.
- Coupons / discount codes.
- Multiple currencies per plan (models already store `currency`).
- Dunning/expiry reminders when a subscription approaches `ends_at`.
- Webhook delivery log UI (inbound events are stored; outbound attempts are not).

**Product API**
- Sandbox/live key pairs per product (currently one `api_key`/`api_secret`).
- Rate limiting on `/api/v1/*` (only the web/forms routes are throttled today).
- Public OpenAPI description for the product teams.

---

## 4. Launch checklist

Run through this before calling the site "live for testing":

- [ ] Hosting account meets requirements (PHP 8.3, needed extensions, MySQL,
      Git, cron, HTTPS).
- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` generated.
- [ ] `APP_URL` set to the real HTTPS URL (it drives uploaded-media URLs).
- [ ] MySQL database created; credentials in `.env`; `php artisan migrate --force`.
- [ ] `composer install --no-dev --optimize-autoloader`.
- [ ] `npm run build` locally and commit `public/build` (Vite 8, Tailwind v4).
- [ ] `php artisan storage:link` (uploaded media under `storage/app/public`).
- [ ] Config/route/view caches built.
- [ ] Queue worker running (`queue:work`) — required for outbound webhooks.
- [ ] Scheduler cron entry (even if no tasks yet, so M5 additions "just work").
- [ ] Admin seeded/reset; sandbox gateway still default for testing.
- [ ] Cashfree sandbox keys entered if testing real redirects.
- [ ] End-to-end test payment → order paid → entitlement → outbound webhook.
- [ ] Backups for the database and `storage/app/public`.

---

## 5. Deep dive — the payments & commerce subsystem

The part of the app that touches money, and therefore the part that needs the
most care before going live.

### 5.1 Layout

```
app/Services/Payments/
  Contracts/PaymentGatewayDriver.php  — the driver contract
  Contracts/Payable.php               — what a driver can charge (Order or ShopOrder)
  DTOs/CheckoutSession.php            — redirect | inline | redirect-form
  Drivers/{Cashfree,PhonePe,PayU,PayPal,Sandbox}Driver.php
  GatewayManager.php                  — maps DB rows → drivers; picks the default
  CheckoutService.php                 — single-plan checkout + completion
  CustomerKey.php                     — derives a stable subscriber key
app/Services/Shop/CheckoutService.php — cart checkout (mirrors the above)
app/Http/Controllers/PaymentWebhookController.php
app/Jobs/SendEntitlementWebhook.php
app/Models/{Order,ShopOrder,Subscription,PaymentEvent,PaymentGateway,Product,Plan}
```

### 5.2 Two checkout flows, one completion path

There are two front doors:

- **Direct:** `GET/POST /{product}` → `CheckoutController` → payment
  `CheckoutService`.
- **Cart:** `/shop/checkout` → `App\Services\Shop\CheckoutService`.

Both converge on the same ideas: create a **pending** order, hand the customer to
a gateway, then trust only the gateway's **status API** to mark it paid.

### 5.3 The trust model (this is the good part)

`PaymentWebhookController` is deliberately paranoid:

1. Resolve the gateway row for the `{driver}` segment (404 if unknown).
2. Verify the webhook signature (defence-in-depth only).
3. Extract the order reference from the provider-specific payload.
4. **Ignore the payload's claim of success.** Call `$driver->confirm($order)`,
   which re-asks the provider (Cashfree order status, PhonePe order status, PayU
   txnStatus, PayPal GET order, sandbox always `true`). If not `true`, return
   `unconfirmed` and do nothing.
5. Only then call `CheckoutService::complete()`.

`complete()` runs in a transaction and is **idempotent**: `Order::claimForCompletion()`
performs the `pending → paid` transition atomically, so concurrent webhooks and
the browser return URL can race and exactly one wins. It writes a `PaymentEvent`,
grants the entitlement, and dispatches the outbound webhook with `afterCommit()`.

**Consequence:** a signed-but-forged webhook cannot mark an order paid. The
provider's status API is the trust root, and idempotency protects against
double-granting.

### 5.4 Entitlements

`CheckoutService::grantEntitlement()` upserts a `Subscription` keyed on
`(product_id, subscriber_key)` — so buying again **extends** the same
entitlement rather than creating duplicates. Validity comes from
`plan->entitlementDays()`; recurring plans also set `renews_at`.

### 5.5 Outbound webhooks

`SendEntitlementWebhook` POSTs a signed, JSON payload to the product's
`callback_url` with retries (`backoff = [30, 120, 600]`). It uses the **same**
HMAC scheme as the inbound product API:

```
X-Agape-Signature = HMAC-SHA256( secret, "{timestamp}.POST.{path}" )
```

so product teams implement signing/verifying once.

### 5.6 Driver readiness (honest assessment)

| Driver | Config | Checkout | Webhook sig | Confirm | Live-ready? |
| --- | --- | --- | --- | --- | --- |
| `sandbox` | none | inline simulator | HMAC (app key) | always true | ✅ Yes (testing only) |
| `cashfree` | `client_id`, `client_secret` | JS SDK session | `base64(HMAC)` | order status | 🟡 Needs one live pass |
| `phonepe` | `merchant_id`, `salt`, `salt_index` | hosted redirect | SHA-256 checksum | order status | 🟡 Unverified |
| `payu` | `merchant_key`, `salt` | signed form POST | SHA-512 hash | txnStatus | 🟡 Unverified |
| `paypal` | `client_id`, `client_secret`, `webhook_id` | orders v2 redirect | verify-webhook-signature | GET order | 🟡 Unverified |

All five implement the full contract, so none of them can accidentally mark an
order paid without a status-API confirmation. The risk is in the parts a status
API cannot prove for you: request shapes, signature strings, and payload
parsing. Each provider must be exercised once end to end against its sandbox
before it is trusted with live traffic.

### 5.7 Known sharp edges

- **No email anywhere.** The mailer is `log`; there are no `Mailable` or
  `Notification` classes. Buyers get no receipt and cannot reset a password by
  email. This is the single biggest product gap for a commerce site.
- **No rate limiting on the product API.** `/api/v1/*` is unthrottled; only the
  web forms and login routes use `throttle`.
- **Outbound webhook attempts are not persisted.** Inbound events are stored in
  `payment_events`; outbound delivery failures only surface in the queue.
- **One API key per product.** No separation between test and live credentials.
- **`.env.example` is un-rebranded**, so a fresh deploy boots with
  `APP_NAME=Laravel`.

### 5.8 What to do before enabling a real gateway

1. Enter sandbox credentials in **Admin → Gateways**, keep `test` mode.
2. Do a full sandbox purchase with a queue worker running.
3. Confirm: order `paid`, `Subscription` created/extended, `payment_events`
   row written, outbound webhook delivered (or product reports receipt).
4. Flip the gateway to `live`, make it default, disable `sandbox`.
5. Whitelist the production domain with the provider and point its webhook at
   `https://<domain>/api/webhooks/<driver>`.
6. Watch the queue and `payment_events` for the first real transactions.

---

## 6. How to work in this repo

- Tests: `php artisan test --compact` (or `vendor/bin/phpunit <file>`).
- Format: `vendor/bin/pint --dirty --format agent`.
- Build assets: `npm run build` (required after Blade/CSS changes).
- Run everything locally: `composer run dev`.
- Conventions and agent guidance live in `AGENTS.md`.

---

*Last updated: 2026-10-15. Keep §1 and §3 current as work lands.*
