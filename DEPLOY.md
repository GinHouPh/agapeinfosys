# Deploying to Serverbyt (StackCP) shared hosting

A step-by-step guide to putting this Laravel app online for **live testing** on
Serverbyt shared hosting using its Git Version Control feature.

Serverbyt runs the **StackCP / 20i** platform, *not* standard cPanel. The control
panel is at **cp.serverbyt.in**, and the Git screens live under
**Manage Hosting → (package) → Web Files → Git™ Version Control**. Commands that
look "cPanel-ish" online mostly will not match; the steps below are for StackCP.

> Goal here is a **staging / live-test** environment, not a hardened production
> launch. Keep the sandbox gateway default and `MAIL_MAILER=log` unless you are
> ready to take real money.

---

## 0. The one thing that matters most

Laravel's web root is its `public/` folder — everything else (`app/`, `vendor/`,
`.env`, `storage/`) must sit **outside** the web-accessible folder. StackCP lets
you **change the domain's document root**, so the clean setup is:

```
/home/<user>/app/            <- the Git repo (private, above the web root)
/home/<user>/app/public/     <- the domain's document root
```

Do **not** clone into `public_html` and do **not** copy `public/`'s contents into
`public_html` with hand-edited `index.php` paths unless you are forced to. Point
the document root instead (§4).

---

## 1. Check the server can run this app

This app requires **PHP 8.3** and the `ctype`, `filter`, `hash`, `mbstring`,
`openssl`, `session`, `tokenizer` extensions (plus `pdo_mysql`).

1. In `cp.serverbyt.in` → **Manage Hosting → (package) → Switch PHP Version**,
   select **PHP 8.3** for the website.
2. On the server (via SSH, §6) confirm the CLI:
   ```bash
   php83 -v
   php83 -m | grep -Ei 'ctype|mbstring|openssl|pdo_mysql|tokenizer'
   ```
   Serverbyt's docs list `php83` as the CLI for PHP 8.3; the default `php` may be
   an old minimal build, so **always call `php83` explicitly** (or add an alias).

If PHP 8.3 is not offered on your package, stop and contact Serverbyt support —
this app will not run on 8.2 or older.

---

## 2. Create the database

1. `cp.serverbyt.in` → **Manage Hosting → (package) → Web Tools → MySQL Databases**.
2. Create a database (6–20 chars; a suffix is added automatically) and a user.
3. Note the **database name, username, password, and host** (usually `localhost`.

Pitfall: each MySQL database on this platform is capped at **1 GB**. Fine for
testing; plan for a real DB if you expect volume.

---

## 3. Build assets locally and commit them

Shared hosting has **no Node/npm build step**, and `cPanel`/StackCP Git deploys
only move files — they do not run `npm run build`. So build on your machine and
commit the output.

Locally, in the project root:

```bash
npm ci
npm run build
```

`public/build` is listed in `.gitignore`, so it will not be committed by default.
Either remove the `/public/build` line from `.gitignore`, or force-add it:

```bash
git add -f public/build
git commit -m "build: production assets"
git push origin main
```

You must repeat this every time a Blade/CSS/JS change lands.

---

## 4. Clone the repo with Git Version Control

1. `cp.serverbyt.in` → **Manage Hosting → (package) → Options → Manage**.
2. Under **Web Files**, open **Git™ Version Control**.
3. On first use it asks for a **name and email** (used for commits) → **Enable**.
4. Choose **Create New Repository** → the **Clone a Repository** tab (or
   **Sign in with GitHub** via SSO if offered).
5. **Clone URL:** your GitHub URL (HTTPS for a public repo; the platform's
   GitHub sign-in for a private one — an SSH deploy key is not needed if you use
   the GitHub app).
6. **Repository Path:** a folder **outside** the web root, e.g. `app`
   (resolves to `/home/<user>/app`).
7. Create it. The code is pulled onto the server (it does **not** go live by
   itself — the document root is still `public_html`).

> Note: StackCP's Git tool is a *pull/deploy* workflow. A cPanel-style
> `.cpanel.yml` is **not** used here. You will run Laravel's post-deploy commands
> over SSH (§6–§7). For updates: **Pull or Deploy → Update from Remote → Deploy
> HEAD Commit**, then run the deploy script (§8).

---

## 5. Point the domain at `public/`

1. `cp.serverbyt.in` → **Manage Hosting → (package) → Options → Manage**.
2. **Manage Domains → Domains** (primary domain).
3. Change **Document Root** from `public_html` to `app/public`.
4. Save. (StackCP allows any directory inside the package — this is the
   supported way to run Laravel here.)

Optional but tidy: add this to `app/public/.htaccess` if it is not already
handled by Laravel's bundled `public/.htaccess` (it is — you normally need
nothing).

---

## 6. Enable SSH and create `.env`

Serverbyt supports SSH under **Manage Hosting → (package) → Security → SSH
Access**. Add your public key (recommended) or set an SSH password, and note the
host for your region:

| Region | SSH host |
| --- | --- |
| UK | `ssh.gb.stackcp.com` |
| US | `ssh.us.stackcp.com` |
| Singapore | `ssh.sg.stackcp.com` |

Log in with the **domain name** as the username, then:

```bash
cd ~/app
cp .env.example .env
nano .env
```

Set at least:

```dotenv
APP_NAME="Agape Infosys"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_DATABASE=xxxxx
DB_USERNAME=xxxxx
DB_PASSWORD=xxxxx

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=log
```

Then generate the key:

```bash
php83 artisan key:generate
```

`.env` is git-ignored, so it stays server-only. Never commit it.

---

## 7. Install dependencies and migrate

Composer on Serverbyt must be run through the chosen PHP:

```bash
cd ~/app
php83 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
php83 artisan migrate --force
php83 artisan storage:link
php83 artisan config:cache
php83 artisan route:cache
php83 artisan view:cache
```

If `composer install` runs out of memory, retry with
`php -d memory_limit=512M` (confirm the limit with `php83 -i | grep memory_limit`).

Seed the demo/admin data once (skip on later deploys):

```bash
php83 artisan db:seed --force
```

---

## 8. The redeploy loop

After the first deploy, publishing a change is:

1. Locally: commit → `npm run build` (if UI changed) → force-add `public/build` →
   push.
2. StackCP: **Git Version Control → Manage → Pull or Deploy → Update from
   Remote → Deploy HEAD Commit**.
3. SSH: run the deploy script below.

Save this as `~/deploy.sh` on the server (outside `public/`):

```bash
#!/bin/bash
set -e
cd ~/app
php83 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
php83 artisan migrate --force
php83 artisan config:cache
php83 artisan route:cache
php83 artisan view:cache
php83 artisan queue:restart
```

```bash
chmod +x ~/deploy.sh
./deploy.sh
```

If StackCP's Git tool offers an **automatic deployment** option (Local Repository
→ Deployment Type = automatic), you can still use it for the file copy — but you
must run `~/deploy.sh` for Composer/migrations/caches. The Git tool does not run
them.

---

## 9. Queue worker and scheduler

Outbound entitlement webhooks are queued (`SendEntitlementWebhook`). Shared
hosting cannot run a persistent daemon, so drive the queue with cron. Under
**Manage Hosting → (package) → Web Tools → Scheduled Tasks (Cron)** add:

```
* * * * * /usr/bin/php83 /home/<user>/app/artisan schedule:run >> /dev/null 2>&1
* * * * * /usr/bin/php83 /home/<user>/app/artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1
```

For pure live *testing* you can instead set `QUEUE_CONNECTION=sync` in `.env` so
webhooks fire inline — simpler, but a slow product endpoint will then delay the
checkout response. Prefer the cron worker.

After any code change, `php83 artisan queue:restart` (the deploy script does it)
so workers pick up new code.

---

## 10. HTTPS

Use StackCP's **FREE SSL Certificate** under **Security → SSL**. Once issued,
make sure `APP_URL` uses `https://` — uploaded-media URLs are built from it
(`config/filesystems.php`). Optionally force HTTPS via `.htaccess`.

---

## 11. Verify the deploy

- [ ] `https://yourdomain.com` loads the marketing site (not a 403/404).
- [ ] `/admin` login works (seed data → `ADMIN_EMAIL` / `ADMIN_PASSWORD`).
- [ ] An uploaded image displays (proves the `storage:link` and `APP_URL`).
- [ ] `/shop` → cart → `/shop/checkout` → sandbox **Simulate successful payment**.
- [ ] Admin → Orders shows the order **paid**; Subscriptions shows the entitlement.
- [ ] Run a product-API call with a valid signature (`QUICKSTART.md` has the curl).
- [ ] Change one Blade string, redeploy, and confirm it appears (Git loop works).

---

## 12. Going live with Cashfree (when testing is done)

1. Admin → **Gateways → Cashfree**: enter sandbox **App ID** + **Secret**, keep
   `test` mode, enable it, set as default; disable `sandbox`.
2. Re-run the sandbox purchase and confirm the redirect + webhook round-trip.
3. When satisfied, switch Cashfree to `live`, re-enter live keys.
4. Whitelist `yourdomain.com` in the Cashfree dashboard and point its webhook at
   `https://yourdomain.com/api/webhooks/cashfree`.
5. Watch `payment_events` and the queue for the first real transactions.

---

## Troubleshooting

| Symptom | Cause / fix |
| --- | --- |
| 500 with no detail | `APP_DEBUG=true` temporarily, or read `storage/logs/laravel.log` over SSH |
| `Unable to locate file in Vite manifest` | `public/build` not committed/pulled — rebuild locally and force-add it |
| Blank/403 at the domain | Document root still `public_html`; set it to `app/public` (§5) |
| `php: command not found` / wrong version | Use `php83` explicitly; default `php` is a minimal build |
| Composer fails on version | Run it through `php83`, not the system `php` |
| MySQL "max size" errors | Databases are capped at 1 GB on this platform |
| Uploaded images 404 | Run `php83 artisan storage:link`; check `APP_URL` scheme |
| Webhooks never delivered | Cron queue worker not set up, or product has no `callback_url` |
| Blank page after deploy | Run `~/deploy.sh` (caches may point at old files) |

---

## Why not the `.cpanel.yml` method you read about?

Those guides are for **cPanel**. Serverbyt is **StackCP/20i**; its Git Version
Control is a pull-based clone/deploy with no `.cpanel.yml` hook. The StackCP-
supported way to serve Laravel is to **change the document root** (which 20i/
StackCP explicitly allows), which is cleaner than copying `public/` into
`public_html` and rewriting `index.php`.
