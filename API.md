# REST API v1

Base URL: `/api/v1`. Auth via Laravel Sanctum personal access tokens (`Authorization: Bearer <token>`). Built for a future Android customer app and delivery app — every endpoint calls the same `app/Services/*` classes the web storefront uses, so there is exactly one implementation of cart/checkout/inventory/order rules.

JSON responses from `Illuminate\Http\Resources\Json\JsonResource` (Product, Category, Cart, Order, Address) are wrapped in a `data` envelope — `{"data": {...}}` for a single resource, `{"data": [...], "links": {...}, "meta": {...}}` for a paginated collection. Plain `response()->json(...)` endpoints (auth, profile, wishlist, delivery) are not wrapped.

## Authentication

| Method | Endpoint | Auth | Body |
|---|---|---|---|
| POST | `/auth/register` | — | `name, email, phone?, password` → `201 {token, user}` |
| POST | `/auth/login` | — | `email, password` → `200 {token, user}` |
| POST | `/auth/logout` | token | revokes the current token |

Registering creates a `Customer` profile and assigns the `Customer` role automatically.

## Catalog (public)

| Method | Endpoint | Notes |
|---|---|---|
| GET | `/categories` | active top-level + child categories |
| GET | `/categories/{slug}` | |
| GET | `/products?q=&category_id=&per_page=` | paginated, active products only |
| GET | `/products/{slug}` | includes variants, brand, category, primary image |
| GET | `/delivery-zones?pincode=` | with `pincode`: `{serviceable: bool, zone}`; without: all active zones |
| GET | `/delivery-slots` | active slots |

## Customer (requires `Authorization: Bearer <token>`)

| Method | Endpoint | Notes |
|---|---|---|
| GET | `/profile` | |
| PUT | `/profile` | `name?, phone?, date_of_birth?, gender?` |
| GET | `/cart` | current customer's cart with totals |
| POST | `/cart/items` | `product_variant_id, quantity` |
| PUT | `/cart/items/{variant}` | `quantity` (0 removes the item) |
| DELETE | `/cart/items/{variant}` | |
| GET | `/wishlist` | |
| POST | `/wishlist/{variant}` | |
| DELETE | `/wishlist/{variant}` | |
| GET/POST | `/addresses` | |
| PUT/DELETE | `/addresses/{address}` | 403 if the address isn't yours |
| POST | `/checkout` | `address_id, delivery_slot_id?, payment_method (cod\|online)` → `201` order |
| GET | `/orders` | paginated order history |
| GET | `/orders/{order_number}` | |
| POST | `/orders/{order_number}/cancel` | 422 if the order is past the cancellable stage |

Checkout resolves the delivery zone automatically from the address's pincode (`DeliveryZone::findServicing()`); it returns `422` if no zone services that pincode.

## Example

```bash
curl -X POST https://shop.allsoft.online/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"customer@groceryhub.test","password":"password"}'

curl https://shop.allsoft.online/api/v1/cart \
  -H "Authorization: Bearer <token>"
```

## Not yet built (API)

- Delivery-agent endpoints (login, assigned orders, accept/pickup/deliver, OTP confirm, location update) — the `delivery_agents`/`deliveries` schema supports this; no agent-facing API controller exists yet. The web admin panel covers agent assignment/status today.
- Payment webhook/callback endpoint for an online gateway (none is integrated — see ARCHITECTURE.md).
