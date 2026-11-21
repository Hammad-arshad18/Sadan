# Sadan Real Estate website — how to put it live on Hostinger

A PHP website (no WordPress). Works on any Hostinger web hosting plan with PHP 8.1 or newer. Properties,
their photos, property requests and the admin login are kept in a MySQL database; the site creates its
tables by itself on the first visit.

## What's in the folder

```
.htaccess            clean web addresses (/buy, /areas/dubai-marina, /ar/...)  ← must be uploaded
index.php            home page
service.php          Buy, Sell, Rent and Manage pages
areas.php            neighbourhoods overview with the Sadan Line
area.php             the six neighbourhood guides
about.php            about page
contact.php          contact page
properties.php       Properties: homes for sale and rent, with filters
property.php         one property, with its photos and the request form
request.php          receives property requests, saves them for the admin panel, emails you
admin/               the admin panel (yoursite.com/admin)
send.php             receives the enquiry form and emails it to you
404.php              "page not found"
sitemap.php          sitemap for Google (served at /sitemap.xml)
inc/config.php       SETTINGS: email, WhatsApp number, RERA number
inc/secrets.php      PRIVATE: database login and the one-time admin setup code (not in git; never share it)
inc/                 shared header, footer and helpers
lang/en.php          all English text
lang/ar.php          all Arabic text
data/site.php        the list of services and neighbourhoods
partials/            reusable page sections
assets/              styles, script, images
uploads/             created by the site: property photos uploaded in the admin panel (not in git)
.user.ini            lets Hostinger's PHP accept full-size photo uploads  ← upload it too
router.php           only for testing on a computer; not needed on Hostinger
DESIGN.md            design notes (you don't need to upload it)
README-DEPLOY.md     this file (you don't need to upload it)
```

## Upload (about 10 minutes)

1. **Database first.** hPanel → **Databases → MySQL Databases**: create a database and a user (tick
   *all privileges*). Write down the database name, user name and password; Hostinger adds your
   account prefix, e.g. `u123456789_sadan`.
2. On your computer, open `inc/secrets.php` and set `DB_HOST` to `'localhost'`, and `DB_NAME`,
   `DB_USER` and `DB_PASS` to what you just created (`inc/secrets.example.php` shows the layout).
   Keep `DB_PORT` at 3306. Your local copy uses DBngin's MySQL instead, so keep a separate copy of
   this file for the live site.
3. hPanel → **Websites** → your domain → **File Manager** → open **public_html**.
4. Delete Hostinger's default `default.php` / `index.php` / `index.html` if there is one.
5. Upload everything above (zip it, upload the zip, then right-click → **Extract**).
   Make sure the hidden files **`.htaccess`** and **`.user.ini`** are included; without `.htaccess` only
   the home page works.
6. hPanel → **Security → SSL**: make sure SSL is active and turn on **Force HTTPS**.
7. Visit your domain, then try `/buy`, `/properties`, `/areas/dubai-marina` and `/ar/` to check the addresses work.
8. Open `yourdomain.com/admin` straight away and create your admin password (see below), then open
   **Settings → System check**: every line should be green.

## Admin panel (yourdomain.com/admin)

**First visit:** it asks for the setup code, which is in `inc/secrets.php` (open it in File Manager),
and a password of at least 10 characters. Do this right after uploading. After that you sign in with
the password only. Change it any time in Settings.

- **Dashboard**: new requests waiting for a reply, requests per day for the last two weeks, homes on
  the website and drafts, what needs attention (unanswered requests, live homes without a permit
  number, drafts), and which homes get the most requests and views.
- **Properties**: every home as a photo card (or a compact list), filtered by status, sale or rent,
  and searchable. The ⋯ menu on each card publishes, marks as sold, rented or archived, makes it a
  *Sadan pick* (shown first with a gold label), duplicates or deletes it.
- **New property / editing a property**:
  - *Photos*: drag photos in (or choose them), drag to reorder; the first is the cover. Big phone
    photos are resized in the browser before uploading, and the server saves two sizes as WebP and
    removes hidden location data.
  - *Basics, price and size, location* (with a map pin), *description* and *amenities*, each in
    English with optional Arabic.
  - *Listing details*: reference (filled in for you, e.g. SAD-0001), the **Trakheesi permit number**
    and the agent.
  - *Status*: **Draft** is hidden, **Published** is on the website, **Sold / Rented / Archived**
    take it off the website. To publish it needs a title, price, community and at least one photo;
    the checklist on the right shows what's missing. Drafts have a *Copy preview link* to check the
    page before it goes live.
- **Requests**: everyone who asked about a property, new ones first. Open one to call, WhatsApp or
  email them (their preferred way is marked), see the home as it was when they asked, move it through
  New → Contacted → Viewing booked → Closed (or Spam), and keep a private note. *Export CSV* opens in
  Excel. Each new request is also emailed to the address in Settings (default: `SITE_EMAIL`).
- **Settings**: show or hide the Properties page on the website (Website → Show the Properties page),
  notification email, default agent, reference prefix, password, and a system check
  (database, photos folder, image tools, upload size).
- **Search** (top bar, or press `/`): finds homes and people by name, phone, email, title,
  community or reference.

**Dubai advertising rules:** every property advert needs a Trakheesi permit from DLD, and RERA fines
adverts without one. Add the permit number to each property before publishing; the dashboard lists
live homes that are missing one.

## Settings (inc/config.php)

- `SITE_EMAIL` — where enquiries go (now `info@sadanllc.com`).
- `MAIL_FROM` — create a mailbox like `no-reply@yourdomain.com` in hPanel → Emails and put it here
  for the best delivery to Gmail and Outlook.
- `WHATSAPP` — your number with country code, digits only. WhatsApp buttons appear when it is set.
- `RERA_ORN` — your RERA office registration number; shown in the footer when set.

Send a test enquiry from the live site after uploading. If it doesn't arrive, check the spam folder
and mark it "Not spam" once.

## Changing things later

- **Text**: `lang/en.php` (English) and `lang/ar.php` (Arabic). Each line is `'key' => 'text',`.
  Change only the text on the right, keep the quotes, and use `\'` for a straight apostrophe inside the text
  (e.g. `'We\'ll reply'`), or simply type the curly ’ which needs nothing. `<em>…</em>` marks the gold accent words in headings.
- **Photos**: replace files in `assets/img/` keeping the same names (WebP or JPG, about 1600px wide,
  under ~400 KB). Browsers pick up the new file automatically.
- **Neighbourhoods and services**: `data/site.php`, plus their text in the two language files.
- **Backups**: export the database now and then (hPanel → Databases → phpMyAdmin → Export) and
  download the `uploads` folder; together they hold every property, photo and request.

## Testing on your own computer (optional)

1. Start MySQL in **DBngin** (the *MYSQL 8* server on port 3306). The database `sadan` already exists;
   `inc/secrets.php` points to it (host `127.0.0.1`, user `root`, no password).
2. Open a terminal in this folder and run:
   `php -d upload_max_filesize=20M -d post_max_size=25M -S localhost:8000 router.php`
3. Visit http://localhost:8000, and http://localhost:8000/admin to create the admin password.

The contact form only really sends email on the live server.

## Photo credits

Current photos are from Unsplash (free to use commercially, no attribution required). Replace them
with your own property photos whenever you can.
