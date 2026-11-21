<?php
/**
 * Properties listed by Sadan in the admin panel: filters and search for the public
 * pages, photo addresses, and display helpers. Only 'published' homes are public.
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const PROP_TYPES = ['apartment', 'villa', 'townhouse', 'penthouse', 'land', 'commercial', 'other'];
const PROP_PER_PAGE = 24;
const PRICE_STEPS = [
    'sale' => [500000, 750000, 1000000, 1500000, 2000000, 3000000, 5000000, 7500000, 10000000, 15000000, 20000000, 30000000],
    'rent' => [30000, 50000, 75000, 100000, 150000, 200000, 300000, 500000, 1000000],
];
const SIZE_STEPS = [500, 750, 1000, 1500, 2000, 3000, 5000, 7500, 10000];
const PROP_STATUSES = ['draft' => 'Draft', 'published' => 'Published', 'sold' => 'Sold', 'rented' => 'Rented', 'archived' => 'Archived'];
const RENT_PERIODS = ['yearly', 'monthly', 'weekly', 'daily'];
/** Amenities offered as tick boxes in the admin. Labels are in lang/*.php as amen.<key>. */
const AMENITIES = [
    'balcony', 'terrace', 'private-pool', 'private-garden', 'maid-room', 'study', 'storage', 'walk-in-closet',
    'built-in-wardrobes', 'kitchen-appliances', 'central-ac', 'covered-parking', 'shared-pool', 'gym', 'spa',
    'kids-play-area', 'security', 'concierge', 'lobby', 'pets-allowed', 'sea-view', 'marina-view', 'burj-view',
    'golf-view', 'park-view', 'beach-access', 'metro-nearby', 'smart-home',
];

/** Rent per year, so sale and rent prices can be filtered on one scale. */
function yearly_price(int $price, string $purpose, string $period): int
{
    if ($purpose !== 'rent') return $price;
    return match ($period) { 'monthly' => $price * 12, 'weekly' => $price * 52, 'daily' => $price * 365, default => $price };
}

/** The guide area a community belongs to ('Dubai Marina' -> 'dubai-marina'), or ''. */
function match_area_slug(string $community, string $location = ''): string
{
    $hay = ' ' . slugify($community . ' ' . $location) . ' ';
    $hay = str_replace('-', ' ', $hay);
    $best = '';
    foreach (areas() as $slug => $a) {
        $needle = ' ' . str_replace('-', ' ', $slug) . ' ';
        if (strpos($hay, $needle) !== false && strlen($slug) > strlen($best)) $best = $slug;
    }
    if ($best === '' && preg_match('/\b(jlt)\b/', $hay)) $best = 'jumeirah-lake-towers';
    if ($best === '' && preg_match('/\b(jvc)\b/', $hay)) $best = 'jumeirah-village-circle';
    return $best;
}

/** Clean filters from the address bar. Unknown values are dropped, never trusted. */
function prop_filters(array $q): array
{
    $int = function ($v, int $max = 1000000000): int {
        $n = (int) preg_replace('/\D/', '', is_scalar($v) ? (string) $v : '');
        return max(0, min($max, $n));
    };
    $purpose = ($q['for'] ?? '') === 'rent' ? 'rent' : 'sale';
    $beds = [];
    foreach ((array) ($q['beds'] ?? []) as $b) {
        if (is_scalar($b) && preg_match('/^[0-5]$/', (string) $b)) $beds[] = (int) $b;
    }
    $beds = array_values(array_unique($beds));
    sort($beds);
    $type = is_string($q['type'] ?? null) && in_array($q['type'], PROP_TYPES, true) ? $q['type'] : '';
    $area = is_string($q['area'] ?? null) ? substr(preg_replace('/[^a-z0-9-]/', '', $q['area']), 0, 80) : '';
    $sorts = ['recommended', 'newest', 'price-asc', 'price-desc', 'size-desc'];
    $sort = is_string($q['sort'] ?? null) && in_array($q['sort'], $sorts, true) ? $q['sort'] : 'recommended';
    $text = is_string($q['q'] ?? null) ? trim(mb_substr(preg_replace('/[\x00-\x1F]/u', '', $q['q']), 0, 80)) : '';
    $f = [
        'for' => $purpose,
        'q' => $text,
        'area' => $area,
        'type' => $type,
        'beds' => $beds,
        'baths' => min(5, $int($q['baths'] ?? 0)),
        'min' => $int($q['min'] ?? 0),
        'max' => $int($q['max'] ?? 0),
        'smin' => $int($q['smin'] ?? 0, 1000000),
        'smax' => $int($q['smax'] ?? 0, 1000000),
        'done' => in_array($q['done'] ?? '', ['ready', 'offplan'], true) ? $q['done'] : '',
        'furn' => in_array($q['furn'] ?? '', ['furnished', 'unfurnished'], true) ? $q['furn'] : '',
        'sort' => $sort,
        'page' => max(1, min(500, $int($q['page'] ?? 1))),
    ];
    if ($f['max'] && $f['min'] > $f['max']) [$f['min'], $f['max']] = [$f['max'], $f['min']];
    if ($f['smax'] && $f['smin'] > $f['smax']) [$f['smin'], $f['smax']] = [$f['smax'], $f['smin']];
    return $f;
}

/** Address-bar query for a filter set (only non-default values). */
function prop_query(array $f, array $change = []): string
{
    $f = array_merge($f, $change);
    $out = [];
    if ($f['for'] === 'rent') $out['for'] = 'rent';
    foreach (['q', 'area', 'type', 'done', 'furn'] as $k) if ($f[$k] !== '') $out[$k] = $f[$k];
    if ($f['beds']) $out['beds'] = $f['beds'];
    foreach (['baths', 'min', 'max', 'smin', 'smax'] as $k) if ($f[$k]) $out[$k] = $f[$k];
    if ($f['sort'] !== 'recommended') $out['sort'] = $f['sort'];
    if ($f['page'] > 1) $out['page'] = $f['page'];
    $qs = http_build_query($out);
    return $qs === '' ? '' : '?' . preg_replace('/%5B\d+%5D/', '%5B%5D', $qs);
}

function prop_where(array $f, bool $withArea = true, bool $withType = true): array
{
    $w = ["status = 'published'", 'purpose = ?'];
    $p = [$f['for']];
    if ($withArea && $f['area'] !== '') { $w[] = 'community_key = ?'; $p[] = $f['area']; }
    if ($withType && $f['type'] !== '') { $w[] = 'type = ?'; $p[] = $f['type']; }
    if ($f['beds']) {
        $parts = [];
        foreach ($f['beds'] as $b) {
            $parts[] = $b === 5 ? 'beds >= 5' : 'beds = ?';
            if ($b !== 5) $p[] = $b;
        }
        $w[] = '(' . implode(' OR ', $parts) . ')';
    }
    if ($f['baths']) { $w[] = 'baths >= ?'; $p[] = $f['baths']; }
    if ($f['min']) { $w[] = 'price_year >= ?'; $p[] = $f['min']; }
    if ($f['max']) { $w[] = 'price_year <= ?'; $p[] = $f['max']; }
    if ($f['smin']) { $w[] = 'size_sqft >= ?'; $p[] = $f['smin']; }
    if ($f['smax']) { $w[] = 'size_sqft <= ?'; $p[] = $f['smax']; }
    if ($f['done'] !== '') { $w[] = 'completion = ?'; $p[] = $f['done']; }
    if ($f['furn'] !== '') { $w[] = 'furnishing = ?'; $p[] = $f['furn']; }
    if ($f['q'] !== '') {
        // MySQL's LIKE escapes with a backslash by default
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $f['q']) . '%';
        $w[] = '(title LIKE ? OR title_ar LIKE ? OR location LIKE ? OR community LIKE ? OR community_ar LIKE ? OR location_ar LIKE ? OR reference LIKE ?)';
        array_push($p, $like, $like, $like, $like, $like, $like, $like);
    }
    return [implode(' AND ', $w), $p];
}

function prop_search(array $f): array
{
    [$where, $params] = prop_where($f);
    $order = [
        'recommended' => 'featured DESC, (cover != \'\') DESC, listed_at DESC, id DESC',
        'newest' => 'listed_at DESC, id DESC',
        'price-asc' => 'price_year ASC, id DESC',
        'price-desc' => 'price_year DESC, id DESC',
        'size-desc' => '(size_sqft IS NULL), size_sqft DESC, id DESC',
    ][$f['sort']];
    $db = db();
    $st = $db->prepare("SELECT COUNT(*) FROM listings WHERE $where");
    $st->execute($params);
    $total = (int) $st->fetchColumn();
    $pages = max(1, (int) ceil($total / PROP_PER_PAGE));
    $page = min($f['page'], $pages);
    $st = $db->prepare("SELECT * FROM listings WHERE $where ORDER BY $order LIMIT " . PROP_PER_PAGE . ' OFFSET ' . (($page - 1) * PROP_PER_PAGE));
    $st->execute($params);
    return ['items' => $st->fetchAll(), 'total' => $total, 'pages' => $pages, 'page' => $page];
}

/** Communities and types with counts, for the filter menus. */
function prop_facets(array $f): array
{
    $db = db();
    [$where, $params] = prop_where($f, false, true);
    $st = $db->prepare("SELECT community_key AS k, MAX(community) AS name, MAX(community_ar) AS name_ar, MAX(area_slug) AS slug, COUNT(*) AS n
        FROM listings WHERE $where AND community_key != '' GROUP BY community_key ORDER BY n DESC, name ASC LIMIT 80");
    $st->execute($params);
    $areas = $st->fetchAll();
    usort($areas, fn($a, $b) => strcasecmp(prop_community_name($a['name'], $a['name_ar'], $a['slug']), prop_community_name($b['name'], $b['name_ar'], $b['slug'])));

    [$where, $params] = prop_where($f, true, false);
    $st = $db->prepare("SELECT type, COUNT(*) AS n FROM listings WHERE $where GROUP BY type");
    $st->execute($params);
    $types = array_column($st->fetchAll(), 'n', 'type');
    return ['areas' => $areas, 'types' => $types];
}

function prop_total_live(): int
{
    return (int) db()->query("SELECT COUNT(*) FROM listings WHERE status = 'published'")->fetchColumn();
}

/** When the public list last changed (newest edit of a published home). */
function prop_last_update(): int
{
    return (int) db()->query("SELECT MAX(updated_at) FROM listings WHERE status = 'published'")->fetchColumn();
}

function prop_get(int $id): ?array
{
    $st = db()->prepare('SELECT * FROM listings WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

function prop_similar(array $l, int $limit = 3): array
{
    $st = db()->prepare("SELECT * FROM listings WHERE status = 'published' AND purpose = ? AND id != ?
        ORDER BY (community_key = ?) DESC, (type = ?) DESC, ABS(price_year - ?) ASC LIMIT " . (int) $limit);
    $st->execute([$l['purpose'], $l['id'], $l['community_key'], $l['type'], $l['price_year']]);
    return $st->fetchAll();
}

/* --------------------------------------------------------------------------
   Display
   -------------------------------------------------------------------------- */

function prop_url(array $l): string
{
    $slug = substr(slugify($l['title']), 0, 70);
    return url('/properties/' . (int) $l['id'] . ($slug !== '' ? '-' . trim($slug, '-') : ''));
}

function prop_title(array $l): string
{
    return LANG === 'ar' && $l['title_ar'] !== '' ? $l['title_ar'] : $l['title'];
}

function prop_description(array $l): string
{
    return LANG === 'ar' && $l['description_ar'] !== '' ? $l['description_ar'] : $l['description'];
}

/** A community name in the page language: our own translation for guide areas. */
function prop_community_name(string $name, string $nameAr = '', string $slug = ''): string
{
    if ($slug !== '' && isset(areas()[$slug])) return strip_tags(t('area.' . areas()[$slug]['key']));
    if (LANG === 'ar' && $nameAr !== '') return $nameAr;
    return $name;
}

function prop_community(array $l): string
{
    return prop_community_name($l['community'], $l['community_ar'], $l['area_slug']);
}

function prop_location(array $l): string
{
    return LANG === 'ar' && $l['location_ar'] !== '' ? $l['location_ar'] : $l['location'];
}

function money(int $n): string
{
    return LANG === 'ar' ? number_format($n) . ' درهم' : 'AED ' . number_format($n);
}

/** Short form for filter menus: AED 1.5M, AED 750K */
function money_short(int $n): string
{
    if ($n >= 1000000) $s = rtrim(rtrim(number_format($n / 1000000, 2), '0'), '.') . (LANG === 'ar' ? ' مليون' : 'M');
    else $s = number_format($n / 1000) . (LANG === 'ar' ? ' ألف' : 'K');
    return LANG === 'ar' ? $s . ' درهم' : 'AED ' . $s;
}

function prop_period(array $l): string
{
    return $l['purpose'] === 'rent' && $l['rent_period'] !== '' ? t('props.per.' . $l['rent_period']) : '';
}

function prop_beds(?int $n): string
{
    if ($n === null) return '';
    if ($n === 0) return t('props.studio');
    return str_replace('{n}', (string) $n, t($n === 1 ? 'props.bed1' : 'props.bedsN'));
}

function prop_baths(?int $n): string
{
    if ($n === null || $n === 0) return '';
    return str_replace('{n}', (string) $n, t($n === 1 ? 'props.bath1' : 'props.bathsN'));
}

function prop_size(?int $n): string
{
    return $n ? str_replace('{n}', number_format($n), t('props.sqft')) : '';
}

/**
 * Uploaded photos are saved in two sizes: <path>-lg.webp (up to 2000 px, gallery)
 * and <path>-md.webp (900 px, cards). A listing stores the paths without the ending.
 */
function photo_url(string $path, string $size = 'lg'): string
{
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path)) return $path;
    return SITE_BASE . '/' . ltrim($path, '/') . '-' . ($size === 'md' ? 'md' : 'lg') . '.webp';
}

/** Photo paths of a listing, in the order chosen in the admin. */
function prop_photo_paths(array $l): array
{
    $p = json_decode($l['photos'] ?: '[]', true);
    return is_array($p) ? array_values(array_filter($p, fn($v) => is_string($v) && $v !== '')) : [];
}

/** Gallery-size photo addresses. */
function prop_photos(array $l, string $size = 'lg'): array
{
    return array_map(fn($p) => photo_url($p, $size), prop_photo_paths($l));
}

function prop_cover(array $l, string $size = 'md'): string
{
    return photo_url((string) $l['cover'], $size);
}

/** Amenity label in the page language (ticked ones have a translation; typed ones are shown as written). */
function amenity_label(string $a): string
{
    return in_array($a, AMENITIES, true) ? strip_tags(t('amen.' . $a)) : $a;
}

/** A secret link that shows an unpublished home (for checking it before publishing). */
function prop_preview_token(int $id): string
{
    return substr(hash_hmac('sha256', 'preview:' . $id, site_secret()), 0, 24);
}

function icon(string $name): string
{
    $paths = [
        'bed' => '<path d="M3 18v-7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7M3 15h18M6 9V6.5A1.5 1.5 0 0 1 7.5 5h3A1.5 1.5 0 0 1 12 6.5V9"/>',
        'bath' => '<path d="M4 12h16v2a5 5 0 0 1-5 5H9a5 5 0 0 1-5-5v-2zM6 12V5.5A1.5 1.5 0 0 1 9 5M7 19l-1 2M17 19l1 2"/>',
        'size' => '<path d="M4 9V4h5M20 15v5h-5M4 4l6 6M20 20l-6-6M15 4h5v5M9 20H4v-5"/>',
        'pin' => '<path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'camera' => '<path d="M4 8h3l2-3h6l2 3h3v11H4z"/><circle cx="12" cy="13" r="3.5"/>',
        'out' => '<path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'prev' => '<path d="M15 5l-7 7 7 7"/>',
        'next' => '<path d="M9 5l7 7-7 7"/>',
        'filter' => '<path d="M4 6h16M7 12h10M10 18h4"/>',
        'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M20 20l-4.2-4.2"/>',
        'grid' => '<path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z"/>',
    ];
    return '<svg class="ic ic-' . $name . '" aria-hidden="true" viewBox="0 0 24 24">' . ($paths[$name] ?? '') . '</svg>';
}

/** Date (and time) in Dubai, in the page language. */
function dubai_date(int $ts, bool $withTime = false): string
{
    $d = (new DateTimeImmutable('@' . $ts))->setTimezone(new DateTimeZone('Asia/Dubai'));
    if (LANG === 'ar') {
        $months = ['يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $s = $d->format('j') . ' ' . $months[(int) $d->format('n') - 1] . ' ' . $d->format('Y');
    } else {
        $s = $d->format('j M Y');
    }
    return $withTime ? $s . (LANG === 'ar' ? '، ' : ', ') . $d->format('H:i') : $s;
}
