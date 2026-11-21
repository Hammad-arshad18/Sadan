<?php
/**
 * Admin panel: page frame (sidebar, top bar, toasts), icons and small shared pieces.
 */
declare(strict_types=1);

const ADMIN_NAV = [
    'dashboard' => ['Dashboard', 'home'],
    'properties' => ['Properties', 'building'],
    'requests' => ['Requests', 'inbox'],
    'settings' => ['Settings', 'gear'],
];

function ai(string $name, string $class = ''): string
{
    static $p = [
        'home' => '<path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1z"/>',
        'building' => '<path d="M4 21V5a1 1 0 0 1 1-1h9a1 1 0 0 1 1 1v16M15 9h4a1 1 0 0 1 1 1v11M2.5 21h19M8 8h3M8 12h3M8 16h3"/>',
        'inbox' => '<path d="M3 13h5l1.5 3h5l1.5-3h5M5.5 5h13l2.5 8v6a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-6z"/>',
        'gear' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h5"/>',
        'logout' => '<path d="M9 21H5a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h4M16 17l5-5-5-5M21 12H9"/>',
        'eye' => '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'edit' => '<path d="M4 20h4L19 9l-4-4L4 16zM14 6l4 4"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 0 0-1-1H5a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h3"/>',
        'trash' => '<path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2L12 17.3l-5.6 2.9 1.1-6.2L3 9.6l6.2-.9z"/>',
        'more' => '<path d="M5 12h.01M12 12h.01M19 12h.01" stroke-width="3.2"/>',
        'grid' => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
        'list' => '<path d="M9 6h12M9 12h12M9 18h12M4 6h.01M4 12h.01M4 18h.01" />',
        'upload' => '<path d="M12 16V4M7 9l5-5 5 5M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/>',
        'image' => '<rect x="3" y="3" width="18" height="18" rx="2.5"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2"/>',
        'chat' => '<path d="M21 12a8.5 8.5 0 0 1-12.3 7.6L3 21l1.4-5.4A8.5 8.5 0 1 1 21 12z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3.5 7 8.5 6 8.5-6"/>',
        'pin' => '<path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'bed' => '<path d="M3 18v-7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7M3 15h18M6 9V6.5A1.5 1.5 0 0 1 7.5 5h3A1.5 1.5 0 0 1 12 6.5V9"/>',
        'bath' => '<path d="M4 12h16v2a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5v-2zM6 12V5.5A1.5 1.5 0 0 1 9 5M7 19l-1 2M17 19l1 2"/>',
        'size' => '<path d="M4 9V4h5M20 15v5h-5M4 4l6 6M20 20l-6-6M15 4h5v5M9 20H4v-5"/>',
        'check' => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'x' => '<path d="M6 6l12 12M18 6 6 18"/>',
        'left' => '<path d="m15 18-6-6 6-6"/>',
        'right' => '<path d="m9 18 6-6-6-6"/>',
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'alert' => '<path d="M12 9v4M12 17h.01M10.3 3.9 2 18a2 2 0 0 0 1.7 3h16.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'download' => '<path d="M12 4v12M7 11l5 5 5-5M4 20h16"/>',
        'grip' => '<path d="M9 6h.01M15 6h.01M9 12h.01M15 12h.01M9 18h.01M15 18h.01" stroke-width="3"/>',
        'link' => '<path d="M10 14a4 4 0 0 0 5.7 0l3-3a4 4 0 0 0-5.7-5.7l-1 1M14 10a4 4 0 0 0-5.7 0l-3 3a4 4 0 0 0 5.7 5.7l1-1"/>',
        'trend' => '<path d="m3 17 6-6 4 4 8-8M15 7h6v6"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 9.2-9.2M17 6l3 3M14 9l2 2"/>',
        'bell' => '<path d="M6 16V11a6 6 0 1 1 12 0v5l2 2H4zM10 21h4"/>',
        'shield' => '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
    ];
    return '<svg class="ai' . ($class !== '' ? ' ' . $class : '') . '" aria-hidden="true" viewBox="0 0 24 24">' . ($p[$name] ?? '') . '</svg>';
}

function admin_fonts(): string
{
    return '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>'
        . '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&family=IBM+Plex+Sans+Arabic:wght@400;500&display=swap">';
}

/** Sign-in and first-visit pages. */
function admin_auth_page(string $title, string $lead, callable $body): void
{ ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($title) ?> · Sadan admin</title>
  <link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
  <?= admin_fonts() ?>
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="auth">
  <div class="auth-art" aria-hidden="true">
    <img class="auth-art-img" src="<?= asset('img/hero.webp') ?>" alt="">
    <div class="auth-art-copy">
      <img src="<?= asset('img/logo-light.webp') ?>" alt="" width="1400" height="288">
      <p>Properties, requests and photos, all in one place.</p>
    </div>
  </div>
  <main class="auth-main">
    <div class="auth-card">
      <img class="auth-logo" src="<?= asset('img/logo.webp') ?>" alt="Sadan Real Estate" width="1400" height="288">
      <h1><?= e($title) ?></h1>
      <?php if ($lead !== ''): ?><p class="auth-lead"><?= $lead ?></p><?php endif; ?>
      <?php $body(); ?>
    </div>
    <p class="auth-foot"><a href="<?= url('/') ?>">← Back to the website</a></p>
  </main>
</body>
</html>
<?php }

/**
 * Page frame. $crumbs = [[label, href|null], ...]; $actions = HTML for the top bar's right side.
 */
function admin_head(string $title, string $current, array $opts = []): void
{
    $opts += ['crumbs' => [], 'actions' => '', 'flash' => null, 'newCount' => 0, 'counts' => [], 'body' => '', 'leaflet' => false];
    ?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e(strip_tags($title)) ?> · Sadan admin</title>
  <link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
  <?= admin_fonts() ?>
<?php if ($opts['leaflet']): ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css">
<?php endif; ?>
  <link rel="stylesheet" href="<?= asset('css/admin.css') ?>">
</head>
<body class="app <?= e($opts['body']) ?>">
  <a class="skip" href="#main">Skip to content</a>
  <aside class="side" id="side" aria-label="Admin menu">
    <div class="side-top">
      <a class="side-logo" href="<?= ADMIN ?>"><img src="<?= asset('img/logo-light.webp') ?>" alt="Sadan Real Estate — admin" width="1400" height="288"></a>
      <button class="side-close icon-btn" type="button" data-nav-close aria-label="Close menu"><?= ai('x') ?></button>
    </div>
    <a class="btn btn-gold side-cta" href="?p=property&amp;id=0"><?= ai('plus') ?><span>New property</span></a>
    <nav class="side-nav">
      <p class="side-label">Manage</p>
<?php foreach (ADMIN_NAV as $key => [$label, $icon]): ?>
      <a href="<?= ADMIN . ($key === 'dashboard' ? '' : '?p=' . $key) ?>"<?= $current === $key ? ' aria-current="page"' : '' ?>>
        <?= ai($icon) ?><span><?= e($label) ?></span>
<?php if ($key === 'requests' && $opts['newCount']): ?><b class="badge badge-gold" title="New requests"><?= (int) $opts['newCount'] ?></b>
<?php elseif ($key === 'properties' && !empty($opts['counts']['published'])): ?><b class="badge"><?= (int) $opts['counts']['published'] ?></b><?php endif; ?>
      </a>
<?php endforeach; ?>
    </nav>
    <div class="side-foot">
      <a class="side-link" href="<?= url('/properties') ?>" target="_blank" rel="noopener"><?= ai('external') ?><span>View website</span></a>
      <form method="post" action="<?= ADMIN ?>"><?= csrf_field() ?><input type="hidden" name="do" value="logout"><button class="side-link" type="submit"><?= ai('logout') ?><span>Sign out</span></button></form>
    </div>
  </aside>
  <div class="scrim" data-nav-close hidden></div>

  <div class="shell">
    <header class="top">
      <button class="icon-btn top-menu" type="button" data-nav-open aria-label="Open menu" aria-controls="side" aria-expanded="false"><?= ai('menu') ?></button>
      <div class="top-title">
<?php if ($opts['crumbs']): ?>
        <nav class="crumbs" aria-label="Breadcrumb">
<?php foreach ($opts['crumbs'] as [$label, $href]): ?>
          <?= $href ? '<a href="' . e($href) . '">' . e($label) . '</a>' : '<span>' . e($label) . '</span>' ?><span class="crumb-sep" aria-hidden="true">/</span>
<?php endforeach; ?>
        </nav>
<?php endif; ?>
        <h1 class="top-h" dir="auto"><?= $title ?></h1>
      </div>
      <form class="top-search" method="get" action="<?= ADMIN ?>" role="search">
        <input type="hidden" name="p" value="search">
        <label class="sr-only" for="top-q">Search properties and requests</label>
        <?= ai('search') ?><input id="top-q" name="q" type="search" placeholder="Search properties, people…" value="<?= e($current === 'search' && is_string($_GET['q'] ?? null) ? $_GET['q'] : '') ?>" autocomplete="off">
        <kbd aria-hidden="true">/</kbd>
      </form>
<?php if ($opts['actions'] !== ''): ?>
      <div class="top-actions"><?= $opts['actions'] ?></div>
<?php endif; ?>
    </header>

    <main class="main" id="main" tabindex="-1">
<?php if ($opts['flash']): [$kind, $msg] = $opts['flash']; ?>
      <div class="toast toast-<?= $kind === 'error' ? 'error' : 'ok' ?>" role="<?= $kind === 'error' ? 'alert' : 'status' ?>" data-toast>
        <?= ai($kind === 'error' ? 'alert' : 'check') ?><p><?= e($msg) ?></p>
        <button class="icon-btn" type="button" data-toast-close aria-label="Dismiss"><?= ai('x') ?></button>
      </div>
<?php endif; ?>
<?php }

function admin_foot(bool $leaflet = false): void
{ ?>
    </main>
  </div>
<?php if ($leaflet): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js" defer></script>
<?php endif; ?>
  <script src="<?= asset('js/admin.js') ?>" defer></script>
</body>
</html>
<?php }

/* ---------- small pieces ---------- */

function pill(string $kind, string $label): string
{
    return '<span class="pill pill-' . e($kind) . '">' . e($label) . '</span>';
}

function status_pill(string $s): string
{
    return pill($s, STATUSES[$s] ?? $s);
}

function listing_pill(string $s): string
{
    return pill('l-' . $s, PROP_STATUSES[$s] ?? $s);
}

function empty_state(string $icon, string $title, string $text, string $action = ''): string
{
    return '<div class="empty"><span class="empty-icon">' . ai($icon) . '</span><h3>' . e($title) . '</h3><p>' . $text . '</p>' . $action . '</div>';
}

function thumb(string $coverPath, string $class = 'thumb'): string
{
    $src = $coverPath === '' ? '' : (preg_match('#^(https?:)?/#', $coverPath) ? $coverPath : photo_url($coverPath, 'md'));
    return $src !== ''
        ? '<span class="' . $class . '"><img src="' . e($src) . '" alt="" loading="lazy"></span>'
        : '<span class="' . $class . ' is-empty">' . ai('image') . '</span>';
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/u', trim($name)) ?: [];
    $s = mb_substr($parts[0] ?? '?', 0, 1) . (count($parts) > 1 ? mb_substr((string) end($parts), 0, 1) : '');
    return mb_strtoupper($s);
}

function avatar(string $name): string
{
    $hue = hexdec(substr(md5($name), 0, 2)) % 6;
    return '<span class="avatar avatar-' . $hue . '" aria-hidden="true">' . e(initials($name)) . '</span>';
}

function pager(int $page, int $pages, array $q): void
{
    if ($pages <= 1) return; ?>
      <nav class="pager" aria-label="Pages">
        <?php if ($page > 1): ?><a class="btn btn-quiet btn-sm" href="?<?= e(http_build_query($q + ['page' => $page - 1])) ?>"><?= ai('left') ?>Previous</a><?php else: ?><span></span><?php endif; ?>
        <span class="pager-n">Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a class="btn btn-quiet btn-sm" href="?<?= e(http_build_query($q + ['page' => $page + 1])) ?>">Next<?= ai('right') ?></a><?php else: ?><span></span><?php endif; ?>
      </nav>
<?php }

function specs_line(array $l): string
{
    $out = [];
    if (($l['beds'] ?? null) !== null) $out[] = ai('bed') . ((int) $l['beds'] === 0 ? 'Studio' : (int) $l['beds'] . ' bed');
    if (!empty($l['baths'])) $out[] = ai('bath') . (int) $l['baths'] . ' bath';
    if (!empty($l['size_sqft'])) $out[] = ai('size') . number_format((int) $l['size_sqft']) . ' sq ft';
    return $out ? '<span class="specs">' . implode('', array_map(fn($s) => '<span>' . $s . '</span>', $out)) . '</span>' : '';
}
