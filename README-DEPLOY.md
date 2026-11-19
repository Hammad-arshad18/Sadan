# Sadan Real Estate website — how to put it live on Hostinger

Plain HTML, CSS and a little JavaScript. No build step, no database, no WordPress.

## What's in the folder

```
index.html          the whole website (one page)
contact.php         sends contact-form enquiries to info@sadanllc.com
assets/css/style.css
assets/js/main.js
assets/img/         photos + favicon
DESIGN.md           colours, fonts and spacing used (for whoever edits the site later)
README-DEPLOY.md    this file (you don't need to upload it)
```

## Upload (about 5 minutes)

1. Log in to **hPanel** → **Websites** → your domain → **File Manager**.
2. Open the **public_html** folder. Delete the default `default.php` / `index.php` if Hostinger put one there.
3. Upload `index.html`, `contact.php` and the whole `assets` folder, keeping the same structure.
   Tip: zip the files first, upload the zip, then right-click → **Extract** inside `public_html`.
4. In hPanel, open **Security → SSL** and make sure SSL is active, then turn on **Force HTTPS**.
5. Visit your domain. Done.

## Make sure the contact form emails arrive

`contact.php` uses Hostinger's built-in mail sending, so it works without setup. For the best delivery to Gmail:

1. In hPanel → **Emails**, create a mailbox on your domain, e.g. `no-reply@yourdomain.com`.
2. Open `contact.php` and set `$from = 'no-reply@yourdomain.com';` near the top.
3. Send a test enquiry from the live site. If it doesn't show up, check Gmail's **Spam** folder and mark it "Not spam" once.

To send enquiries somewhere else, change `$to` at the top of `contact.php`.

The form only works on the live server (it needs PHP). Opening `index.html` straight from your computer will show the page, but sending will fail.

## Changing things later

- **Text**: edit `index.html` in any text editor (or hPanel's File Manager editor). Each section is labelled with a comment like `<!-- SERVICES -->`.
- **Photos**: replace files in `assets/img/` with your own, keeping the same file names, or change the `src="..."` in `index.html`. Keep them under ~400 KB each (export as WebP or JPG around 1600px wide).
- **Colours/fonts**: all at the top of `assets/css/style.css` (see DESIGN.md).

## Photo credits

Current photos are from Unsplash (free to use commercially, no attribution required). Replace with your own property photos whenever you can; real photos of your listings will always look better.
