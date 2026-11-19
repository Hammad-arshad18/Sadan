<?php
/**
 * Sadan Real Estate — contact form handler.
 * Sends each enquiry to the address below using the hosting server's mail().
 * Works on Hostinger shared hosting without extra setup.
 */

// ---- Settings ---------------------------------------------------------------
$to      = 'info@sadanllc.com';
$subject = 'New website enquiry';
// Sender must be an address on YOUR domain for good delivery, e.g. no-reply@yourdomain.com.
// Left empty, it uses no-reply@<current domain> automatically.
$from    = '';
// -----------------------------------------------------------------------------

$wantsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

function respond($ok, $error = '') {
    global $wantsJson;
    if ($wantsJson) {
        header('Content-Type: application/json; charset=utf-8');
        if (!$ok) http_response_code(400);
        echo json_encode(['ok' => $ok, 'error' => $error]);
    } else {
        header('Location: index.html?sent=' . ($ok ? '1' : '0') . '#contact');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

// Honeypot: bots fill hidden fields, people don't. Pretend success.
if (!empty($_POST['website'])) {
    respond(true);
}

$clean = function ($key, $max) {
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

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($interest, $allowed, true)) {
    respond(false, 'Add your name and a valid email address so we can reply.');
}

// Strip line breaks from anything that goes into headers
$name  = str_replace("\n", ' ', $name);
$email = str_replace("\n", '', $email);

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
$body .= "Area:      " . ($area !== '' ? $area : '-') . "\n\n";
$body .= "Message:\n" . ($message !== '' ? $message : '(none)') . "\n\n";
$body .= "------------------------------------------------\n";
$body .= "Sent " . gmdate('j M Y, H:i') . " UTC. Reply to this email to answer {$name} directly.\n";

$headers = [
    'From: Sadan Website <' . $from . '>',
    'Reply-To: ' . mb_encode_mimeheader($name) . ' <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
];

$fullSubject = '=?UTF-8?B?' . base64_encode($subject . ' — ' . $interest . ' — ' . $name) . '?=';

$sent = @mail($to, $fullSubject, $body, implode("\r\n", $headers), '-f' . $from);

respond($sent, $sent ? '' : 'The enquiry was not sent. Email us at info@sadanllc.com instead.');
