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

## Deployment log — `tongdon.com`

Concrete values for this deployment (keep them handy). Update as things change.

| Item | Value |
| --- | --- |
| Platform | Serverbyt → StackCP, panel `cp.serverbyt.in` |
| Account home | `/home/sites/30b/6/6912a84ed3` |
| Git repo path | `/home/sites/30b/6/6912a84ed3/agapeinfosys` (panel value: `agapeinfosys`) |
| SSH login | `tongdon.com@ssh.gb.stackcp.com` (key-based) |
| Local private key | `C:\Users\Jace\AppData\Local\Temp\opencode\agapeinfosys_key` |
| PHP CLI | `php83` → `/usr/bin/php83` (PHP 8.3.35 NTS) |
| Composer | `/usr/bin/composer` (run as `php83 /usr/bin/composer …`) |
| Node/npm | **not available** — build assets locally |
| MySQL host | `sdb-g.hosting.stackcp.net` — **not** `localhost` |
| MySQL user | `giginhaokip` (shared by the account's databases) |
| Existing DB | `tongdon-313835ec32` — belongs to the **other** app in `public_html`; do not reuse |
| Document root | `agapeinfosys/public` (relative to account root) |
| Git deploy | branch `main`, deploy script `deploy.sh` |

### Status

Done on the server:

- [x] SSH key added in **Security → SSH Access**; SSH login works.
- [x] Repo cloned via **Git™ Version Control** to `~/agapeinfosys`.
- [x] `php83 /usr/bin/composer install --no-dev --optimize-autoloader` succeeded.
- [x] `.env` created; `php83 artisan key:generate` ran.
- [x] `php83 artisan storage:link` succeeded (`public/storage` → `storage/app/public`).
- [x] `public/build` present (assets built locally and committed).
- [x] `config:cache`, `route:cache`, `view:cache` succeeded.
- [x] `deploy.sh` committed and pushed.

Still to do:

- [ ] **Create a new MySQL database** in the panel and attach user `giginhaokip`, then put its **exact suffixed name** in `.env` (the `agapeinfosys` DB was never actually created).
- [ ] Set `DB_HOST=sdb-g.hosting.stackcp.net` in `.env` (`localhost` does not work here).
- [ ] `php83 artisan migrate --force` (failed: DB not configured yet).
- [ ] `php83 artisan db:seed --force` (seeds the admin + demo content).
- [ ] Re-run `php83 artisan config:cache` **after** fixing `.env` (the earlier cache holds the wrong DB config).
- [ ] Choose the domain to serve the app — `tongdon.com` currently points at the **other** app in `public_html`, so use a subdomain (e.g. `agape.tongdon.com`) or an addon domain, and set its **Document Root** to `agapeinfosys/public`.
- [ ] Set Git **Deployment branch** = `main` and **Deployment script** = `deploy.sh`.
- [ ] Add the queue/scheduler **cron** entries.
- [ ] Point DNS for the chosen domain at the package.
- [ ] Issue the **FREE SSL** certificate and confirm `APP_URL` is `https://…`.

---

## 0. The one thing that matters most

Laravel's web root is its `public/` folder — everything else (`app/`, `vendor/`,
`.env`, `storage/`) must sit **outside** the web-accessible folder. StackCP lets
you **change the domain's document root**, so the clean setup is:

```
/home/sites/30b/6/6912a84ed3/agapeinfosys/          <- the Git repo (private)
/home/sites/30b/6/6912a84ed3/agapeinfosys/public/   <- the domain's document root
```

Do **not** clone into `public_html` and do **not** copy `public/`'s contents into
`public_html` with hand-edited `index.php` paths. Point the document root instead
(§5).

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
   The SSH MOTD lists the available binaries. On this account `php83` is a
   wrapper at `/usr/bin/php83` (real binary `/usr/php83/usr/bin/php`). The default
   `php` may be a minimal build, so **always call `php83` explicitly**.

If PHP 8.3 is not offered on your package, stop and contact Serverbyt support —
this app will not run on 8.2 or older.

---

## 2. Create the database

1. `cp.serverbyt.in` → **Manage Hosting → (package) → Web Tools → MySQL Databases**.
2. Create a database (6–20 chars) and a user; attach the user to the database with
   full privileges.
3. Copy the **exact** values from the panel — the database name gets a suffix and
   the **host is a remote server**, not `localhost`.

### The MySQL host is remote (important)

Testing from the SSH node showed that `localhost` / `127.0.0.1` do **not** work
(socket missing / connection refused). The database is reachable over TCP on a
per-account host:

```
sdb-g.hosting.stackcp.net        # this account
```

Database names are **suffixed** by the platform (the existing one is
`tongdon-313835ec32`). After you create a database in the panel, copy its **full
suffixed name** — that is what goes in `DB_DATABASE`. The `giginhaokip` user is
shared across the account's databases; attach it to the new database with full
privileges.

Pitfall: each MySQL database on this platform is capped at **1 GB**. Fine for
testing; plan for a real DB if you expect volume.

---

## 3. Build assets locally and commit them

Shared hosting has **no Node/npm**, and the StackCP Git tool only moves files — it
does not run `npm run build`. So build on your machine and commit the output.

Locally, in the project root:

```bash
npm ci
npm run build
```

`public/build` is listed in `.gitignore`, so it will not be committed by default.
Force-add it:

```bash
git add -f public/build
git commit -m "build: production assets"
git push origin main
```

Repeat this every time a Blade/CSS/JS change lands.

---

## 4. Clone the repo with Git Version Control

1. `cp.serverbyt.in` → **Manage Hosting → (package) → Options → Manage**.
2. Under **Web Files**, open **Git™ Version Control**.
3. On first use it asks for a **name and email** (used for commits) → **Enable**.
4. Choose **Create New Repository** → the **Clone a Repository** tab (or
   **Sign in with GitHub** via SSO if offered).
5. **Clone URL:** your GitHub URL.
6. **Repository Path:** a folder **outside** the web root. On this account it is
   `agapeinfosys` (resolves to `/home/sites/30b/6/6912a84ed3/agapeinfosys`).
7. Create it. The code is pulled onto the server (it does **not** go live by
   itself — the document root is still `public_html`).

> Note: StackCP's Git tool is a *pull/deploy* workflow. A cPanel-style
> `.cpanel.yml` is **not** used here. Run Laravel's post-deploy steps via the
> **Deployment script** field (§8).

---

## 5. Point the domain at `public/`

> The account already hosts another app in `public_html` (served by
> `tongdon.com`). **Do not repoint the primary domain** or you will take that
> site down. Use a **subdomain** (e.g. `agape.tongdon.com`) or an addon domain
> for this Laravel app.

1. `cp.serverbyt.in` → **Manage Hosting → (package) → Options → Manage**.
2. **Manage Domains → Domains** (the (sub)domain for this app).
3. Change **Document Root** from `public_html` to `agapeinfosys/public`.
4. Save. (StackCP allows any directory inside the package — this is the
   supported way to run Laravel here.)

Laravel's bundled `public/.htaccess` handles rewrites — you normally need nothing
extra.

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

Log in with the **domain name** as the username (e.g. `tongdon.com`), then:

```bash
cd ~/agapeinfosys
cp .env.example .env
nano .env
```

Set at least:

```dotenv
APP_NAME="Agape Infosys"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tongdon.com

DB_CONNECTION=mysql
DB_HOST=sdb-g.hosting.stackcp.net     # this account's DB host, NOT localhost
DB_PORT=3306
DB_DATABASE=<exact suffixed name>     # e.g. agapeinfosys-xxxxxxxx
DB_USERNAME=giginhaokip
DB_PASSWORD=<password>

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

> **If you already ran `config:cache` before fixing the DB values**, you must
> re-run `php83 artisan config:cache` after editing `.env` (see §7).

---

## 7. Install dependencies and migrate

Composer on Serverbyt must be run through the chosen PHP:

```bash
cd ~/agapeinfosys
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

`deploy.sh` is committed at the repo root and uses the correct paths for this
account:

```bash
#!/bin/bash
set -e
cd "$(dirname "$0")" || exit 1
php83 /usr/bin/composer install --no-dev --optimize-autoloader --no-interaction
php83 artisan migrate --force
php83 artisan config:cache
php83 artisan route:cache
php83 artisan view:cache
php83 artisan queue:restart
```

In StackCP → **Git™ Version Control → (repo)**:

- **Deployment branch** → `main` → **Update**
- **Deployment script** → `deploy.sh` → **Update**

After the first deploy, publishing a change is:

1. Locally: commit → `npm run build` (if UI changed) → `git add -f public/build` →
   push.
2. StackCP: **Pull or Deploy → Update from Remote → Deploy HEAD Commit**. The
   deploy runs `deploy.sh` automatically.
3. Done — verify the site.

If the automatic script does not run, SSH in and run it manually:

```bash
cd ~/agapeinfosys && bash deploy.sh
```

---

## 9. Queue worker and scheduler

Outbound entitlement webhooks are queued (`SendEntitlementWebhook`). Shared
hosting cannot run a persistent daemon, so drive the queue with cron. Under
**Manage Hosting → (package) → Web Tools → Scheduled Tasks (Cron)** add:

```
* * * * * /usr/bin/php83 /home/sites/30b/6/6912a84ed3/agapeinfosys/artisan schedule:run >> /dev/null 2>&1
* * * * * /usr/bin/php83 /home/sites/30b/6/6912a84ed3/agapeinfosys/artisan queue:work --stop-when-empty --max-time=55 --tries=3 >> /dev/null 2>&1
```

For pure live *testing* you can instead set `QUEUE_CONNECTION=sync` in `.env` so
webhooks fire inline — simpler, but a slow product endpoint will delay the
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

- [ ] `https://tongdon.com` loads the marketing site (not a 403/404).
- [ ] `/admin` login works (seed data → `ADMIN_EMAIL` / `ADMIN_PASSWORD`).
- [ ] An uploaded image displays (proves `storage:link` + `APP_URL`).
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
4. Whitelist the app's domain in the Cashfree dashboard and point its webhook at
   `https://<app-domain>/api/webhooks/cashfree`.
5. Watch `payment_events` and the queue for the first real transactions.

---

## Troubleshooting

| Symptom | Cause / fix |
| --- | --- |
| `vendor/autoload.php` missing | Deploy pulled code but deps aren't installed — run `php83 /usr/bin/composer install --no-dev` |
| `Connection refused` (Host `127.0.0.1`, DB `laravel`) | `.env` DB lines are still commented — set them, DB host is the remote `sdb-N` host |
| `Access denied … to database` (1044) | DB name needs its panel **suffix**, or the user isn't attached to the DB |
| `Access denied for user` (1045) | Wrong DB user/password, or wrong `sdb-N` host — copy exact values from MySQL Databases |
| Site still shows old config after `.env` edit | Re-run `php83 artisan config:cache` |
| 500 with no detail | `APP_DEBUG=true` temporarily, or read `storage/logs/laravel.log` over SSH |
| `Unable to locate file in Vite manifest` | `public/build` not committed/pulled — rebuild locally and force-add it |
| Blank/403 at the domain | Document root still `public_html`; set it to `agapeinfosys/public` (§5) |
| Domain doesn't resolve | Point DNS at the package; `tongdon.com` did not resolve during setup |
| `php: command not found` / wrong version | Use `php83`; default `php` is a minimal build |
| Composer fails on version | Run `php83 /usr/bin/composer …`, not the system `php` |
| MySQL "max size" errors | Databases are capped at 1 GB on this platform |
| Uploaded images 404 | Run `php83 artisan storage:link`; check `APP_URL` scheme |
| Webhooks never delivered | Cron queue worker not set up, or product has no `callback_url` |
| Blank page after deploy | Run `bash deploy.sh` (caches may point at old files) |

---

## Why not the `.cpanel.yml` method you read about?

Those guides are for **cPanel**. Serverbyt is **StackCP/20i**; its Git Version
Control is a pull-based clone/deploy with no `.cpanel.yml` hook (use the
**Deployment script** field instead). The StackCP-supported way to serve Laravel
is to **change the document root** (which 20i/StackCP explicitly allows), which is
cleaner than copying `public/` into `public_html` and rewriting `index.php`.
