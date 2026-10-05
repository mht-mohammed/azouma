# Azouma (عزومة) — Restaurant Discovery Guide for Gaza

Azouma is a fast, Arabic-first, lightweight website for discovering restaurants in **Gaza, Palestine**.

**The problem:** restaurant information in Gaza is scattered across social media and often outdated — opening hours change, places close temporarily or relocate after the destruction, and people waste time and effort finding somewhere actually open.

**The solution:** a directory where visitors browse approved restaurants and see **photos, opening hours, contact info, current operating status, last-update date, verification badge, and a detailed written address**. Owners manage their own listing; an admin reviews and verifies every restaurant before it appears publicly. Because precise maps are unreliable after the destruction, locations are conveyed through detailed written addresses rather than map pins.

## Key features by role

**Visitor (no account needed)**
- Browse approved restaurants with cover photos, status, and verified badges
- Filter by category and area, search by name
- Restaurant page: gallery, weekly hours (Saturday first) with an "open now" indicator, call/WhatsApp buttons, and a form to report incorrect information

**Customer (registered user)**
- Everything a visitor can do (account required for future features such as favorites and reviews)

**Owner**
- Separate registration at `/owner/register`
- Dashboard showing the approval status (including the rejection reason)
- Create and edit their one restaurant: details, weekly hours, operating status, and up to 10 photos (auto-optimized to WebP in the background)
- Editing critical data (name, category, area, phone) sends an approved restaurant back for re-approval
- In-app notifications when the restaurant is approved or rejected

**Admin**
- Dashboard with counts and shortcuts
- Review queue: approve, reject (reason required), verify/unverify
- Reports inbox (mark as resolved), category and area management

## Tech stack

- PHP 8.2, Laravel 12 (Blade + Breeze), MySQL, Tailwind CSS, Vite
- `intervention/image` (GD driver) for queued WebP image processing
- Laravel queues (database driver), notifications (database + log mail), cache for lookups
- PHPUnit with in-memory SQLite for tests, Laravel Pint for code style
- No external CDNs, fonts, or map providers — everything is self-hosted for slow connections

## Architecture decisions

- **Thin controllers.** HTTP-only logic; validation lives in Form Requests, authorization in Policies and a `role` middleware.
- **Action classes** (`app/Actions`) for multi-step business rules such as approval, rejection, verification, and the "critical edit sends back to pending" rule.
- **PHP backed enums** with Arabic `label()` methods instead of raw strings or DB enums, so the same migrations run on MySQL and SQLite.
- **Policies + scoped queries.** Owners can only ever touch their own restaurant (scoped queries 404 everything else); admins are gated by role middleware.
- **Queues** for image optimization and notifications so uploads and approvals stay fast.
- **Caching** for rarely-changing lookups (categories, areas) with automatic invalidation via model events.
- **Route model binding by slug** for public restaurant URLs.

## Database overview

- `users` — name, email, password, `role` (`admin`, `owner`, `customer`)
- `categories`, `areas` — `name_ar` + unique slug
- `restaurants` — owner, category, area, details, optional coordinates, `status` (`pending`, `approved`, `rejected`), `operating_status` (`open`, `temporarily_closed`, `relocated`), verification fields
- `restaurant_images` — path, optimized thumbnail, dimensions, cover flag, sort order
- `opening_hours` — one row per weekday (`0` = Saturday … `6` = Friday); closing before opening means after midnight
- `reports` — visitor reports with reason, message, and status (`new`, `resolved`)
- Standard Laravel tables: `notifications`, `jobs`, `cache`, `sessions`

## Installation

Requirements: PHP 8.2 with the GD extension, Composer, Node.js, MySQL, Git.

```powershell
git clone <your-repo-url> Azouma
cd Azouma
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Edit `.env` for your machine (database `azouma`, `APP_TIMEZONE=Asia/Gaza`, `APP_LOCALE=ar`):

```powershell
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Useful while developing (each in its own terminal):

```powershell
npm run dev
php artisan queue:work
```

Production checklist: `php artisan migrate --force`, `php artisan optimize`, `php artisan storage:link`, and a supervised `php artisan queue:work` process.

## Running tests

```powershell
php artisan test
vendor\bin\pint --test
```

Tests run on in-memory SQLite, so every migration must stay compatible with both MySQL and SQLite.

## Demo accounts (LOCAL DEMO only — never use in production)

Password for all accounts: `password`

| Email | Role |
| --- | --- |
| `admin@azouma.local` | Admin |
| `owner1@azouma.local`, `owner2@azouma.local` | Owners |
| `customer@azouma.local` | Customer |

All seeded restaurants, phone numbers, and images are fictional placeholders.

## Roadmap

Ordered by value: "near me" search, reviews and ratings, menu and prices, favorites, REST API with Sanctum, English UI, Docker setup.

## Author

**Mohammed Hazem** — Junior PHP/Laravel developer
- GitHub: https://github.com/mht-mohammed
- LinkedIn: https://www.linkedin.com/in/mohammedhazem
- Email: mohammedhazem.dev@gmail.com
