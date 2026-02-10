## Kiosk Dashboard

[![CI](https://github.com/hamzahjamad/kiosk-dashboard/actions/workflows/ci.yml/badge.svg)](https://github.com/hamzahjamad/kiosk-dashboard/actions/workflows/ci.yml)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A kiosk dashboard for prayer times, weather, and holidays. 

**Features:**

- **Public dashboard:** prayer times, weather, holidays, and configurable backgrounds.
- **Admin** (at `/admin`): manage prayer and weather settings, holidays, backgrounds, and users.
- **Holidays:** optional [Calendarific](https://calendarific.com/) API for public holiday sync; without it, manage holidays manually in Admin → Holidays.
- **Data sync:** prayer times and weather synced by scheduled jobs; configure sources in Admin → Prayer and Admin → Weather.

[Screenshots](docs/screenshots.md): visual overview of the dashboard and admin.

### Prerequisites (local)

- PHP 8.2+, [Composer](https://getcomposer.org/), Node.js and npm.
- **Database:**
  - Default: **SQLite** ([.env.example](.env.example) has `DB_CONNECTION=sqlite`); no separate DB install.
  - Optional: MySQL; uncomment the `DB_*` variables in `.env`.

### Getting started (local)

**Quick path:**

- From repo root: `composer run setup` (installs deps, copies `.env`, generates key, migrations, build), then `composer run dev` (server + queue + Vite).

**Manual steps:**

1. Install dependencies and build assets:
   ```bash
   composer install
   npm install
   npm run build
   ```
2. Copy `.env.example` to `.env` and set `APP_KEY`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
3. Optional: set `CALENDARIFIC_API_KEY` in `.env` for public holiday sync. Without it, manage holidays manually in Admin → Holidays.
4. Run migrations and seed:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
5. Default admin (change password after first login):
   - **Email:** `admin@kiosk.local`
   - **Password:** `admin123`
6. Run the app: `php artisan serve` + `npm run dev` in another terminal, or `composer run dev` for all in one.

### Testing

- Run tests: `composer run test` or `php artisan test`.

### Quick start (Docker Hub)

**Option A: pre-built image:** use **`hamzahjamad/kiosk-dashboard`** from Docker Hub (no clone or build).

1. Ensure Docker and Docker Compose are installed.
2. Create a folder; add [docker-compose.hub.yaml](docker-compose.hub.yaml) (clone repo or [download](https://github.com/hamzahjamad/kiosk-dashboard/blob/main/docker-compose.hub.yaml)) and a `.env` with:
   - **Required:** `APP_KEY`: generate via `php artisan key:generate` in any Laravel app, or `openssl rand -base64 32` then `APP_KEY=base64:<paste>` in `.env`.
   - **Optional:** `APP_URL`, `APP_PORT` (default 8080), `CALENDARIFIC_API_KEY`; for first run only: `RUN_SEED=true` to create default admin, or run `docker exec kiosk-app php artisan db:seed --force` after start.
3. Start the stack:
   ```bash
   docker-compose -f docker-compose.hub.yaml up -d
   ```
   - App on port from `APP_PORT` (default 8080). Default admin: **Email** `admin@kiosk.local`, **Password** `admin123` (change after first login).

### Docker (build from source)

**Option B: build from source:** build and run from the repo. Use `docker-compose up -d` (builds from Dockerfile). For pre-built image instead, use `docker-compose -f docker-compose.hub.yaml up -d` as in Quick start above.

1. Copy Docker env and set `APP_KEY`:
   ```bash
   cp .env.docker .env
   php artisan key:generate
   ```
   Or generate a key and set `APP_KEY=base64:...` in `.env`.
2. Start the stack:
   ```bash
   docker-compose up -d
   ```
   - Entrypoint runs migrations and scheduler (weather and prayer sync).
   - Seeders are **not** run by default: set `RUN_SEED=true` for first run, or `docker exec kiosk-app php artisan db:seed --force` once.
   - App on port from `APP_PORT` (default 8080).

### For maintainers

**Screenshots**

- Refresh screenshots doc and images: `composer run screenshots`, `make screenshots`, or `npm run screenshots` (app must be running).
- One-time: `npm install` and `npx playwright install chromium`.
- Admin screenshots: set `SCREENSHOT_LOGIN_EMAIL` and `SCREENSHOT_LOGIN_PASSWORD` (default seeded: `admin@kiosk.local` / `admin123`; see [AdminUserSeeder](database/seeders/AdminUserSeeder.php)).

**Publishing images to Docker Hub**

- Images are built and pushed via GitHub Actions. Fork and configure secrets below to publish your own image.

**GitHub Actions secrets** (Settings → Secrets and variables → Actions):

- `DOCKERHUB_USERNAME`: your Docker Hub username
- `DOCKERHUB_TOKEN`: Docker Hub [Access Token](https://hub.docker.com/settings/security) with Read/Write (do not use password)

**Pulling / deploying the image**

1. Pull: `docker pull <your-username>/kiosk-dashboard:latest`.
2. Use `docker-compose` or your orchestrator; env from a secure source (e.g. `.env` not in repo).
   - Required: `APP_KEY`, `APP_URL`, DB vars.
   - Optional: `CALENDARIFIC_API_KEY`; `RUN_SEED=true` only for first deploy.
3. New deployment: migrations run via entrypoint; seed once: `docker exec <container> php artisan db:seed --force`. Do not set `RUN_SEED=true` on every start in production.

### Mail

- Password reset uses the app mail config to send email.
- Set `MAIL_MAILER` and SMTP (or other) settings in `.env` for real delivery.
- Default `MAIL_MAILER=log`: reset links are only written to the log.
- Registration does not send email verification; users can log in immediately after registering.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
