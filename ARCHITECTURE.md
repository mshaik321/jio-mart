# Architecture

## Layers

```
                        GroceryHub
                           |
                   Laravel Backend
                    /            \
          Web Application       REST API (/api/v1, Sanctum tokens)
         (Blade + Livewire)            |
                    \          +-------+-------+
                     \         |               |
                      \   Android App    Delivery App
                       \  (future)        (future)
                        \_______________________/
                                   |
                          app/Services/*
                     (Cart, Checkout, Inventory,
                      Order, Purchase, Return,
                      Review, Coupon, AuditLog)
                                   |
                            Eloquent Models
                                   |
                              MySQL / SQLite
```

The storefront, the admin panel, and the REST API are three separate presentation layers over the **same** service layer. No business rule is implemented twice. A future Android or delivery-agent app talks to `/api/v1` and gets the identical cart/checkout/inventory/order behavior the web storefront has, because the API controllers call the same `App\Services\*` classes as the Livewire components.

## Why services, not fat controllers

Controllers (web, admin, API) do three things only: authorize, validate, call a service. All of the actual business rules — stock checks, discount math, order-number generation, status-transition legality, inventory ledger writes — live in `app/Services`. This is what lets three different front doors (Blade pages, Livewire components, JSON API) share one set of rules without copy-paste.

| Service | Responsibility |
|---|---|
| `CartService` | Add/update/remove cart items, guest-cart session scoping, guest→customer cart merge on login, totals |
| `CouponService` | Coupon validation and discount calculation |
| `CheckoutService` | The critical path: validates stock/coupon under a DB transaction with row locks, snapshots order/item data, decrements stock, records payment/status-history |
| `InventoryService` | The **only** writer of stock. Every change — sale, cancellation, purchase receipt, return, manual adjustment — goes through `recordMovement()`, which writes an append-only `inventory_movements` row and the `inventories.quantity_on_hand` snapshot together, under a row lock |
| `OrderService` | Status transitions, cancellation (restores stock via `InventoryService`) |
| `PurchaseService` | Receiving stock against a purchase order (partial/full), updates PO status |
| `ReturnService` | Return request → approve/reject → complete (restores stock, marks refunded) |
| `ReviewService` | Review submission with verified-purchase detection, moderation |
| `AuditLogService` | Records admin actions the spec calls out by name: product created/updated/deleted, inventory adjusted, order status changed, coupon created, user permissions changed |

## Why `Customer` is separate from `User`

`users` is the single authenticatable table for **both** staff and customers — one login system, one password reset flow, one email-verification flow, Spatie roles distinguish "Admin" from "Customer." `customers` is a 1:1 extension holding commerce-only data (date of birth, gender, loyalty points, referral code) and is the FK target for addresses/orders/cart/wishlist/reviews/returns.

This was a judgment call, not a literal implementation of the spec's flat `users` + `customers` table list — it keeps staff accounts out of commerce tables entirely (an `Admin` user never has an address or a cart) while still sharing one auth system. See [DATABASE.md](DATABASE.md) for the full reasoning and the handful of other deliberate deviations.

## Single-warehouse assumption (V1)

`CheckoutService`, `OrderService::cancel()`, and `PurchaseService` all resolve `Warehouse::where('is_active', true)->firstOrFail()` — V1 ships with one fulfillment warehouse. The schema already supports multiple warehouses (inventory is keyed by `warehouse_id` + `product_variant_id`), so adding warehouse selection later is a routing/business-rule change, not a schema migration.

## Search (V1 vs. future)

Product search/filter (`app/Support/ProductFilters.php`) runs as plain MySQL `WHERE`/`LIKE` queries against `product_variants` joined to `products`. The spec explicitly asks for this to be architected so Elasticsearch/Solr could replace it later: `ProductFilters::apply()` is the single seam a future search backend would swap into, and `SearchController`/`CategoryController` don't know or care how the match happened.

## RBAC

Roles and granular permissions via `spatie/laravel-permission`. Seeded roles: Super Admin, Admin, Store Manager, Inventory Manager, Order Manager, Delivery Manager, Delivery Agent, Customer (see `database/seeders/RolesAndPermissionsSeeder.php` for the exact permission matrix). `$this->authorize('products.view')` works directly in controllers with no per-model Policy needed, because Spatie registers a `Gate::before` callback that treats any ability name matching a permission name as a permission check. Policies (`app/Policies`) exist only for **customer-owned-resource** checks that permissions can't express — "is this *your* order/address/review" (`OrderPolicy`, `AddressPolicy`, `ReviewPolicy`, `OrderReturnPolicy`).

## Inventory is a ledger, not a counter

`inventories.quantity_on_hand` is a cached snapshot; `inventory_movements` is the source of truth and is **append-only** — nothing ever updates or deletes a movement row. Every stock change writes a movement under `DB::transaction()` with `lockForUpdate()` on the inventory row, so concurrent sales/returns/purchases can't desync the balance. This is what makes `OrderLifecycleTest` (cancel restores stock, partial PO receipt increments it correctly, return restores it) possible to assert with confidence.

## Snapshots for historical integrity

`order_items` stores `product_name`, `sku`, `variant_name`, `unit_price`, `mrp`, `tax_rate` at the moment of purchase — not FKs resolved at render time. `orders` stores the shipping address fields directly (`shipping_recipient_name`, `shipping_line1`, etc.) alongside a nullable `address_id`. If a product is renamed, repriced, or deleted, or an address is edited, past orders still show exactly what the customer saw and paid at checkout.

## Deferred/stubbed by design (not forgotten)

- **Online payments**: `payments.provider` and `PaymentMethod::Online` exist; there is no real gateway integration (Razorpay etc.) — COD is the only functioning payment method. No payment credentials exist to integrate against, and the spec explicitly says "do not use real payment credentials."
- **SMS/push notifications**: database notifications only (`notifications` table, Laravel's notification system). Email/SMS/push channels are not wired to a real provider.
- **GPS live tracking**: `delivery_agents.current_lat/lng` columns exist; there's no live location-update endpoint yet. The API has the shape a future endpoint would fill in.
