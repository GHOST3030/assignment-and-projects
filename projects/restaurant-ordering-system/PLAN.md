# Restaurant Ordering System — Laravel Build Plan

A step-by-step plan to build a restaurant ordering web app in Laravel.
Customers browse the menu, add items to a cart, and place orders.
Admins manage the menu and update order statuses.

**Stack:** Laravel 11, Blade + Tailwind CSS, SQLite (easy for development), Laravel Breeze for authentication.

---

## Phase 1 — Project Setup (Day 1)

- [x] Create the project: `composer create-project laravel/laravel restaurant-ordering-system`
- [x] Configure `.env` to use SQLite (`DB_CONNECTION=sqlite`) and create `database/database.sqlite`
- [x] Install Breeze for login/register: `composer require laravel/breeze --dev && php artisan breeze:install blade`
- [x] Run `npm install && npm run dev` and `php artisan migrate`
- [x] Verify you can register and log in at `http://localhost:8000`

## Phase 2 — Database Design (Days 2–3)

Create migrations for these tables:

| Table | Key columns |
|---|---|
| `users` | add `role` column: `customer` (default) or `admin` |
| `categories` | `name` (e.g. Appetizers, Mains, Drinks, Desserts) |
| `menu_items` | `category_id`, `name`, `description`, `price`, `image`, `is_available` |
| `orders` | `user_id`, `status`, `total`, `notes` |
| `order_items` | `order_id`, `menu_item_id`, `quantity`, `price` (price at time of order) |

Order `status` values: `pending` → `preparing` → `ready` → `delivered`, plus `cancelled`.

- [x] Write the 4 new migrations + the `role` column migration
- [x] Create models: `Category`, `MenuItem`, `Order`, `OrderItem`
- [x] Define relationships:
  - Category `hasMany` MenuItem
  - MenuItem `belongsTo` Category
  - Order `belongsTo` User, `hasMany` OrderItem
  - OrderItem `belongsTo` Order and MenuItem
- [x] Write seeders: 1 admin user, 4 categories, ~12 menu items
- [x] Run `php artisan migrate:fresh --seed` and test relationships in `php artisan tinker`

## Phase 3 — Customer Menu Browsing (Days 4–5)

- [x] `MenuController@index` — show available menu items grouped by category
- [x] Category filter (click a category to see only its items)
- [x] Menu item detail page (description, price; image display comes with Phase 6 uploads)
- [x] Blade layout with navbar (Menu, Login/Register or user menu + Logout — Cart and My Orders links land in Phases 4–5 once those routes exist)

## Phase 4 — Shopping Cart (Days 6–8)

Use the **session** to store the cart (no database table needed).

- [x] `CartController` with actions: add item, update quantity, remove item, view cart
- [x] Cart page showing items, quantities, line totals, and grand total
- [x] Cart badge in the navbar showing item count
- [x] Validate: can't add unavailable items, quantity must be 1–20

## Phase 5 — Checkout & Orders (Days 9–11)

- [x] `OrderController@store` — convert the cart into an `orders` row + `order_items` rows inside a DB transaction, then clear the cart
- [x] Copy each item's current price into `order_items.price` (menus change; orders shouldn't)
- [x] Order confirmation page with order number and summary (redirects to the order detail page)
- [x] "My Orders" page — customer sees their order history and each order's status
- [x] Customers can cancel an order only while it's still `pending`

## Phase 6 — Admin Dashboard (Days 12–15)

- [x] Middleware `IsAdmin` that only lets users with `role = admin` through
- [x] Admin menu CRUD: create/edit/delete menu items with image upload (`php artisan storage:link`), toggle availability
- [x] Admin category CRUD
- [x] Orders board: list all orders filtered by status; button to advance status (pending → preparing → ready → delivered)
- [x] Simple dashboard stats: today's orders count, today's revenue, most-ordered item

## Phase 7 — Polish & Validation (Days 16–18)

- [x] Form Request classes for all forms (menu item, checkout) with proper validation messages
- [x] Authorization: customers can only view/cancel their own orders (use a Policy)
- [x] Flash messages for success/error actions
- [x] Empty states (empty cart, no orders yet)
- [x] Style everything consistently with Tailwind

## Phase 8 — Testing & Extras (Days 19–21)

- [x] Feature tests: guest can view menu, customer can add to cart and place an order, admin can update order status, non-admin is blocked from admin routes
- [x] Optional extras:
  - [x] Search menu items by name
  - [x] Email notification when order status changes (Mailable + log driver)
  - [ ] Fake payment step at checkout (skipped — out of scope for a learning project)
  - [ ] Live-ish status page that polls for updates (skipped — out of scope for a learning project)

---

## Suggested Route Map

```
GET  /                      → menu (home)
GET  /menu/{item}           → item details
GET  /cart                  → view cart
POST /cart/add/{item}       → add to cart
PATCH/DELETE /cart/{item}   → update / remove
POST /checkout              → place order
GET  /orders                → my orders
GET  /orders/{order}        → order details
POST /orders/{order}/cancel → cancel (pending only)

/admin (IsAdmin middleware)
  resource /admin/menu-items
  resource /admin/categories
  GET   /admin/orders
  PATCH /admin/orders/{order}/status
```

## What You'll Learn

Migrations & relationships (one-to-many, has-many-through), session handling,
DB transactions, middleware & policies, file uploads, form validation,
seeders, and feature testing — a complete, portfolio-ready Laravel app.
