## Kiosk Dashboard

[![CI](https://github.com/hamzahjamad/kiosk-dashboard/actions/workflows/ci.yml/badge.svg)](https://github.com/hamzahjamad/kiosk-dashboard/actions/workflows/ci.yml)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A kiosk dashboard for prayer times, weather, holidays, and backgrounds. The public dashboard is read-only; admin features (under `/admin`) require login.

**Features:**

- **Public dashboard:** prayer times, weather, holidays, and configurable backgrounds.
- **Admin** (at `/admin`): manage prayer and weather settings, holidays, backgrounds, and users.
- **Holidays:** optional [Calendarific](https://calendarific.com/) API for public holiday sync; without it, manage holidays manually in Admin → Holidays.
- **Data sync:** prayer times and weather are synced by scheduled jobs; configure sources in Admin → Prayer and Admin → Weather.

### Prerequisites (local)

- PHP 8.2+, [Composer](https://getcomposer.org/), Node.js and npm.
- Default database is **SQLite** ([.env.example](.env.example) has `DB_CONNECTION=sqlite`); no separate DB install needed for local. To use MySQL instead, uncomment the `DB_*` variables in `.env`.

### Getting started (local)

**Quick path:** From the repo root, run `composer run setup` (installs PHP/Node deps, copies `.env`, generates key, runs migrations, builds assets), then `composer run dev` to start the server, queue, and Vite together.

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
3. Optionally set `CALENDARIFIC_API_KEY` in `.env` for public holiday sync (Calendarific API). Without it, holidays can be managed manually in Admin → Holidays.
4. Run migrations and seed the database:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
5. Default admin user (change password after first login):
   - **Email:** `admin@kiosk.local`
   - **Password:** `admin123`
6. Run the app: `php artisan serve` and in another terminal `npm run dev`, or use `composer run dev` for server + queue + Vite in one go.

### Testing

Run the test suite with `composer run test` or `php artisan test`.

### Quick start (Docker Hub)

**Option A: run the pre-built image** — use the published image **`hamzahjamad/kiosk-dashboard`** from Docker Hub. No need to clone the repo or build.

1. Ensure Docker and Docker Compose are installed.
2. Create a folder and put [docker-compose.hub.yaml](docker-compose.hub.yaml) there (clone this repo or [download the file](https://github.com/hamzahjamad/kiosk-dashboard/blob/main/docker-compose.hub.yaml)). In the same folder, add a `.env` file with at least:
   - `APP_KEY` — generate one: run `php artisan key:generate` in any Laravel app, or run `openssl rand -base64 32` and set `APP_KEY=base64:<paste>` in `.env`.
   - Optionally: `APP_URL`, `APP_PORT` (default 8080), `CALENDARIFIC_API_KEY`. For the first run only you can set `RUN_SEED=true` to create the default admin user, or run `docker exec kiosk-app php artisan db:seed --force` after starting.
3. From that folder, start the stack:
   ```bash
   docker-compose -f docker-compose.hub.yaml up -d
   ```
   The app is served on the port set by `APP_PORT` (default 8080). Default admin: **Email** `admin@kiosk.local`, **Password** `admin123` (change after first login).

### Docker (build from source)

**Option B: build image from source** — build and run from the repository instead of using the published image. Use the default `docker-compose up -d` (builds from the Dockerfile). To use the pre-built image from Docker Hub instead, use `docker-compose -f docker-compose.hub.yaml up -d` as in the Quick start section above.

1. Copy the Docker env file and set `APP_KEY`:
   ```bash
   cp .env.docker .env
   php artisan key:generate
   ```
   Or generate a key and paste it into `.env` as `APP_KEY=base64:...`.
2. Start the stack:
   ```bash
   docker-compose up -d
   ```
   The entrypoint runs migrations and the scheduler (weather and prayer sync). By default seeders are **not** run; set `RUN_SEED=true` in `.env` for the first run (e.g. to create the default admin user), or run once manually: `docker exec kiosk-app php artisan db:seed --force`. The app is served on the port set by `APP_PORT` (default 8080).

### For maintainers

If you fork this repository and want to build and publish your own images to Docker Hub, the workflow is set up so images are built and pushed via GitHub Actions.

**Required GitHub Actions secrets** (Settings → Secrets and variables → Actions):

- `DOCKERHUB_USERNAME`: your Docker Hub username
- `DOCKERHUB_TOKEN`: a Docker Hub [Access Token](https://hub.docker.com/settings/security) with Read/Write permission (do not use your password)

**Pulling docker image:**

1. Pull the image: `docker pull <your-username>/kiosk-dashboard:latest`.
2. Use `docker-compose` or your orchestrator with env from a secure source (e.g. `.env` not in repo). Required env: `APP_KEY`, `APP_URL`, DB vars; optional: `CALENDARIFIC_API_KEY`, `RUN_SEED=true` only for first deploy.
3. For a new deployment, run migrations (handled by the entrypoint) and seed once: `docker exec <container> php artisan db:seed --force`. Do not set `RUN_SEED=true` on every start in production.

### Mail

Password reset sends an email via the app's mail system. Set `MAIL_MAILER` and your SMTP (or other) settings in `.env` for reset emails to be delivered; with the default `MAIL_MAILER=log`, reset links are only written to the log. Registration does not send an email verification; users can log in immediately after registering.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
