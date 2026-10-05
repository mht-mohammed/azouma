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
- Date: 2026-10-05 (branch `phase-1-data-layer`)
- What I built: 6 migrations (categories, areas, users.role, restaurants, restaurant_images, opening_hours); 4 PHP enums with Arabic labels; 5 models with relationships, scopes and auto Arabic slugs; 5 factories; 4 seeders (12 fictional Gaza restaurants); 20 new tests.
- Concepts I reviewed (migrations, relationships, factories, seeders):
- Decisions and why: string columns + PHP enums instead of DB enum (works on MySQL and SQLite); slug generated only on create with numeric suffixes so links never break; DatabaseSeeder without WithoutModelEvents so the slug event fires.

(Add one section per phase.)
