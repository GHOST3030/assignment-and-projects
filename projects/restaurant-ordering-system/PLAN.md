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

- [ ] `MenuController@index` — show available menu items grouped by category
- [ ] Category filter (click a category to see only its items)
- [ ] Menu item detail page (photo, description, price)
- [ ] Blade layout with navbar (Menu, Cart, My Orders, Login/Logout)

## Phase 4 — Shopping Cart (Days 6–8)

Use the **session** to store the cart (no database table needed).

- [ ] `CartController` with actions: add item, update quantity, remove item, view cart
- [ ] Cart page showing items, quantities, line totals, and grand total
- [ ] Cart badge in the navbar showing item count
- [ ] Validate: can't add unavailable items, quantity must be 1–20

## Phase 5 — Checkout & Orders (Days 9–11)

- [ ] `OrderController@store` — convert the cart into an `orders` row + `order_items` rows inside a DB transaction, then clear the cart
- [ ] Copy each item's current price into `order_items.price` (menus change; orders shouldn't)
- [ ] Order confirmation page with order number and summary
- [ ] "My Orders" page — customer sees their order history and each order's status
- [ ] Customers can cancel an order only while it's still `pending`

## Phase 6 — Admin Dashboard (Days 12–15)

- [ ] Middleware `IsAdmin` that only lets users with `role = admin` through
- [ ] Admin menu CRUD: create/edit/delete menu items with image upload (`php artisan storage:link`), toggle availability
- [ ] Admin category CRUD
- [ ] Orders board: list all orders filtered by status; button to advance status (pending → preparing → ready → delivered)
- [ ] Simple dashboard stats: today's orders count, today's revenue, most-ordered item

## Phase 7 — Polish & Validation (Days 16–18)

- [ ] Form Request classes for all forms (menu item, checkout) with proper validation messages
- [ ] Authorization: customers can only view/cancel their own orders (use a Policy)
- [ ] Flash messages for success/error actions
- [ ] Empty states (empty cart, no orders yet)
- [ ] Style everything consistently with Tailwind

## Phase 8 — Testing & Extras (Days 19–21)

- [ ] Feature tests: guest can view menu, customer can add to cart and place an order, admin can update order status, non-admin is blocked from admin routes
- [ ] Optional extras if you have time:
  - Search menu items by name
  - Email notification when order status changes (Mailable + log driver)
  - Fake payment step at checkout
  - Live-ish status page that polls for updates

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
