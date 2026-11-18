# Agape Central Licensing Server — Production Deployment & Operations Guide

**Target Host:** AgapeInfosys Web Platform (`agapeinfosys.com`)  
**Stack:** Laravel 11+ / PHP 8.3 / MySQL / Redis  
**Document Version:** 1.0.0  

---

## 1. Environment Configuration (`.env`)

Add and configure the following variables in your production `.env` file on `agapeinfosys.com`:

```env
# ==============================================================================
# CENTRAL LICENSING SERVER CONFIGURATION
# ==============================================================================

# Envato API Token (Required for CodeCanyon purchase code verification)
# Generate at: https://build.envato.com/create-token/
# Permissions required:
#   - "View and search Envato sites"
#   - "View your items' sales history"
#   - "Verify purchases of your items"
ENVATO_API_TOKEN=

# Cashfree Payment Gateway (Required for agapeinfosys.com direct order webhooks)
# Obtain from Cashfree Merchant Dashboard -> Developers -> API Keys
CASHFREE_APP_ID=
CASHFREE_SECRET_KEY=

# Storage Disk for Release Zips ('local' stores under storage/app/private, or 's3')
RELEASE_DISK=local

# Queue Connection (Set to 'database' or 'redis' for async email dispatch)
QUEUE_CONNECTION=database

# Cache Store (Set to 'redis' or 'database' for distributed API rate limiting)
CACHE_STORE=database
```

---

## 2. Server Permissions & Storage Structure

The licensing server stores downloadable software release archives in **private storage** so they cannot be accessed directly via public web URLs:

```bash
# 1. Ensure private releases directory exists
mkdir -p storage/app/private/releases

# 2. Set proper ownership and permissions for web server (e.g. www-data or cPanel user)
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 3. Production Database Migrations & Initial Seeding

Run the licensing migrations and seed the initial software product catalogue (`srs-genie`):

```bash
# Run pending migrations
php artisan migrate --force

# Seed the default SRS Genie product and v2.5.0 baseline release
php artisan db:seed --class=SoftwareProductSeeder --force
```

---

## 4. Server Task Scheduling (Cron Job)

The licensing server includes automated scheduled tasks:
- **`license:detect-abuse`** (Hourly) — Scans for abnormal IP velocity (> 10 distinct IPs/24h) and domain limit flooding (> 5 breaches/48h), automatically suspending compromised keys.
- **`license:prune-logs --days=90`** (Daily) — Cleans up telemetry logs older than 90 days.

Add the Laravel scheduler cron entry to your server (`crontab -e`):

```cron
* * * * * cd /home/agapeinfosys/public_html && php artisan schedule:run >> /dev/null 2>&1
```

---

## 5. Background Queue Worker (Supervisor)

To deliver purchase receipt emails (`LicenseIssuedMail`) instantaneously without slowing down webhook responses, configure a queue worker:

### Supervisor Configuration (`/etc/supervisor/conf.d/agape-worker.conf`):

```ini
[program:agape-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/agapeinfosys/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/agapeinfosys/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start agape-worker:*
```

---

## 6. Cloudflare & Reverse Proxy IP Restoration

Because the licensing server enforces IP velocity checks and domain bindings based on client IP, ensure your web server trusts proxy headers (e.g., Cloudflare `CF-Connecting-IP` or `X-Forwarded-For`).

In `bootstrap/app.php` (or `TrustProxies` middleware):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->trustProxies(at: '*');
})
```

---

## 7. Useful Operational Commands (Cheat Sheet)

```bash
# Issue a license manually for a client or enterprise partner
php artisan license:issue srs-genie buyer@agency.com --tier=agency --domains=5

# Publish a new software update package
php artisan release:publish srs-genie 2.6.0 /path/to/update.zip --changelog="Bug fixes & AI updates"

# Manually trigger piracy & abuse detection scan
php artisan license:detect-abuse

# Prune audit telemetry older than 60 days
php artisan license:prune-logs --days=60
```
