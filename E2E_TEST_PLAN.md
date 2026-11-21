# GroceryHub — End-to-End Test Plan (Playwright)

Pre-handover acceptance test plan. Every case below was derived from the actual routes, controllers, validation rules, seeders and business docs in this repo (`routes/*.php`, `app/Http/Controllers`, `app/Livewire`, `app/Services`, `database/seeders`, `BUSINESS_REQUIREMENTS.md`).

- **Scope:** web storefront, admin panel, REST API (`/api/v1`).
- **Out of scope (not built, per BUSINESS_REQUIREMENTS.md):** OTP login, real online payment gateway, SMS/push, GPS live tracking, delivery-agent mobile API, mobile apps.
- **Disabled for now:** "Continue with Google" sign-in (buttons and `/auth/google/*` routes removed). See section 14.

---

## 1. Environment and setup

| Item | Value |
|---|---|
| Base URL | `http://127.0.0.1:8080` (port 8000 may be taken by other apps; use `php artisan serve --port=8080`) |
| Stack | Laravel 13, PHP 8.3, Livewire 3 / Volt, Tailwind, SQLite locally |
| Mail | `MAIL_MAILER=log`: no real email is sent. Verification and reset links are written to `storage/logs/laravel.log` |
| Languages | English (`en`) and Telugu (`te`) |
| Images | Served from `public/storage` (requires `php artisan storage:link`) |

**Before each full run (clean, repeatable data):**

```bash
export PATH=/opt/homebrew/opt/php@8.3/bin:$PATH
ADMIN_EMAIL=admin@groceryhub.test ADMIN_PASSWORD='Admin#Test123' php artisan migrate:fresh --seed --force
php artisan storage:link
php artisan serve --port=8080
```

Set `ADMIN_PASSWORD` explicitly so the tests know it. Otherwise the seeder generates a random one.

### Seed data the tests rely on

| Data | Value |
|---|---|
| Super Admin | `admin@groceryhub.test` / the `ADMIN_PASSWORD` set above |
| Demo customer | `customer@groceryhub.test` / `password` (mobile `9876500000`, default address "Home", pincode `110001`) |
| Catalog | 15 categories, 129 products, English + Telugu names, WebP images |
| Delivery zone A | "City Zone A", pincodes `110001, 110002, 110003`, charge ₹0, free above ₹299 |
| Delivery zone B | "City Zone B", pincodes `110011, 110012, 110013`, charge ₹25, free above ₹499 |
| Delivery slots | 9 AM-12 PM, 12 PM-3 PM, 6 PM-9 PM (max 100 orders each) |
| Coupon `WELCOME10` | 10% off, min order ₹199, max discount ₹100, 1 use per customer |
| Coupon `FLAT50` | ₹50 off, min order ₹499, 2 uses per customer |
| Staff roles | Super Admin, Admin, Store Manager, Inventory Manager, Order Manager, Delivery Manager, Delivery Agent, Customer |
| Unserviceable pincode | any other value, e.g. `560001` |

### Suggested Playwright setup

```
e2e/
  playwright.config.ts      # baseURL, projects: chromium, firefox, webkit, mobile-chrome
  fixtures/auth.ts          # storageState for customer + admin (login once, reuse)
  helpers/mail.ts           # read verification/reset links from storage/logs/laravel.log
  helpers/db.ts             # call `php artisan migrate:fresh --seed` between suites
  tests/
    01-storefront.spec.ts   02-auth.spec.ts   03-cart-wishlist.spec.ts
    04-checkout.spec.ts     05-orders.spec.ts 06-account.spec.ts
    07-localization.spec.ts 08-admin-*.spec.ts 09-api.spec.ts
    10-security.spec.ts     11-ui-quality.spec.ts
```

- Use `workers: 1` for suites that mutate stock or orders. Checkout and admin tests share one warehouse and one inventory.
- Livewire pages update through network calls. Wait on visible state (`expect(locator).toBeVisible()`), never on fixed sleeps.
- Use the Playwright `request` fixture for API tests (section 11).
- The selectors in this plan are described by visible text or role. Confirm them against the rendered pages when writing the specs.

---

## 2. Storefront browsing (guest)

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| SF-01 | Home page loads | Open `/` | 200. Header (logo, search, language toggle, wishlist, cart, login), category nav and product sections render. No console errors |
| SF-02 | **Images load everywhere** | Visit `/`, a category, a product, `/search?q=rice` | Every `<img>` has `naturalWidth > 0`. No broken-image icons. All `/storage/catalog/...` requests return 200. *(Regression for the missing storage-link bug)* |
| SF-03 | Category navigation | Click each of the 15 categories in the nav bar | Each opens `/categories/{slug}` with a heading, English and Telugu name, product count and grid. Active category is highlighted |
| SF-04 | Category page content | Open "Nuts" | Shows 7 products, each with image, name (EN + TE), unit weight, price, struck-through MRP, "% OFF" badge, stock label, Add to Cart |
| SF-05 | Price filter | On a category page set Min `100`, Max `300`, click Apply Filters | URL carries `min_price`/`max_price`. Only products in range remain. Count updates |
| SF-06 | In-stock filter | Tick "In stock only", apply | URL has `in_stock=1`. No out-of-stock items shown |
| SF-07 | Sorting | Sort by price low-high, high-low, newest | Order matches `price_asc`, `price_desc`, `newest`. Verify the sequence of prices |
| SF-08 | Search (English) | Search "rice" | `/search?q=rice` lists matching products only. Filters and sort also work here |
| SF-09 | Search (Telugu) | Search a Telugu product name (e.g. `బాదం`) | Matches the right product |
| SF-10 | Search, no results | Search `zzzzqq` | Friendly empty state, no error |
| SF-11 | Product detail | Open a product | Image, EN + TE name, description, unit, price, MRP, discount, stock state, Add to Cart, wishlist heart, reviews section |
| SF-12 | Inactive/unknown slug | Open `/products/does-not-exist` and an inactive product or category | 404 page, no stack trace |
| SF-13 | 404 and error pages | Open `/nope` | Branded 404, no debug output |

---

## 3. Registration, login, logout, account security

Validation rules come from `pages/auth/register.blade.php` and the auth tests.

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| AU-01 | Register successfully | `/register`: name `Test User`, unique email, mobile `9123456780`, password + matching confirmation | Account created and logged in. Redirected to the email-verification notice |
| AU-02 | Email verification | Read the verification link from `laravel.log`, open it | Account becomes verified. Verified-only pages (checkout, orders) become accessible |
| AU-03 | Unverified user is blocked | Log in as an unverified user, open `/checkout` and `/orders` | Redirected to the verify-email notice. "Resend" works |
| AU-04 | Duplicate email | Register with `customer@groceryhub.test` | Error: "An account with this email already exists. Try logging in instead." |
| AU-05 | Duplicate mobile | Register with `9876500000` | Error: "This mobile number is already registered." |
| AU-06 | Invalid mobile | Use `12345`, `abcdefghij`, and a 9-digit number | Error: "Enter a valid 10-digit Indian mobile number." |
| AU-07 | Mobile normalisation | Use `+91 98765 43210` or a spaced format | Accepted and stored as a normalised 10-digit number. Duplicate check uses the normalised value |
| AU-08 | Password rules | Short password (<8), mismatched confirmation | Inline validation errors, no account created |
| AU-09 | Required fields / name length | Empty name, 1-character name | Validation errors |
| AU-10 | Registration rate limit | Submit many registrations quickly from one IP | Throttled with a clear message |
| AU-11 | Login (customer) | Log in with `customer@groceryhub.test` / `password` | Lands on the storefront or intended page. Header shows My Account and Log out |
| AU-12 | Login, bad credentials | Wrong password and unknown email | Generic failure message. No hint about which part was wrong |
| AU-13 | Login, case-insensitive email | Log in with `CUSTOMER@GroceryHub.test` | Succeeds |
| AU-14 | Login rate limit | 6 wrong attempts in a row | Locked out after 5 with a "try again in N seconds" message |
| AU-15 | Deactivated user | Admin deactivates a user (see AD-USER). That user then logs in | Login refused |
| AU-16 | Logout | Click Log out | Session ended. Protected URLs redirect to `/login` |
| AU-17 | Intended-URL redirect | While logged out open `/orders`, log in | Returned to `/orders` after login |
| AU-18 | Forgot / reset password | `/forgot-password`, request a link, take it from `laravel.log`, set a new password | Can log in with the new password only |
| AU-19 | Profile update | `/profile`: change name, email, mobile, password | Changes persist. Changing email re-triggers verification |
| AU-20 | Staff vs customer areas | Customer opens `/admin`. Staff opens `/checkout` | Customer gets 403 on `/admin`. Staff without a customer profile is handled (profile is created on first use) |
| AU-21 | Google sign-in is gone | Open `/login` and `/register`. Open `/auth/google/redirect` | No "Continue with Google" button anywhere. The route returns 404 |

---

## 4. Cart and wishlist

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| CW-01 | Add to cart (guest) | From a product card click Add to Cart | Cart badge increments without a page reload. Stepper (+ / -) replaces the button |
| CW-02 | Quantity stepper | Press + and - on a card and on the product page | Quantity changes. At 0 the item is removed. Badge matches |
| CW-03 | Stock limit | Increase quantity past available stock (and past `max_order_qty` if set) | Blocked with a stock message. Quantity does not exceed the limit |
| CW-04 | Minimum order quantity | Use a product with `min_order_qty` > 1 | Enforced as configured |
| CW-05 | Cart page | Open `/cart` | Lists items with image, name, unit, price, line total. Subtotal, tax, discount and total are correct |
| CW-06 | Update / remove in cart | Change quantity, remove an item, empty the cart | Totals recalculate live. Empty state shown |
| CW-07 | Cart persists | Add items, reload the page, open a new tab in the same session | Cart contents unchanged |
| CW-08 | **Guest cart merges on login** | As guest add items, then log in as the demo customer | Guest items appear in the customer's cart. Items already in the cart are merged, not lost |
| CW-09 | Coupon needs login | As guest, apply `WELCOME10` | "Please log in to apply a coupon." |
| CW-10 | Apply valid coupon | Logged in, subtotal ≥ ₹199, apply `WELCOME10` | Discount = 10%, capped at ₹100. Shown in totals. Removable |
| CW-11 | Coupon rejected | Invalid code (`BADCODE`); `WELCOME10` below ₹199; `FLAT50` below ₹499 | Specific error each time (invalid / minimum order not met) |
| CW-12 | Coupon is case/space tolerant | Enter ` welcome10 ` | Applied as `WELCOME10` |
| CW-13 | Fixed coupon | Subtotal ≥ ₹499, apply `FLAT50` | ₹50 off |
| CW-14 | Remove coupon | Click remove | Total returns to undiscounted |
| CW-15 | Wishlist (guest) | Click a heart on a product, open `/wishlist` | Item appears. Heart state is consistent across pages |
| CW-16 | Wishlist (logged in) | Add and remove items. Log out and back in | Wishlist persists for the account |
| CW-17 | Move wishlist item to cart | From `/wishlist` add an item to the cart | Item is in the cart |

---

## 5. Checkout (critical path)

Preconditions: logged in and verified as the demo customer, at least one item in the cart.

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| CO-01 | Checkout requires login | As guest open `/checkout` | Redirected to `/login`. After login returns to checkout |
| CO-02 | Checkout page | Open `/checkout` | Default address preselected. Delivery slots listed. Payment defaults to Cash on Delivery. Totals shown with delivery charge |
| CO-03 | **Happy path, COD, zone A** | Cart total ≥ ₹299, address pincode `110001`, choose a slot, Place order | Redirected to `/orders/{number}/confirmation`. Order number shown. Cart now empty. Cart badge reset |
| CO-04 | Delivery charge, zone A | Pincode `110001`, any amount | Delivery ₹0 |
| CO-05 | Delivery charge, zone B (below threshold) | Add an address with pincode `110011`, subtotal < ₹499 | Delivery ₹25 added to the total |
| CO-06 | Free delivery, zone B | Subtotal ≥ ₹499 with `110011` | Delivery ₹0 |
| CO-07 | Unserviceable pincode | Add address with pincode `560001` and select it, Place order | Error: "Sorry, we do not deliver to this pincode yet." No order created |
| CO-08 | No address | Delete all addresses, open checkout | Prompt to add an address. Cannot place an order |
| CO-09 | Coupon at checkout | Apply `WELCOME10` in the cart, then check out | Discount reflected in the checkout totals and the final order |
| CO-10 | Totals maths | Compare grand total on checkout, confirmation and order detail | `subtotal + tax + delivery − discount` is identical on all three |
| CO-11 | Stock decrements | Note stock in admin Inventory, place an order, check again | `quantity_on_hand` reduced by the ordered quantity. A `sale` movement is recorded |
| CO-12 | Insufficient stock | Add an item, then reduce stock in admin below the cart quantity, Place order | Order rejected with a stock message. Nothing is decremented |
| CO-13 | Double-submit | Click Place order twice quickly | Exactly one order is created |
| CO-14 | Coupon usage limit | Use `WELCOME10` once and re-apply on a second order (limit 1) | Second use rejected |
| CO-15 | Order snapshot | After ordering, rename the product or edit the address in admin/account | The old order still shows the original name, price and address |
| CO-16 | Online payment option | Check the payment choices | Only COD works. If "Online" is visible it must not complete a real payment (stub). Record the behaviour for sign-off |

---

## 6. Orders, cancellation, returns, reviews (customer)

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| OR-01 | Order list | Open `/orders` | Own orders only, newest first, with status and total |
| OR-02 | Order detail | Open an order | Items, address, slot, payment method and status, status history timeline |
| OR-03 | Order isolation | Customer A opens `/orders/{B's order number}` | 403/404. No data leaks |
| OR-04 | Cancel pending order | Cancel an order in Pending, Confirmed or Processing | Status becomes Cancelled. Stock restored (`sale_cancellation` movement). History updated |
| OR-05 | Cancel not allowed | Admin sets the order to Packed or Delivered, customer tries to cancel | Cancel is unavailable or refused with "This order can no longer be cancelled." |
| OR-06 | Return request | On a Delivered order, request a return: pick items, quantity, reason | Return created in "requested" state. Visible to admin |
| OR-07 | Return validation | Empty reason, zero quantity, no item selected | Validation errors |
| OR-08 | Return only when delivered | Try on a non-delivered order | Refused: "Only delivered orders can be returned." |
| OR-09 | Submit review | On a purchased product submit rating 1-5 with title/body | Message: "Thanks for your review! It will appear once approved." Not public yet |
| OR-10 | Review validation | Rating 0 or 6, body over 2000 characters | Validation errors |
| OR-11 | Review requires login | As guest try to submit a review | Redirected to login |
| OR-12 | Review appears after approval | Admin approves (AD-REV), reload the product page | Review visible, marked verified purchase if the customer bought it |

---

## 7. Addresses (customer)

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| AD-01 | List addresses | Open `/addresses` | Shows "Home" (default) |
| AD-02 | Add address | Fill label, name, phone, line1, city, state, pincode, mark default | Saved. Appears in list and in checkout |
| AD-03 | Address validation | Leave required fields empty. Pincode over 10 characters | Errors per field |
| AD-04 | Edit address | Change line1 and pincode | Updated everywhere |
| AD-05 | Default switching | Mark another address as default | Only one default at a time |
| AD-06 | Delete address | Delete a non-default address | Removed. Past orders unaffected (snapshot) |
| AD-07 | Address isolation | Customer opens another customer's `/addresses/{id}/edit` | 403/404 |

---

## 8. Localization

| ID | Use case | Steps | Expected result |
|---|---|---|---|
| LC-01 | Switch to Telugu | Click `తెలుగు` in the header | UI strings switch (nav, buttons, login, cart). Product cards show Telugu name first, English beneath |
| LC-02 | Persists in session | Navigate several pages, reload | Language stays Telugu |
| LC-03 | Switch back | Click `English` | Returns to English |
| LC-04 | Telugu on forms | In Telugu, submit invalid login and registration | Validation messages appear in Telugu |
| LC-05 | Telugu rendering | Check Telugu text on cards, product page, cart | No broken glyphs, no overflow or clipping |
| LC-06 | Invalid locale | Open `/language/xx` | Falls back safely, no error |

---

## 9. Admin panel

Log in at `/login` as the admin, then open `/admin`. Verify the dashboard first, then each module for list, create, edit, delete, validation, and permission boundaries.

| ID | Module | Use cases and expected results |
|---|---|---|
| AD-DASH | Dashboard `/admin` | Today's orders and sales, pending / delivered / cancelled counts, low-stock list, revenue. Numbers change after a new order is placed |
| AD-CAT | Categories | Create (with parent, EN + TE name, image), edit, delete. Appears or disappears in the storefront nav. Duplicate or empty name is rejected |
| AD-BRAND | Brands | Create, edit, delete |
| AD-PROD | Products | Create with all required fields (`category`, `unit`, `sku` unique, `name`, `mrp`, `selling_price`, `status`). Draft/inactive products are hidden from the storefront. Validation: duplicate SKU, negative price, `tax_rate` over 100, status outside draft/active/inactive. Telugu translation saved and shown |
| AD-VAR | Variants | Add, edit, delete a variant on a product. Price and stock reflect on the storefront |
| AD-IMG | Product images | Upload a valid image. Upload a non-image or a file over 2MB and it is rejected. Set primary. Delete image. The storefront image updates |
| AD-SUP | Suppliers | Full CRUD |
| AD-INV | Inventory | List stock with low-stock flag. Adjust by +N / −N with a note. A `0` adjustment is rejected. A movement row appears in the ledger. The storefront stock label changes |
| AD-PO | Purchase orders | Create (supplier, warehouse, date, 1+ items). Receive partially (status "partial", stock +N). Receive the rest (status "received"). Delete a draft. A movement row exists per receipt |
| AD-ORD | Orders | Filter and search. Open an order. Change status through the workflow. Each change adds a history entry visible to the customer. Cancel from admin restores stock |
| AD-DEL | Delivery | Assign an agent to an order. Move through Ready → Assigned → PickedUp → OutForDelivery → Delivered, and test Failed. Customer sees the updated status |
| AD-COUP | Coupons | Create percentage and fixed coupons. Validation: duplicate code, negative value. Test dates, usage limits, min order. Verify the coupon works on the storefront cart. Deactivate and confirm it is rejected |
| AD-ZONE | Delivery zones | Create or edit a zone. Pincodes entered as a string are parsed. Charge and free-delivery threshold are applied at checkout |
| AD-SLOT | Delivery slots | CRUD. Inactive slots do not show at checkout |
| AD-AGENT | Delivery agents | CRUD and assignment |
| AD-RET | Returns | Approve, reject, complete with a refund amount. Completion restocks inventory and marks the order refunded. Customer sees the outcome |
| AD-REV | Reviews | Approve and reject. Only approved reviews appear on the storefront |
| AD-USER | Users | Create, edit, deactivate a user. Assign a role |
| AD-ROLE | Roles & permissions | Edit a role's permissions. The change takes effect on next request |
| AD-REP | Reports | Date-range filter. Sales by day, revenue / discount / tax totals, status breakdown, top products, low stock. Cross-check totals against known orders |
| AD-SET | Settings | Update site name, support contact, default tax, low-stock default. Values persist |
| AD-AUD | Audit logs | Product created / updated / deleted, inventory adjusted, order status changed, coupon created and permission change each produce a log entry with the acting user |

**Role-based access (RBAC)** — for each staff role below, log in and confirm allowed pages load and disallowed pages return **403**:

| ID | Role | Expected |
|---|---|---|
| RB-01 | Super Admin | Everything |
| RB-02 | Admin | Per the permission matrix in `RolesAndPermissionsSeeder.php` |
| RB-03 | Inventory Manager | Inventory and purchasing yes. Users, roles and settings no |
| RB-04 | Order Manager | Orders yes. Catalog editing no |
| RB-05 | Delivery Manager | Delivery pages yes. Others no |
| RB-06 | Customer | No admin pages (403) |
| RB-07 | Guest | `/admin/*` redirects to `/login` |

---

## 10. End-to-end business journeys

Run these as single continuous scenarios. They catch integration faults the single-page tests miss.

| ID | Journey |
|---|---|
| E2E-01 | **Guest to delivered order.** Browse → search → add items → register → verify email → add address (`110001`) → apply `WELCOME10` → checkout COD → see confirmation → admin confirms → packs → assigns agent → out for delivery → delivered → customer sees "Delivered" → customer leaves a review → admin approves → review is public |
| E2E-02 | **Cancellation restores stock.** Record stock → place an order → stock drops → customer cancels → stock restored → ledger shows both movements |
| E2E-03 | **Return and refund.** Deliver an order → customer requests a return → admin approves → completes with refund → stock restored → order shows refunded |
| E2E-04 | **Restock via purchase order.** Product reaches low stock → admin creates a PO → receives it partially, then fully → storefront shows back in stock and the customer can buy |
| E2E-05 | **Out-of-stock behaviour.** Set stock to 0 in admin → storefront shows out of stock and blocks Add to Cart → restock → it can be bought again |
| E2E-06 | **Catalog change visibility.** Admin creates category + product with image and Telugu name → appears in nav, category, search → customer buys it → admin deactivates → disappears from storefront, old order intact |
| E2E-07 | **Zone pricing.** Same cart delivered to `110001` (₹0), `110011` (₹25 or free ≥ ₹499), `560001` (blocked) |
| E2E-08 | **Telugu customer journey.** Complete a purchase entirely with the UI in Telugu |
| E2E-09 | **Concurrency.** Two browser contexts buy the last unit at the same time → exactly one succeeds, stock never goes negative |

---

## 11. REST API (`/api/v1`) — Playwright `request` fixture

Use JSON headers (`Accept: application/json`). Sanctum bearer token for authenticated routes.

| ID | Endpoint | Cases |
|---|---|---|
| API-01 | `POST auth/register`, `POST auth/login` | Success returns a token. Validation errors return 422 with field messages. Bad credentials return 401/422 |
| API-02 | `GET categories`, `categories/{slug}` | List and detail. Unknown slug returns 404 |
| API-03 | `GET products`, `products/{slug}` | Pagination, filters, detail. Inactive product returns 404 |
| API-04 | `GET delivery-zones`, `delivery-slots` | Public. Matches the seed data |
| API-05 | Auth required | Without a token, `cart`, `wishlist`, `addresses`, `checkout`, `orders`, `profile` all return 401 |
| API-06 | `GET/PUT profile` | Read and update. Validation on bad input |
| API-07 | Cart: `GET cart`, `POST cart/items`, `PUT/DELETE cart/items/{variant}` | Add, update, remove. Stock limit returns 422 |
| API-08 | Wishlist: `GET`, `POST/DELETE wishlist/{variant}` | Add, list, remove |
| API-09 | Addresses `apiResource` | CRUD. Another user's address returns 403/404 |
| API-10 | `POST checkout` | Full path: cart → order. Totals equal the web checkout for the same cart. Unserviceable pincode and empty cart are rejected |
| API-11 | Orders: `GET orders`, `orders/{order}`, `POST orders/{order}/cancel` | Own orders only. Cancel works only for cancellable states |
| API-12 | `POST auth/logout` | Token is revoked and the next call returns 401 |
| API-13 | `Accept-Language: te` | Catalog and messages come back in Telugu |
| API-14 | Staff token on customer API | Behaviour documented (the `customer` middleware applies) |

---

## 12. Security checks

| ID | Check | Expected |
|---|---|---|
| SE-01 | CSRF | POST without the CSRF token (form posts) returns 419 |
| SE-02 | XSS | Put `<script>alert(1)</script>` in name, address, review body, product name (admin) | Rendered escaped, no script executes |
| SE-03 | SQL injection | Search `' OR 1=1 --`, filter params, `sort=;drop` | Returns normally, no error, no extra data |
| SE-04 | IDOR | Access other customers' orders, addresses, returns by changing ids | 403/404 |
| SE-05 | Mass assignment | Add hidden fields (`is_admin`, `role`, `price`) to forms | Ignored |
| SE-06 | Upload safety | Upload `.php`, `.svg` with script, or oversized files as a product image | Rejected |
| SE-07 | Auth throttling | Login and registration throttles (AU-10, AU-14) | Enforced |
| SE-08 | Debug leakage | Trigger errors on web and API with `APP_DEBUG=false` | No stack traces, paths or env values in responses |
| SE-09 | Session | After logout, the back button and old cookie do not give access | Access denied |
| SE-10 | Headers / HTTPS | On the staging/prod URL: HTTPS enforced, cookies `Secure` + `HttpOnly` | As expected |
| SE-11 | Secrets | `/.env`, `/storage/logs/laravel.log`, `/composer.json` are not reachable over HTTP | 403/404 |

---

## 13. UI quality, accessibility and compatibility

| ID | Check | Expected |
|---|---|---|
| UI-01 | Browsers | Chromium, Firefox, WebKit all pass the smoke suite (SF-01, CW-01, CO-03, AD-DASH) |
| UI-02 | Responsive | 360×800, 768×1024, 1440×900. No horizontal scroll. Nav, filters and checkout usable. Cart and wishlist reachable |
| UI-03 | Console and network | No JS errors. No failed requests (4xx/5xx) on any storefront page |
| UI-04 | Accessibility | Run `@axe-core/playwright` on home, category, product, cart, checkout, login. No serious or critical violations. Images have `alt`. Form fields have labels. Keyboard-only checkout works |
| UI-05 | Visual regression | Screenshot compare home, category, product, cart in EN and TE |
| UI-06 | Performance | Home and category load under ~3 s locally. Product images are WebP and sized sensibly |
| UI-07 | Empty states | Empty cart, empty wishlist, no orders, search with no results all show helpful text |
| UI-08 | Flash messages | Success and error messages appear, are readable and clear on the next page |
| UI-09 | Back / refresh | Refresh on the order confirmation page does not create a duplicate order |

---

## 14. Not in scope or known limits (confirm with the customer before handover)

- **Google sign-in** is disabled. The controller, service, migration and config are still in the code and can be re-enabled. Instructions are in comments in `routes/auth.php`. `GoogleLoginTest` is skipped.
- **Online payment** is a stub. Only COD completes.
- **OTP login, SMS/push, GPS tracking, delivery-agent app API** are not built.
- **Email** is `log` only. Real SMTP is needed before go-live (affects verification and password reset in production).
- **Reports** are one consolidated page, not the 11 separate report types in the original spec.
- **Single warehouse** is assumed in V1 checkout, cancellation and purchasing logic.

---

## 15. Exit criteria for handover

- All **Critical** cases pass on Chromium, plus the smoke suite on Firefox and WebKit. Critical = SF-02, AU-01/02/11/16/18, CW-08, CO-03/07/11/12/13, OR-04, E2E-01/02/03, API-10, SE-01/03/04, RB-01/06/07.
- No open defects of severity high or above. Every failure has a ticket.
- Automated suite: `php artisan test` is green (currently 119 passed, 15 skipped). Playwright suite is green on a freshly seeded database.
- Run once against the staging/production URL with `APP_DEBUG=false`. A production deploy, real SMTP and HTTPS are checked.
- Admin credentials are rotated and demo accounts (`customer@groceryhub.test`) are removed or changed before the customer goes live.
- A signed-off results sheet (case ID, pass/fail, tester, date, notes) accompanies the handover.
