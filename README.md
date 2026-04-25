# Admission Portal

Laravel 13 starter with Jetstream (Livewire + Volt), Spatie Permission, and Spatie Query Builder pre-wired.

## What is initialized

- Jetstream authentication + profile screens
- Volt page mounting via `App\Providers\VoltServiceProvider`
- Role and permission support on `App\Models\User`
- Middleware aliases for `role`, `permission`, and `role_or_permission`
- Registration disabled for public users
- Seeded baseline roles/permissions and auth users
- Protected `/api/users` endpoint with filter/sort/pagination support via Spatie Query Builder
- Upload/reporting stack with Media Library, DomPDF, and Laravel Excel

## Quick start

All PHP/Composer/NPM commands run from Laradock `workspace`.

Environment defaults are configured for Laradock MySQL (`DB_HOST=mysql`) in both `.env` and `.env.example`.

```bash
cp .env.example .env
cd laradock
docker compose up -d workspace
docker compose exec -T workspace composer install
docker compose exec -T workspace npm install
docker compose exec -T workspace php artisan key:generate
docker compose exec -T workspace php artisan migrate --seed
docker compose exec -T workspace npm run build
```

Run local development services inside `workspace`:

```bash
cd laradock
docker compose exec -T workspace composer run dev
```

## Seeded users

- Admin: `admin@example.com` / `password`
- Moderator: `moderator@example.com` / `password`

## Seeded roles and permissions

- Role `admin`: `users.view`, `users.manage`
- Role `moderator`: `users.view`

## Development workflow (TDD)

1. Write or update failing feature/unit tests first.
2. Implement the smallest change to make tests pass.
3. Refactor while keeping tests green.

Run tests from Laradock workspace:

```bash
cd laradock
docker compose exec -T workspace sh -lc "cd /var/www && php artisan test"
```

## Uploads and Reporting Libraries

- `spatie/laravel-medialibrary`: applicant photo/signature/marksheet file management
- `barryvdh/laravel-dompdf`: server-side PDF generation for admit cards and printable reports
- `maatwebsite/excel`: tabular report exports (`xlsx`, `csv`)

Published setup files:

- `config/media-library.php`
- `database/migrations/2026_04_25_045206_create_media_table.php`
- `config/dompdf.php`
- `config/excel.php`

Centralized upload constraints:

- `config/applicant_uploads.php`
- `app/Http/Requests/Applicant/StoreApplicantDocumentsRequest.php`

## API example

Authenticated users with `users.view` can query users:

```bash
GET /api/users?filter[name]=fuad&sort=-created_at&per_page=10
```

Allowed query parameters:

- `filter[name]` (partial)
- `filter[email]` (partial)
- `sort=id|name|email|created_at` (prefix `-` for desc)
- `per_page` (integer)

Public categories endpoint:

```bash
GET /api/categories?filter[type]=exam&filter[parent_ulid]=01HTXYZ...&filter[search]=admission
```

Supported category query parameters:

- `filter[type]` (example: `exam`, `location`)
- `filter[parent_ulid]` (`null` or `root` to fetch top-level nodes)
- `filter[search]` (partial match on category name)
- `per_page` (integer)

