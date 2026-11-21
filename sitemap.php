<?php
/** sitemap.xml for search engines (served at /sitemap.xml by .htaccess). */
require __DIR__ . '/inc/bootstrap.php';
header('Content-Type: application/xml; charset=utf-8');
$paths = ['/', '/buy', '/sell', '/rent', '/manage', '/areas', '/about', '/contact'];
if (props_on()) array_splice($paths, 5, 0, ['/properties']);
foreach (areas() as $slug => $a) {
    if (!empty($a['guide'])) $paths[] = '/areas/' . $slug;
}
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
foreach ($paths as $p) {
    foreach (['en', 'ar'] as $lang) {
        echo '  <url><loc>' . e(absolute_url(url($p, $lang))) . '</loc>';
        echo '<xhtml:link rel="alternate" hreflang="en" href="' . e(absolute_url(url($p, 'en'))) . '"/>';
        echo '<xhtml:link rel="alternate" hreflang="ar" href="' . e(absolute_url(url($p, 'ar'))) . '"/></url>' . "\n";
    }
}
// Published properties
if (props_on()) try {
    require_once __DIR__ . '/inc/listings.php';
    foreach (db()->query("SELECT id, title, updated_at FROM listings WHERE status = 'published' ORDER BY id DESC LIMIT 5000") as $l) {
        $slug = trim(substr(slugify($l['title']), 0, 70), '-');
        $p = '/properties/' . (int) $l['id'] . ($slug !== '' ? '-' . $slug : '');
        $mod = gmdate('Y-m-d', (int) $l['updated_at']);
        foreach (['en', 'ar'] as $lang) {
            echo '  <url><loc>' . e(absolute_url(url($p, $lang))) . '</loc><lastmod>' . $mod . '</lastmod>';
            echo '<xhtml:link rel="alternate" hreflang="en" href="' . e(absolute_url(url($p, 'en'))) . '"/>';
            echo '<xhtml:link rel="alternate" hreflang="ar" href="' . e(absolute_url(url($p, 'ar'))) . '"/></url>' . "\n";
        }
    }
} catch (Throwable $e) {
    error_log('Sadan sitemap: ' . $e->getMessage());
}
echo '</urlset>' . "\n";
