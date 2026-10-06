# GroceryHub

A grocery e-commerce marketplace built with Laravel 13, Blade, Livewire, Tailwind CSS, and MySQL/SQLite. Original branding, UI, database design, and source code — not affiliated with or copying any existing commercial grocery platform.

**Live:** https://shop.allsoft.online · **Admin:** https://shop.allsoft.online/admin · **API:** https://shop.allsoft.online/api/v1

## Stack

- PHP 8.3, Laravel 13.34
- MySQL 8 in production, SQLite for local development/tests
- Blade + Livewire 3 (Volt for auth) + Tailwind CSS + Alpine.js
- Laravel Sanctum (REST API token auth)
- Laravel Socialite ("Continue with Google" sign-in)
- English + తెలుగు (Telugu) storefront, with a schema ready for more languages
- Spatie laravel-permission (RBAC)

## Quick start (local development)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build   # or `npm run dev` while developing
php artisan serve
```

Visit `http://127.0.0.1:8000`. The seeder prints a demo admin login (or set `ADMIN_EMAIL`/`ADMIN_PASSWORD` in `.env` before seeding to control it). A demo customer account is also seeded: `customer@groceryhub.test` / `password`.

The seed includes a 129-product starter grocery catalog (15 categories, English + Telugu names, a bundled image per product). Its prices are indicative demo values, not market prices. To add just the catalog to an existing database: `php artisan db:seed --class=GroceryCatalogSeeder` (safe to re-run; see DATABASE.md).

"Continue with Google" needs `GOOGLE_CLIENT_ID` / `GOOGLE_CLIENT_SECRET` in `.env`. Without them the button explains Google sign-in is unavailable. See DEPLOYMENT.md → Google sign-in.

- Storefront: `/`
- Admin panel: `/admin` (staff roles only)
- REST API: `/api/v1`

## Running tests

```bash
php artisan test
```

130 feature tests cover services (checkout/inventory/returns/purchasing), registration/login/Google sign-in, the storefront and its Telugu localization, the catalog seeder, the full admin panel, and the REST API. See [TESTING.md](TESTING.md).

## Documentation

- [ARCHITECTURE.md](ARCHITECTURE.md) — layers, design decisions, Android-readiness
- [DATABASE.md](DATABASE.md) — schema, relationships, deliberate deviations from a literal reading of the spec
- [API.md](API.md) — REST API v1 reference
- [DEPLOYMENT.md](DEPLOYMENT.md) — production deployment runbook and current status
- [BUSINESS_REQUIREMENTS.md](BUSINESS_REQUIREMENTS.md) — feature checklist against the original spec
- [TESTING.md](TESTING.md) — test suite breakdown and how to extend it

## Project structure

```
app/
  Enums/            Status/type enums (OrderStatus, PaymentMethod, ...)
  Http/Controllers/
    Storefront/      Customer-facing controllers
    Admin/            Admin panel controllers
    Api/V1/           REST API controllers
  Livewire/Storefront/  Interactive cart/wishlist/checkout components
  Models/             Eloquent models
  Policies/            Customer-owned-resource authorization
  Services/            Business logic (Cart, Checkout, Inventory, Order,
                        Purchase, Return, Review, Coupon, AuditLog, GoogleAuth)
lang/te.json, lang/te/  Telugu UI strings (English is the fallback locale)
database/seeders/data/grocery-catalog.php   bilingual starter catalog
database/seeders/images/catalog/            one 600x600 WebP per product
resources/views/
  storefront/, admin/, livewire/storefront/
routes/
  web.php    storefront + account routes
  admin.php  /admin routes (auth + staff middleware)
  api.php    /api/v1 routes (Sanctum)
```

Business logic lives in `app/Services`, not in controllers — the same services back the Blade/Livewire storefront, the admin panel, and the REST API, so mobile clients built against the API never duplicate rules already enforced server-side.
