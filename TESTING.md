# Testing

```bash
php artisan test              # full suite
php artisan test tests/Feature/Services/   # one directory
php artisan test --filter=test_name
./vendor/bin/pint --test      # code style check (no changes)
./vendor/bin/pint             # auto-fix
```

130 tests, 2172 assertions, all passing as of the last run on this branch. PHPUnit against an in-memory SQLite database (`phpunit.xml`), isolated from the local dev SQLite file and from any MySQL database.

## Suite breakdown

| Directory | What it covers |
|---|---|
| `tests/Feature/Auth/` | Registration (validation, duplicate email/mobile, mobile normalisation, rate limiting), login/logout (deactivated users, case-insensitive email), password reset, email verification. `GoogleLoginTest` covers the Google redirect (OAuth `state`), new-customer creation, existing-Google-user login, safe linking by verified email, unverified-email/missing-email/staff/deactivated/different-Google-account refusals, cancelled consent, state mismatch and unexpected OAuth errors, using `Socialite::fake()` |
| `tests/Feature/Services/` | `CheckoutFlowTest` — stock decrement, tax/discount/delivery-charge math, insufficient-stock rejection, free-delivery threshold. `OrderLifecycleTest` — cancel restores stock, can't cancel a delivered order, purchase-order partial/full receipt, return request → complete restores stock |
| `tests/Feature/Storefront/` | Home/category/search/product-detail rendering (images, both names, unit, price, discount, stock states, Telugu search, inactive product/category 404s), the add-to-cart quantity stepper and stock limits, guest-cart merge on login, English/Telugu UI and catalog switching (`LocalizationTest`), **full checkout end-to-end**, and three HTTP-level renders for the Livewire full-page routes (see note below) |
| `tests/Feature/Seeders/` | `GroceryCatalogSeederTest` — every product has an English name, a Telugu name/description and a bundled 600×600 WebP image; required staples and their Telugu names; re-seeding is idempotent and never overwrites admin edits or resurrects deleted products; opening stock goes through the inventory ledger; production skips opening stock unless enabled |
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
