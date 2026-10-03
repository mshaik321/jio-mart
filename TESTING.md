# Testing

```bash
php artisan test              # full suite
php artisan test tests/Feature/Services/   # one directory
php artisan test --filter=test_name
./vendor/bin/pint --test      # code style check (no changes)
./vendor/bin/pint             # auto-fix
```

74 tests, 235 assertions, all passing as of the last run on this branch. PHPUnit against an in-memory SQLite database (`phpunit.xml`), isolated from the local dev SQLite file and from any MySQL database.

## Suite breakdown

| Directory | What it covers |
|---|---|
| `tests/Feature/Auth/` | Registration, login/logout, password reset, email verification (Breeze defaults, adjusted for soft-deleted users and role-based redirects) |
| `tests/Feature/Services/` | `CheckoutFlowTest` — stock decrement, tax/discount/delivery-charge math, insufficient-stock rejection, free-delivery threshold. `OrderLifecycleTest` — cancel restores stock, can't cancel a delivered order, purchase-order partial/full receipt, return request → complete restores stock |
| `tests/Feature/Storefront/` | Home/category/search/product-detail rendering, cart add/update via Livewire, **full checkout end-to-end**, and three HTTP-level renders for the Livewire full-page routes (see note below) |
| `tests/Feature/Admin/` | One file per admin area: catalog (products+variants+images), inventory+purchasing, orders+coupons+delivery, returns+reviews, users+roles, reports+settings+audit-logs. Every file asserts the permission boundary too (a role without the permission gets 403) |
| `tests/Feature/Api/` | Register/login, public catalog browsing, authenticated cart→checkout→order→cancel round trip, unauthenticated requests rejected |

## A bug class worth knowing about: Livewire full-page layout

`Route::get('/cart', CartPage::class)` — a Livewire component used directly as a route — requires an explicit `#[Layout('layouts.storefront')]` attribute on the component class. Without it, Livewire looks for a hardcoded default layout view it never finds and throws `MissingLayoutException` — a 500 on every real request to that route.

**`Livewire::test(CartPage::class)` does not exercise this code path at all.** It renders the component in isolation and never goes through the "wrap in a page layout" step that only fires on an actual HTTP hit. This is exactly how an earlier version of this branch shipped with 69/69 tests green while `/cart`, `/wishlist`, and `/checkout` all 500'd on a real request — caught only by manually starting `php artisan serve` and curling the routes.

The fix: `tests/Feature/Storefront/BrowsingTest.php` now has `test_cart_page_renders_over_http`, `test_wishlist_page_renders_over_http`, `test_checkout_page_renders_over_http_for_authenticated_customer` — real `$this->get('/cart')` calls, not `Livewire::test()`. **Any new Livewire full-page route needs the same kind of test**, not just a component-level one.

## Factories

`database/factories/*` cover every model that gets created in a test or seeder. Notably `CustomerFactory` creates its own `User` (so `Customer::factory()->create()` works standalone), and `ProductVariantFactory`/`ProductFactory` generate Indian-grocery-plausible price ranges.

## What's not covered

- No browser/JS-interaction tests (Dusk or similar) — Livewire interactivity is tested via `Livewire::test()` component assertions plus the HTTP-level page-render tests described above, not via a real browser driving `wire:click`.
- No load/performance testing.
- No tests for the delivery-agent mobile API (not built yet — see API.md).
