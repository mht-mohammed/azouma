# Azouma (عزومة) — Agent Build Prompt

> Paste the "Master Context" once at the start of the session, then send ONE phase prompt at a time. Do not send all phases together.

---

## MASTER CONTEXT (send first)

You are a senior Laravel engineer pairing with me. I am a junior back-end developer (PHP/Laravel) who is refreshing my Laravel knowledge while building this project. Your goal is both to build the project correctly AND to help me learn, so explain decisions briefly and let me understand every change.

### Project
**Azouma (عزومة)** is a restaurant discovery directory for **Gaza, Palestine**. Visitors browse restaurants, filter them, open a restaurant page to see photos of the place, opening hours, contact info, current status, and its exact location on a map. Restaurant owners register and manage their own restaurant. An admin approves restaurants before they are published.

### Tech decisions (already made, do not change)
- Laravel 12, PHP 8.2+, MySQL, Laravel Breeze (Blade), Tailwind CSS, Vite.
- Project already created, Breeze installed, database connected, tests passing.
- Roles via a `role` column on `users` (`admin`, `owner`, `customer`) enforced with Policies and middleware. Do NOT install a roles/permissions package.
- Maps: Leaflet + OpenStreetMap (no API keys, no billing).
- Images: stored locally with `storage:link`.
- UI: Arabic first, RTL, lightweight pages (the internet in Gaza is slow). English can be added later.

### Database design (MVP)
```
users               id, name, email, password, role
categories          id, name_ar, slug
areas               id, name_ar, slug
restaurants         id, owner_id, category_id, area_id, name, slug, description,
                    phone, address, latitude, longitude,
                    status            (pending | approved | rejected)
                    operating_status  (open | temporarily_closed | relocated)
                    is_verified, last_verified_at, timestamps
restaurant_images   id, restaurant_id, path, is_cover, sort_order
opening_hours       id, restaurant_id, day_of_week, opens_at, closes_at, is_closed
```
Use foreign keys, indexes on foreign keys and `slug`, and `slug` for public URLs.

### Rules you must follow
1. **Work in small steps.** Complete only the phase I send. Do not build ahead.
2. **Keep it simple.** No over-engineering. No packages unless I approve them. Prefer built-in Laravel features.
3. **Code quality:** use Form Requests for validation, Policies for authorization, Eloquent relationships with eager loading (avoid N+1), route model binding, resource controllers where appropriate. Keep controllers thin.
4. **Security:** validate everything, authorize every owner/admin action, validate image uploads (type and size), never trust user input for ownership (always scope by the authenticated owner), escape output.
5. **Tests:** write Feature tests for each phase (PHPUnit). Run `php artisan test` and make sure everything passes before you finish a phase.
6. **Git:** after each logical step, suggest a clear English commit message (e.g. `Add restaurants migration`). Do not commit secrets or `.env`.
7. **Data:** seeders must use clearly **fictional** restaurants and fake phone numbers. Never use real restaurant names, real phone numbers, or real photos. Mark demo data as demo.
8. **Do not guess.** If something is ambiguous, ask me one short question before proceeding.

### Output format at the end of EVERY phase
1. **What was built** (short list).
2. **Files created/changed** (list).
3. **Laravel concepts used** — 3 to 5 bullets, each explained in 1–2 simple sentences, so I can review them.
4. **How to test manually** (exact commands and URLs).
5. **Tests added** and the result of `php artisan test`.
6. **Suggested commit messages.**
7. **A short entry I can paste into `NOTES.md`** (what I learned, and why we chose this approach).
8. Then **stop and wait** for my approval before the next phase.

---

## PHASE 1 — Data layer

Goal: a working database structure with relationships and demo data.

Tasks:
- Create migrations for all tables in the design above (with foreign keys, indexes, and enums or string columns with constants for `status` and `operating_status`).
- Create Eloquent models with relationships: Restaurant belongsTo owner (User), Category, Area; hasMany images and opening hours. Add casts, fillable, and useful scopes (`approved`, `open`).
- Add the `role` column to the users table and a default of `customer`.
- Create factories and seeders: categories (e.g. مشويات، مأكولات بحرية، وجبات سريعة، حلويات، كافيهات، مأكولات شعبية), areas (use real neighborhood names of Gaza that I can edit later), one admin, two owners, and about 12 fictional restaurants with images placeholders, opening hours, and coordinates inside Gaza City.
- Tests: model relationship tests and a test that seeders run.

Acceptance: `php artisan migrate:fresh --seed` works and `php artisan test` passes.

---

## PHASE 2 — Public pages

Goal: visitors can browse and view restaurants.

Tasks:
- Home / restaurants list page: shows only `approved` restaurants, cover image, name, category, area, and operating status badge. Pagination.
- Filtering and search: by category, by area, and by name (query string), with the filters kept in the URL.
- Restaurant details page (`/restaurants/{slug}`): image gallery, description, address, phone, opening hours, operating status, verified badge, "last updated" date, and a Leaflet map with a marker at the exact latitude/longitude.
- Eager load relationships to avoid N+1 queries.
- Lazy-load images and keep pages light.
- Tests: list shows only approved restaurants, filters work, details page works, pending restaurants return 404 for the public.

Acceptance: pages work, tests pass, no N+1 queries on the list page.

---

## PHASE 3 — Auth, roles, and owner dashboard

Goal: owners can manage their own restaurant.

Tasks:
- Owner registration (role `owner`) using Breeze, plus role-based redirects after login.
- Middleware or Policy so only the owner can edit their own restaurant, and only admins access the admin area.
- Owner dashboard: create and edit a restaurant, set category and area, pick location on a Leaflet map (click to set latitude/longitude), manage opening hours, upload and delete images, choose cover image, and update `operating_status`.
- New or edited restaurants go to `pending` and must be re-approved when critical data changes (decide and explain which fields).
- Form Requests for validation, secure image upload handling.
- Tests: an owner cannot edit another owner's restaurant, validation rules, image upload rules.

---

## PHASE 4 — Admin approval workflow

Goal: admin controls what is published.

Tasks:
- Admin dashboard listing pending restaurants.
- Approve or reject (with a rejection reason) and mark a restaurant as verified (`is_verified`, `last_verified_at`).
- Notify the owner of the decision (database notification or email using Laravel Notifications, using the `log` mail driver locally).
- Add a public "Report incorrect information" form on the restaurant page, stored in a `reports` table and visible to the admin.
- Tests: only admins can approve, approved restaurants appear publicly, notifications are sent.

---

## PHASE 5 — Arabic RTL, performance, and polish

Tasks:
- Full Arabic RTL layout with Tailwind, Arabic text in all views, Laravel localization files (`lang/ar`), and a design that is clean and light for slow connections.
- Queue job that resizes and compresses uploaded images after upload.
- Cache the list of categories and areas and the most visited pages where it makes sense.
- Basic SEO: page titles, meta descriptions, readable slugs, Open Graph tags for restaurant pages.
- Tests for the queue job and the cache behavior.

---

## PHASE 6 — Quality and documentation

Tasks:
- Review the whole project: remove dead code, check authorization on every route, check N+1 queries, and list any security concerns.
- Add a professional `README.md` (overview, features, tech stack, roles, installation steps including `npm install && npm run build`, `php artisan migrate --seed`, `php artisan storage:link`, demo accounts, and a short "Architecture decisions" section).
- Add a GitHub Actions workflow that runs `php artisan test` on every push.
- Suggest what to build next (e.g. "near me" geo search, reviews, menu, favorites, REST API).
