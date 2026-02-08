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
   The entrypoint runs migrations, seeders, and the scheduler (weather and prayer sync). The app is served on the port set by `APP_PORT` (default 8080).

### Mail

Password reset sends an email via the app's mail system. Set `MAIL_MAILER` and your SMTP (or other) settings in `.env` for reset emails to be delivered; with the default `MAIL_MAILER=log`, reset links are only written to the log. Registration does not send an email verification; users can log in immediately after registering.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
