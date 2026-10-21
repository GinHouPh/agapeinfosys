# Roadmap — Agape Infosys

Where the project is, what is finished, what is left, and in what order it should
land. Written from the code and git history, not from a wish list.

- **Stack:** Laravel `^13.17` on PHP 8.3, Tailwind CSS v4, Vite 8, PHPUnit 12.
- **Data:** SQLite by default (`DB_CONNECTION=sqlite`); MySQL/Postgres via `.env`.
- **Frontend:** no runtime JS dependencies. Blade + vanilla JS + Tailwind v4.
  No Livewire, no Filament, no Alpine, no SPA framework.
- **Size:** 41 models, 73 controllers, 183 Blade views, 45 migrations,
  28 registered builder widgets (+1 abstract base), 56 feature test files,
  586 test methods.

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
| Visual page builder (drag-drop, nesting, undo/redo, clipboard, device preview, design tokens) | ✅ Shipped | 27 widgets; flagship subsystem |
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
| Transactional email (order receipt, password reset, admin alerts) | ✅ Shipped | `App\Mail`, `App\Notifications` |
| Refund workflow (admin-initiated) | ❌ Not started | Cancellation of subscriptions exists; refunds do not |
| CI (tests + Pint on push) | ❌ Not started | No `.github` |
| Production deploy + backups + monitoring | 🟡 Partial | `DEPLOY.md` exists; backups/monitoring not automated |

Legend: ✅ shipped · 🟡 partial / needs verification · ❌ not started.

---

## 2. The platform module plan

Thirteen modules define the platform. Eight of them are substantially built;
five are greenfield or have a genuine, named gap. This table is the scope of
the current planning cycle.

| # | Core Module | Purpose | Priority | Status | Gap to close |
| --- | --- | --- | --- | --- | --- |
| 1 | Platform Administration | Manage the entire CMS platform | P0 | ✅ Shipped | ✅ Phase 5 landed — health console, feature flags, maintenance mode, audit trail. Still missing: backups and log rotation |
| 2 | Dashboard & Reporting | Operational analytics and KPIs | P0 | ✅ Shipped | ✅ Phases 9 and 13 landed — dated KPIs, revenue series, top plans/products, CSV export, and a traffic report |
| 3 | Visual Website Builder | Drag-and-drop website design | P0 | ✅ Shipped | ✅ Phases 4 and 13 landed — saved components, media picker, widget versioning. Still missing: A/B testing |
| 4 | Page & Layout Management | Create and organize website pages | P0 | ✅ Shipped | ✅ Phase 8 landed — hierarchy, revisions, scheduled publish, duplication, per-page colours |
| 5 | Design System | Manage themes, styles, fonts, and colors | P0 | ✅ Shipped | ✅ Phases 3 and 13 landed — editable tokens, fonts, radii, shadows, live theme switching, per-page overrides |
| 6 | Media & Asset Library | Upload, organise and reuse media | P0 | ✅ Shipped | ✅ Phases 10 and 13 landed — library, gallery, alt-text, focal point, and the in-builder picker |
| 7 | Content Collections & Modelling | Define custom content types | P1 | ✅ Shipped | ✅ Phases 11 and 13 landed — types, fields, records, public rendering, and a collection widget |
| 8 | Localization & Multi-language | Serve the site in more than one locale | P1 | ✅ Shipped | ✅ Phases 12 and 13 landed — languages, translations, per-block text, URL-prefixed addresses, `hreflang` |
| 9 | Theme & Component System | Reusable components and layouts | P0 | ✅ Shipped | ✅ Phases 4 and 13 landed — saved components with slots, page templates, and widget versioning |
| 10 | Navigation Management | Manage menus and navigation | P0 | 🟡 Partial | ✅ Phase 1 landed — menus, nested items, admin editor, header/footer wired. Still missing: drag-to-reorder and live preview |
| 11 | Forms & Data Collection | Build forms and collect submissions | P1 | ✅ Shipped | ✅ Phase 6 landed — file uploads, conditional questions, CSV export, notifications, spam defence. Still missing: multi-page forms |
| 12 | User & Access Management | Manage users and permissions | P0 | ✅ Shipped | ✅ Phase 2 landed — roles, permissions, per-route enforcement, audit trail. Still missing: 2FA, team/org structure |
| 13 | SEO & Metadata | Optimize website discoverability | P1 | ✅ Shipped | ✅ Phases 7 and 13 landed — per-record metadata, sitemap, news sitemap, robots, 301 manager, JSON-LD, `hreflang` |

> Modules #2, #6, #7 and #8 were named during planning and confirmed as
> Dashboard & Reporting, Media & Asset Library, Content Collections &
> Modelling, and Localization & Multi-language respectively.

### Non-negotiables for this cycle

- **`users.is_admin` stays.** It becomes the superuser bypass. The column is
  not removed, renamed, or repurposed, so every existing test and middleware
  keeps working unchanged.
- **Colours stay token names.** The builder stores `accent`, not `#d90429`.
  `DesignTokens::COLORS` remains the allow-list and
  `tests/Feature/DesignTokensTest.php` keeps its meaning.
- **No new runtime npm dependencies.** The builder is hand-rolled vanilla JS
  and stays that way.
- **Every phase ends green:** `vendor/bin/pint --dirty --format agent`,
  `npm run build`, `php artisan test --compact`.

---

## 3. Target architecture — new schema

Nine migrations. Scalar configuration reuses the existing `settings` key/value
table rather than adding new ones.

### Module 12 — RBAC (P0)

```
roles            id, name, slug, description, is_system, timestamps
permissions      id, name, slug, group, description
permission_role  role_id, permission_id              (pivot)
role_user        user_id, role_id                    (pivot)
activity_log     id, user_id, subject_type, subject_id, action, description,
                 properties json, ip_address, user_agent, timestamps
```

`EnsureUserIsAdmin` keeps working. A new `EnsurePermission` middleware
(alias `can:`) reads the pivots.

### Module 10 — Navigation (P0)

```
menus            id, name, slug, location, description, is_active, timestamps
menu_items       id, menu_id, parent_id, label, link_type, link_value, icon,
                 badge, css_class, target, sort_order, is_active, timestamps
```

`link_type` ∈ `route | url | page | record | category` so admins choose a named
route, a builder page, a category, or an external URL without touching code.

### Module 5 — Design System (P0)

```
design_themes    id, name, slug, description, is_default, is_active,
                 tokens json, created_by, timestamps
```

`tokens` json shape:

```json
{
  "dark":  { "--color-accent": "#d90429", "--color-ink": "#0c0c0c" },
  "light": { "--color-accent": "#d90429", "--color-ink": "#f6f6f3" },
  "font":  { "display": "Akshar", "body": "Roboto" },
  "radius": { "card": "0px" },
  "shadow": { "card": "none" }
}
```

Only the *values* become editable. The builder still stores token **names**.

### Module 9 — Theme & Component System (P0)

```
component_templates  id, name, slug, category, widget, description, icon,
                     schema json, slots json, thumbnail, is_active,
                     usage_count, created_by, timestamps
page_templates       id, name, slug, description, thumbnail, schema json, is_active
```

### Module 13 — SEO (P1)

```
seo_metas   id, metaable_type, metaable_id, meta_title, meta_description,
             meta_keywords, og_title, og_description, og_image, twitter_card,
             canonical_url, noindex, sitemap_priority, sitemap_changefreq,
             timestamps                            (unique on [type, id])
redirects   id, from_path, to_path, status_code, is_active, hits, timestamps
```

### Module 11 — Forms upgrades (P1) — column additions only

```
form_fields       + validation json, condition json, width, file_settings json
form_submissions  + meta json (ip/ua/referer), page_url, is_spam
```

### Module 1 — Platform Administration (P0)

```
feature_flags  id, key unique, name, description, is_enabled,
               rollout_percentage, timestamps
```

Audit trail reuses `activity_log`. Health, cache, queue and log views are
read-only projections over existing Laravel services — no schema.

### Module 6 — Media Library (P0)

```
media          id, disk, directory, filename, original_name, mime_type, size,
               width, height, alt_text, title, focal_x, focal_y, is_public,
               created_by, timestamps
```

### Module 4 — Page upgrades (P0)

```
pages          + parent_id, template_id, scheduled_for, sort_order
page_revisions id, page_id, title, schema json, note, created_by, timestamps
```

### Module 7 — Collections & Modelling (P1)

```
collection_types  id, name, slug, description, icon, route_pattern, is_active
collection_fields id, collection_type_id, name, label, type, options json,
                   validation json, sort_order
collection_records id, collection_type_id, slug, title, data json, is_published,
                    published_at, timestamps
```

### Module 8 — Localization (P1)

```
locales        id, code, name, native_name, direction, is_default, is_active
translations   id, locale_id, translatable_type, translatable_id, field, value,
               timestamps                            (unique on [type,id,field,locale])
```

---

## 4. Delivery phases

Phase numbering is execution order, not module numbering.

```
Phase 1  Navigation Management      (M10, P0)   3d   ─┐ no deps
Phase 2  User & Access / RBAC       (M12, P0)   5d   ─┘
Phase 3  Design System              (M5,  P0)   6d   ─┐
Phase 4  Theme & Component System   (M9,  P0)   6d   ─┘ needs Phase 3
Phase 5  Platform Admin console     (M1,  P0)   5d   ─┐
Phase 8  Page & Layout + Builder    (M4/M3,P0)  6d   ─┘ needs Phase 2 + Phase 4
Phase 6  Forms upgrades             (M11, P1)   5d   ─┐
Phase 7  SEO & Metadata             (M13, P1)   5d   ─┘ independent
Phase 9  Dashboard & Reporting      (M2,  P0)   4d   ─┐
Phase 10 Media & Asset Library      (M6,  P0)   6d   ─┘ needs Phase 2
Phase 11 Collections & Modelling    (M7,  P1)   6d   ─┐
Phase 12 Localization               (M8,  P1)   6d   ─┘ needs Phase 11
Phase 13 Polish                     (all, P1)   8d   ─┘ closes every named gap
                                       ── 71d total
```

> **All thirteen phases have shipped.** The work was done in full: every
> phase above is marked ✅, the test suite grew from 381 to 586 methods, and
> the eight gaps the earlier phases named are closed by Phase 13.

### Phase 1 — Navigation Management (P0) · 3 days · ✅ SHIPPED

Closes the loudest gap: an admin cannot move a menu item today.

Delivered:
- `menus` and `menu_items` tables; `Menu`, `MenuItem` models; `MenuLocation`
  and `MenuItemType` enums.
- `MenuService` resolves a location into a nested tree — route, URL, builder
  page, published record (`service:slug`) and shop category link types. The
  tree is rebuilt per request from one eager-loaded query rather than cached,
  so an edit can never go stale.
- `Admin\NavigationController` and `Admin\MenuItemController` with
  `Store*`/`Update*` form requests; nested item editing and one-level
  nesting with self-parenting blocked.
- Admin views under `resources/views/admin/navigation/`; "Navigation" added
  to the admin sidebar.
- `resources/views/partials/header.blade.php` and `footer.blade.php` now
  render from the service. The nav glyph map moved to
  `config/navigation.php` so the editor and renderer agree on which icons
  exist.
- **Fallback rule:** with no active menu for a location, the hardcoded
  navigation in `config/navigation.php` renders. The header never breaks.
- `MenuSeeder` seeds the header, footer and legal menus with today's links;
  `tests/Feature/NavigationTest.php` asserts the seeded menus never drift
  from the fallback.

Tests: `tests/Feature/NavigationTest.php` — 22 tests covering tree
resolution, active/inactive menus, ordering, nesting, every link type,
self-parenting, cross-menu isolation, the fallback path, the seeder, and the
admin authorisation.

### Phase 2 — User & Access Management (P0) · 5 days · ✅ SHIPPED

Delivered:
- `roles`, `permissions`, `permission_role`, `role_user` and `activity_log`
  tables; `Role`, `Permission` and `ActivityLog` models.
- `config/permissions.php` — a 26-permission catalogue grouped into
  Dashboard, Content, Inbox, Commerce, Clients and System, one capability
  per admin area.
- `User::isSuperAdmin()`, `hasPermission()`, `hasAnyPermission()` and
  `permissionSlugs()`. **`users.is_admin` is unchanged and still the
  superuser bypass** — no column was renamed, removed or repurposed, so
  every existing test and middleware keeps working.
- `EnsurePermission` middleware registered as `can:` in `bootstrap/app.php`.
  Every admin route now names the permission it needs, so a role-holding
  admin reaches only their own area.
- `EnsureUserIsAdmin` now admits superusers **and** users holding at least
  one permission; anyone else still gets a 403.
- `Admin\RoleController` — create, edit, assign permissions, delete. Built-in
  roles are protected from deletion.
- `Admin\ActivityLogController` — filterable audit trail. The
  `RecordsActivity` trait writes entries from mutating controllers.
- Role assignment added to the client editor; the admin sidebar hides links
  the viewer cannot reach (`x-admin-link` gained a `permission` prop).
- `RoleSeeder` seeds Superadmin, Content Editor, Support, Commerce and
  Designer; `PermissionSeeder` syncs the catalogue.

Tests: `tests/Feature/AccessControlTest.php` — 16 tests covering the
superuser bypass, per-permission grants and denials, role CRUD, catalogue
validation, protected system roles, non-superuser admin sign-in, the audit
trail, sidebar filtering and role assignment.

> **Security note.** The admin sign-in form now accepts any account that
> holds a role. An account with no roles is still rejected, and the client
> dashboard is unaffected.

### Phase 3 — Design System (P0) · 6 days · ✅ SHIPPED

Delivered:
- `design_themes` table; `DesignTheme` model with `is_active` / `is_default`.
- `config/design.php` — the default token values (mirroring the `@theme`
  block in `app.css`), the font menu and the radius/shadow scales.
- `ThemeCompiler` — turns a theme's values into the CSS custom properties
  the site reads, and emits them inline in the document head **before** the
  stylesheet so they beat the `@theme` layer. Switching a theme repaints
  every `text-accent`, `bg-surface` and `border-line` utility at once.
- `layouts/app.blade.php` emits the active theme; with no active theme the
  stylesheet stands on its own, so nothing breaks.
- `Admin\DesignController` — a token editor with a colour picker per token
  per scheme, font selects, radius/shadow selects, and a live preview that
  repaints as you type. Only one theme can be live at a time.
- `App\Support\Colour` flattens an rgba() token for the colour picker while
  the token itself keeps its authored value.
- The font menu is real: `vite.config.js` now bundles Outfit and Inter
  alongside Akshar and Roboto, so the picker offers fonts the build actually
  ships.
- **The builder still stores token *names*.** `DesignTokens::COLORS` remains
  the allow-list and `tests/Feature/DesignTokensTest.php` keeps its meaning.

Tests: `tests/Feature/DesignSystemTest.php` — 12 tests covering theme CRUD,
token-name and colour validation, hex and rgba acceptance, single-live-theme
enforcement, compiler output, the seeded theme matching the stylesheet, and
the style tag reaching the page head.

### Phase 4 — Theme & Component System (P0) · 6 days · ✅ SHIPPED

Delivered:
- `component_templates` and `page_templates` tables; `ComponentTemplate` and
  `PageTemplate` models.
- `TemplateService` — turns a saved template back into blocks: fills the
  slots, refreshes every block id so a template can be inserted twice, and
  counts the usage.
- **Slots.** A component declares named holes (`{{heading}}`, `{{body}}`).
  Inserting one asks for each value, falls back to the slot's own default,
  and the placeholder is substituted wherever it appears in the settings —
  including inside repeaters.
- **Builder: save as component.** A new `☆` button on the block toolbar
  copies the selected section out of the canvas into the component library.
  The stored schema is run through the same `SchemaSanitizer` the page save
  uses, so a template can never carry settings the builder would not write.
- **Builder: insert component.** The palette aside gains a "Saved
  components" list, fetched from the server so a component saved in one tab
  shows up in the next. Clicking one fills its slots and drops it in.
- **Page templates.** "Start from a template" on the new-page form seeds the
  canvas from a saved layout instead of leaving it blank.
- `Admin\TemplateController` and `resources/views/admin/templates/index.blade.php`
  — the library view, with usage counts and delete actions.
- `TemplateSeeder` ships three starter components (call to action, three
  feature cards, statement) and a landing-page layout.

Tests: `tests/Feature/ComponentTemplatesTest.php` — 12 tests covering the
library view, saving and deleting components, slot substitution and
defaults, fresh block ids, usage counts, the builder's JSON endpoints and
page creation from a template.

### Phase 5 — Platform Administration console (P0) · 5 days · ✅ SHIPPED

Delivered:
- `feature_flags` table; `FeatureFlag` model; `FeatureFlagService`.
  A flag that does not exist reads as **off** — nothing has to create a row
  before a switch can be used. A flag can be scheduled (`starts_at` /
  `ends_at`) and rolled out to a percentage of visitors, bucketed by
  address so the same visitor always sees the same side of it.
- `SystemHealthService` — a read-only report over things that already
  exist: environment and debug state, HTTPS on the app URL, PHP and Laravel
  versions, database reachability, media disk writability, queue backlog,
  mail transport, configured and default payment gateways, and the content
  counts.
- `Admin\SystemController` — the console: the health table, the feature
  flag list and the maintenance switch.
- **Maintenance mode.** `App\Http\Middleware\MaintenanceMode` is appended to
  the `web` group, so the public site answers with a branded 503 while
  admins still get through. The switch lives in the existing `settings`
  store under `maintenance`.
- `Admin\FeatureFlagController` — create, switch, schedule, roll out and
  delete flags.
- The audit trail from Phase 2 now records these changes too.

Tests: `tests/Feature/PlatformAdministrationTest.php` — 15 tests covering the
health console, the maintenance switch on and off, the admin bypass, flag
CRUD, missing flags reading as off, schedule windows and rollout buckets.

### Phase 6 — Forms & Data Collection upgrades (P1) · 5 days · ✅ SHIPPED

Delivered:
- **File uploads.** `FormFieldType::File` with a per-field type and size
  limit. Uploads are validated before they are stored and land in
  `storage/app/public/uploads/forms/{form-slug}`; the answer holds the path.
- **Conditional questions.** A question can carry a condition — "show this
  when *country* equals *India*" — with `equals`, `not_equals`, `contains`,
  `not_contains`, `is_filled` and `is_empty`. The condition is enforced on
  the server (a hidden field is neither required nor stored) and mirrored in
  the browser, where a hidden field is also disabled so it cannot be posted.
- **Field width.** Full or half row, so a form can be laid out rather than
  running as one long column.
- **Extra validation rules.** A question can carry its own Laravel rules.
- **CSV export.** `Admin\FormSubmissionController@export` streams the
  submissions of one form as a CSV, one column per question.
- **Submit notifications.** A form can name an address to announce new
  submissions to, with its own subject line.
- **Spam defence.** A hidden honeypot field, a per-form submission rate, and
  an `is_spam` flag on the submission. A spam submission is recorded but
  told everything went well, so the bot learns nothing.
- Submissions now record where they came from: IP, user agent, referrer and
  the page they were sent from.

Tests: `tests/Feature/FormsUpgradeTest.php` — 11 tests covering uploads and
their type checks, both sides of a conditional question, the honeypot, the
rate limit, notification on and off, the CSV shape, and a question's
condition and width being saved.

### Phase 7 — SEO & Metadata (P1) · 5 days · ✅ SHIPPED

Delivered:
- `seo_metas` table (a morph) and the `HasSeoMeta` trait, applied to
  `Page`, `Service`, `Project` and `Post`.
- `SeoService` — resolves the metadata a page is published with. A value on
  the record's own SEO panel wins; failing that the record falls back to the
  title and excerpt it already carries.
- `Admin\SeoController` — a per-record panel for the title, description,
  keywords, canonical URL, social title, social description, social image,
  Twitter card, indexing, sitemap priority and change frequency. Linked
  from the admin index of pages, services, case studies and articles.
- **`/sitemap.xml`** — every published page and record, honouring `noindex`
  and the per-record priority and change frequency. Drafts are left out.
- **`/robots.txt`** — served by a route. The static `public/robots.txt` was
  removed, because a static file shadows the route and would have kept the
  old policy in place.
- **301 manager.** A `redirects` table and `Admin\RedirectController`.
  `App\Http\Middleware\RedirectPaths` runs before any route sees the
  request, so a legacy address never reaches a 404. Hits are counted.
- **Open Graph and Twitter tags** now read from the resolved metadata, and
  a `noindex` record emits `robots: noindex, nofollow`.
- **JSON-LD.** A `@graph` with `Organization`, `WebSite` and a per-record
  entry (`Article`, `Service`, `CreativeWork`, `WebPage`), emitted into the
  document head. The record is resolved from the current route, so the tags
  always match the page.

Tests: `tests/Feature/SeoTest.php` — 15 tests covering the sitemap's
contents and exclusions, robots, the SEO panel, metadata precedence, the
tags reaching the head, JSON-LD, and redirects firing, staying quiet and
counting their hits.

### Phase 8 — Page & Layout + Builder polish (P0) · 6 days · ✅ SHIPPED

Delivered:
- **Page hierarchy.** `pages.parent_id` and a `pages.path` column. A page
  nested under another is served at `/pages/parent/child`; moving a parent
  re-paths everything beneath it. `Page::ancestors()` and
  `Page::breadcrumb()` walk the tree.
  - The public route binds on the path; the admin binds on the id, so a
    rename never breaks a bookmarked edit screen. `Page::adminRoute()`
    builds admin URLs explicitly.
- **Revision history.** A `page_revisions` table; every layout change keeps
  the version it replaced (the last 20). `Admin\PageController@revisions`
  lists them, `@diff` shows what changed, `@restore` puts one back — and
  the version it replaces is kept too.
- `PageDiffService` — flattens two schemas into one line per setting and
  reports what was added, changed or removed.
- **Scheduled publishing.** `PageStatus::Scheduled` plus a `scheduled_for`
  date. A scheduled page is not live before its date and is live after it,
  with no one touching it. The `pages:publish-scheduled` command runs every
  minute from the scheduler.
- **Duplication** — copy a page and its layout in one click, as a draft.
- **Ordering** — `sort_order` on pages, with a reorder endpoint.
- **The builder canvas was extracted.** The ~1,600 lines of inline
  JavaScript that lived in `resources/views/admin/pages/edit.blade.php` now
  live in `resources/js/builder/canvas.js` as a plain module. The server
  hands it its whole state — block tree, widget manifest, device tiers,
  colour tokens and endpoints — as one JSON block, so the script contains
  no Blade at all. It builds as its own 30 KB bundle.

Tests: `tests/Feature/PageHierarchyTest.php` — 17 tests covering paths,
nesting, re-pathing, breadcrumbs, admin addressing, scheduled publishing
and the command, revisions, trimming, restoring, diffing and duplication.
`tests/Feature/AdminPagesTest.php` now also asserts the canvas is handed
its state as JSON.

> **URL contract change.** A builder page's public URL is now its *path*
> rather than its slug. Top-level pages are unchanged (`/pages/about`); a
> nested page is `/pages/about/team`.

### Phase 9 — Dashboard & Reporting (P0) · 4 days · ✅ SHIPPED

Delivered:
- `DashboardService` — the numbers, over a period: revenue (paid orders and
  paid shop orders, in their own currencies), order count, new
  subscriptions, active subscriptions and form submissions. The same
  length of time immediately before is reported alongside, for comparison.
- **A dated dashboard.** Revenue, orders, subscriptions and submissions as
  headline cards, with a revenue-by-day chart. The period is chosen with
  7 / 30 / 90 / 365-day presets.
- **A revenue report** at Admin → Reports: the daily series, the top plans
  and the top products by revenue.
- **CSV export** of the report.
- The chart is a handful of SVG rectangles in a Blade partial — no charting
  library, no runtime dependency.

Tests: `tests/Feature/DashboardReportingTest.php` — 8 tests covering the
KPIs, paid-versus-pending orders, the period boundary, the length of the
revenue series, the plan ranking, the report screen, the CSV and the
submission count.

### Phase 10 — Media & Asset Library (P0) · 6 days · ✅ SHIPPED

Delivered:
- A `media` table: the disk, the directory, the stored filename, the name
  the file arrived with, its type and size, its pixel dimensions, its alt
  text, a title, and a focal point.
- `MediaService` — stores a file and records it, records a file already on
  disk, and deletes both the row and the file.
- `Admin\MediaController` — the library: a grid of everything uploaded,
  search by name, title or alt text, a filter between images and documents,
  and an editor for the alt text, title and focal point.
- **Images uploaded in the page builder are recorded too**, so the library
  shows everything the site has, not just what was uploaded through the
  library screen.
- The focal point is stored as a percentage, so a crop can keep the subject
  in frame whatever size the image is shown at.

Tests: `tests/Feature/MediaLibraryTest.php` — 9 tests covering uploading,
the recorded dimensions, searching, filtering, describing a file, deleting
it from disk, the builder upload reaching the library, and recording a file
that is already there.

### Phase 11 — Collections & Modelling (P1) · 6 days · ✅ SHIPPED

Delivered:
- `collection_types`, `collection_fields` and `collection_records` tables;
  `CollectionType`, `CollectionField` and `CollectionRecord` models.
- **A content type an author defines.** A type is a name, a description and
  a set of fields — text, long text, number, email, URL, date, dropdown,
  single choice and multiple choice — each with its own label, options and
  extra validation rules.
- `CollectionService` — the validation rules for a record and the shape of
  its data. A value the type does not declare is **dropped**, so a record
  can never carry a field its type has not got.
- `Admin\CollectionController` — the type list, the field editor, and the
  records of a type, with the record form built from the type's own fields.
- **Public rendering.** A record is served at `/{collection}/{record}`, or
  at the type's own path pattern (`/work/{slug}`) if one is set. Only
  published records are served.
- The public route is registered **last**, just before the root-level
  `{product}` catch-all, so `/admin/…`, `/shop/…` and `/services/…` are
  always matched first.

Tests: `tests/Feature/CollectionsTest.php` — 11 tests covering creating a
type, adding a field, creating a record, a required field, an undeclared
value being dropped, a published record being served, an unpublished one
not being, an unknown collection, deleting a record and the records screen.

### Phase 12 — Localization & Multi-language (P1) · 6 days · ✅ SHIPPED

Delivered:
- `locales` and `translations` tables; `Locale` and `Translation` models.
- `LocaleService` — the language in play for a request. A visitor's choice is
  kept in a cookie; a visitor who has not chosen gets their browser's
  preference; failing that, the site's default language.
- `SetLocale` middleware puts the language into place for the request.
- **A language switcher in the public footer**, so a visitor can change it
  without an account.
- `HasTranslations` trait on `Page`, `Service`, `Project` and `Post`.
  A translation is one field of one record in one language. A plain
  attribute is addressed by its name; a setting inside a builder schema is
  addressed as `block.<id>.<setting>`, which survives the section being
  moved.
- **The page renders in the visitor's language.** `PageRenderer` applies the
  translated settings before rendering — but only for visitors. The builder
  canvas and the preview endpoint still show the original text, so an author
  always edits the source, never a translation.
- `TranslationService` — which fields of a record can be translated (text,
  long text and URL settings only; colours, numbers and toggles are not
  language) and what each one is called.
- `Admin\LocaleController` and `Admin\TranslationController` — the language
  list and a translation editor per record per language.

Tests: `tests/Feature/LocalizationTest.php` — 10 tests covering adding and
defaulting a language, protecting the default, the visitor's choice, the
cookie, saving a translation, a translated page rendering in the visitor's
language, the fallback to the original text, the editor listing the
translatable fields, and the browser preference.

### Phase 13 — Polish (P1) · 8 days · ✅ SHIPPED

The eight gaps the earlier phases named, closed.

- **Repeater-field translation.** Every repeater row now carries an id of
  its own, so a translation addresses the *row* rather than its position —
  reordering a list no longer shuffles its translations onto its
  neighbour. Nine repeater widgets moved to version 2 to carry the change.
- **Widget versioning.** A widget declares a version and the migrations
  that carry a block forward. `SchemaMigrator` applies them when a page is
  read, so an old page renders with the widget as it is today without
  anyone running anything; `builder:migrate-pages` bakes the result in.
- **`/sitemap_news.xml`** — the published articles, in the Google News
  format.
- **A collection widget.** `collection-grid` lists the records of any
  collection an author defines. A control whose options are filled in at
  request time is a first-class case now, not a special one.
- **The in-builder media picker.** An image control offers "Choose from
  library" beside its upload, opening a modal over the canvas that lists
  the media library.
- **Per-page token overrides.** A page can change the accent it renders
  with without changing the theme every other page uses. The overrides are
  emitted after the theme, so they win.
- **URL-prefixed languages.** `/de/about` is the German About page. The
  prefix is read before the router sees the request, so the routes behind
  it stay the same ones, and a `LocalisedUrlGenerator` puts the language
  into every address the site generates. `hreflang` links point at the
  same page in every language.
- **Traffic.** Every public page request is recorded — path, status,
  referrer, a visitor key and a hashed address. No address is stored.
  Admin → Traffic shows the views by day, the most-read pages and where
  the visitors came from.

Tests: `RepeaterTranslationTest`, `WidgetVersioningTest`,
`CollectionWidgetTest`, `MediaPickerTest`, `PageTokenOverrideTest`,
`TrafficTest` and `LocaleUrlTest` — 38 tests across the eight items.

---

## 5. Backlog (unscheduled)

**Live server — verify before the next release**
- **The queue worker is not running.** `jobs` holds 6 rows and no
  `queue:work` process is running, so the outbound entitlement webhooks
  (`SendEntitlementWebhook`) are queued and never delivered. Shared hosting
  cannot run a daemon: the two cron entries in `DEPLOY.md` §9 have to be
  added under **Web Tools → Scheduled Tasks**, and `crontab` is blocked for
  the SSH user so this cannot be checked or fixed over SSH.
- **The scheduler has never run.** `pages:publish-scheduled` is registered
  and `schedule:list` shows it, but it only fires if the `schedule:run`
  cron entry is in place — so a page saved with a future date will not go
  live on its own.
- Backups of the database and `storage/app/public` are not automated.

**Housekeeping / correctness**
- Rebrand defaults: `.env.example` `APP_NAME=Laravel` → `Agape Infosys`;
  `config/app.php` default still `Laravel`.
- Delete the unused stock `resources/views/welcome.blade.php` (dead 70 KB file,
  no route references it).
- Refresh docs: `README.md` is still the stock Laravel readme.
- Add `.ai/rules` (the `AGENTS.md` references it but the directory does not
  exist).
- `logo.png` sits untracked in the project root; nothing references it yet.

**Features**
- Refund workflow (admin action + gateway refund call + entitlement revoke).
- Invoice/receipt generation (PDF) tied to an order.
- Customer self-service: request refund, edit billing details.
- Coupons / discount codes.
- Multiple currencies per plan (models already store `currency`).
- Dunning/expiry reminders when a subscription approaches `ends_at`.
- Webhook delivery log UI (inbound events are stored; outbound attempts are not).
- A retention policy for `page_views` — it is a row per public request and
  needs pruning before the table matters.
- Drag-to-reorder for the navigation editor (the order is set by number
  today).
- A/B testing for pages.
- Multi-page forms.
- Two-factor authentication, and a team or organisation structure above
  single users.

**Product API**
- Sandbox/live key pairs per product (currently one `api_key`/`api_secret`).
- Rate limiting on `/api/v1/*` (only the web/forms routes are throttled today).
- Public OpenAPI description for the product teams.

---

## 6. Launch checklist

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

## 7. Deep dive — the payments & commerce subsystem

The part of the app that touches money, and therefore the part that needs the
most care before going live.

### 7.1 Layout

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

### 7.2 Two checkout flows, one completion path

There are two front doors:

- **Direct:** `GET/POST /{product}` → `CheckoutController` → payment
  `CheckoutService`.
- **Cart:** `/shop/checkout` → `App\Services\Shop\CheckoutService`.

Both converge on the same ideas: create a **pending** order, hand the customer
to a gateway, then trust only the gateway's **status API** to mark it paid.

### 7.3 The trust model (this is the good part)

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

### 7.4 Entitlements

`CheckoutService::grantEntitlement()` upserts a `Subscription` keyed on
`(product_id, subscriber_key)` — so buying again **extends** the same
entitlement rather than creating duplicates. Validity comes from
`plan->entitlementDays()`; recurring plans also set `renews_at`.

### 7.5 Outbound webhooks

`SendEntitlementWebhook` POSTs a signed, JSON payload to the product's
`callback_url` with retries (`backoff = [30, 120, 600]`). It uses the **same**
HMAC scheme as the inbound product API:

```
X-Agape-Signature = HMAC-SHA256( secret, "{timestamp}.POST.{path}" )
```

so product teams implement signing/verifying once.

### 7.6 Driver readiness (honest assessment)

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

### 7.7 Known sharp edges

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

### 7.8 What to do before enabling a real gateway

1. Enter sandbox credentials in **Admin → Gateways**, keep `test` mode.
2. Do a full sandbox purchase with a queue worker running.
3. Confirm: order `paid`, `Subscription` created/extended, `payment_events`
   row written, outbound webhook delivered (or product reports receipt).
4. Flip the gateway to `live`, make it default, disable `sandbox`.
5. Whitelist the production domain with the provider and point its webhook at
   `https://<domain>/api/webhooks/<driver>`.
6. Watch the queue and `payment_events` for the first real transactions.

---

## 8. How to work in this repo

- Tests: `php artisan test --compact` (or `vendor/bin/phpunit <file>`).
- Format: `vendor/bin/pint --dirty --format agent`.
- Build assets: `npm run build` (required after Blade/CSS changes).
- Run everything locally: `composer run dev`.
- Conventions and agent guidance live in `AGENTS.md`.

---

*Last updated: 2026-10-21. Keep §1, §2 and §3 current as work lands.*
