# Quick Start

Get the Agape Infosys site running locally, take a checkout through the payment
simulator, and know where everything lives.

The app is a Laravel 13 + Tailwind v4 (dark theme) site with:

- a marketing site (Home, About, Services, Portfolio, Blog, Contact) managed from the **admin panel**;
- a **shop** (`/shop`) where visitors buy plans for the digital products (Tongdon, SMT, MotionMix, Ekklesia);
- a central **checkout** (`/{product}`) and **multi-gateway payments** (Cashfree first, plus PhonePe, PayU, PayPal, and a built-in sandbox);
- a signed **API + webhooks** layer the products themselves consume for entitlements.

Money is stored as **integer minor units** (paise): `29900` = ₹299.00.

---

## Requirements

- PHP **8.3+**
- **Composer 2**
- **Node 20+** (npm)
- **SQLite** (default — no database server needed; MySQL/Postgres work via `.env`)

---

## Install

```bash
composer run setup      # composer install, .env, app key, migrations, npm install, npm run build
php artisan db:seed     # admin user, demo content, products, plans, gateways
```

Starting over? `php artisan migrate:fresh --seed` (this resets the admin
password and logs out any open sessions).

> **Windows note:** if `php` is not on PATH in PowerShell, prefix commands with:
> ```powershell
> $env:PATH = "$env:LOCALAPPDATA\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe;$env:PATH"
> ```

---

## Run

```bash
composer run dev
```

`composer run dev` wraps `php artisan dev`, which starts everything together:

| Process      | Where                          |
| ------------ | ------------------------------ |
| App server   | http://127.0.0.1:8000          |
| Queue worker | runs `SendEntitlementWebhook` (outbound webhooks) |
| Logs (Pail)  | live `laravel.log` tail        |
| Vite         | asset dev server               |

Prefer plain commands? `php artisan serve` + `npm run dev`, and start a queue
worker separately with `php artisan queue:work`. Production builds need
`npm run build` after any Blade/CSS change.

---

## What's where

| URL                                   | What                                       |
| ------------------------------------- | ------------------------------------------ |
| `/`                                   | Marketing site                             |
| `/shop`                               | Product catalog (cart badge, browse)       |
| `/shop/{product}`                     | Plans + add to cart (e.g. `/shop/tongdon`) |
| `/shop/cart`, `/shop/checkout`        | Cart → customer details → payment          |
| `/{product}`                          | Direct single-plan checkout (e.g. `/tongdon`) |
| `/admin`                              | Admin panel login                          |
| `POST /api/webhooks/{driver}`         | Inbound payment webhooks (cashfree, sandbox, …) |
| `GET /api/v1/entitlement`             | Product entitlement query (signed)         |
| `GET /api/v1/orders/{reference}`      | Order status lookup (signed)               |
| `POST /api/v1/orders/lookup`          | Latest order by email (signed)             |

> Shop routes are registered **before** the `/{product}` catch-all, so static
> pages and `/shop` are never swallowed by a product slug.

---

## Admin panel

- **URL:** http://127.0.0.1:8000/admin
- **Login:** `admin@agapeinfosys.com` / `change-me-please`
- Override at seed time with `ADMIN_EMAIL`, `ADMIN_PASSWORD`, `ADMIN_NAME` in `.env`, then re-run `php artisan db:seed --class=UserSeeder`.

Sections: **Content** (Services, Case Studies, Articles, Testimonials) ·
**Inbox** (contact messages) · **Commerce** (Products, Plans, Orders,
Shop Orders, Subscriptions, Gateways).

---

## Seeded demo data

- **4 products** — `tongdon`, `smt`, `motionmix`, `ekklesia`, each with:
  - Monthly — ₹299.00 recurring (30-day interval)
  - Yearly — ₹2,999.00 one-time (365 days)
  - Lifetime — ₹9,999.00 one-time (3650 days)
- **5 gateways** — Cashfree, PhonePe, PayU, PayPal (all present, disabled, `test` mode) and **Sandbox (simulator), enabled and default**.
- Demo services, case studies, articles, testimonials, clients, site settings.

---

## Taking a test payment (no keys needed)

1. The **sandbox** gateway is the seeded default, so checkout works immediately.
2. Add a plan to the cart → `/shop/checkout` → enter a subscriber key/email → **Proceed to payment**.
3. On the pay page, use **Simulate successful payment** (or *failed*).
4. The order completes, subscriptions are granted, and an outbound webhook job
   is queued — check **Admin → Orders / Shop Orders / Subscriptions**.

## Going live with Cashfree

1. Cashfree Merchant Dashboard → **Developers → API Keys** → copy the **App ID** and **Secret**.
2. **Admin → Gateways → Cashfree** → paste credentials, pick `live` mode, enable it, and make it the **default** gateway.
3. Whitelist your domain (e.g. `agapeinfosys.com`) in the Cashfree dashboard.
4. Point Cashfree webhooks at `https://agapeinfosys.com/api/webhooks/cashfree`.
5. Disable the sandbox gateway (or just unset its default flag).

PhonePe / PayU / PayPal follow the same pattern (add their credentials in
**Gateways**) — each still needs provider-sandbox verification before being
trusted with live traffic.

**How completion is trusted:** webhooks are defence-in-depth only. Every
completion is confirmed server-to-server against the provider's status API
(`confirm()`), so a signed-but-forged webhook can never mark an order paid.

---

## Product API (for tongdon.com, smt.com, …)

Every request carries three headers:

| Header             | Value                                             |
| ------------------ | ------------------------------------------------- |
| `X-Agape-Key`      | product's `api_key` (Admin → Products)            |
| `X-Agape-Timestamp`| Unix seconds (± 300s tolerance)                   |
| `X-Agape-Signature`| `HMAC-SHA256(secret, "{timestamp}.{METHOD}.{path}")` hex — `path` has **no** leading slash |

```bash
TS=$(date +%s)
SIG=$(php -r "echo hash_hmac('sha256', '${TS}.GET.api/v1/entitlement', getenv('API_SECRET'));")

curl -sS "https://agapeinfosys.com/api/v1/entitlement?subscriber_key=USER-123" \
  -H "X-Agape-Key: $API_KEY" \
  -H "X-Agape-Timestamp: $TS" \
  -H "X-Agape-Signature: $SIG"
```

**Outbound webhooks** (payment confirmed → product notified) use the same
signing scheme and are POSTed to each product's `callback_url` with payload
`{event, product, order, subscription, timestamp}`. They run on the **queue**
with retries (30s/120s/600s backoff) — make sure a queue worker is running.

---

## Common tasks

- **Add a product:** Admin → Products (name, slug, image, API credentials auto-generated; use *Rotate* to invalidate old secrets).
- **Add/edit a plan:** Admin → Plans (billing type, price in minor units, validity/interval, active flag).
- **Change the default gateway:** Admin → Gateways → *Set as default*.
- **Reset demo data:** `php artisan migrate:fresh --seed`.

---

## Tests & code style

```bash
php artisan test --compact              # 118 tests (shop, checkout, gateways, webhooks, admin, pages)
vendor/bin/pint --dirty --format agent  # format changed PHP files (run before committing)
npm run build                           # after adding/changing Blade views
```

---

## Troubleshooting

| Symptom | Fix |
| --- | --- |
| `Unable to locate file in Vite manifest` | `npm run build` (or run `npm run dev`) |
| Styling missing on new pages | New Tailwind classes need a rebuild — `npm run build` |
| Admin session dead after seeding | Expected — `migrate:fresh --seed` regenerates `APP_KEY`-adjacent state; log in again |
| `php` not recognized | See the Windows PATH note above |
| Checkout says no gateway available | Enable a gateway in **Admin → Gateways**; sandbox is the seeded default |
| Webhook never reaches a product | Queue worker not running (`php artisan queue:work`) or product has no `callback_url` |
