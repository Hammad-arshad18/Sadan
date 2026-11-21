<?php
/**
 * Property request handler. Saves the request for the admin panel (Requests) and
 * emails it to the notification address. Answers JSON to the page's script, or
 * redirects back to the property when JavaScript is off.
 */
declare(strict_types=1);
require __DIR__ . '/inc/config.php';
require __DIR__ . '/inc/db.php';

function photo_md(string $path): string
{
    return $path === '' ? '' : SITE_BASE . '/' . ltrim($path, '/') . '-md.webp';
}

$wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
$lang = ($_POST['lang'] ?? '') === 'ar' ? 'ar' : 'en';
$T = require __DIR__ . '/lang/' . $lang . '.php';
$listingId = (int) ($_POST['listing_id'] ?? 0);
$back = SITE_BASE . ($lang === 'ar' ? '/ar' : '') . '/properties/' . $listingId;

function reply(bool $ok, string $error = ''): void
{
    global $wantsJson, $back;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        if (!$ok) http_response_code(400);
        echo json_encode(['ok' => $ok, 'error' => $error], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . $back . '?sent=' . ($ok ? '1' : '0') . '#request');
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || $listingId <= 0) {
    header('Location: ' . SITE_BASE . '/properties');
    exit;
}

// Spam traps: a hidden field people never fill in, and forms sent within 3 seconds of loading.
$ts = (int) ($_POST['ts'] ?? 0);
if (!empty($_POST['website']) || ($ts > 0 && time() - $ts < 3)) reply(true);

$clean = function (string $key, int $max): string {
    $v = isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    $v = str_replace(["\r", "\0"], '', $v);
    return mb_substr($v, 0, $max);
};
$name = str_replace("\n", ' ', $clean('name', 100));
$email = str_replace("\n", '', $clean('email', 150));
$phone = preg_replace('/[^0-9+()\-\s]/', '', $clean('phone', 30));
$message = $clean('message', 2000);
$intent = in_array($_POST['intent'] ?? '', ['viewing', 'info', 'offer'], true) ? $_POST['intent'] : 'info';
$pref = in_array($_POST['contact_pref'] ?? '', ['call', 'whatsapp', 'email'], true) ? $_POST['contact_pref'] : 'call';

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) $email = '';
$phoneDigits = preg_replace('/\D/', '', $phone);
if ($name === '' || ($email === '' && strlen($phoneDigits) < 7)) reply(false, $T['prop.req.missing']);

try {
    $db = db();
    $key = client_key('request');
    if (throttle_count($key, 900) >= 5) reply(false, $T['prop.req.busy']);

    $st = $db->prepare("SELECT * FROM listings WHERE id = ? AND status = 'published'");
    $st->execute([$listingId]);
    $l = $st->fetch();
    if (!$l || setting('props_public', '1') !== '1') reply(false, $T['prop.req.error']);

    // What the property looked like when they asked (kept even if it is edited or deleted later)
    $snapshot = array_intersect_key($l, array_flip(['id', 'purpose', 'type', 'title', 'price', 'rent_period',
        'beds', 'baths', 'size_sqft', 'community', 'location', 'agent', 'permit', 'reference']));
    $snapshot['cover'] = photo_md((string) $l['cover']);
    $now = time();
    $db->prepare('INSERT INTO requests (listing_id, listing, intent, name, email, phone, contact_pref, message, lang, status, note, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$listingId, json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $intent, $name, $email, $phone, $pref, $message, $lang, 'new', '', $now, $now]);
    $requestId = (int) $db->lastInsertId();
    throttle_hit($key);
} catch (Throwable $e) {
    error_log('Sadan request: ' . $e->getMessage());
    reply(false, $T['prop.req.error']);
}

// Email the team (best effort: the request is already saved in the admin panel)
$to = setting('notify_email', SITE_EMAIL);
if ($to !== '' && filter_var($to, FILTER_VALIDATE_EMAIL)) {
    $host = preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $site = ($https ? 'https://' : 'http://') . $host . SITE_BASE;
    $from = MAIL_FROM !== '' ? MAIL_FROM : 'no-reply@' . preg_replace('/^www\.|:\d+$/', '', $host);
    $intents = ['viewing' => 'Book a viewing', 'info' => 'Get more details', 'offer' => 'Make an offer'];
    $price = 'AED ' . number_format((int) $l['price']) . ($l['purpose'] === 'rent' && $l['rent_period'] ? ' (' . $l['rent_period'] . ')' : '');

    $body  = "New property request from the Sadan Real Estate website\n";
    $body .= "--------------------------------------------------------\n\n";
    $body .= "Wants to:   {$intents[$intent]}\n";
    $body .= "Name:       {$name}\n";
    $body .= "Phone:      " . ($phone !== '' ? $phone : '-') . "\n";
    $body .= "Email:      " . ($email !== '' ? $email : '-') . "\n";
    $body .= "Reach by:   " . ucfirst($pref) . "\n";
    $body .= "Language:   " . ($lang === 'ar' ? 'Arabic' : 'English') . "\n\n";
    $body .= "Message:\n" . ($message !== '' ? $message : '(none)') . "\n\n";
    $body .= "Property\n";
    $body .= "  {$l['title']}\n";
    $body .= "  {$price} · for " . ($l['purpose'] === 'rent' ? 'rent' : 'sale') . " · " . ($l['location'] ?: $l['community']) . "\n";
    if ($l['reference'] !== '') $body .= "  Reference: {$l['reference']}\n";
    if ($l['agent'] !== '') $body .= "  Agent:     {$l['agent']}\n";
    if ($l['permit'] !== '') $body .= "  Permit:    {$l['permit']}\n";
    $body .= "  On site:   {$site}/properties/{$l['id']}\n\n";
    $body .= "Open it in the admin panel: {$site}/admin/?p=request&id={$requestId}\n";

    $headers = ['From: Sadan Website <' . $from . '>', 'MIME-Version: 1.0', 'Content-Type: text/plain; charset=UTF-8'];
    if ($email !== '') $headers[] = 'Reply-To: ' . mb_encode_mimeheader($name) . ' <' . $email . '>';
    $subject = '=?UTF-8?B?' . base64_encode('Property request — ' . $intents[$intent] . ' — ' . $name) . '?=';
    @mail($to, $subject, $body, implode("\r\n", $headers), '-f' . $from);
}

reply(true);
