<?php
/**
 * Sadan admin panel (/admin): list properties with photos, follow up on property
 * requests, and change settings. One password, set on the first visit with the setup
 * code in inc/secrets.php.
 */
declare(strict_types=1);

require __DIR__ . '/../inc/bootstrap.php';
require __DIR__ . '/../inc/listings.php';
require __DIR__ . '/../inc/uploads.php';
require __DIR__ . '/../inc/admin/ui.php';
require __DIR__ . '/../inc/admin/actions.php';
require __DIR__ . '/../inc/admin/pages.php';

header('X-Frame-Options: DENY');
header("Content-Security-Policy: frame-ancestors 'none'");
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Content-Type-Options: nosniff');

$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
session_name('sadan_admin');
session_set_cookie_params(['lifetime' => 0, 'path' => SITE_BASE . '/admin', 'secure' => $https, 'httponly' => true, 'samesite' => 'Strict']);
session_start();

const ADMIN = SITE_BASE . '/admin/';
const IDLE_LIMIT = 8 * 3600;
const STATUSES = ['new' => 'New', 'contacted' => 'Contacted', 'viewing' => 'Viewing booked', 'closed' => 'Closed', 'spam' => 'Spam'];
const INTENTS = ['viewing' => 'Book a viewing', 'info' => 'More details', 'offer' => 'Make an offer'];

/* ---------- helpers ---------- */

function go(string $query = '', string $flash = '', string $kind = 'ok'): void
{
    if ($flash !== '') $_SESSION['flash'] = [$kind, $flash];
    header('Location: ' . ADMIN . ($query !== '' ? '?' . $query : ''));
    exit;
}

function json_out(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function csrf(): string
{
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf() . '">';
}

function post(string $k, int $max = 5000): string
{
    $v = $_POST[$k] ?? '';
    return is_string($v) ? mb_substr(trim(str_replace(["\0", "\r"], '', $v)), 0, $max) : '';
}

function post_list(string $k): array
{
    $v = $_POST[$k] ?? [];
    return is_array($v) ? array_values(array_filter($v, 'is_string')) : [];
}

function dubai(int $ts): DateTimeImmutable
{
    return (new DateTimeImmutable('@' . $ts))->setTimezone(new DateTimeZone('Asia/Dubai'));
}

function when(int $ts): string
{
    if ($ts <= 0) return '—';
    $d = dubai($ts);
    $diff = time() - $ts;
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    $today = dubai(time())->format('Ymd');
    if ($d->format('Ymd') === $today) return 'today, ' . $d->format('H:i');
    if ($d->format('Ymd') === dubai(time() - 86400)->format('Ymd')) return 'yesterday, ' . $d->format('H:i');
    return $d->format($d->format('Y') === dubai(time())->format('Y') ? 'j M, H:i' : 'j M Y');
}

function aed(int $n, string $period = ''): string
{
    $per = ['yearly' => 'year', 'monthly' => 'month', 'weekly' => 'week', 'daily' => 'day'][$period] ?? '';
    return 'AED ' . number_format($n) . ($per !== '' ? ' / ' . $per : '');
}

function logged_in(): bool
{
    return !empty($_SESSION['admin']) && ($_SESSION['seen'] ?? 0) > time() - IDLE_LIMIT
        && hash_equals((string) ($_SESSION['admin_v'] ?? ''), substr(setting('admin_hash'), -16));
}

function listing_title(array $l): string
{
    return trim((string) $l['title']) !== '' ? (string) $l['title'] : 'Untitled property';
}

/* ---------- database ---------- */

try {
    $db = db();
} catch (Throwable $e) {
    http_response_code(500);
    error_log('Sadan admin DB: ' . $e->getMessage());
    echo '<!doctype html><meta charset="utf-8"><meta name="viewport" content="width=device-width"><title>Admin</title>'
        . '<div style="font:16px/1.6 system-ui;max-width:560px;margin:12vh auto;padding:24px"><h1 style="font-size:22px">The database could not be reached</h1>'
        . '<p>' . e($e->getMessage()) . '</p><p>Check the database details (DB_HOST, DB_NAME, DB_USER, DB_PASS) in <code>inc/secrets.php</code>, and that MySQL is running.</p></div>';
    exit;
}

$p = is_string($_GET['p'] ?? null) ? $_GET['p'] : 'dashboard';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$isAjax = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'fetch';

if ($method === 'POST') {
    // A photo bigger than post_max_size arrives as an empty POST
    if (!$_POST && !$_FILES && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $isAjax ? json_out(['ok' => false, 'error' => 'That upload is larger than the server allows (' . ini_get('post_max_size') . ').'], 413)
                : go('', 'That upload is larger than the server allows (' . ini_get('post_max_size') . ').', 'error');
    }
    $token = (string) ($_POST['csrf'] ?? $_SERVER['HTTP_X_CSRF'] ?? '');
    if (!hash_equals(csrf(), $token)) {
        $isAjax ? json_out(['ok' => false, 'error' => 'Your session expired. Reload the page and try again.'], 403)
                : go('', 'The form expired. Please try again.', 'error');
    }
}

/* ---------- first visit: create the password ---------- */

$hasPassword = setting('admin_hash') !== '';
$setupCode = defined('ADMIN_SETUP_CODE') ? (string) constant('ADMIN_SETUP_CODE') : '';
$setupReady = $setupCode !== '' && $setupCode !== 'change-me-to-a-long-random-code' && strlen($setupCode) >= 12;

if (!$hasPassword) {
    $error = '';
    if ($method === 'POST' && ($_POST['do'] ?? '') === 'setup' && $setupReady) {
        $key = client_key('setup');
        if (throttle_count($key, 900) >= 5) $error = 'Too many attempts. Wait 15 minutes and try again.';
        elseif (!hash_equals($setupCode, post('code', 200))) { throttle_hit($key); $error = 'That setup code is not right. It is in inc/secrets.php.'; }
        elseif (mb_strlen(post('password', 200)) < 10) $error = 'Use at least 10 characters for the password.';
        elseif (post('password', 200) !== post('confirm', 200)) $error = 'The two passwords do not match.';
        else {
            setting_set('admin_hash', password_hash(post('password', 200), PASSWORD_DEFAULT));
            session_regenerate_id(true);
            $_SESSION = ['admin' => true, 'seen' => time(), 'admin_v' => substr(setting('admin_hash'), -16)];
            go('', 'Your admin password is set. Welcome.');
        }
    }
    admin_auth_page('Create your password', $setupReady ? 'First visit. Enter the setup code from <code>inc/secrets.php</code> and choose the password you’ll use from now on.' : '', function () use ($setupReady, $error) {
        if (!$setupReady) { ?>
          <p class="auth-lead">Before you can set a password, add a setup code to the website files:</p>
          <ol class="steps">
            <li>Copy <code>inc/secrets.example.php</code> to <code>inc/secrets.php</code>.</li>
            <li>Change <code>ADMIN_SETUP_CODE</code> in it to something long and random (12+ characters).</li>
            <li>Reload this page.</li>
          </ol>
        <?php return; } ?>
          <?php if ($error): ?><p class="alert alert-error" role="alert"><?= ai('alert') ?><span><?= e($error) ?></span></p><?php endif; ?>
          <form method="post" class="form-stack">
            <?= csrf_field() ?><input type="hidden" name="do" value="setup">
            <label class="field"><span class="label">Setup code</span><input name="code" type="password" required autocomplete="off"></label>
            <label class="field"><span class="label">New password <small>At least 10 characters</small></span><input name="password" type="password" required minlength="10" autocomplete="new-password"></label>
            <label class="field"><span class="label">Repeat the password</span><input name="confirm" type="password" required minlength="10" autocomplete="new-password"></label>
            <button class="btn btn-primary btn-block" type="submit">Set password and sign in</button>
          </form>
    <?php });
    exit;
}

/* ---------- sign in ---------- */

if (!logged_in()) {
    if ($isAjax) json_out(['ok' => false, 'error' => 'You were signed out. Reload the page to sign in again.'], 401);
    $error = '';
    if ($method === 'POST' && ($_POST['do'] ?? '') === 'login') {
        $key = client_key('login');
        if (throttle_count($key, 900) >= 6) {
            $error = 'Too many attempts. Wait 15 minutes and try again.';
        } elseif (password_verify(post('password', 200), setting('admin_hash'))) {
            throttle_clear($key);
            if (password_needs_rehash(setting('admin_hash'), PASSWORD_DEFAULT)) setting_set('admin_hash', password_hash(post('password', 200), PASSWORD_DEFAULT));
            session_regenerate_id(true);
            $_SESSION = ['admin' => true, 'seen' => time(), 'admin_v' => substr(setting('admin_hash'), -16)];
            $next = is_string($_POST['next'] ?? null) && preg_match('/^[a-z0-9=&_%-]*$/i', $_POST['next']) ? $_POST['next'] : '';
            go($next);
        } else {
            throttle_hit($key);
            usleep(400000);
            $error = 'That password is not right.';
        }
    }
    $next = $method === 'GET' ? (string) ($_SERVER['QUERY_STRING'] ?? '') : '';
    admin_auth_page('Welcome back', 'Sign in to manage properties and requests.', function () use ($error, $next) { ?>
          <?php if ($error): ?><p class="alert alert-error" role="alert"><?= ai('alert') ?><span><?= e($error) ?></span></p><?php endif; ?>
          <form method="post" class="form-stack">
            <?= csrf_field() ?><input type="hidden" name="do" value="login"><input type="hidden" name="next" value="<?= e($next) ?>">
            <label class="field"><span class="label">Password</span><input name="password" type="password" required autocomplete="current-password" autofocus></label>
            <button class="btn btn-primary btn-block" type="submit">Sign in</button>
          </form>
    <?php });
    exit;
}
$_SESSION['seen'] = time();

/* ---------- actions (POST) ---------- */

if ($method === 'POST') {
    admin_action((string) ($_POST['do'] ?? ''));
    go('', 'Unknown action.', 'error');
}

/* ---------- CSV export ---------- */

if ($p === 'export') {
    $rows = $db->query('SELECT * FROM requests ORDER BY created_at DESC')->fetchAll();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="sadan-requests-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $safe = fn($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    fputcsv($out, ['ID', 'Received (Dubai)', 'Status', 'Wants to', 'Name', 'Phone', 'Email', 'Reach by', 'Language', 'Message', 'Property', 'Reference', 'Price (AED)', 'Area', 'Note']);
    foreach ($rows as $r) {
        $l = json_decode($r['listing'], true) ?: [];
        fputcsv($out, array_map($safe, [$r['id'], dubai((int) $r['created_at'])->format('Y-m-d H:i'), STATUSES[$r['status']] ?? $r['status'], INTENTS[$r['intent']] ?? $r['intent'],
            $r['name'], $r['phone'], $r['email'], $r['contact_pref'], $r['lang'], $r['message'], $l['title'] ?? '', $l['reference'] ?? '', $l['price'] ?? '',
            $l['location'] ?? ($l['community'] ?? ''), $r['note']]));
    }
    exit;
}

/* ---------- pages ---------- */

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$frame = [
    'flash' => $flash,
    'newCount' => (int) $db->query("SELECT COUNT(*) FROM requests WHERE status = 'new'")->fetchColumn(),
    'counts' => array_column($db->query('SELECT status, COUNT(*) n FROM listings GROUP BY status')->fetchAll(), 'n', 'status'),
];

match ($p) {
    'properties' => page_properties($frame),
    'property' => page_property((int) ($_GET['id'] ?? 0), $frame),
    'requests' => page_requests($frame),
    'request' => page_request((int) ($_GET['id'] ?? 0), $frame),
    'settings' => page_settings($frame),
    'search' => page_search($frame),
    default => page_dashboard($frame),
};
