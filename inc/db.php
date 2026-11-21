<?php
/**
 * The site's database (MySQL): properties, their photos, property requests, admin
 * settings and rate limits. Connection details live in inc/secrets.php.
 * Tables are created on first use.
 */
declare(strict_types=1);

const DB_SCHEMA = 1;

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    foreach (['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'] as $c) {
        if (!defined($c)) throw new RuntimeException("The database is not set up: $c is missing from inc/secrets.php.");
    }
    $port = defined('DB_PORT') ? (int) constant('DB_PORT') : 3306;
    $host = (string) constant('DB_HOST');
    $dsn = $host === 'localhost' && $port === 3306
        ? 'mysql:host=localhost;dbname=' . constant('DB_NAME') . ';charset=utf8mb4'
        : 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . constant('DB_NAME') . ';charset=utf8mb4';

    $pdo = new PDO($dsn, (string) constant('DB_USER'), (string) constant('DB_PASS'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '+00:00'");
    db_migrate($pdo);
    return $pdo;
}

function db_migrate(PDO $pdo): void
{
    try {
        $v = (int) $pdo->query("SELECT value FROM settings WHERE name = 'schema'")->fetchColumn();
    } catch (PDOException $e) {
        $v = 0;
    }
    if ($v >= DB_SCHEMA) return;

    $opts = 'ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    $pdo->exec("CREATE TABLE IF NOT EXISTS listings (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        status VARCHAR(12) NOT NULL DEFAULT 'draft',
        featured TINYINT(1) NOT NULL DEFAULT 0,
        purpose VARCHAR(4) NOT NULL DEFAULT 'sale',
        type VARCHAR(20) NOT NULL DEFAULT 'apartment',
        title VARCHAR(200) NOT NULL DEFAULT '',
        title_ar VARCHAR(200) NOT NULL DEFAULT '',
        description TEXT NOT NULL,
        description_ar TEXT NOT NULL,
        price BIGINT UNSIGNED NOT NULL DEFAULT 0,
        rent_period VARCHAR(10) NOT NULL DEFAULT '',
        price_year BIGINT UNSIGNED NOT NULL DEFAULT 0,
        beds TINYINT UNSIGNED NULL,
        baths TINYINT UNSIGNED NULL,
        size_sqft INT UNSIGNED NULL,
        community VARCHAR(120) NOT NULL DEFAULT '',
        community_ar VARCHAR(120) NOT NULL DEFAULT '',
        community_key VARCHAR(120) NOT NULL DEFAULT '',
        area_slug VARCHAR(80) NOT NULL DEFAULT '',
        location VARCHAR(200) NOT NULL DEFAULT '',
        location_ar VARCHAR(200) NOT NULL DEFAULT '',
        lat DECIMAL(9,6) NULL,
        lng DECIMAL(9,6) NULL,
        completion VARCHAR(10) NOT NULL DEFAULT '',
        furnishing VARCHAR(12) NOT NULL DEFAULT '',
        cover VARCHAR(255) NOT NULL DEFAULT '',
        photos TEXT NOT NULL,
        amenities TEXT NOT NULL,
        agent VARCHAR(120) NOT NULL DEFAULT '',
        permit VARCHAR(60) NOT NULL DEFAULT '',
        reference VARCHAR(60) NOT NULL DEFAULT '',
        views INT UNSIGNED NOT NULL DEFAULT 0,
        listed_at INT UNSIGNED NOT NULL DEFAULT 0,
        created_at INT UNSIGNED NOT NULL DEFAULT 0,
        updated_at INT UNSIGNED NOT NULL DEFAULT 0,
        KEY listings_browse (status, purpose, price_year),
        KEY listings_community (community_key),
        KEY listings_updated (updated_at)
    ) $opts");

    $pdo->exec("CREATE TABLE IF NOT EXISTS photos (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        listing_id INT UNSIGNED NULL,
        path VARCHAR(255) NOT NULL,
        width SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        height SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        bytes INT UNSIGNED NOT NULL DEFAULT 0,
        created_at INT UNSIGNED NOT NULL DEFAULT 0,
        UNIQUE KEY photos_path (path),
        KEY photos_listing (listing_id)
    ) $opts");

    $pdo->exec("CREATE TABLE IF NOT EXISTS requests (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        listing_id INT UNSIGNED NULL,
        listing TEXT NOT NULL,
        intent VARCHAR(12) NOT NULL DEFAULT '',
        name VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL DEFAULT '',
        phone VARCHAR(30) NOT NULL DEFAULT '',
        contact_pref VARCHAR(12) NOT NULL DEFAULT '',
        message TEXT NOT NULL,
        lang VARCHAR(2) NOT NULL DEFAULT 'en',
        status VARCHAR(12) NOT NULL DEFAULT 'new',
        note TEXT NOT NULL,
        created_at INT UNSIGNED NOT NULL,
        updated_at INT UNSIGNED NOT NULL,
        KEY requests_status (status, created_at),
        KEY requests_listing (listing_id)
    ) $opts");

    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        name VARCHAR(64) NOT NULL PRIMARY KEY,
        value TEXT NOT NULL
    ) $opts");

    $pdo->exec("CREATE TABLE IF NOT EXISTS throttle (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        k VARCHAR(80) NOT NULL,
        at INT UNSIGNED NOT NULL,
        KEY throttle_k (k, at)
    ) $opts");

    $pdo->prepare("INSERT INTO settings (name, value) VALUES ('schema', ?) ON DUPLICATE KEY UPDATE value = VALUES(value)")
        ->execute([(string) DB_SCHEMA]);
}

/** 'Dubai Marina' -> 'dubai-marina' */
function slugify(string $s): string
{
    $s = strtolower(trim($s));
    $s = preg_replace('/[^a-z0-9]+/', '-', $s);
    return trim((string) $s, '-');
}

/* ---------- settings ---------- */

function settings_all(bool $reload = false): array
{
    static $cache = null;
    if ($cache === null || $reload) {
        $cache = [];
        foreach (db()->query('SELECT name, value FROM settings') as $row) $cache[$row['name']] = $row['value'];
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    return settings_all()[$key] ?? $default;
}

function setting_set(string $key, string $value): void
{
    db()->prepare('INSERT INTO settings (name, value) VALUES (?, ?) ON DUPLICATE KEY UPDATE value = VALUES(value)')
        ->execute([$key, $value]);
    settings_all(true);
}

/** A random value created once and kept (salts IP hashes and preview links). */
function site_secret(): string
{
    $s = setting('secret');
    if ($s === '') {
        $s = bin2hex(random_bytes(32));
        setting_set('secret', $s);
    }
    return $s;
}

/* ---------- rate limits ---------- */

/** Visitor's IP, hashed, so raw addresses are never stored. */
function client_key(string $scope): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    return $scope . ':' . substr(hash_hmac('sha256', $ip, site_secret()), 0, 32);
}

function throttle_count(string $key, int $windowSeconds): int
{
    $db = db();
    $db->prepare('DELETE FROM throttle WHERE at < ?')->execute([time() - 86400]);
    $st = $db->prepare('SELECT COUNT(*) FROM throttle WHERE k = ? AND at >= ?');
    $st->execute([$key, time() - $windowSeconds]);
    return (int) $st->fetchColumn();
}

function throttle_hit(string $key): void
{
    db()->prepare('INSERT INTO throttle (k, at) VALUES (?, ?)')->execute([$key, time()]);
}

function throttle_clear(string $key): void
{
    db()->prepare('DELETE FROM throttle WHERE k = ?')->execute([$key]);
}
