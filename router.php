<?php
/**
 * LOCAL TESTING ONLY. Mirrors the .htaccess rules for PHP's built-in server:
 *   php -S localhost:8000 router.php
 * Hostinger uses .htaccess instead; this file is blocked there.
 */
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if (preg_match('#^/(inc|lang|data|partials)/|^/router\.php$|/\.user\.ini$|^/uploads/.*\.(php|phtml|phar)$#i', $path)) { http_response_code(403); exit; }
if (preg_match('#^/admin/?$#', $path)) { require __DIR__ . '/admin/index.php'; return true; }
if ($path !== '/' && is_file($file)) return false;

$p = preg_replace('#^/ar(/|$)#', '/', $path);
$routes = [
    '#^/$#'                            => ['index.php', []],
    '#^/(buy|sell|rent|manage)/?$#'    => ['service.php', ['s' => 1]],
    '#^/areas/?$#'                     => ['areas.php', []],
    '#^/areas/([a-z-]+)/?$#'           => ['area.php', ['a' => 1]],
    '#^/(about|contact)/?$#'           => [null, []],
    '#^/properties/?$#'                => ['properties.php', []],
    '#^/properties/([0-9]+)(?:-[a-z0-9-]*)?/?$#' => ['property.php', ['id' => 1]],
    '#^/sitemap\.xml$#'                => ['sitemap.php', []],
];
foreach ($routes as $re => [$target, $params]) {
    if (preg_match($re, $p, $m)) {
        if ($target === null) $target = $m[1] . '.php';
        foreach ($params as $k => $i) $_GET[$k] = $m[$i];
        require __DIR__ . '/' . $target;
        return true;
    }
}
http_response_code(404);
require __DIR__ . '/404.php';
