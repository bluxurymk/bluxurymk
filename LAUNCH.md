# B Luxury Hair Salon — Launch Checklist

Phase 6 of [PLAN.md](PLAN.md). Work top to bottom: content and accounts first, then the server, then the go-live test on a phone. Tick items as they are done.

---

## 1. From the salon

Content the site cannot launch without. Most of it is entered later in the admin panel (`/admin`), not in code.

- [ ] **Logo**: original file (SVG, AI, PDF or high-res PNG), from whoever made the flyer
- [ ] **Photos**: salon interior, the team, and finished work (home, about and gallery pages)
- [ ] **Services**: final list, with a duration and a price for each
- [ ] **Stylists**: names and photos, which services each one does, and whether customers may choose a stylist
- [ ] **Working hours**: for the salon, plus any stylist whose hours differ
- [ ] **Days off and holidays** for the coming months
- [ ] **Deposit**: amount, payment deadline (default 48h) and cancellation rule
- [ ] **Booking emails**: the address that receives new-booking notifications
- [ ] **Google account** that owns the salon calendar (only if Google Calendar sync is wanted)
- [ ] **Instagram / Facebook** links
- [ ] **Domain**: choice of name (e.g. `bluxury.mk`), registered **in the salon's name**
- [ ] **Hosting**: paid for (requirements in section 3)
- [ ] **Privacy policy**: wording approved by the salon, ideally checked against the Macedonian personal data law (ЗЗЛП)

## 2. Code tasks before launch

Small changes still needed in the codebase.

- [ ] **Real logo**: replace the temporary text logo in `resources/views/components/logo.blade.php`
- [ ] **Site photos**: pass real images to the `<x-photo-frame>` placeholders in `pages/home.blade.php`, `pages/about.blade.php` and `pages/service-show.blade.php`
- [x] **Team section**: the About page lists active stylists (name, photo, bio) from the admin
- [x] **Data retention**: bookings are deleted 2 years after their appointment (`Booking::RETENTION_YEARS`, daily `model:prune`), as the privacy policy promises
- [ ] **Privacy page third parties**: once the providers are chosen, name the email provider (Brevo or Resend), the hosting company and, if used, Google Calendar
- [x] **Admin 2FA**: authenticator-app two-factor login with recovery codes is available. Each admin turns it on under Profile (top-right menu → Profile)
- [ ] Merge the phase branches into `main` and tag the release

## 3. Accounts and hosting

- [ ] **Domain** registered (`.mk` through a MARnet-accredited registrar; optional `.com` that redirects to it)
- [ ] **Hosting** (cPanel shared hosting) that provides:
  - [ ] **PHP 8.4 or newer**, for both the website **and the command line / cron**. The locked dependencies require PHP ≥ 8.4.1 and will not install on 8.3
  - [ ] PHP extensions: `pdo_mysql`, `mbstring`, `openssl`, `intl`, `gd` **with WebP support**, `fileinfo`, `zip`, `curl`
  - [ ] `memory_limit` of 256M (128M can fail on large phone photos)
  - [ ] MySQL 8 or MariaDB 10.6+
  - [ ] SSH access, Composer, and cron jobs
  - [ ] Free SSL (AutoSSL / Let's Encrypt)
  - [ ] Daily backups
- [ ] **Email sending** account: [Brevo](https://www.brevo.com) or [Resend](https://resend.com) free plan, with the domain verified
- [ ] **Google Cloud** (optional, for calendar sync): follow the setup steps under Phase 5 in [PLAN.md](PLAN.md)

## 4. DNS

- [ ] `A` record for `@` and `www` pointing at the hosting server
- [ ] Email-provider records, copied **exactly** as Brevo or Resend shows them:
  - [ ] SPF (`TXT` on `@`). If the domain already has an SPF record, add the provider's `include:` to it instead of creating a second one
  - [ ] DKIM
  - [ ] Domain verification code
- [ ] DMARC: `TXT` on `_dmarc` with `v=DMARC1; p=none; rua=mailto:<salon email>`. Tighten to `p=quarantine` once mail is arriving reliably
- [ ] Provider dashboard shows the domain as **verified**

## 5. Server setup

Run these over SSH. Paths assume the app lives in `~/bluxury`; adjust to the hosting account.

1. [ ] **Get the code**
   ```bash
   git clone <repository-url> ~/bluxury
   cd ~/bluxury
   composer install --no-dev --optimize-autoloader
   ```
2. [ ] **Build the front end locally and upload it.** Shared hosting usually has no Node.js, and `public/build` is not in git:
   ```bash
   # on your computer
   npm ci && npm run build
   # then upload public/build/ to ~/bluxury/public/build/ (SFTP or rsync)
   ```
3. [ ] **Point the domain at `~/bluxury/public`.** In cPanel, set the domain's document root. For the primary domain, replace `public_html` with a symlink to `~/bluxury/public`.
4. [ ] **Create the database and user** in cPanel (MySQL Databases). Note the prefixed names.
5. [ ] **Configure the environment**: `cp .env.example .env`, then set every value in section 6 and run:
   ```bash
   php artisan key:generate
   ```
6. [ ] **Database and first data.** Only the service seeder runs on production. Never run plain `db:seed`: it creates a `test@example.com` user plus placeholder stylists and hours.
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=ServiceSeeder --force
   ```
7. [ ] **Files and assets**
   ```bash
   php artisan storage:link
   php artisan filament:assets
   ```
   If the host blocks symlinks, uploaded photos will not show. Ask the host to allow them.
8. [ ] **Admin account** with a strong, unique password:
   ```bash
   php artisan make:filament-user
   ```
9. [ ] **Cache for speed.** Re-run after every deploy or `.env` change:
   ```bash
   php artisan optimize
   php artisan filament:optimize
   ```
10. [ ] **Cron job** (cPanel → Cron Jobs → every minute). Use the full path of a **PHP 8.4+** binary; cron's default `php` is often an older version:
    ```
    * * * * * cd ~/bluxury && /path/to/php84 artisan schedule:run >> /dev/null 2>&1
    ```
    This one entry runs everything: unpaid-booking expiry (every 15 min), reminders (hourly), the email queue (every minute) and deleting bookings past the 2-year retention (daily).
11. [ ] **HTTPS**: issue the certificate (AutoSSL) and turn on cPanel → Domains → **Force HTTPS Redirect**.
12. [ ] **Fill in the admin** at `https://<domain>/admin`:
    - [ ] Services: real durations and prices, inactive ones switched off
    - [ ] Stylists: photos, and services linked to each
    - [ ] Working hours, holidays and days off
    - [ ] Settings: contact details, socials and booking rules
    - [ ] Gallery photos

### Later deploys

```bash
cd ~/bluxury
php artisan down
git pull
composer install --no-dev --optimize-autoloader
# upload a freshly built public/build/ if CSS or JS changed
php artisan migrate --force
php artisan optimize
php artisan filament:optimize
php artisan up
```

## 6. Production `.env`

| Key | Value |
|---|---|
| `APP_NAME` | `"B Luxury Hair Salon"` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` (**never** `true` on the live site) |
| `APP_KEY` | generated by `php artisan key:generate` |
| `APP_URL` | `https://bluxury.mk` (the real domain, with https) |
| `APP_LOCALE` | `mk` |
| `LOG_STACK` / `LOG_LEVEL` | `daily` / `warning` |
| `DB_CONNECTION` | `mysql` |
| `DB_HOST` | `localhost` |
| `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | from cPanel (with the account prefix) |
| `SESSION_DRIVER` | `database` |
| `SESSION_SECURE_COOKIE` | `true` |
| `CACHE_STORE` | `database` |
| `QUEUE_CONNECTION` | `database` |
| `MAIL_MAILER` | `smtp` |
| `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD` | the SMTP details from the Brevo or Resend dashboard |
| `MAIL_FROM_ADDRESS` | an address on the verified domain, e.g. `rezervacii@bluxury.mk` |
| `MAIL_FROM_NAME` | `"${APP_NAME}"` |
| `CALENDAR_FEED_SECRET` | long random value, e.g. from `php -r "echo bin2hex(random_bytes(24));"` |
| `GOOGLE_CALENDAR_ENABLED` | `true` only after the Google setup is done |
| `GOOGLE_CALENDAR_ID` | the calendar's ID from its Google settings |
| `GOOGLE_CALENDAR_CREDENTIALS` | leave empty to use `storage/app/private/google-calendar.json` |
| `BOOKING_*` | optional defaults; the admin Settings page overrides them |

The salon's contact email (Settings → Контакт) is where new-booking notifications go.

## 7. Go-live test (on a phone)

Use your own email and phone number for these test bookings. Afterwards, cancel them in the admin.

- [ ] Every page loads over **https** with no browser warnings, and Cyrillic text shows in the right fonts
- [ ] Menu, map, phone link and Instagram/Facebook links work
- [ ] **Book an appointment** through the whole wizard
  - [ ] Confirmation page shows the cash deposit rules and the private manage link
  - [ ] "Received" email arrives (check spam). `termin.ics` opens in the phone calendar, and the Google Calendar link works
  - [ ] The salon address gets the new-booking notification
- [ ] **Admin**: log in, then click "Капар примен" on the test booking
  - [ ] "Confirmed" email arrives
  - [ ] Event appears in Google Calendar (if enabled)
  - [ ] Booking appears in the iPhone subscribed calendar (Settings page link; Apple refreshes slowly, up to an hour or more)
- [ ] **Cancel** from the email's manage link: cancellation email arrives, and the time slot is bookable again
- [ ] **Double booking**: try to book the same slot twice; the second attempt is refused
- [ ] **Expiry**: set the deposit deadline to 1 hour in Settings, book, and wait about 75 minutes. The booking is auto-cancelled and the email arrives. Restore the deadline afterwards
- [ ] **Reminder**: a confirmed booking for tomorrow gets the reminder email within the hour
- [ ] **Gallery**: upload a photo from the phone in the admin; it appears on `/galerija`
- [ ] Server check: `php artisan schedule:list` lists 4 tasks, and `php artisan queue:failed` is empty
- [ ] Error pages do not show debug details (open a URL that does not exist)

## 8. After launch

- [ ] Backups: confirm the host's daily backups include the **database** and `storage/app`, and download a copy monthly
- [ ] Check `storage/logs/` and `php artisan queue:failed` weekly for the first month
- [ ] Run `composer audit` and update dependencies regularly; test locally before deploying
- [ ] Hand-over to the salon: admin login, turning on two-factor login (Profile), how to confirm deposits, add days off, and upload photos
- [ ] Submit the site to Google Search Console and create a Google Business Profile with the booking link
