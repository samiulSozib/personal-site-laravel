# Personal site and admin (Laravel + Inertia)

One Laravel application serves everything:

- **Frontend** (the public site) at `/en` and `/bn`: a bilingual (English / বাংলা) professional site with a home page, About, work Experience, Projects, and a blog about AI, coding, and engineering. Inertia + React, rendered on the server, with animated AI-themed visuals (a live neural-network canvas, a typing terminal, and illustrated covers).
- **Dashboard** at `/admin`: an overview with stats and a profile checklist, plus editors for posts, projects, work experience, the About page (skills, principles, education), the CV and profile photo, site settings, and newsletter subscribers. Also Inertia + React.
- **JSON API** at `/api/*`: read-only content plus the newsletter signup endpoint.
- **CV download** at `/cv`: serves the PDF uploaded in the dashboard (**Resume & photo**) and counts downloads.
- **Feed, sitemap, robots**: `/feed.xml`, `/sitemap.xml`, `/robots.txt`.

## Running it locally

Requires PHP 8.3+, Composer, and Node 20+.

```bash
composer install
npm install
cp .env.example .env             # then set ADMIN_EMAIL and ADMIN_PASSWORD
php artisan key:generate
php artisan migrate --seed       # creates the admin user and loads the starter content
php artisan serve --port=8010    # the app: http://127.0.0.1:8010
npm run dev                      # Vite dev server, in a second terminal (hot reload)
```

Then open (or use Herd: `http://my-claude-application-for-design.test`):

- Public site: `http://127.0.0.1:8010/en`
- Admin login: `http://127.0.0.1:8010/login` (sign in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`, then `/admin`)

For a production build, run `npm run build` and point `APP_URL` at your real domain.

## Deploying (one host for the public site, the admin, and the API)

Deploy the whole repository. It serves the frontend and the dashboard, both rendered with Inertia.

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build          # client bundles in public/build, server bundle in bootstrap/ssr
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Set in `.env` on the server: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://your-domain.com`, `ADMIN_EMAIL`, a strong `ADMIN_PASSWORD`, and your database settings.

Run the server-side renderer as a long-running process (for example with Supervisor), so the public pages are sent as finished HTML:

```bash
php artisan inertia:start-ssr    # listens on 127.0.0.1:13714 by default
```

If it is not running, the pages still work, but the browser renders them, so search engines see less content.

## Environment variables (`.env`)

| Variable | Purpose |
| --- | --- |
| `APP_URL` | Public base URL. Used for canonical links, the feed, and the sitemap. |
| `ADMIN_EMAIL`, `ADMIN_PASSWORD` | Login created by `php artisan db:seed`. Change the password before going live. |
| `NEWSLETTER_WEBHOOK_URL` | Optional. Each signup is also POSTed here as `{email, lang, source}`. |

## Tests

```bash
php artisan test
```

## Content

- The starter content (posts, projects, work experience, site identity, About page) is stored as JSON in `database/seed-data/` and loaded by `ContentSeeder`. Companies and roles marked "(sample)" are placeholders: replace them in the dashboard.
- Re-running `php artisan db:seed` updates records in place (posts and projects by slug, experience by company and start date), so it is safe to run again.
- Blog categories are **AI & ML**, **Coding**, and **Engineering** (`app/Support/Content.php`).
- The CV is stored privately in `storage/app/private/cv/` and only reachable through `/cv`. The profile photo is stored in `storage/app/public/profile/`, so run `php artisan storage:link` once.
- `php artisan migrate:fresh --seed` resets the database to the starter content.
- Posts are published with the **Published** checkbox. Drafts are hidden from the site, feed, and sitemap.

## Layout

| Path | What it is |
| --- | --- |
| `app/Http/Controllers/Site/` | Frontend pages, feed, sitemap, robots |
| `app/Http/Controllers/Admin/` | Dashboard controllers |
| `app/Http/Controllers/Api/` | Public JSON API |
| `resources/js/frontend/` | Frontend: `app.tsx` (browser entry), `ssr.tsx` (server entry), `pages/`, `components/`, `lib/` (import alias `@frontend/`) |
| `resources/js/dashboard/` | Dashboard, login, and account: `app.jsx` (entry), `pages/`, `components/`, `layouts/`, `lib/` (import alias `@dashboard/`) |
| `resources/css/frontend.css`, `resources/css/dashboard.css` | Styles for each side |
| `resources/views/frontend.blade.php`, `resources/views/dashboard.blade.php` | Root templates; `HandleInertiaRequests` picks one by URL |

## Learn more

- [Laravel documentation](https://laravel.com/docs)
- [Inertia.js documentation](https://inertiajs.com)
