<?php
/**
 * Admin panel pages: dashboard, properties (list and editor), requests (inbox and
 * detail), settings and search.
 */
declare(strict_types=1);

const TYPE_LABELS = ['apartment' => 'Apartment', 'villa' => 'Villa', 'townhouse' => 'Townhouse', 'penthouse' => 'Penthouse', 'land' => 'Land', 'commercial' => 'Commercial', 'other' => 'Other'];
function amen_en(string $k): string
{
    return strip_tags($GLOBALS['T_EN']['amen.' . $k] ?? $k);
}

/* ======================================================================
   Dashboard
   ====================================================================== */

function page_dashboard(array $frame): void
{
    $db = db();
    $c = fn(string $sql, array $p = []) => (int) (($st = $db->prepare($sql)) && $st->execute($p) ? $st->fetchColumn() : 0);
    $now = time();
    $new = $frame['newCount'];
    $week = $c('SELECT COUNT(*) FROM requests WHERE created_at > ? AND status != \'spam\'', [$now - 7 * 86400]);
    $prevWeek = $c('SELECT COUNT(*) FROM requests WHERE created_at BETWEEN ? AND ? AND status != \'spam\'', [$now - 14 * 86400, $now - 7 * 86400]);
    $pub = (int) ($frame['counts']['published'] ?? 0);
    $pubSale = $c("SELECT COUNT(*) FROM listings WHERE status = 'published' AND purpose = 'sale'");
    $drafts = (int) ($frame['counts']['draft'] ?? 0);
    $views = $c("SELECT COALESCE(SUM(views), 0) FROM listings WHERE status = 'published'");

    // Requests per day, last 14 days (Dubai time)
    $days = [];
    $start = dubai($now)->setTime(0, 0)->modify('-13 days');
    for ($i = 0; $i < 14; $i++) $days[$start->modify("+$i days")->format('Y-m-d')] = 0;
    $st = $db->prepare("SELECT created_at FROM requests WHERE created_at >= ? AND status != 'spam'");
    $st->execute([$start->getTimestamp()]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $ts) {
        $k = dubai((int) $ts)->format('Y-m-d');
        if (isset($days[$k])) $days[$k]++;
    }
    $max = max(1, ...array_values($days));

    $waiting = $db->query("SELECT * FROM requests WHERE status = 'new' ORDER BY created_at ASC LIMIT 5")->fetchAll();
    $noPermit = $db->query("SELECT id, title, cover FROM listings WHERE status = 'published' AND permit = '' ORDER BY updated_at DESC LIMIT 5")->fetchAll();
    $draftRows = $db->query("SELECT id, title, cover, photos, price, community, updated_at FROM listings WHERE status = 'draft' ORDER BY updated_at DESC LIMIT 4")->fetchAll();
    $top = $db->query("SELECT l.id, l.title, l.cover, l.price, l.rent_period, l.purpose, l.views, l.community,
        (SELECT COUNT(*) FROM requests r WHERE r.listing_id = l.id AND r.status != 'spam') AS req_n
        FROM listings l WHERE l.status = 'published' ORDER BY req_n DESC, l.views DESC LIMIT 5")->fetchAll();
    $recent = $db->query('SELECT * FROM requests ORDER BY created_at DESC LIMIT 6')->fetchAll();

    $hour = (int) dubai($now)->format('G');
    $hello = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $delta = $week - $prevWeek;

    admin_head('Dashboard', 'dashboard', $frame + ['actions' => '<a class="btn btn-primary" href="?p=property&amp;id=0">' . ai('plus') . '<span>New property</span></a>']);
    echo props_hidden_note('p=dashboard');
    ?>
      <section class="hello">
        <div>
          <p class="hello-date"><?= e(dubai($now)->format('l, j F Y')) ?> · Dubai</p>
          <h2 class="hello-h"><?= $hello ?>.</h2>
          <p class="hello-sub"><?= $new ? ($new === 1 ? 'One new request is' : $new . ' new requests are') . ' waiting for a reply.' : 'You’re all caught up on requests.' ?></p>
        </div>
      </section>

      <section class="stats" aria-label="Summary">
        <a class="stat stat-hot<?= $new ? ' is-on' : '' ?>" href="?p=requests&amp;status=new">
          <span class="stat-icon"><?= ai('bell') ?></span>
          <span class="stat-label">New requests</span>
          <span class="stat-n"><?= $new ?></span>
          <span class="stat-foot"><?= $new ? 'Reply soon: buyers move fast' : 'Nothing waiting' ?></span>
        </a>
        <a class="stat" href="?p=requests">
          <span class="stat-icon"><?= ai('inbox') ?></span>
          <span class="stat-label">Requests, last 7 days</span>
          <span class="stat-n"><?= $week ?></span>
          <span class="stat-foot"><?php if ($prevWeek || $week): ?><b class="<?= $delta >= 0 ? 'up' : 'down' ?>"><?= $delta >= 0 ? '+' : '−' ?><?= abs($delta) ?></b> vs the week before<?php else: ?>No requests yet<?php endif; ?></span>
        </a>
        <a class="stat" href="?p=properties&amp;status=published">
          <span class="stat-icon"><?= ai('building') ?></span>
          <span class="stat-label">On the website</span>
          <span class="stat-n"><?= $pub ?></span>
          <span class="stat-foot"><?= $pubSale ?> for sale · <?= $pub - $pubSale ?> for rent</span>
        </a>
        <a class="stat" href="?p=properties&amp;status=draft">
          <span class="stat-icon"><?= ai('edit') ?></span>
          <span class="stat-label">Drafts</span>
          <span class="stat-n"><?= $drafts ?></span>
          <span class="stat-foot"><?= number_format($views) ?> page views on live homes</span>
        </a>
      </section>

      <div class="dash">
        <section class="card dash-chart">
          <div class="card-head">
            <div><h2>Requests</h2><p class="hint">Last 14 days</p></div>
            <a class="link" href="?p=requests">Open inbox</a>
          </div>
          <div class="bars" role="img" aria-label="Requests per day over the last 14 days: <?= e(implode(', ', array_values($days))) ?>">
<?php foreach ($days as $d => $n): $dt = new DateTimeImmutable($d); ?>
            <div class="bar<?= $d === dubai($now)->format('Y-m-d') ? ' is-today' : '' ?>" title="<?= e($dt->format('D j M')) ?>: <?= $n ?>">
              <span class="bar-n"><?= $n ?: '' ?></span>
              <span class="bar-fill" style="height: <?= $n ? max(6, (int) round($n / $max * 100)) : 2 ?>%"></span>
              <span class="bar-d"><?= e($dt->format('j')) ?></span>
            </div>
<?php endforeach; ?>
          </div>
        </section>

        <section class="card dash-todo">
          <div class="card-head"><h2>Needs attention</h2></div>
<?php if (!$waiting && !$noPermit && !$draftRows): ?>
          <?= empty_state('check', 'All clear', 'No requests waiting, and every live home has its permit number.') ?>
<?php else: ?>
          <ul class="todo">
<?php foreach ($waiting as $r): $l = json_decode($r['listing'], true) ?: []; ?>
            <li><a href="?p=request&amp;id=<?= (int) $r['id'] ?>"><?= avatar($r['name']) ?><span class="todo-body"><b><?= e($r['name']) ?></b> wants to <?= e(strtolower(INTENTS[$r['intent']] ?? 'ask')) ?><small dir="auto"><?= e($l['title'] ?? '') ?></small></span><span class="todo-when"><?= ai('clock') ?><?= e(when((int) $r['created_at'])) ?></span></a></li>
<?php endforeach; ?>
<?php foreach ($noPermit as $l): ?>
            <li><a href="?p=property&amp;id=<?= (int) $l['id'] ?>#f-permit"><span class="todo-icon is-warn"><?= ai('alert') ?></span><span class="todo-body"><b>Permit number missing</b><small dir="auto"><?= e(listing_title($l)) ?></small></span><span class="todo-when">Add it</span></a></li>
<?php endforeach; ?>
<?php foreach ($draftRows as $l): ?>
            <li><a href="?p=property&amp;id=<?= (int) $l['id'] ?>"><span class="todo-icon"><?= ai('edit') ?></span><span class="todo-body"><b>Draft</b><small dir="auto"><?= e(listing_title($l)) ?><?= ($m = publish_missing_short($l)) ? ' · needs ' . e($m) : ' · ready to publish' ?></small></span><span class="todo-when"><?= e(when((int) $l['updated_at'])) ?></span></a></li>
<?php endforeach; ?>
          </ul>
<?php endif; ?>
        </section>

        <section class="card dash-top">
          <div class="card-head"><div><h2>Most asked about</h2><p class="hint">Live homes by requests, then views</p></div><a class="link" href="?p=properties">All properties</a></div>
<?php if (!$top): ?>
          <?= empty_state('building', 'No live properties yet', 'Add your first property with photos and publish it. It appears here once it’s on the website.', '<a class="btn btn-primary btn-sm" href="?p=property&amp;id=0">' . ai('plus') . 'New property</a>') ?>
<?php else: ?>
          <ol class="rank">
<?php foreach ($top as $i => $l): ?>
            <li><a href="?p=property&amp;id=<?= (int) $l['id'] ?>"><span class="rank-n"><?= $i + 1 ?></span><?= thumb((string) $l['cover']) ?><span class="rank-body"><b dir="auto"><?= e(listing_title($l)) ?></b><small><?= e(aed((int) $l['price'], (string) $l['rent_period'])) ?> · <?= e($l['community']) ?></small></span><span class="rank-stats"><span title="Requests"><?= ai('inbox') ?><?= (int) $l['req_n'] ?></span><span title="Page views"><?= ai('eye') ?><?= number_format((int) $l['views']) ?></span></span></a></li>
<?php endforeach; ?>
          </ol>
<?php endif; ?>
        </section>

        <section class="card dash-recent">
          <div class="card-head"><h2>Latest requests</h2><a class="link" href="?p=requests">See all</a></div>
          <?php request_list($recent, true); ?>
        </section>
      </div>
<?php
    admin_foot();
}

function publish_missing_short(array $l): string
{
    $m = [];
    if (trim((string) $l['title']) === '') $m[] = 'title';
    if ((int) $l['price'] <= 0) $m[] = 'price';
    if (trim((string) $l['community']) === '') $m[] = 'community';
    if (!prop_photo_paths($l)) $m[] = 'photos';
    return implode(', ', $m);
}

/* ======================================================================
   Properties
   ====================================================================== */

function page_properties(array $frame): void
{
    $db = db();
    $status = is_string($_GET['status'] ?? null) && array_key_exists($_GET['status'], PROP_STATUSES) ? $_GET['status'] : '';
    $for = in_array($_GET['for'] ?? '', ['sale', 'rent'], true) ? $_GET['for'] : '';
    $q = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 80) : '';
    $sorts = ['updated' => 'Recently edited', 'newest' => 'Newest first', 'price-desc' => 'Price: high to low', 'price-asc' => 'Price: low to high', 'requests' => 'Most requests', 'views' => 'Most views'];
    $sort = is_string($_GET['sort'] ?? null) && isset($sorts[$_GET['sort']]) ? $_GET['sort'] : 'updated';
    $view = ($_COOKIE['admin_view'] ?? '') === 'list' ? 'list' : 'grid';

    $where = ['1 = 1'];
    $params = [];
    if ($status !== '') { $where[] = 'l.status = ?'; $params[] = $status; }
    if ($for !== '') { $where[] = 'l.purpose = ?'; $params[] = $for; }
    if ($q !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
        $where[] = '(l.title LIKE ? OR l.title_ar LIKE ? OR l.community LIKE ? OR l.location LIKE ? OR l.reference LIKE ? OR l.permit LIKE ? OR l.id = ?)';
        array_push($params, $like, $like, $like, $like, $like, $like, ctype_digit($q) ? (int) $q : 0);
    }
    $w = implode(' AND ', $where);
    $order = [
        'updated' => 'l.updated_at DESC', 'newest' => 'l.created_at DESC', 'price-desc' => 'l.price_year DESC', 'price-asc' => 'l.price_year ASC',
        'requests' => 'req_n DESC, l.updated_at DESC', 'views' => 'l.views DESC',
    ][$sort];
    $st = $db->prepare("SELECT COUNT(*) FROM listings l WHERE $w");
    $st->execute($params);
    $total = (int) $st->fetchColumn();
    $per = 24;
    $pages = max(1, (int) ceil($total / $per));
    $page = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
    $st = $db->prepare("SELECT l.*, (SELECT COUNT(*) FROM requests r WHERE r.listing_id = l.id AND r.status != 'spam') AS req_n,
        (SELECT COUNT(*) FROM requests r WHERE r.listing_id = l.id AND r.status = 'new') AS req_new
        FROM listings l WHERE $w ORDER BY l.featured DESC, $order, l.id DESC LIMIT $per OFFSET " . (($page - 1) * $per));
    $st->execute($params);
    $rows = $st->fetchAll();

    $base = array_filter(['p' => 'properties', 'status' => $status, 'for' => $for, 'q' => $q, 'sort' => $sort !== 'updated' ? $sort : ''], fn($v) => $v !== '');
    $back = http_build_query($base + ($page > 1 ? ['page' => $page] : []));
    $all = array_sum(array_map('intval', $frame['counts']));

    admin_head('Properties', 'properties', $frame + ['actions' => '<a class="btn btn-primary" href="?p=property&amp;id=0">' . ai('plus') . '<span>New property</span></a>']);
    echo props_hidden_note($back);
    ?>
      <div class="toolbar">
        <nav class="tabs" aria-label="Filter by status">
          <a href="?<?= e(http_build_query(array_diff_key($base, ['status' => 1]))) ?>"<?= $status === '' ? ' aria-current="page"' : '' ?>>All <span><?= $all ?></span></a>
<?php foreach (PROP_STATUSES as $k => $label): ?>
          <a href="?<?= e(http_build_query(['status' => $k] + $base)) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><i class="dot dot-<?= $k ?>"></i><?= e($label) ?> <span><?= (int) ($frame['counts'][$k] ?? 0) ?></span></a>
<?php endforeach; ?>
        </nav>
        <form class="filters" method="get" data-autosubmit>
          <input type="hidden" name="p" value="properties"><?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?>
          <label class="search-field"><span class="sr-only">Search properties</span><?= ai('search') ?><input name="q" type="search" value="<?= e($q) ?>" placeholder="Title, community, reference…"></label>
          <label class="select-field"><span class="sr-only">For sale or rent</span><select name="for"><option value="">Sale &amp; rent</option><option value="sale"<?= $for === 'sale' ? ' selected' : '' ?>>For sale</option><option value="rent"<?= $for === 'rent' ? ' selected' : '' ?>>For rent</option></select></label>
          <label class="select-field"><span class="sr-only">Sort</span><select name="sort"><?php foreach ($sorts as $k => $label): ?><option value="<?= $k ?>"<?= $sort === $k ? ' selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></label>
          <noscript><button class="btn btn-quiet btn-sm" type="submit">Apply</button></noscript>
          <div class="view-toggle" role="group" aria-label="Layout">
            <button type="button" class="icon-btn<?= $view === 'grid' ? ' is-on' : '' ?>" data-view="grid" aria-pressed="<?= $view === 'grid' ? 'true' : 'false' ?>" title="Cards"><?= ai('grid') ?></button>
            <button type="button" class="icon-btn<?= $view === 'list' ? ' is-on' : '' ?>" data-view="list" aria-pressed="<?= $view === 'list' ? 'true' : 'false' ?>" title="List"><?= ai('list') ?></button>
          </div>
        </form>
      </div>

<?php if (!$rows): ?>
      <section class="card">
<?php if ($all === 0): ?>
        <?= empty_state('building', 'Add your first property', 'Upload its photos, fill in the price, size and location, and publish it. It appears on the Properties page of the website straight away.', '<a class="btn btn-primary" href="?p=property&amp;id=0">' . ai('plus') . 'New property</a>') ?>
<?php else: ?>
        <?= empty_state('search', 'Nothing matches', 'Try another search, or clear the filters.', '<a class="btn btn-quiet btn-sm" href="?p=properties">Clear filters</a>') ?>
<?php endif; ?>
      </section>
<?php else: ?>
      <p class="result-n"><?= number_format($total) ?> <?= $total === 1 ? 'property' : 'properties' ?></p>
      <ul class="plist plist-<?= $view ?>" data-plist>
<?php foreach ($rows as $l): $lid = (int) $l['id']; $n = count(prop_photo_paths($l)); ?>
        <li class="pitem status-<?= e($l['status']) ?>">
          <a class="pitem-media" href="?p=property&amp;id=<?= $lid ?>" tabindex="-1" aria-hidden="true">
            <?= thumb((string) $l['cover'], 'pitem-img') ?>
            <span class="pitem-badges"><?= listing_pill($l['status']) ?><?php if ($l['featured']): ?><span class="pill pill-gold"><?= ai('star') ?>Pick</span><?php endif; ?></span>
            <?php if ($n): ?><span class="pitem-count"><?= ai('image') ?><?= $n ?></span><?php endif; ?>
          </a>
          <div class="pitem-body">
            <p class="pitem-price"><?= e(aed((int) $l['price'], (string) $l['rent_period'])) ?><small><?= $l['purpose'] === 'rent' ? 'Rent' : 'Sale' ?> · <?= e(TYPE_LABELS[$l['type']] ?? $l['type']) ?></small></p>
            <h3 class="pitem-title"><a href="?p=property&amp;id=<?= $lid ?>" dir="auto"><?= e(listing_title($l)) ?></a></h3>
            <p class="pitem-loc"><?= ai('pin') ?><span dir="auto"><?= e(trim($l['location'] . ($l['location'] !== '' && $l['community'] !== '' ? ', ' : '') . $l['community']) ?: 'No location yet') ?></span></p>
            <?= specs_line($l) ?>
          </div>
          <div class="pitem-foot">
            <span class="pitem-stats">
              <a href="?p=requests&amp;listing=<?= $lid ?>" title="Requests"><?= ai('inbox') ?><?= (int) $l['req_n'] ?><?php if ((int) $l['req_new']): ?><b class="dot-new" title="<?= (int) $l['req_new'] ?> new"></b><?php endif; ?></a>
              <span title="Page views"><?= ai('eye') ?><?= number_format((int) $l['views']) ?></span>
              <span class="pitem-ref"><?= e($l['reference']) ?></span>
            </span>
            <span class="pitem-actions">
              <a class="icon-btn" href="?p=property&amp;id=<?= $lid ?>" title="Edit"><?= ai('edit') ?><span class="sr-only">Edit</span></a>
              <details class="menu">
                <summary class="icon-btn" title="More"><?= ai('more') ?><span class="sr-only">More actions for <?= e(listing_title($l)) ?></span></summary>
                <div class="menu-list">
                  <a href="<?= e(listing_public_url($l)) ?>" target="_blank" rel="noopener"><?= ai($l['status'] === 'published' ? 'external' : 'eye') ?><?= $l['status'] === 'published' ? 'View on website' : 'Preview' ?></a>
<?php foreach (PROP_STATUSES as $k => $label): if ($k === $l['status']) continue; ?>
                  <button form="act" type="submit" name="act" value="listing-status|<?= $lid ?>|<?= $k ?>"><i class="dot dot-<?= $k ?>"></i>Mark as <?= strtolower($label) ?></button>
<?php endforeach; ?>
                  <button form="act" type="submit" name="act" value="listing-feature|<?= $lid ?>|<?= $l['featured'] ? '0' : '1' ?>"><?= ai('star') ?><?= $l['featured'] ? 'Remove Sadan pick' : 'Make a Sadan pick' ?></button>
                  <button form="act" type="submit" name="act" value="listing-duplicate|<?= $lid ?>|"><?= ai('copy') ?>Duplicate</button>
                  <button form="act" type="submit" class="danger" name="act" value="listing-delete|<?= $lid ?>|" data-confirm="Delete “<?= e(listing_title($l)) ?>” and its photos for good? Requests about it are kept."><?= ai('trash') ?>Delete</button>
                </div>
              </details>
            </span>
          </div>
        </li>
<?php endforeach; ?>
      </ul>
      <form id="act" method="post" action="<?= ADMIN ?>" hidden><?= csrf_field() ?><input type="hidden" name="back" value="<?= e($back) ?>"></form>
<?php pager($page, $pages, $base); endif;
    admin_foot();
}

function listing_public_url(array $l): string
{
    $slug = trim(substr(slugify((string) $l['title']), 0, 70), '-');
    $u = SITE_BASE . '/properties/' . (int) $l['id'] . ($slug !== '' ? '-' . $slug : '');
    return $l['status'] === 'published' ? $u : $u . '?preview=' . prop_preview_token((int) $l['id']);
}

/* ---------- property editor ---------- */

function page_property(int $id, array $frame): void
{
    $l = $id ? prop_get($id) : null;
    if ($id && !$l) {
        admin_head('Not found', 'properties', $frame + ['crumbs' => [['Properties', '?p=properties']]]);
        echo '<section class="card">' . empty_state('search', 'This property doesn’t exist', 'It may have been deleted.', '<a class="btn btn-quiet btn-sm" href="?p=properties">Back to properties</a>') . '</section>';
        admin_foot();
        return;
    }
    $isNew = !$l;
    $l = $l ?? [
        'id' => 0, 'status' => 'draft', 'featured' => 0, 'purpose' => 'sale', 'type' => 'apartment', 'title' => '', 'title_ar' => '', 'description' => '', 'description_ar' => '',
        'price' => 0, 'rent_period' => 'yearly', 'beds' => null, 'baths' => null, 'size_sqft' => null, 'community' => '', 'community_ar' => '', 'location' => '', 'location_ar' => '',
        'lat' => null, 'lng' => null, 'completion' => '', 'furnishing' => '', 'cover' => '', 'photos' => '[]', 'amenities' => '[]', 'agent' => setting('default_agent'),
        'permit' => '', 'reference' => next_reference(), 'views' => 0, 'listed_at' => 0, 'created_at' => 0, 'updated_at' => 0,
    ];
    $v = fn(string $k) => e((string) ($l[$k] ?? ''));
    $checked = fn(bool $on) => $on ? ' checked' : '';
    $photos = prop_photo_paths($l);
    $amen = json_decode($l['amenities'] ?: '[]', true) ?: [];
    $amenOther = array_values(array_diff($amen, AMENITIES));
    $reqN = 0;
    $reqNew = 0;
    if (!$isNew) {
        $st = db()->prepare("SELECT COUNT(*), SUM(status = 'new') FROM requests WHERE listing_id = ? AND status != 'spam'");
        $st->execute([$id]);
        [$reqN, $reqNew] = array_map('intval', $st->fetch(PDO::FETCH_NUM));
    }
    // Communities for suggestions: our area guides plus ones already used
    $communities = [];
    foreach (areas() as $slug => $a) $communities[] = strip_tags($GLOBALS['T_EN']['area.' . $a['key']] ?? ucwords(str_replace('-', ' ', $slug)));
    foreach (db()->query("SELECT DISTINCT community FROM listings WHERE community != '' ORDER BY community LIMIT 300")->fetchAll(PDO::FETCH_COLUMN) as $cm) $communities[] = $cm;
    $communities = array_values(array_unique($communities));
    sort($communities);

    $title = $isNew ? 'New property' : e(listing_title($l));
    $actions = '';
    if (!$isNew) {
        $actions .= '<a class="btn btn-quiet" href="' . e(listing_public_url($l)) . '" target="_blank" rel="noopener">' . ai($l['status'] === 'published' ? 'external' : 'eye') . '<span>' . ($l['status'] === 'published' ? 'View on website' : 'Preview') . '</span></a>';
    }
    $actions .= '<button class="btn btn-primary" type="submit" form="listing-form">' . ai('check') . '<span>' . ($isNew ? 'Create property' : 'Save changes') . '</span></button>';

    admin_head($title, 'properties', $frame + ['crumbs' => [['Properties', '?p=properties']], 'actions' => $actions, 'leaflet' => true, 'body' => 'is-editor']);
    ?>
      <form id="listing-form" class="editor" method="post" enctype="multipart/form-data" action="<?= ADMIN ?>" data-editor data-dirty-guard novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="do" value="listing-save">
        <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">

        <div class="editor-main">
          <section class="card" id="photos" aria-labelledby="h-photos">
            <div class="card-head">
              <div><h2 id="h-photos">Photos</h2><p class="hint">Drag to change the order. The first photo is the cover. JPEG, PNG or WebP, up to 20 MB each.</p></div>
              <span class="hint" data-photo-count><?= count($photos) ?> / <?= PHOTO_MAX_PER_LISTING ?></span>
            </div>
            <div class="uploader" data-uploader data-max="<?= PHOTO_MAX_PER_LISTING ?>">
              <ul class="photos" data-photo-list aria-label="Photos, in order">
<?php foreach ($photos as $i => $path): ?>
                <li class="photo" draggable="true" data-path="<?= e($path) ?>">
                  <img src="<?= e(photo_url($path, 'md')) ?>" alt="Photo <?= $i + 1 ?>" loading="lazy">
                  <input type="hidden" name="photos[]" value="<?= e($path) ?>">
                  <span class="photo-cover">Cover</span>
                  <span class="photo-tools">
                    <button type="button" class="icon-btn" data-move="-1" title="Move earlier"><?= ai('left') ?><span class="sr-only">Move earlier</span></button>
                    <button type="button" class="icon-btn" data-move="1" title="Move later"><?= ai('right') ?><span class="sr-only">Move later</span></button>
                    <button type="button" class="icon-btn" data-remove title="Remove"><?= ai('trash') ?><span class="sr-only">Remove photo</span></button>
                  </span>
                </li>
<?php endforeach; ?>
              </ul>
              <label class="drop" data-drop>
                <input class="sr-only" type="file" name="new_photos[]" accept="image/jpeg,image/png,image/webp,image/avif" multiple data-file>
                <span class="drop-icon"><?= ai('upload') ?></span>
                <span class="drop-text"><b>Drop photos here</b> or <u>choose from your computer</u></span>
                <span class="hint">Landscape photos at least 1600 px wide look best. Location data in the files is removed.</span>
              </label>
              <ul class="upload-errors" data-upload-errors aria-live="polite"></ul>
            </div>
          </section>

          <section class="card" aria-labelledby="h-basics">
            <div class="card-head"><h2 id="h-basics">Basics</h2></div>
            <div class="grid-2">
              <fieldset class="field">
                <legend class="label">Listing</legend>
                <div class="seg" data-purpose>
                  <label><input type="radio" name="purpose" value="sale"<?= $checked($l['purpose'] === 'sale') ?>><span>For sale</span></label>
                  <label><input type="radio" name="purpose" value="rent"<?= $checked($l['purpose'] === 'rent') ?>><span>For rent</span></label>
                </div>
              </fieldset>
              <label class="field"><span class="label">Property type</span>
                <select name="type"><?php foreach (TYPE_LABELS as $k => $label): ?><option value="<?= $k ?>"<?= $l['type'] === $k ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select>
              </label>
            </div>
            <label class="field"><span class="label">Title <small>English · shown on the cards and the property page</small></span>
              <input name="title" value="<?= $v('title') ?>" maxlength="200" placeholder="e.g. Bright 2-bedroom with marina view" data-count data-check-src="title">
            </label>
            <label class="field"><span class="label">Title in Arabic <small>Optional · shown on the Arabic site</small></span>
              <input name="title_ar" value="<?= $v('title_ar') ?>" maxlength="200" dir="rtl" lang="ar" placeholder="مثال: شقة بغرفتي نوم بإطلالة على المارينا" data-check-src="arabic">
            </label>
          </section>

          <section class="card" aria-labelledby="h-price">
            <div class="card-head"><h2 id="h-price">Price and size</h2></div>
            <div class="grid-2">
              <label class="field"><span class="label">Price</span>
                <span class="affix"><span class="affix-l">AED</span><input name="price" value="<?= (int) $l['price'] ? number_format((int) $l['price']) : '' ?>" inputmode="numeric" autocomplete="off" placeholder="0" data-money data-check-src="price"></span>
              </label>
              <label class="field" data-rent-only><span class="label">Rent is per</span>
                <select name="rent_period"><?php foreach (['yearly' => 'Year', 'monthly' => 'Month', 'weekly' => 'Week', 'daily' => 'Day'] as $k => $label): ?><option value="<?= $k ?>"<?= ($l['rent_period'] ?: 'yearly') === $k ? ' selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select>
              </label>
              <label class="field"><span class="label">Size <small data-sqm></small></span>
                <span class="affix"><input name="size_sqft" value="<?= $l['size_sqft'] ? (int) $l['size_sqft'] : '' ?>" inputmode="numeric" autocomplete="off" placeholder="0" data-size><span class="affix-r">sq ft</span></span>
              </label>
              <p class="field field-note" data-ppsf></p>
            </div>
            <fieldset class="field">
              <legend class="label">Bedrooms</legend>
              <div class="chips">
<?php foreach (['' => 'Not set', 'studio' => 'Studio', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7+'] as $k => $label):
    $on = $k === '' ? $l['beds'] === null : ($k === 'studio' ? $l['beds'] !== null && (int) $l['beds'] === 0 : $l['beds'] !== null && (int) $l['beds'] === (int) $k); ?>
                <label class="chip"><input type="radio" name="beds" value="<?= $k ?>"<?= $checked($on) ?>><span><?= $label ?></span></label>
<?php endforeach; ?>
              </div>
            </fieldset>
            <fieldset class="field">
              <legend class="label">Bathrooms</legend>
              <div class="chips">
<?php foreach (['' => 'Not set', '1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6', '7' => '7+'] as $k => $label):
    $on = $k === '' ? $l['baths'] === null : $l['baths'] !== null && (int) $l['baths'] === (int) $k; ?>
                <label class="chip"><input type="radio" name="baths" value="<?= $k ?>"<?= $checked($on) ?>><span><?= $label ?></span></label>
<?php endforeach; ?>
              </div>
            </fieldset>
            <div class="grid-2">
              <fieldset class="field" data-sale-only>
                <legend class="label">Completion</legend>
                <div class="chips">
<?php foreach (['' => 'Not set', 'ready' => 'Ready', 'offplan' => 'Off-plan'] as $k => $label): ?>
                  <label class="chip"><input type="radio" name="completion" value="<?= $k ?>"<?= $checked($l['completion'] === $k) ?>><span><?= $label ?></span></label>
<?php endforeach; ?>
                </div>
              </fieldset>
              <fieldset class="field">
                <legend class="label">Furnishing</legend>
                <div class="chips">
<?php foreach (['' => 'Not set', 'furnished' => 'Furnished', 'partly' => 'Partly', 'unfurnished' => 'Unfurnished'] as $k => $label): ?>
                  <label class="chip"><input type="radio" name="furnishing" value="<?= $k ?>"<?= $checked($l['furnishing'] === $k) ?>><span><?= $label ?></span></label>
<?php endforeach; ?>
                </div>
              </fieldset>
            </div>
          </section>

          <section class="card" aria-labelledby="h-loc">
            <div class="card-head"><div><h2 id="h-loc">Location</h2><p class="hint">The community groups homes in the website’s Area filter.</p></div></div>
            <div class="grid-2">
              <label class="field"><span class="label">Community</span>
                <input name="community" value="<?= $v('community') ?>" list="communities" maxlength="120" placeholder="e.g. Dubai Marina" autocomplete="off" data-check-src="community">
              </label>
              <label class="field"><span class="label">Community in Arabic <small>Optional</small></span>
                <input name="community_ar" value="<?= $v('community_ar') ?>" maxlength="120" dir="rtl" lang="ar" placeholder="مثال: دبي مارينا">
              </label>
              <label class="field"><span class="label">Building or street <small>Optional</small></span>
                <input name="location" value="<?= $v('location') ?>" maxlength="200" placeholder="e.g. Silverene Tower A">
              </label>
              <label class="field"><span class="label">Building in Arabic <small>Optional</small></span>
                <input name="location_ar" value="<?= $v('location_ar') ?>" maxlength="200" dir="rtl" lang="ar">
              </label>
            </div>
            <datalist id="communities"><?php foreach ($communities as $cm): ?><option value="<?= e($cm) ?>"><?php endforeach; ?></datalist>
            <div class="field">
              <span class="label">Pin on the map <small>Optional · click the map to place the pin, drag it to adjust</small></span>
              <div class="map" data-map data-lat="<?= $l['lat'] !== null ? (float) $l['lat'] : '' ?>" data-lng="<?= $l['lng'] !== null ? (float) $l['lng'] : '' ?>"><p class="map-fallback">The map loads here. You can also type the coordinates below.</p></div>
              <div class="map-row">
                <label class="field-inline"><span>Lat</span><input name="lat" value="<?= $l['lat'] !== null ? e((string) (float) $l['lat']) : '' ?>" inputmode="decimal" data-lat></label>
                <label class="field-inline"><span>Lng</span><input name="lng" value="<?= $l['lng'] !== null ? e((string) (float) $l['lng']) : '' ?>" inputmode="decimal" data-lng></label>
                <button class="btn btn-quiet btn-sm" type="button" data-map-find><?= ai('search') ?>Find the community</button>
                <button class="btn btn-quiet btn-sm" type="button" data-map-clear>Clear pin</button>
              </div>
            </div>
          </section>

          <section class="card" aria-labelledby="h-desc">
            <div class="card-head"><div><h2 id="h-desc">Description</h2><p class="hint">Leave an empty line between paragraphs.</p></div></div>
            <label class="field"><span class="label">English</span>
              <textarea name="description" rows="9" maxlength="10000" placeholder="What makes this home special: layout, light, view, condition, building and community…" data-count data-check-src="description"><?= $v('description') ?></textarea>
            </label>
            <label class="field"><span class="label">Arabic <small>Optional</small></span>
              <textarea name="description_ar" rows="7" maxlength="10000" dir="rtl" lang="ar"><?= $v('description_ar') ?></textarea>
            </label>
          </section>

          <section class="card" aria-labelledby="h-amen">
            <div class="card-head"><div><h2 id="h-amen">Amenities and features</h2><p class="hint">Shown in English and Arabic on the website.</p></div></div>
            <div class="ticks">
<?php foreach (AMENITIES as $k): ?>
              <label class="tick"><input type="checkbox" name="amenities[]" value="<?= $k ?>"<?= $checked(in_array($k, $amen, true)) ?>><span><?= ai('check') ?><?= e(amen_en($k)) ?></span></label>
<?php endforeach; ?>
            </div>
            <label class="field"><span class="label">Other features <small>Separate with commas · shown as written</small></span>
              <input name="amenities_other" value="<?= e(implode(', ', $amenOther)) ?>" maxlength="1000" placeholder="e.g. Upgraded kitchen, Vastu compliant">
            </label>
          </section>

          <section class="card" aria-labelledby="h-ref">
            <div class="card-head"><div><h2 id="h-ref">Listing details</h2><p class="hint">Dubai rules require the Trakheesi advertising permit number on every property advert.</p></div></div>
            <div class="grid-3">
              <label class="field"><span class="label">Reference</span><input name="reference" value="<?= $v('reference') ?>" maxlength="60" dir="ltr"></label>
              <label class="field" id="f-permit"><span class="label">Permit number <small>Trakheesi</small></span><input name="permit" value="<?= $v('permit') ?>" maxlength="60" dir="ltr" inputmode="numeric" placeholder="e.g. 7112345678" data-check-src="permit"></label>
              <label class="field"><span class="label">Agent <small>Internal</small></span><input name="agent" value="<?= $v('agent') ?>" maxlength="120" placeholder="Who handles it"></label>
            </div>
          </section>
        </div>

        <aside class="editor-side">
          <section class="card side-card">
            <h2 class="side-h">Publishing</h2>
            <label class="field"><span class="label">Status</span>
              <span class="status-select"><i class="dot dot-<?= e($l['status']) ?>" data-status-dot></i>
                <select name="status" data-status><?php foreach (PROP_STATUSES as $k => $label): ?><option value="<?= $k ?>"<?= $l['status'] === $k ? ' selected' : '' ?>><?= $label ?><?= $k === 'published' ? ' (on the website)' : ($k === 'draft' ? ' (hidden)' : '') ?></option><?php endforeach; ?></select>
              </span>
            </label>
            <label class="switch"><input type="checkbox" name="featured" value="1"<?= $checked((bool) $l['featured']) ?>><span class="switch-ui" aria-hidden="true"></span><span><b>Sadan pick</b><small>Shown first, with a gold label</small></span></label>
            <button class="btn btn-primary btn-block" type="submit"><?= ai('check') ?><?= $isNew ? 'Create property' : 'Save changes' ?></button>
<?php if (!$isNew): ?>
            <p class="side-meta">Last saved <?= e(when((int) $l['updated_at'])) ?><?php if ((int) $l['listed_at']): ?> · Listed <?= e(dubai((int) $l['listed_at'])->format('j M Y')) ?><?php endif; ?></p>
<?php if ($l['status'] !== 'published'): ?>
            <button class="btn btn-quiet btn-sm btn-block" type="button" data-copy="<?= e(absolute_url(listing_public_url($l))) ?>"><?= ai('link') ?>Copy preview link</button>
<?php endif; ?>
<?php endif; ?>
          </section>

          <section class="card side-card">
            <h2 class="side-h">Ready to publish?</h2>
            <ul class="checklist" data-checklist>
              <li data-check="photos" class="must"><?= ai('check') ?>At least one photo <small>(5+ is better)</small></li>
              <li data-check="title" class="must"><?= ai('check') ?>Title</li>
              <li data-check="price" class="must"><?= ai('check') ?>Price</li>
              <li data-check="community" class="must"><?= ai('check') ?>Community</li>
              <li data-check="description"><?= ai('check') ?>Description</li>
              <li data-check="permit"><?= ai('check') ?>Trakheesi permit number</li>
              <li data-check="arabic"><?= ai('check') ?>Arabic title</li>
            </ul>
            <p class="hint">The first four are needed to publish.</p>
          </section>

<?php if (!$isNew): ?>
          <section class="card side-card">
            <h2 class="side-h">Activity</h2>
            <dl class="mini-stats">
              <div><dt>Requests</dt><dd><a href="?p=requests&amp;listing=<?= (int) $l['id'] ?>"><?= $reqN ?></a><?php if ($reqNew): ?> <span class="pill pill-new"><?= $reqNew ?> new</span><?php endif; ?></dd></div>
              <div><dt>Page views</dt><dd><?= number_format((int) $l['views']) ?></dd></div>
              <div><dt>Created</dt><dd><?= e(dubai((int) $l['created_at'])->format('j M Y')) ?></dd></div>
            </dl>
          </section>
          <section class="card side-card side-actions">
            <button class="btn btn-quiet btn-sm" type="submit" form="dup-form"><?= ai('copy') ?>Duplicate</button>
            <button class="btn btn-danger-quiet btn-sm" type="submit" form="del-form"><?= ai('trash') ?>Delete</button>
          </section>
<?php endif; ?>
        </aside>

        <div class="savebar" data-savebar>
          <span class="savebar-msg" data-savebar-msg>Unsaved changes</span>
          <button class="btn btn-primary" type="submit"><?= ai('check') ?><?= $isNew ? 'Create property' : 'Save changes' ?></button>
        </div>
      </form>
<?php if (!$isNew): ?>
      <form id="dup-form" method="post" action="<?= ADMIN ?>" hidden><?= csrf_field() ?><input type="hidden" name="do" value="listing-duplicate"><input type="hidden" name="id" value="<?= (int) $l['id'] ?>"></form>
      <form id="del-form" method="post" action="<?= ADMIN ?>" hidden data-confirm="Delete this property and all its photos for good? Requests about it are kept."><?= csrf_field() ?><input type="hidden" name="do" value="listing-delete"><input type="hidden" name="id" value="<?= (int) $l['id'] ?>"></form>
<?php endif; ?>
      <script type="application/json" id="admin-cfg"><?= json_encode(['csrf' => csrf(), 'endpoint' => ADMIN], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
<?php
    admin_foot(true);
}

/* ======================================================================
   Requests
   ====================================================================== */

function request_list(array $rows, bool $compact = false): void
{
    if (!$rows) {
        echo empty_state('inbox', 'No requests yet', 'When someone asks about a property on the website, it lands here and is emailed to you.');
        return;
    } ?>
          <ul class="rlist<?= $compact ? ' rlist-compact' : '' ?>">
<?php foreach ($rows as $r): $l = json_decode($r['listing'], true) ?: []; ?>
            <li class="ritem<?= $r['status'] === 'new' ? ' is-new' : '' ?>">
              <a href="?p=request&amp;id=<?= (int) $r['id'] ?>">
                <?= avatar($r['name']) ?>
                <span class="ritem-who"><b><?= e($r['name']) ?></b><small><?= e($r['phone'] ?: $r['email']) ?></small></span>
                <span class="ritem-what"><?= thumb((string) ($l['cover'] ?? ''), 'thumb thumb-sm') ?><span><span class="ritem-prop" dir="auto"><?= e($l['title'] ?? 'Property removed') ?></span><small><?= e(INTENTS[$r['intent']] ?? $r['intent']) ?><?= isset($l['price']) ? ' · ' . e(aed((int) $l['price'], (string) ($l['rent_period'] ?? ''))) : '' ?></small></span></span>
                <span class="ritem-meta"><?= status_pill($r['status']) ?><small><?= e(when((int) $r['created_at'])) ?></small></span>
              </a>
            </li>
<?php endforeach; ?>
          </ul>
<?php }

function page_requests(array $frame): void
{
    $db = db();
    $status = is_string($_GET['status'] ?? null) && array_key_exists($_GET['status'], STATUSES) ? $_GET['status'] : '';
    $q = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 80) : '';
    $listing = (int) ($_GET['listing'] ?? 0);
    $where = ['1 = 1'];
    $params = [];
    if ($status !== '') { $where[] = 'status = ?'; $params[] = $status; }
    else { $where[] = "status != 'spam'"; }
    if ($listing) { $where[] = 'listing_id = ?'; $params[] = $listing; }
    if ($q !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
        $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR listing LIKE ? OR message LIKE ?)';
        array_push($params, $like, $like, $like, $like, $like);
    }
    $w = implode(' AND ', $where);
    $st = $db->prepare("SELECT COUNT(*) FROM requests WHERE $w");
    $st->execute($params);
    $total = (int) $st->fetchColumn();
    $pages = max(1, (int) ceil($total / 30));
    $page = max(1, min($pages, (int) ($_GET['page'] ?? 1)));
    $st = $db->prepare("SELECT * FROM requests WHERE $w ORDER BY (status = 'new') DESC, created_at DESC LIMIT 30 OFFSET " . (($page - 1) * 30));
    $st->execute($params);
    $rows = $st->fetchAll();
    $counts = array_column($db->query('SELECT status, COUNT(*) n FROM requests GROUP BY status')->fetchAll(), 'n', 'status');
    $open = array_sum(array_map('intval', array_diff_key($counts, ['spam' => 1])));
    $lt = $listing ? prop_get($listing) : null;
    $base = array_filter(['p' => 'requests', 'q' => $q, 'listing' => $listing ?: ''], fn($v) => $v !== '');

    admin_head('Requests', 'requests', $frame + ['actions' => '<a class="btn btn-quiet" href="?p=export">' . ai('download') . '<span>Export CSV</span></a>']);
    ?>
      <div class="toolbar">
        <nav class="tabs" aria-label="Filter by status">
          <a href="?<?= e(http_build_query($base)) ?>"<?= $status === '' ? ' aria-current="page"' : '' ?>>All <span><?= $open ?></span></a>
<?php foreach (STATUSES as $k => $label): ?>
          <a href="?<?= e(http_build_query(['status' => $k] + $base)) ?>"<?= $status === $k ? ' aria-current="page"' : '' ?>><?= e($label) ?> <span><?= (int) ($counts[$k] ?? 0) ?></span></a>
<?php endforeach; ?>
        </nav>
        <form class="filters" method="get">
          <input type="hidden" name="p" value="requests"><?php if ($status): ?><input type="hidden" name="status" value="<?= e($status) ?>"><?php endif; ?><?php if ($listing): ?><input type="hidden" name="listing" value="<?= $listing ?>"><?php endif; ?>
          <label class="search-field"><span class="sr-only">Search requests</span><?= ai('search') ?><input name="q" type="search" value="<?= e($q) ?>" placeholder="Name, phone, email, property…"></label>
        </form>
      </div>
<?php if ($listing): ?>
      <p class="filter-note">Showing requests about <b dir="auto"><?= e($lt ? listing_title($lt) : 'property #' . $listing) ?></b> <a class="link" href="?<?= e(http_build_query(array_diff_key(['status' => $status] + $base, ['listing' => 1]))) ?>">Show all</a></p>
<?php endif; ?>
      <section class="card card-flush"><?php request_list($rows); ?></section>
<?php
    pager($page, $pages, ($status ? ['status' => $status] : []) + $base);
    admin_foot();
}

function page_request(int $id, array $frame): void
{
    $st = db()->prepare('SELECT * FROM requests WHERE id = ?');
    $st->execute([$id]);
    $r = $st->fetch();
    if (!$r) {
        admin_head('Not found', 'requests', $frame + ['crumbs' => [['Requests', '?p=requests']]]);
        echo '<section class="card">' . empty_state('search', 'This request doesn’t exist', 'It may have been deleted.', '<a class="btn btn-quiet btn-sm" href="?p=requests">Back to requests</a>') . '</section>';
        admin_foot();
        return;
    }
    $l = json_decode($r['listing'], true) ?: [];
    $live = !empty($l['id']) ? prop_get((int) $l['id']) : null;
    $phone = preg_replace('/[^0-9+]/', '', $r['phone']);
    $wa = preg_replace('/\D/', '', $r['phone']);
    if (strpos($wa, '00') === 0) $wa = substr($wa, 2);
    elseif (strpos($wa, '0') === 0) $wa = '971' . substr($wa, 1);
    $propTitle = $l['title'] ?? 'the property';
    $subject = rawurlencode('Your enquiry about ' . $propTitle . ' — Sadan Real Estate');
    $hello = rawurlencode('Hello ' . $r['name'] . ', this is Sadan Real Estate about your enquiry for ' . $propTitle . '.');
    // Earlier requests from the same person
    $others = [];
    if ($r['phone'] !== '' || $r['email'] !== '') {
        $st = db()->prepare('SELECT * FROM requests WHERE id != ? AND ((phone != \'\' AND phone = ?) OR (email != \'\' AND email = ?)) ORDER BY created_at DESC LIMIT 5');
        $st->execute([$id, $r['phone'], $r['email']]);
        $others = $st->fetchAll();
    }
    if ($r['status'] === 'new') {
        // Opening a new request doesn't change it; the buttons below do.
    }
    admin_head(e($r['name']), 'requests', $frame + ['crumbs' => [['Requests', '?p=requests']], 'actions' => status_pill($r['status'])]);
    ?>
      <div class="detail">
        <div class="detail-main">
          <section class="card person">
            <div class="person-head">
              <?= avatar($r['name']) ?>
              <div><h2><?= e($r['name']) ?></h2><p class="hint">Wants to <b><?= e(strtolower(INTENTS[$r['intent']] ?? 'ask')) ?></b> · <?= e(when((int) $r['created_at'])) ?> · from the <?= $r['lang'] === 'ar' ? 'Arabic' : 'English' ?> page · prefers <?= e($r['contact_pref'] === 'call' ? 'a phone call' : ($r['contact_pref'] === 'whatsapp' ? 'WhatsApp' : 'email')) ?></p></div>
            </div>
            <div class="contact-actions">
<?php if ($r['phone'] !== ''): ?>
              <a class="contact-btn<?= $r['contact_pref'] === 'call' ? ' is-pref' : '' ?>" href="tel:<?= e($phone) ?>"><?= ai('phone') ?><span><b>Call</b><small dir="ltr"><?= e($r['phone']) ?></small></span></a>
<?php if (strlen($wa) >= 8): ?>
              <a class="contact-btn<?= $r['contact_pref'] === 'whatsapp' ? ' is-pref' : '' ?>" href="https://wa.me/<?= e($wa) ?>?text=<?= $hello ?>" target="_blank" rel="noopener"><?= ai('chat') ?><span><b>WhatsApp</b><small dir="ltr">+<?= e($wa) ?></small></span></a>
<?php endif; ?>
<?php endif; ?>
<?php if ($r['email'] !== ''): ?>
              <a class="contact-btn<?= $r['contact_pref'] === 'email' ? ' is-pref' : '' ?>" href="mailto:<?= e($r['email']) ?>?subject=<?= $subject ?>"><?= ai('mail') ?><span><b>Email</b><small><?= e($r['email']) ?></small></span></a>
<?php endif; ?>
            </div>
            <h3 class="sub-h">Message</h3>
            <blockquote class="message" dir="auto"><?= $r['message'] !== '' ? nl2br(e($r['message'])) : '<span class="hint">No message.</span>' ?></blockquote>
          </section>

          <section class="card">
            <div class="card-head"><div><h2>Progress</h2><p class="hint">Move the request along as you follow up.</p></div></div>
            <form method="post" class="steps-bar">
              <?= csrf_field() ?><input type="hidden" name="do" value="request-status"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
<?php foreach (STATUSES as $k => $label): ?>
              <button type="submit" name="status" value="<?= $k ?>" class="step step-<?= $k ?><?= $r['status'] === $k ? ' is-on' : '' ?>"<?= $r['status'] === $k ? ' aria-pressed="true"' : ' aria-pressed="false"' ?>><?= $r['status'] === $k ? ai('check') : '' ?><?= e($label) ?></button>
<?php endforeach; ?>
            </form>
            <form method="post" class="form-stack note-form">
              <?= csrf_field() ?><input type="hidden" name="do" value="request-save"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><input type="hidden" name="status" value="<?= e($r['status']) ?>">
              <label class="field"><span class="label">Private note <small>Only visible here</small></span><textarea name="note" rows="4" placeholder="e.g. Called on Tuesday, viewing Saturday 11:00 with the landlord’s agent"><?= e($r['note']) ?></textarea></label>
              <div class="row-between">
                <button class="btn btn-primary btn-sm" type="submit">Save note</button>
                <button class="btn btn-danger-quiet btn-sm" type="submit" form="req-del"><?= ai('trash') ?>Delete request</button>
              </div>
            </form>
            <form id="req-del" method="post" hidden data-confirm="Delete this request for good?"><?= csrf_field() ?><input type="hidden" name="do" value="request-delete"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"></form>
          </section>
        </div>

        <aside class="detail-side">
          <section class="card snap">
<?php if ($l): ?>
            <?= thumb((string) ($l['cover'] ?? ''), 'snap-img') ?>
            <div class="snap-body">
              <p class="snap-price"><?= e(aed((int) ($l['price'] ?? 0), (string) ($l['rent_period'] ?? ''))) ?> <small>for <?= ($l['purpose'] ?? '') === 'rent' ? 'rent' : 'sale' ?></small></p>
              <h2 class="snap-title" dir="auto"><?= e($l['title'] ?? '') ?></h2>
              <p class="pitem-loc"><?= ai('pin') ?><span dir="auto"><?= e(trim(($l['location'] ?? '') . (!empty($l['location']) && !empty($l['community']) ? ', ' : '') . ($l['community'] ?? ''))) ?></span></p>
              <?= specs_line($l) ?>
              <dl class="mini-stats">
                <div><dt>Reference</dt><dd><?= e($l['reference'] ?? '') ?: '—' ?></dd></div>
                <div><dt>Agent</dt><dd><?= e($l['agent'] ?? '') ?: '—' ?></dd></div>
              </dl>
              <div class="snap-actions">
<?php if ($live): ?>
                <a class="btn btn-quiet btn-sm" href="?p=property&amp;id=<?= (int) $live['id'] ?>"><?= ai('edit') ?>Open property</a>
<?php if ($live['status'] === 'published'): ?><a class="btn btn-quiet btn-sm" href="<?= e(listing_public_url($live)) ?>" target="_blank" rel="noopener"><?= ai('external') ?>On the website</a><?php endif; ?>
<?php else: ?>
                <p class="hint">This property has since been deleted. The details above are what they saw.</p>
<?php endif; ?>
              </div>
<?php if ($live && $live['status'] !== 'published'): ?><p class="hint">Now marked <?= e(strtolower(PROP_STATUSES[$live['status']] ?? $live['status'])) ?>, so it’s off the website.</p><?php endif; ?>
            </div>
<?php else: ?>
            <?= empty_state('building', 'No property saved', 'This request has no property details.') ?>
<?php endif; ?>
          </section>
<?php if ($others): ?>
          <section class="card">
            <div class="card-head"><h2>Also asked about</h2></div>
            <?php request_list($others, true); ?>
          </section>
<?php endif; ?>
        </aside>
      </div>
<?php
    admin_foot();
}

/* ======================================================================
   Settings
   ====================================================================== */

function page_settings(array $frame): void
{
    $uploadsOk = is_dir(UPLOAD_DIR) ? is_writable(UPLOAD_DIR) : is_writable(dirname(UPLOAD_DIR));
    $dbv = '';
    try { $dbv = (string) db()->query('SELECT VERSION()')->fetchColumn(); } catch (Throwable $e) { }
    $photoBytes = (int) db()->query('SELECT COALESCE(SUM(bytes), 0) FROM photos')->fetchColumn();
    $photoN = (int) db()->query('SELECT COUNT(*) FROM photos')->fetchColumn();
    admin_head('Settings', 'settings', $frame);
    ?>
      <div class="settings">
        <section class="card" id="website">
          <div class="card-head"><div><h2><?= ai('eye') ?>Website</h2><p class="hint">Choose what visitors can see.</p></div></div>
          <form method="post" class="form-stack">
            <?= csrf_field() ?><input type="hidden" name="do" value="settings-site"><input type="hidden" name="props_public" value="0">
            <label class="switch"><input type="checkbox" name="props_public" value="1"<?= props_public() ? ' checked' : '' ?>><span class="switch-ui" aria-hidden="true"></span><span><b>Show the Properties page</b><small>When off, the Properties link leaves the menu and footer, property pages are hidden from visitors and Google, and no new requests come in. Your properties stay saved here, and preview links still work.</small></span></label>
            <div><button class="btn btn-primary btn-sm" type="submit">Save</button></div>
          </form>
        </section>

        <section class="card">
          <div class="card-head"><div><h2><?= ai('bell') ?>Notifications and defaults</h2><p class="hint">Where new requests are emailed, and what new properties start with.</p></div></div>
          <form method="post" class="form-stack">
            <?= csrf_field() ?><input type="hidden" name="do" value="settings-notify">
            <label class="field"><span class="label">Email new requests to <small>Empty uses <?= e(SITE_EMAIL) ?></small></span><input name="notify_email" type="email" value="<?= e(setting('notify_email')) ?>" placeholder="<?= e(SITE_EMAIL) ?>" autocomplete="email"></label>
            <div class="grid-2">
              <label class="field"><span class="label">Default agent on new properties <small>Optional</small></span><input name="default_agent" value="<?= e(setting('default_agent')) ?>" maxlength="120"></label>
              <label class="field"><span class="label">Reference prefix <small>e.g. SAD → SAD-0042</small></span><input name="ref_prefix" value="<?= e(setting('ref_prefix', 'SAD')) ?>" maxlength="8" dir="ltr"></label>
            </div>
            <div><button class="btn btn-primary btn-sm" type="submit">Save</button></div>
          </form>
        </section>

        <section class="card" id="password">
          <div class="card-head"><div><h2><?= ai('key') ?>Password</h2><p class="hint">Changing it signs out every other device.</p></div></div>
          <form method="post" class="form-stack">
            <?= csrf_field() ?><input type="hidden" name="do" value="settings-password">
            <input type="text" name="username" value="admin" autocomplete="username" hidden>
            <label class="field"><span class="label">Current password</span><input name="current_password" type="password" required autocomplete="current-password"></label>
            <div class="grid-2">
              <label class="field"><span class="label">New password <small>10+ characters</small></span><input name="new_password" type="password" required minlength="10" autocomplete="new-password"></label>
              <label class="field"><span class="label">Repeat the new password</span><input name="confirm_password" type="password" required minlength="10" autocomplete="new-password"></label>
            </div>
            <div><button class="btn btn-primary btn-sm" type="submit">Change password</button></div>
          </form>
        </section>

        <section class="card">
          <div class="card-head"><div><h2><?= ai('shield') ?>System check</h2><p class="hint">Useful after uploading the site to Hostinger.</p></div></div>
          <ul class="syscheck">
            <li class="ok"><?= ai('check') ?><span><b>Database connected</b><small>MySQL <?= e($dbv) ?> · <?= e((string) constant('DB_NAME')) ?></small></span></li>
            <li class="<?= $uploadsOk ? 'ok' : 'bad' ?>"><?= ai($uploadsOk ? 'check' : 'alert') ?><span><b>Photos folder <?= $uploadsOk ? 'is writable' : 'cannot be written to' ?></b><small><?= $uploadsOk ? $photoN . ' photos · ' . number_format($photoBytes / 1048576, 1) . ' MB' : 'Give the folder “uploads” write permission (755) in Hostinger’s File Manager.' ?></small></span></li>
            <li class="<?= function_exists('imagewebp') ? 'ok' : 'bad' ?>"><?= ai(function_exists('imagewebp') ? 'check' : 'alert') ?><span><b>Image tools <?= function_exists('imagewebp') ? 'available' : 'missing' ?></b><small>PHP <?= e(PHP_VERSION) ?> with GD<?= function_exists('imagewebp') ? ' and WebP' : ': enable the GD extension in hPanel → PHP Configuration' ?></small></span></li>
            <li class="ok"><?= ai('upload') ?><span><b>Largest upload the server accepts</b><small><?= e(ini_get('upload_max_filesize')) ?> per photo · <?= e(ini_get('post_max_size')) ?> per request</small></span></li>
          </ul>
        </section>
      </div>
<?php
    admin_foot();
}

/* ======================================================================
   Search
   ====================================================================== */

function page_search(array $frame): void
{
    $q = is_string($_GET['q'] ?? null) ? mb_substr(trim($_GET['q']), 0, 80) : '';
    $props = $reqs = [];
    if ($q !== '') {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
        $st = db()->prepare('SELECT * FROM listings WHERE title LIKE ? OR title_ar LIKE ? OR community LIKE ? OR location LIKE ? OR reference LIKE ? OR permit LIKE ? OR id = ? ORDER BY updated_at DESC LIMIT 12');
        $st->execute([$like, $like, $like, $like, $like, $like, ctype_digit($q) ? (int) $q : 0]);
        $props = $st->fetchAll();
        $st = db()->prepare('SELECT * FROM requests WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR listing LIKE ? ORDER BY created_at DESC LIMIT 12');
        $st->execute([$like, $like, $like, $like]);
        $reqs = $st->fetchAll();
    }
    admin_head($q !== '' ? 'Results for “' . e($q) . '”' : 'Search', 'search', $frame);
    if ($q === '') {
        echo '<section class="card">' . empty_state('search', 'Search everything', 'Type a name, phone number, email, property title, community or reference in the search box above.') . '</section>';
        admin_foot();
        return;
    } ?>
      <section class="card">
        <div class="card-head"><h2>Properties <span class="hint"><?= count($props) ?></span></h2></div>
<?php if (!$props): ?><p class="hint">No properties match.</p><?php else: ?>
        <ol class="rank">
<?php foreach ($props as $l): ?>
          <li><a href="?p=property&amp;id=<?= (int) $l['id'] ?>"><?= thumb((string) $l['cover']) ?><span class="rank-body"><b dir="auto"><?= e(listing_title($l)) ?></b><small><?= e(aed((int) $l['price'], (string) $l['rent_period'])) ?> · <?= e($l['community']) ?> · <?= e($l['reference']) ?></small></span><?= listing_pill($l['status']) ?></a></li>
<?php endforeach; ?>
        </ol>
<?php endif; ?>
      </section>
      <section class="card card-flush">
        <div class="card-head card-head-pad"><h2>Requests <span class="hint"><?= count($reqs) ?></span></h2></div>
<?php if (!$reqs): ?><p class="hint card-head-pad">No requests match.</p><?php else: request_list($reqs, true); endif; ?>
      </section>
<?php
    admin_foot();
}

/** Whether the public Properties page is switched on (Settings → Website). */
function props_public(): bool
{
    return setting('props_public', '1') === '1';
}

/** Notice shown in the admin while the Properties page is hidden from the website. */
function props_hidden_note(string $back = ''): string
{
    if (props_public()) return '';
    return '<form method="post" class="site-note">' . csrf_field()
        . '<input type="hidden" name="do" value="settings-site"><input type="hidden" name="props_public" value="1">'
        . '<input type="hidden" name="back" value="' . e($back !== '' ? $back : 'p=properties') . '">'
        . ai('eye') . '<p><b>The Properties page is hidden from the website.</b> Visitors can’t see any property right now. You can keep adding and editing properties here.</p>'
        . '<button class="btn btn-quiet btn-sm" type="submit">Show it on the website</button></form>';
}

