<?php
/**
 * Sadan Real Estate — site settings. Change these, nothing else needed.
 */

// Folder the site lives in. '' when the site is at the root of the domain (normal on Hostinger).
const SITE_BASE = '';

// Where enquiries are sent, and the address shown on the site.
const SITE_EMAIL = 'info@sadanllc.com';

// Sender address for enquiry emails. Use a mailbox on your own domain for good delivery,
// e.g. 'no-reply@sadanllc.com'. Left empty, no-reply@<your domain> is used.
const MAIL_FROM = '';

// WhatsApp number with country code, digits only (e.g. '9715XXXXXXXX').
// Empty = all WhatsApp buttons stay hidden.
const WHATSAPP = '';

// RERA office registration number (ORN). Shown in the footer when filled in.
const RERA_ORN = '';

// Where property photos uploaded in the admin panel are saved (inside the website folder,
// so they can be shown on the site). PHP must be able to write to it.
const UPLOAD_DIR = __DIR__ . '/../uploads';

// Private values (the database login and the one-time admin setup code) live in inc/secrets.php, which is not in git.
if (is_file(__DIR__ . '/secrets.php')) require_once __DIR__ . '/secrets.php';
