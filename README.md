# Slow Goods

Curated e-commerce demo inspired by digital minimalism and slow living.

**Less screen. More life.**

This monorepo contains a Vue 3 SPA storefront and a Laravel REST API with PostgreSQL.

## Architecture

```text
slow-goods/
├── frontend/          # Vue 3 + Vite + Pinia + Vue Router (SPA)
├── backend/           # Laravel 10 API + Sanctum
├── docker-compose.yml # frontend + backend + postgres
├── README.md
└── .gitignore
```

- **Frontend**: standalone SPA talking to `/api`
- **Backend**: JSON API only (no Blade storefront)
- **Auth**: Laravel Sanctum personal access tokens
- **Cart**: Pinia + `localStorage`, synced to server cart when authenticated
- **Checkout**: server-side price/stock validation and stock decrements
- **Ask Slow**: OpenAI when `OPENAI_API_KEY` is set, otherwise catalog fallback

## Tech stack

| Layer | Stack |
| --- | --- |
| Frontend | Vue 3, Vite, TypeScript, Vue Router, Pinia |
| Backend | Laravel 10, PHP 8.1+, Sanctum |
| Database | PostgreSQL 16 (Docker) / Neon for production |
| Deploy | Vercel (frontend + serverless-compatible Laravel API entry) |

## Local setup (without Docker)

### Prerequisites

- PHP 8.1+ with SQLite or PostgreSQL extension
- Composer
- Node.js 20+
- Optional: Docker Desktop for Postgres / full compose stack

### Backend

```bash
cd backend
cp .env.example .env
php artisan key:generate
```

For local Postgres (recommended):

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=slow_goods
DB_USERNAME=slow_goods
DB_PASSWORD=secret
```

Start only Postgres:

```bash
docker compose up -d postgres
```

Then:

```bash
composer install
php artisan migrate --seed
php artisan serve --port=8000
```

### Frontend

```bash
cd frontend
cp .env.example .env
npm install
npm run dev
```

Open http://localhost:5173

`VITE_API_URL` should point to `http://localhost:8000/api`.

## Docker setup

```bash
docker compose up
```

Services:

| Service | URL |
| --- | --- |
| Frontend | http://localhost:5173 |
| Backend API | http://localhost:8000/api |
| Postgres | localhost:5432 |

Compose runs migrations and seeders on backend start.

Optional AI key:

```bash
# PowerShell
$env:OPENAI_API_KEY="sk-..."
docker compose up
```

## Environment variables

### Frontend

```env
VITE_API_URL=http://localhost:8000/api
```

### Backend

```env
APP_KEY=
APP_URL=http://localhost:8000
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=slow_goods
DB_USERNAME=slow_goods
DB_PASSWORD=secret
FRONTEND_URL=http://localhost:5173
OPENAI_API_KEY=
OPENAI_MODEL=gpt-4o-mini
```

AI credentials stay **backend-only**. Never put them in Vue.

## Database

```bash
cd backend
php artisan migrate
php artisan db:seed
```

Seed creates:

- 6 categories
- ~30 products
- curated kits / goals endpoints backed by catalog data
- demo users (see below)

## Demo accounts (development only)

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@slowgoods.test` | `password` |
| Customer | `customer@slowgoods.test` | `password` |

## Features

- Homepage with hero, featured products, categories, goals, kits, brand story, Ask Slow
- Product browse: search, category filter, price sort, pagination, detail page
- Cart / wishlist / checkout (demo payment methods)
- Orders with statuses: pending → confirmed → processing → shipped → delivered / cancelled
- Reviews (one review per user per product; updates instead of duplicates)
- Admin: products, categories, orders, reviews
- Ask Slow AI assistant with OpenAI or graceful fallback

## Tests

```bash
cd backend
php artisan test
```

Covered flows include auth, products, cart, checkout, stock validation, admin authorization, wishlist/reviews, and AI fallback.

## Production / Vercel

### 1. Database (Neon PostgreSQL)

1. Create a Neon project
2. Copy the connection string into backend env:
   - `DB_CONNECTION=pgsql`
   - `DB_HOST=...`
   - `DB_PORT=5432`
   - `DB_DATABASE=...`
   - `DB_USERNAME=...`
   - `DB_PASSWORD=...`

### 2. Run migrations safely

From a CI job, one-off container, or local machine pointed at production:

```bash
php artisan migrate --force
```

**Never** run `php artisan migrate:fresh` in production.

Do **not** auto-seed destructive demo data into an existing production database. Seed only empty/demo environments.

### 3. Frontend on Vercel

- Root directory: `frontend`
- Build command: `npm run build`
- Output directory: `dist`
- Env: `VITE_API_URL=https://your-api-domain/api`
- `vercel.json` already rewrites SPA routes to `index.html`

### 4. Laravel API on Vercel

The `backend/` folder includes:

- `api/index.php` serverless entry
- `vercel.json` using `vercel-php`

Deploy `backend` as a separate Vercel project and set production env vars (`APP_KEY`, DB_*, `APP_URL`, `FRONTEND_URL`, optional `OPENAI_API_KEY`).

If serverless PHP constraints become limiting, host the API on any PHP 8.2+ platform (Forge, Railway, Fly) and keep the Vue app on Vercel.

### 5. CORS / frontend connection

Set backend:

```env
FRONTEND_URL=https://your-frontend.vercel.app
APP_URL=https://your-api.vercel.app
```

Frontend:

```env
VITE_API_URL=https://your-api.vercel.app/api
```

## Scripts cheat sheet

```bash
# Backend
cd backend && php artisan serve
cd backend && php artisan migrate --seed
cd backend && php artisan test

# Frontend
cd frontend && npm run dev
cd frontend && npm run build

# Docker
docker compose up
docker compose up -d postgres
```

## Notes

- Checkout totals are calculated on the backend from current product prices.
- Ask Slow never invents products; recommendations come from the live catalog.
- This is a demo store: payment methods are simulated only.
