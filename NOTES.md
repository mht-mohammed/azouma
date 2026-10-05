# Azouma — Project Notes

## Setup
- Laravel 12, PHP 8.2+, MySQL, Breeze (Blade), Tailwind CSS, Vite
- Editor: VS Code

## Phase log

### Phase 0 — Environment and project setup
- Date: 2026-10-04
- Installed PHP 8.2, Composer, Node.js, Git
- Created Laravel 12 project with Breeze and connected MySQL
- Problems: PHP not in PATH (fixed via environment variables), PowerShell blocked npm scripts (fixed with Set-ExecutionPolicy)

### Phase 1 — Data layer
- Date: 2026-10-05 | Branch: `phase-1-data-layer`

**What I did**
I defined the requirements and data model for Azouma and built it with an AI coding agent, then reviewed the code, ran the migrations and seeders, and verified the full test suite.

**What is in it**
- 6 migrations (categories, areas, user roles, restaurants, restaurant images, opening hours)
- 4 enums, 5 models with relationships and scopes, an Arabic slug helper
- Factories and seeders with fictional demo data
- 20 new tests (45 passing in total)

**Key decisions I understand and agree with**
- Status fields are strings cast to enums, so the same migrations work on MySQL and SQLite.
- Slugs keep Arabic letters and never change after creation, so shared links don't break.
- Images and opening hours use cascade delete, since they only make sense with their restaurant.
- The week starts on Saturday (0), as it does in Gaza.

**What I learned**
Migrations and foreign keys, enums and casts, relationships and scopes, route model binding by slug, factories vs seeders, and testing with in-memory SQLite.

### Phase 2 — Public pages
- Date: 2026-10-05 | Branch: `phase-2-public-pages`

**What I did**
Built the public pages (home list with filters, restaurant details with map) with an AI coding agent, verified them in a real browser at mobile width, and confirmed the full test suite and production build pass.

**What is in it**
- Public RTL layout plus 5 Blade components (cards, badges, headers, empty state)
- Home `/` showing only approved restaurants, with category/area/search filters and pagination
- Details page: gallery, status banner with last-update date, weekly hours from Saturday, open-now indicator, call/WhatsApp buttons and the owner's written address (map and directions removed: after the destruction, places are located by written address, not maps)
- `search` scope, `isOpenNow()`, and a small query class keeping the controller thin

**Key decisions I understand and agree with**
- Location is shown as the owner's written address plus coordinates-free contact buttons (call, WhatsApp).

### Phase 3 — Auth, roles, and owner dashboard
- Date: 2026-10-05 | Branch: `phase-3-auth-owner`

**What I did**
Built owner registration, role-based login redirects, a role middleware, a restaurant policy, and the full owner dashboard (create/edit, quick status change, weekly hours, image manager) with an AI coding agent, and verified everything with tests plus a real browser check.

**What is in it**
- `/owner/register` for owners (public `/register` stays for customers, always role `customer`)
- Login redirects by role: owner → dashboard, admin → placeholder page, customer → home
- `role:owner` / `role:admin` middleware, `RestaurantPolicy` (one restaurant per owner enforced)
- Dashboard with approval status + rejection reason, critical edits send approved restaurants back to `pending`
- Hours editor (7 days, closed checkbox, overnight allowed), images (upload/delete/cover/reorder, max 10, files deleted with rows), coordinates now optional (address text is the location)

**Key decisions I understand and agree with**
- Owner routes bind restaurants by ID while public URLs use slugs.
- Status/verification fields are absent from owner requests, so injected values can never be mass-assigned.
- Image files live on the `public` disk and deleting a cover promotes the next image, keeping exactly one cover.
- Leaflet loads only on the details page, bundled locally — no CDN.
- Filters stay in the URL and pagination links, and the list is eager-loaded (no N+1).


