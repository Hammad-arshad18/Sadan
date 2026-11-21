<?php
/**
 * Sadan Real Estate — enquiry form handler.
 * Emails each enquiry to SITE_EMAIL (inc/config.php) with the server's mail().
 * Answers JSON to the page's script, or redirects back when JavaScript is off.
 */
declare(strict_types=1);
require __DIR__ . '/inc/config.php';

$subject = 'New website enquiry';
$wantsJson = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;
$lang = ($_POST['lang'] ?? '') === 'ar' ? 'ar' : 'en';

// Only send people back to a page on this site
$back = (string) ($_POST['back'] ?? '/contact');
if (!preg_match('#^/[a-z0-9/\-]*$#i', $back)) $back = SITE_BASE . '/contact';

function respond(bool $ok, string $error = ''): void
{
    global $wantsJson, $back;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        if (!$ok) http_response_code(400);
        echo json_encode(['ok' => $ok, 'error' => $error], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ' . $back . '?sent=' . ($ok ? '1' : '0') . '#contact-form');
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: ' . SITE_BASE . '/contact');
    exit;
}

// Spam traps: a hidden field people never fill in, and forms sent within 3 seconds of loading.
$ts = (int) ($_POST['ts'] ?? 0);
if (!empty($_POST['website']) || ($ts > 0 && time() - $ts < 3)) {
    respond(true);
}

$clean = function (string $key, int $max): string {
    $v = isset($_POST[$key]) ? trim((string) $_POST[$key]) : '';
    $v = str_replace(["\r", "\0"], '', $v);
    return mb_substr($v, 0, $max);
};

$name     = $clean('name', 100);
$email    = $clean('email', 150);
$phone    = $clean('phone', 30);
$interest = $clean('interest', 50);
$message  = $clean('message', 3000);
$type     = $clean('property_type', 60);
$area     = $clean('area', 60);

$allowed = ['Buying', 'Selling', 'Renting', 'Property management'];
$errors = [
    'en' => ['missing' => 'Add your name and a valid email address so we can reply.', 'failed' => 'The enquiry was not sent. Email us at ' . SITE_EMAIL . ' instead.'],
    'ar' => ['missing' => 'أضف اسمك وبريداً إلكترونياً صالحاً حتى نتمكن من الرد.', 'failed' => 'لم يُرسل الاستفسار. راسلنا على ' . SITE_EMAIL . ' بدلاً من ذلك.'],
];

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($interest, $allowed, true)) {
    respond(false, $errors[$lang]['missing']);
}

// No line breaks in anything that goes into headers
$name  = str_replace("\n", ' ', $name);
$email = str_replace("\n", '', $email);

$from = MAIL_FROM;
if ($from === '') {
    $host = preg_replace('/^www\./', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    $host = preg_replace('/[^a-z0-9.\-]/i', '', $host);
    $from = 'no-reply@' . $host;
}

$body  = "New enquiry from the Sadan Real Estate website\n";
$body .= "------------------------------------------------\n\n";
$body .= "Name:      {$name}\n";
$body .= "Email:     {$email}\n";
$body .= "Phone:     " . ($phone !== '' ? $phone : '-') . "\n";
$body .= "Interest:  {$interest}\n";
$body .= "Property:  " . ($type !== '' ? $type : '-') . "\n";
$body .= "Area:      " . ($area !== '' ? $area : '-') . "\n";
$body .= "Language:  " . ($lang === 'ar' ? 'Arabic' : 'English') . "\n";
$body .= "Page:      {$back}\n\n";
$body .= "Message:\n" . ($message !== '' ? $message : '(none)') . "\n\n";
$body .= "------------------------------------------------\n";
$body .= "Sent " . gmdate('j M Y, H:i') . " UTC. Reply to this email to answer {$name} directly.\n";

$headers = [
    'From: Sadan Website <' . $from . '>',
    'Reply-To: ' . mb_encode_mimeheader($name) . ' <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
];

$fullSubject = '=?UTF-8?B?' . base64_encode($subject . ' — ' . $interest . ' — ' . $name) . '?=';
$sent = @mail(SITE_EMAIL, $fullSubject, $body, implode("\r\n", $headers), '-f' . $from);

respond($sent, $sent ? '' : $errors[$lang]['failed']);
