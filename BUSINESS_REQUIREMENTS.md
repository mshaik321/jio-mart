# Business Requirements Checklist

Tracked against the original 52-section specification. ✅ = built and tested, 🟡 = built but partial/simplified, ⛔ = not built. Nothing here is marked done without a passing test or a manual verification exercising it — see TESTING.md for what each claim is backed by.

## Core commerce

| Area | Status | Notes |
|---|---|---|
| Customer registration/login/logout/password reset/email verification | ✅ | Breeze (Livewire/Volt), tested |
| Customer profile, mobile number | ✅ | `users.phone`, profile page |
| OTP login | ⛔ | Architecture allows it (`phone_verified_at` column exists) — no OTP provider integrated |
| Addresses (CRUD, default) | ✅ | Storefront + API |
| Categories (with subcategories) | ✅ | Self-referencing `parent_id` |
| Brands | ✅ | |
| Products, variants, images | ✅ | Image upload with mime/size validation |
| Product search/filter (category, brand, price, stock, sort) | ✅ | MySQL `LIKE`, architected for a future Elasticsearch swap (see ARCHITECTURE.md) |
| Cart (guest + customer, stock-validated) | ✅ | Guest cart merges into customer cart on login |
| Wishlist | ✅ | |
| Coupons (percentage/fixed, min order, max discount, usage limits, dates) | ✅ | |
| Checkout (address → delivery zone/slot → coupon → payment → confirm) | ✅ | Transactional, row-locked, revalidates stock |
| Orders + full status history | ✅ | 10 statuses, snapshot of product/address data at order time |
| Order cancellation (restores stock) | ✅ | |
| Payments | 🟡 | COD fully functional; online gateway is a stub (`PaymentMethod::Online` exists, no real provider — spec says not to use real payment credentials) |
| Returns (request → approve/reject → complete → refund + restock) | ✅ | |
| Reviews (rating, verified-purchase detection, moderation) | ✅ | |

## Inventory & purchasing

| Area | Status | Notes |
|---|---|---|
| Stores, warehouses | ✅ | Single active warehouse assumed in V1 business logic (schema supports more) |
| Inventory (on-hand/reserved, low-stock threshold) | ✅ | |
| Inventory movements (append-only ledger) | ✅ | Every stock change — sale, cancellation, purchase, return, adjustment — goes through one service |
| Suppliers | ✅ | |
| Purchase orders (draft→ordered→partial→received, multi-item) | ✅ | |

## Delivery

| Area | Status | Notes |
|---|---|---|
| Delivery zones (pincode-based charge + free-delivery threshold) | ✅ | |
| Delivery slots | ✅ | |
| Delivery agents | ✅ | Admin CRUD + assignment |
| Delivery status workflow | ✅ | Ready→Assigned→PickedUp→OutForDelivery→Delivered/Failed |
| GPS live tracking | ⛔ | Columns exist (`current_lat/lng`); no live-update endpoint |
| Delivery-agent mobile API (login, assigned orders, accept/pickup/OTP/location) | ⛔ | Not built — web admin covers assignment/status today |

## Admin panel

| Module | Status |
|---|---|
| Dashboard (today's orders/sales, pending/delivered/cancelled, low stock, revenue) | ✅ |
| Categories, Brands, Products+Variants+Images, Inventory, Suppliers, Purchases | ✅ |
| Orders, Payments (view), Coupons, Delivery (zones/slots/agents/assignment) | ✅ |
| Returns, Reviews moderation | ✅ |
| Users, Roles & Permissions | ✅ |
| Reports (sales by day, revenue/discount/tax totals, status breakdown, top products, low stock) | 🟡 | One consolidated report page with date-range filtering, not 11 separate report types (Sales/Orders/Product/Customer/Inventory/Purchase/Profit/Discount/Tax/Returns/Payment) as individually listed in spec §26. The numbers themselves are all derivable from what's shown; dedicated pages per type weren't built. |
| Settings | ✅ | Key-value (`settings` table), covers site name/support contact/default tax/low-stock default |
| Audit logs | ✅ | Logs the exact actions spec §27 names as examples: product created/updated/deleted, inventory adjusted, order status changed, coupon created, user permissions changed. Not instrumented on every possible admin action. |

## REST API

✅ `/api/v1` — auth, catalog, cart, wishlist, addresses, checkout, orders, profile, delivery zones/slots. See API.md for the full endpoint list and what's deliberately not built (delivery-agent endpoints, payment webhooks).

## Security (§31, §48)

✅ CSRF (Laravel default on web routes), authentication, Spatie-permission-backed authorization, request validation on every mutating endpoint, login rate-limiting (Breeze default, 5 attempts), bcrypt password hashing, `#[Fillable]` mass-assignment allowlists on every model (a test caught and fixed three models that were missing it), Eloquent/query-builder throughout (no raw SQL string interpolation), Blade auto-escaping (no `{!! !!}` anywhere in the codebase), file upload restricted to image mime types + 2MB cap. `.env` is gitignored; no secrets committed.

🟡 Not independently verified: SQL injection/XSS penetration testing beyond code review, and a dedicated `security-review` pass (available as a skill in this environment but not run this session).

## Testing (§33)

✅ 74 automated tests, real passing suite (not claimed without running it — see TESTING.md). Laravel Pint applied. No static analysis tool (PHPStan/Larastan) configured or run.

## Documentation (§35)

✅ README, ARCHITECTURE, DATABASE, API, DEPLOYMENT, BUSINESS_REQUIREMENTS (this file), TESTING.

## Deployment (§37–§49)

See DEPLOYMENT.md. Summary: the application is fully built, tested, and runnable locally. **No deployment to `shop.allsoft.online` has happened** — this environment has no DNS/hosting/SSH/FTP credentials for that domain. DEPLOYMENT.md lists exactly what's needed to complete it.

## Explicitly out of scope / not attempted

- Android app, iOS app, delivery app (the API is built *for* them per the spec's architecture diagram; the client apps themselves were never asked for)
- Real payment gateway integration
- Real SMS/push notification provider integration
- Elasticsearch/Solr (architected for, not implemented — see ARCHITECTURE.md)
