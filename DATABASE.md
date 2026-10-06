# Database

43 migrations, MySQL 8 in production / SQLite for local dev and tests. All foreign keys are explicit (`foreignId()->constrained()`), with `restrictOnDelete()`/`nullOnDelete()`/`cascadeOnDelete()` chosen per relationship (see rationale below).

## Entity groups

**Identity & access** — `users`, `customers`, `addresses`, plus Spatie's `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`.

**Catalog** — `categories` (self-referencing `parent_id` for subcategories), `brands`, `units`, `products` (with `sort_order` for merchandising), `product_variants`, `product_images`, `product_translations`, `category_translations`.

**Identity additions** — `users.google_id` (nullable, unique): the Google account ID linked by "Continue with Google". No Google access/refresh tokens are stored.

**Inventory & purchasing** — `stores`, `warehouses`, `inventories` (current snapshot), `inventory_movements` (append-only ledger), `suppliers`, `purchase_orders`, `purchase_order_items`.

**Cart & wishlist** — `carts`, `cart_items`, `wishlist_items`.

**Orders** — `orders`, `order_items`, `order_status_histories`, `payments`, `coupons`, `coupon_usages`.

**Delivery** — `delivery_zones`, `delivery_slots`, `delivery_agents`, `deliveries`.

**Post-sale** — `returns` (model `OrderReturn` — `Return` is a reserved PHP keyword), `return_items`, `reviews`.

**Platform** — `audit_logs`, `settings`, `notifications` (Laravel's standard notifications table).

## Deliberate deviations from a literal reading of section 7

The spec says "modify this structure if your architectural analysis identifies a better design" — these are the places that happened, with the reasoning:

1. **`users` vs `customers` split.** The spec lists both as separate entities without saying how they relate. Decision: `users` is the single authenticatable identity for staff *and* customers (one login/password-reset/email-verification system); `customers` is a 1:1 commerce-profile extension (`user_id` FK, unique) holding `date_of_birth`, `gender`, `referral_code`, `loyalty_points`, `marketing_opt_in`. Every commerce table (`addresses`, `orders`, `carts`, `wishlist_items`, `reviews`, `returns`) FKs to `customer_id`, not `user_id` — an Admin user literally cannot have a cart or an address. See ARCHITECTURE.md.

2. **No `wishlists` header table.** The spec lists `wishlists` and `wishlist_items` as separate tables, mirroring `carts`/`cart_items`. A cart needs a header row (it holds `coupon_id`, and a guest cart needs `session_id` before any items exist). A wishlist has no header-level state — there's nothing to hang off a `wishlists` row that isn't already on each item. `wishlist_items` alone (`customer_id`, `product_variant_id`, unique pair) covers it with one less join on every wishlist read.

3. **No separate `attributes`/EAV tables.** The spec mentions "Attributes" for variants (e.g., Rice 1kg/5kg/10kg). A full EAV schema (attributes, attribute_values, product_attribute_values) is the right call for a catalog with dozens of filterable facets per category; V1's variant differences are packs/sizes. `product_variants.attributes` is a JSON column (`{"pack": "pouch"}` etc.) — enough to describe a variant without three extra tables and the joins to match. If per-category faceted filtering becomes a real requirement, this is the column that gets promoted to real tables.

4. **Address and order-address relationship.** `orders.address_id` is nullable with `nullOnDelete()`, but the order also stores `shipping_recipient_name`, `shipping_phone`, `shipping_line1/2`, `shipping_city`, `shipping_state`, `shipping_pincode` directly. This is intentional duplication, not an oversight: an edited or deleted address must never change what a past order says it shipped to. See "Snapshots" in ARCHITECTURE.md.

5. **Order line items snapshot product data.** `order_items` has its own `product_name`, `sku`, `variant_name`, `unit_price`, `mrp`, `tax_rate` columns alongside nullable `product_id`/`product_variant_id` FKs (`nullOnDelete()`). Same reasoning: a renamed/repriced/deleted product must not rewrite history.

## Soft deletes (section 28)

Applied to exactly the models the spec names — `users`, `categories`, `brands`, `customers`, `suppliers`, `products` — plus two more that needed the same treatment for the same reason: `product_variants` (an order_item's `product_variant_id` must stay resolvable for historical orders even after a variant is discontinued) and `addresses` (same logic — orders reference addresses, don't let a delete break that FK target) and `coupons` (so `coupon_usages`/`orders.coupon_id` keep resolving after a coupon is retired). Transactional tables are explicitly **not** soft-deleted: `orders`, `payments`, `inventory_movements`, `audit_logs` are permanent by design — the spec is explicit that these must "remain historically reliable."

## Inventory ledger invariant

`inventories.quantity_on_hand` only ever changes inside `InventoryService::recordMovement()`, under `lockForUpdate()`, in the same transaction as the `inventory_movements` insert that explains the change. Nothing else writes to that column. `inventory_movements.type` values: `purchase`, `sale`, `sale_cancellation`, `return`, `adjustment`, `damage`, `transfer_in`, `transfer_out` — the model constant is `ReturnStock` (not `Return`) for the same reserved-keyword reason as `OrderReturn`.

## Running migrations

```bash
php artisan migrate          # new environment
php artisan migrate:fresh --seed   # local dev reset (never run against production)
```

`RolesAndPermissionsSeeder`, `AdminUserSeeder`, `OperationsSeeder` (stores/warehouses/suppliers/delivery zones+slots+agents/coupons), `GroceryCatalogSeeder` (bilingual starter catalog), `DemoCustomerSeeder` run in that order from `DatabaseSeeder`.

## Multilingual catalog (English + Telugu, Hindi-ready)

English copy stays on the base columns (`products.name`, `description`, `short_description`; `categories.name`, `description`). Every other language is one row per locale in `product_translations` / `category_translations` (`unique(product_id, locale)`). English is the fallback locale, and order snapshots, admin search and the API's `name` field already read it. A missing or blank translation falls back to English (`App\Models\Concerns\HasTranslations`).

**Adding Hindi** needs no schema change: add `'hi' => 'हिन्दी'` to `config/app.php` → `supported_locales`, add `lang/hi.json` (copy `lang/te.json` keys), and enter Hindi names in the admin product/category forms, which show a field set for every supported locale.

## GroceryCatalogSeeder

Data lives in `database/seeders/data/grocery-catalog.php`: 15 categories and 129 products, each with an English + Telugu name and description, a pack-size variant or two, and indicative demo prices. Prices are not market prices. Records are keyed on stable SKUs (`GRO-ALMOND-001`, variant `GRO-ALMOND-001-250G`) and slugs.

- **Idempotent / non-destructive** — only ever creates missing records. It never updates an existing product, so admin price or name edits survive a re-run. It never resurrects a product or category an admin deleted, and never deletes anything.
- **Images** — `database/seeders/images/catalog/{slug}.webp` (original illustrations made for this project, 600×600, about 12 KB each) are copied to the `public` disk under `catalog/` the first time they're needed.
- **Opening stock** — written through `InventoryService::recordMovement()` as an `adjustment` movement, only for variants with no stock record yet. Dried Cranberries is deliberately sold out and Pistachios/Cheese Slices are low, to demo the stock states.
- **Environment aware** — in production, opening stock is skipped unless `CATALOG_SEED_OPENING_STOCK=true` (production stock should come from purchase orders).
