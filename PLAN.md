# B Luxury Hair Salon — Project Plan

Website with online appointment booking for **B Luxury Hair Salon**, Kumanovo, North Macedonia.
This file is the single source of truth for the project. Claude Code: read this before every task, follow it, and update the checkboxes in "Progress" when a task is done.

---

## 1. Key decisions

| Topic | Decision |
|---|---|
| Stack | Latest stable Laravel, Blade, Tailwind CSS, Alpine.js, MySQL |
| Admin | Filament at `/admin` |
| Hosting | Standard cPanel shared hosting (PHP + MySQL + cron), Macedonian provider |
| Domain | `.mk` preferred (e.g. `bluxury.mk`), optional `.com` redirect. Registered in the salon's name |
| Deposit (капар) | **Paid in person, cash, at the salon within 48h.** No online payment, no bank transfer |
| Unpaid deposit | Booking auto-cancels after the deadline, slot is released |
| Source of truth | App database + admin panel |
| Salon calendar | Google Calendar sync (optional via `.env`) + private iCal feed for Apple/any phone |
| Customer calendar | `.ics` attachment + "Add to Google Calendar" link in emails |
| Emails | Laravel Mail, queued. Local: Mailpit. Production: Brevo or Resend (free plan) via SMTP |
| Gallery | Salon uploads photos in admin. Auto-resize + WebP |
| Language | Public UI in Macedonian (Cyrillic), strings in `lang/mk`. Code, comments, DB in English |

Do NOT add: React/Vue SPA, online payments, Firebase, SMS, or any package without a clear reason.

---

## 2. Salon info

- Name: B Luxury Hair Salon
- Tagline: „Повеќе од фризура… тоа е чувство!“
- Secondary tagline: Стил · Квалитет · Убавина
- Address: ул. Димитар Влахов, Куманово
- Phone: +389 78 906 112
- Email: boban_hair@yahoo.com
- Socials: TBD (get from salon)

Services (seed data, durations/prices are placeholders, editable in admin):

| Service | Subtitle | Duration (placeholder) |
|---|---|---|
| Женски фризури | Класични и модерни трендови | 60 min |
| Кератин | За здрава и сјајна коса | 150 min |
| Бојаж | Природен и совршен изглед | 120 min |
| Балајаж | Светлина и димензија | 180 min |
| Airtouch | Нежен преод, природен резултат | 210 min |
| Ламинација | Силна, гладка и светкава коса | 90 min |
| Терапии | Нега и обнова на косата | 45 min |

---

## 3. Design

Reference: `public/images/reference/poster.png` (the salon's flyer). Match its vibe, do not copy it 1:1.

- **Colors:** black marble (#0E0C0A / #1A1612), gold gradient (#C9A15B → #E8CF95 → #A67C3D), cream (#F5EFE6), warm beige (#E9DFD1)
- **Fonts (must support Cyrillic):** Cormorant Garamond (headings), Montserrat (body), Marck Script (script accents)
- **Elements:** thin gold line icons inside gold circles, circular/oval photo frames, gold divider lines, subtle marble texture, subtle gold shimmer on hero headings
- **Structure inspiration (lpdprofessional.com, not copied):** sticky nav with always-visible gold "Резервирај термин" button, hero with tagline + phone, service cards with "Прочитај повеќе", team section, closing CTA band "Закажете термин", footer with Google Map embed + contact + socials
- Mobile-first, fast, minimal JS, accessible (contrast, alt text, focus states)

Pages: Почетна, Услуги, Галерија, За нас, Контакт, Политика на приватност, Резервирај термин.

---

## 4. Booking logic

**Flow:** service → stylist (optional, "Било кој") → date → free time slot → name / phone / email / note + required privacy consent → success page.

**Availability (`AvailabilityService`):**
- Working hours per day + blocked slots (holidays, days off)
- Service duration + configurable buffer between appointments
- Minimum notice (e.g. 2h) and max days ahead (e.g. 60)
- Bookings with status `pending_deposit` or `confirmed` block the slot
- Prevent double-booking inside a DB transaction (lock)

**Statuses (`BookingStatus` enum):** `pending_deposit`, `confirmed`, `cancelled`, `completed`, `no_show`

**Deposit rules:**
- New booking → `pending_deposit`, `deposit_due_at = now + 48h` (configurable)
- Success page + email clearly state: deposit of X ден. paid **in person, cash, at the salon within 48h**, otherwise the booking is cancelled automatically
- Admin clicks "Капар примен" → `confirmed` → confirmation email + Google Calendar event
- Deadline passes unpaid → `cancelled` → cancellation email

**Customer self-service:** each booking has a `manage_token`; `/rezervacija/{token}` shows the booking and lets the customer cancel.

---

## 5. Emails (Markdown mailables, black/gold branding, queued)

| Email | When | Extras |
|---|---|---|
| BookingReceived | On booking | Deposit instructions, .ics, Google Calendar link |
| DepositReminder | 24h after booking if still unpaid | |
| BookingConfirmed | Admin marks deposit received | .ics |
| BookingReminder | 24h before appointment | |
| BookingCancelled | Auto-expire, admin cancel, customer cancel | |
| AdminNewBooking | On booking, to salon email | |

Track `*_sent_at` columns so no email is sent twice.

**Scheduled commands (`routes/console.php`):**
- `bookings:expire-unpaid` — every 15 min
- `bookings:send-reminders` — hourly

Production cron (cPanel): `* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1`
Queue on shared hosting: run `php artisan queue:work --stop-when-empty` from the scheduler every minute.

---

## 6. Calendar

- `GoogleCalendarService`: create/update/delete events on confirm/cancel. Service account, salon calendar shared with it. `GOOGLE_CALENDAR_ENABLED=false` by default. Store `google_event_id`.
- Private iCal feed `/kalendar/{secret}.ics` with confirmed bookings (salon can subscribe on iPhone/Apple Calendar; refresh is slower than Google sync).
- `IcsGenerator` for email attachments.

---

## 7. Data model

- **services:** name, slug, category, subtitle, description, icon, duration_min, price, is_active, sort_order
- **stylists:** name, photo, bio, is_active; pivot **service_stylist**
- **working_hours:** stylist_id (nullable = salon-wide), day_of_week, opens_at, closes_at
- **blocked_slots:** stylist_id (nullable), starts_at, ends_at, reason
- **bookings:** service_id, stylist_id, customer_name, customer_phone, customer_email, note, starts_at, ends_at, status, deposit_amount, deposit_due_at, deposit_paid_at, manage_token, google_event_id, consent_at, received_sent_at, deposit_reminder_sent_at, reminder_sent_at, cancelled_at, cancel_reason
- **gallery_images:** path, alt, category, sort_order, is_visible
- **settings:** key/value (deposit amount, deposit hours, reminder hours, buffer, min notice, contact info, socials)
- **users:** admin login

---

## 8. Project structure

```
app/
  Enums/BookingStatus.php
  Models/ Service, Stylist, Booking, WorkingHour, BlockedSlot, GalleryImage, Setting
  Services/ AvailabilityService, GoogleCalendarService, IcsGenerator
  Http/Controllers/ PageController, BookingController, CalendarFeedController
  Mail/ BookingReceived, DepositReminder, BookingConfirmed, BookingReminder, BookingCancelled, AdminNewBooking
  Console/Commands/ ExpireUnpaidBookings, SendBookingReminders
  Filament/Resources/ Booking, Service, Stylist, WorkingHour, BlockedSlot, GalleryImage (+ Settings page)
database/ migrations, seeders
lang/mk/
resources/
  css/app.css, js/app.js
  views/
    layouts/app.blade.php
    components/ navbar, footer, service-card, section-title, gold-divider, cta-band
    pages/ home, services, service-show, gallery, about, contact, privacy
    booking/ create, success, manage
    emails/
public/images/
  reference/poster.png   (design reference only, not shown on site)
  logo/                  (logo files)
  salon/                 (placeholder/real photos)
routes/ web.php, console.php
tests/Feature/
```

**Routes (Macedonian slugs):** `/`, `/uslugi`, `/uslugi/{slug}`, `/galerija`, `/za-nas`, `/kontakt`, `/politika-na-privatnost`, `/rezerviraj`, `/rezerviraj/uspeshno`, `/rezervacija/{token}`, `/kalendar/{secret}.ics`, `/admin`

---

## 9. Security & legal

- Validation with Macedonian messages, rate limit on booking endpoint, honeypot anti-spam, CSRF
- Privacy policy page + required consent checkbox (personal data: name, phone, email)
- `.env` never committed; admin password strong; HTTPS on production

---

## 10. Costs

| Item | Cost | Who pays |
|---|---|---|
| Laravel, Filament, Tailwind, Alpine, MySQL, Herd, Mailpit, Git/GitHub, Google Calendar API | Free | — |
| PhpStorm / IntelliJ Ultimate | Free (JetBrains student license) | — |
| Brevo / Resend | Free plan | — |
| Domain .mk | ~€20–30 / year | Salon |
| Hosting | Yearly, depends on provider | Salon |
| Claude Code | Existing Claude subscription | Developer |

---

## 11. Needed from the salon

- [ ] Original logo file (SVG/AI/PDF/high-res PNG) — ask whoever made the flyer
- [ ] Real photos of the salon and work
- [ ] Final services list with durations and prices
- [ ] Working hours and days off
- [ ] Number of stylists, names, photos; can customers choose a stylist?
- [ ] Deposit amount and cancellation rule
- [ ] Google account for the calendar + email for booking notifications
- [ ] Instagram / Facebook links
- [ ] Domain choice + hosting purchase

---

## 12. Progress

### Phase 1 — Setup, design, static pages
- [x] Laravel project, Tailwind, Alpine, fonts, `lang/mk`
- [x] Layout: navbar (sticky, gold CTA), footer (map, contact, socials)
- [x] Pages: home, services, gallery (placeholder), about, contact, privacy
- [x] Temporary text logo (Cormorant + gold gradient) until real logo arrives

### Phase 2 — Booking
- [x] Migrations, models, enum, seeders (services now come from the DB; `settings` and `gallery_images` tables move to Phase 3 with their admin pages; booking rules live in `config/salon.php` → `booking` until then)
- [x] AvailabilityService + tests
- [x] Booking steps UI, success page, manage page
- [x] Double-booking protection + test

### Phase 3 — Admin (Filament)
- [x] Resources: bookings (calendar + list, "Капар примен", cancel, no-show), services, stylists, working hours, blocked slots, gallery (WebP)
- [x] Settings page (overrides `config/salon.php` via `SalonSettings` + `ApplySalonSettings` web middleware)

### Phase 4 — Emails & automation
- Note: status changes live on `Booking` (`confirmDeposit()`, `cancel()`, `markCompleted()`, `markNoShow()`) — hook emails there. Queued jobs and console commands do not run web middleware, so call `SalonSettings::applyToConfig()` before using salon config there.
- [x] All mailables + branded template (`bluxury` Markdown theme; sent from `SendBookingEmails` listener on `BookingWasPlaced` / `DepositWasConfirmed` / `BookingWasCancelled`)
- [x] expire-unpaid + send-reminders commands + tests (deposit reminder goes out halfway through the deposit window = 24h by default)
- [x] Queue setup for shared hosting (`queue:work --stop-when-empty` scheduled every minute)

### Phase 5 — Calendar
- [x] .ics attachments + Google link (on BookingReceived and BookingConfirmed)
- [x] Google Calendar sync (`GoogleCalendarService` uses the REST API with a service-account JWT, no SDK; queued `SyncBookingToGoogleCalendar`)
- [x] Private iCal feed (`/kalendar/{CALENDAR_FEED_SECRET}.ics`, link shown on the admin Settings page)

Google Calendar setup at launch: create a Google Cloud project → enable the Calendar API → create a service account and a JSON key → save it as `storage/app/private/google-calendar.json` → share the salon's calendar with the service account email ("Make changes to events") → set `GOOGLE_CALENDAR_ENABLED=true` and `GOOGLE_CALENDAR_ID` (the calendar's ID from its settings).

### Phase 6 — Launch
- [ ] Real content, logo, photos
- [ ] Domain + hosting + SSL
- [ ] Deploy, `.env`, migrations, storage link
- [ ] Cron + SMTP (Brevo/Resend), SPF/DKIM for deliverability
- [ ] Final test on phone
