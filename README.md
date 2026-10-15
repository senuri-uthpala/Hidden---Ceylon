# Hidden Ceylon — Travel & Tourism Website

A full-stack Sri Lanka travel and tourism website. Public-facing site with destination
search, tour packages, travel guides, reviews, FAQ and a booking/contact form — plus a
PHP + MySQL admin panel to manage destinations, packages and incoming enquiries.

**Tech stack:** HTML5, CSS3, JavaScript (vanilla), PHP 8, MySQL/MariaDB.

## Setup (WAMP / XAMPP / any Apache+PHP+MySQL stack)

1. Copy the whole `hidden_ceylon` folder into your server's web root
   (`C:\wamp64\www\hidden_ceylon` or `C:\xampp\htdocs\hidden_ceylon`).
2. Start Apache **and** MySQL from the WAMP/XAMPP control panel.
3. Open **`http://localhost/hidden_ceylon/setup.php`** in the browser.
   That page checks PHP, the MySQL connection and the tables, and has an
   **Install database now** button that imports `database/schema.sql` for you
   (no phpMyAdmin needed). Green ticks all the way down = ready.
4. Website: `http://localhost/hidden_ceylon/index.php`
   Admin panel: `http://localhost/hidden_ceylon/admin/login.php`

> Do **not** double-click `index.php`. A `file:///...` URL has no PHP engine
> behind it, so you'll see raw code or a blank page. It must be opened over
> `http://localhost/...`.

Prefer doing it manually? Import `database/schema.sql` in phpMyAdmin instead —
it creates `hidden_ceylon_db`, all tables, and seeds 19 destinations, 5 packages
and the admin account. The script drops and recreates its own tables, so it is
safe to re-run.

**Default admin login:** `admin` / `Admin@123`

## Troubleshooting

| What you see | What it means |
|---|---|
| "Database is not set up yet" screen | MySQL is off, or `schema.sql` was never imported — open `setup.php` |
| Raw `<?php` code in the browser | Opened as a file instead of through `http://localhost/` |
| "Access denied for user 'root'" on setup.php | `DB_USER` / `DB_PASS` in `config/config.php` don't match your MySQL |
| Admin login rejects `admin` / `Admin@123` | Old data — re-run the installer on `setup.php` to reseed the admin user |
| Booking form says "Server did not return JSON" | PHP fatal or DB down — check `setup.php` first |
| MAMP users | Change `DB_PORT` in `config/config.php` to `8889` |

Delete `setup.php` before putting the site on a public host.

## Project structure

```
hidden_ceylon/
├── database/
│   └── schema.sql          # tables + seed data — import this first
├── config/
│   ├── config.php          # DB credentials, site constants, session start
│   └── database.php        # PDO connection (getDB()) + friendly DB-error screen
├── includes/
│   ├── functions.php       # e(), clean(), CSRF helpers, require_admin(), flash()
│   ├── admin_header.php    # admin layout: sidebar + open <body>
│   └── admin_footer.php    # admin layout: close tags
├── admin/
│   ├── login.php           # admin authentication
│   ├── logout.php
│   ├── dashboard.php       # stats + recent enquiries
│   ├── enquiries.php       # view / filter / update status / delete enquiries
│   ├── destinations.php    # CRUD for destinations
│   └── packages.php        # CRUD for tour packages
├── assets/
│   ├── css/style.css       # public site styles
│   ├── css/admin.css       # admin panel styles
│   └── js/main.js          # search/filter, modal, slider, FAQ, booking form
├── setup.php                # environment check + one-click database installer
├── index.php                # public site — pulls destinations & packages from MySQL
└── process_booking.php      # AJAX endpoint the booking form submits to
```

## How data flows

- `index.php` queries `destinations` and `packages` (active rows only) and
  injects them into the page as `window.HC_DESTINATIONS` / `window.HC_PACKAGES`
  JSON for `assets/js/main.js` to render — so editing content in the admin panel
  updates the public site immediately, no code changes needed.
- The booking form on the public site submits via `fetch()` to
  `process_booking.php`, which validates input, checks the CSRF token, and
  inserts a row into `enquiries`.
- The admin panel's Enquiries page lists all submissions, lets you change status
  (`new` → `contacted` → `closed`), and delete spam/duplicates.

## Security features implemented

- Prepared statements (PDO) everywhere — no raw string-built SQL
- CSRF tokens on every state-changing form (login, booking, admin saves/deletes)
- Output escaping via `e()` on all dynamic HTML output (XSS protection)
- Passwords hashed with `password_hash()` / verified with `password_verify()`
- Session ID regenerated on login
- `admin/*` pages guarded by `require_admin()` — redirect to login if no session

## Notes for the FYP writeup

- All PHP files pass `php -l` with no syntax errors.
- The full flow (login → dashboard → public booking submission → saved to MySQL
  → visible in admin → status update → destination CRUD) was tested end-to-end
  against a live MariaDB + PHP built-in server before delivery.

## Fix log (latest revision)

- Added `setup.php` — environment health check plus a one-click importer for
  `database/schema.sql`, so a missing/half-imported database is obvious and
  fixable from the browser.
- `config/database.php` no longer dies with a bare message: it shows a styled
  "database is not ready" screen with the actual cause, and returns JSON (not
  HTML) when the failing request is the booking endpoint.
- Connection now retries `localhost` ↔ `127.0.0.1` automatically, and the port
  is configurable via `DB_PORT` (MAMP = 8889).
- `schema.sql` is re-runnable (`DROP TABLE IF EXISTS` for the project's own
  tables) and the seeded admin password is stored as a portable `$2y$` bcrypt
  hash instead of the `$2b$` variant.
- New destinations/packages added from the admin panel now get the next
  `sort_order` instead of 0, so they appear at the end of the list, not the top.
- `assets/js/main.js` guards every element lookup and reports a clear message if
  the booking endpoint returns something other than JSON — one missing element
  no longer kills the whole script.

Verified end-to-end on PHP 8.3 + MariaDB 10.11: setup → install → public page
render → booking submit (valid, invalid email, bad CSRF) → admin login → CRUD on
destinations and packages → enquiry status update → delete → logout, with no PHP
warnings logged.

## Destination photos
Photos live in `assets/images/destinations/`. Each destination has an `image` column in the DB.
- Fresh install: `setup.php` → Install database (schema.sql already links the 9 photos).
- Existing DB: run `database/add_image_column.sql` once in phpMyAdmin.
- Admin → Destinations lets you upload / replace / remove a photo. Without a photo, the card falls back to its gradient.

## Logo, fonts, guide photos
- Logo: `assets/images/logo.png` (light-text version for the dark UI). `logo-original.png` is the untouched one for light backgrounds.
- Fonts: Playfair Display (headings) + Poppins (body). Change `--disp` / `--body` in `assets/css/style.css` and the Google Fonts `<link>` in `index.php`.
- Hero background: `assets/images/hero/hero.jpg` — swap for any 1920px+ landscape photo, no code change needed.
- Travel guide card photos: `assets/images/guides/` (referenced in `assets/js/main.js` → `guides`).

## More destinations
`database/add_more_destinations.sql` adds 9 more places (Dambulla, Polonnaruwa, Horton Plains, Adam's Peak, Haputale, Unawatuna, Hikkaduwa, Arugam Bay, Udawalawe). Run it once in phpMyAdmin on an existing DB (fresh installs get them from schema.sql).
Photos: drop `dambulla.jpg`, `polonnaruwa.jpg`, `horton-plains.jpg`, `adams-peak.jpg`, `haputale.jpg`, `unawatuna.jpg`, `hikkaduwa.jpg`, `arugam-bay.jpg`, `udawalawe.jpg` into `assets/images/destinations/` — they are picked up automatically. Or upload from Admin → Destinations.

### "See more" on destinations
The home page shows 3 destinations at a time; each click on **See more places** adds 3 more. Searching or picking a region filter shows every match. Change `PAGE_SIZE` in `assets/js/main.js` to show more/fewer at first. Destination #19 is Sinharaja Rainforest (drop `sinharaja.jpg` into `assets/images/destinations/` for a photo). Existing DBs: re-run `database/add_more_destinations.sql` once.
Missing extra places are added automatically the first time the home page loads (`ensure_extra_destinations()`; data in `database/extra_destinations.json`). Delete `database/.extra_destinations_done` to run it again.

## Running a fresh clone from GitHub
1. Put the folder in `htdocs/hidden_ceylon` (XAMPP) and start Apache + MySQL.
2. Copy `config/config.sample.php` to `config/config.php`, then set your DB details and a private `ADMIN_INVITE_CODE`.
3. Open `http://localhost/hidden_ceylon/setup.php` and click Install database.
4. Admin login: `/admin/login.php` (default `admin` / `Admin@123` - change it straight away).
