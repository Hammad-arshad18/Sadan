<?php
/**
 * Loaded by every page: language, text helpers, links and site data.
 * English lives at /..., Arabic at /ar/... (see .htaccess).
 */
declare(strict_types=1);

require __DIR__ . '/config.php';

$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (SITE_BASE !== '' && strpos($requestPath, SITE_BASE) === 0) {
    $requestPath = substr($requestPath, strlen(SITE_BASE)) ?: '/';
}

define('LANG', preg_match('#^/ar(/|$)#', $requestPath) ? 'ar' : 'en');
define('DIR', LANG === 'ar' ? 'rtl' : 'ltr');
// Current path without the language prefix, e.g. '/areas/dubai-marina'
define('PATH', LANG === 'ar' ? ((string) substr($requestPath, 3) ?: '/') : $requestPath);

$GLOBALS['T'] = require __DIR__ . '/../lang/' . LANG . '.php';
$GLOBALS['T_EN'] = LANG === 'en' ? $GLOBALS['T'] : require __DIR__ . '/../lang/en.php';

/** Text for a key. Copy is trusted site content and may contain <em>. */
function t(string $key): string
{
    return $GLOBALS['T'][$key] ?? $GLOBALS['T_EN'][$key] ?? $key;
}

/** Text for a key with all tags removed (for attributes and meta tags). */
function tp(string $key): string
{
    return e(strip_tags(t($key)));
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

/** Link to a page in the current (or given) language. */
function url(string $path = '/', ?string $lang = null): string
{
    $lang = $lang ?? LANG;
    $path = '/' . ltrim($path, '/');
    if ($lang === 'ar') {
        $path = $path === '/' ? '/ar/' : '/ar' . $path;
    }
    return SITE_BASE . $path;
}

/** Asset URL with a version stamp so browsers pick up changes. */
function asset(string $path): string
{
    $file = __DIR__ . '/../assets/' . $path;
    return SITE_BASE . '/assets/' . $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function absolute_url(string $path): string
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') === '443';
    $host = preg_replace('/[^a-z0-9.\-:]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost');
    return ($https ? 'https://' : 'http://') . $host . $path;
}

/** Shared inline SVG arrow used on buttons. */
function arrow(): string
{
    return '<svg class="i-arrow" aria-hidden="true" viewBox="0 0 20 20"><path d="M4 10h12M11 5l5 5-5 5"/></svg>';
}

$SITE = require __DIR__ . '/../data/site.php';

function services(): array { return $GLOBALS['SITE']['services']; }
function areas(): array { return $GLOBALS['SITE']['areas']; }

/**
 * Whether the Properties page is switched on in the admin panel (Settings).
 * When it is off, the menu links disappear and property pages answer 404.
 */
function props_on(): bool
{
    static $on = null;
    if ($on === null) {
        try {
            require_once __DIR__ . '/db.php';
            $on = setting('props_public', '1') === '1';
        } catch (Throwable $e) {
            error_log('Sadan props_on: ' . $e->getMessage());
            $on = false;
        }
    }
    return $on;
}

function render_404(): void
{
    http_response_code(404);
    require __DIR__ . '/../404.php';
    exit;
}
