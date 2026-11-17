# Senyum Dental Clinic — demo website

A clinic website with an online booking-request form and a staff admin panel, built to the standard a small business would accept: tested, secured, accessible and backed up.

> **Demo project.** "Senyum Dental Clinic" is fictional. The site shows a demo notice and is not for real patients.

## Features

| Area | What it does |
|---|---|
| Public site | Home, services, dentists, FAQs and editable pages; English |
| Booking requests | Patients send a request; front-desk staff confirm or decline it. Only the data needed to book is collected (no health questions) |
| Admin panel | [Filament](https://filamentphp.com) panel for bookings, services, dentists, FAQs, pages, users and clinic settings |
| Staff roles | Owner, Front desk, Content editor, Dentist. Each role sees only what it needs. Two-factor login available |
| Privacy (Malaysia PDPA) | Consent tick with notice version, bilingual privacy notice (EN/BM), automatic deletion after the retention period, export/erase tools for the owner |
| Security | Security headers and CSP, rate-limited form with honeypot and timing check, strong password rules, locked-down test copy |
| SEO & accessibility | Sitemap, meta tags, structured data, keyboard- and screen-reader-friendly pages |
| Backups | Daily encrypted backups ([spatie/laravel-backup](https://github.com/spatie/laravel-backup)) to the server and S3-compatible storage, with a restore command |

## Tech stack

Laravel 13 · Filament 5 · PHP 8.5 · Tailwind CSS · Vite · MySQL (SQLite for tests) · Resend for email

## Run it locally

Requires PHP 8.5, Composer and Node 24 (or [Laravel Herd](https://herd.laravel.com)).

```bash
git clone <this repo> senyum-dental && cd senyum-dental
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan app:create-owner   # creates the first admin account
php artisan serve
```

Open http://localhost:8000. The admin panel is at `/admin`.

## Tests

```bash
php artisan test
```

The feature tests cover the website, booking flow, admin access by role, privacy tools, security hardening, backups and health checks. They run on every push via GitHub Actions.

## Note

This public copy is a cleaned snapshot. Deployment scripts, server details and the internal project documents are kept in a private repository.
