## Kiosk Dashboard

[![CI](https://github.com/hamzahjamad/kiosk-dashboard/actions/workflows/ci.yml/badge.svg)](https://github.com/hamzahjamad/kiosk-dashboard/actions/workflows/ci.yml)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A kiosk dashboard for prayer times, weather, holidays, and backgrounds. The public dashboard is read-only; admin features (under `/admin`) require login.

### Getting started (local)

1. Copy `.env.example` to `.env` and set `APP_KEY`:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
2. Optionally set `CALENDARIFIC_API_KEY` in `.env` for public holiday sync (Calendarific API).
3. Run migrations and seed the database:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```
4. Default admin user (change password after first login):
   - **Email:** `admin@kiosk.local`
   - **Password:** `admin123`

### Docker

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

### Production / Docker Hub

Images are built and pushed to Docker Hub via GitHub Actions on push to `main`/`master` and on version tags `v*`.

**Required GitHub Actions secrets** (Settings → Secrets and variables → Actions):

- `DOCKERHUB_USERNAME`: your Docker Hub username
- `DOCKERHUB_TOKEN`: a Docker Hub [Access Token](https://hub.docker.com/settings/security) with Read/Write permission (do not use your password)

**Pulling and running in production:**

1. Pull the image: `docker pull <your-username>/kiosk-dashboard:latest` (or use a specific tag such as `sha-<commit-sha>` or `v1.0.0`).
2. Use `docker-compose` or your orchestrator with env from a secure source (e.g. `.env` not in repo). Required env: `APP_KEY`, `APP_URL`, DB vars; optional: `CALENDARIFIC_API_KEY`, `RUN_SEED=true` only for first deploy.
3. For a new deployment, run migrations (handled by the entrypoint) and seed once: `docker exec <container> php artisan db:seed --force`. Do not set `RUN_SEED=true` on every start in production.

### Mail

Password reset sends an email via the app's mail system. Set `MAIL_MAILER` and your SMTP (or other) settings in `.env` for reset emails to be delivered; with the default `MAIL_MAILER=log`, reset links are only written to the log. Registration does not send an email verification; users can log in immediately after registering.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
