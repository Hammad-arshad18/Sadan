<?php
/**
 * One property: gallery, facts, description, map, and the request form
 * (posted to request.php, saved for the admin panel and emailed).
 * Unpublished homes can be checked with the preview link from the admin panel.
 */
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/listings.php';

$id = (int) ($_GET['id'] ?? 0);
$l = null;
try {
    $l = $id > 0 ? prop_get($id) : null;
} catch (Throwable $e) {
    error_log('Sadan property: ' . $e->getMessage());
}
if (!$l) render_404();

$preview = $l['status'] !== 'published' && is_string($_GET['preview'] ?? null)
    && hash_equals(prop_preview_token((int) $l['id']), $_GET['preview']);
if (!props_on() && !$preview) render_404();
$gone = $l['status'] !== 'published' && !$preview;
if ($preview) {
    header('X-Robots-Tag: noindex, nofollow');
} elseif ($gone) {
    http_response_code(410);
} else {
    // One address per home: fix old or mistyped slugs
    $want = prop_url($l);
    $have = SITE_BASE . rtrim($requestPath, '/');
    if ($have !== $want) {
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        header('Location: ' . $want . ($qs !== '' ? '?' . preg_replace('/[^a-z0-9=&_%-]/i', '', $qs) : ''), true, 301);
        exit;
    }
}

$title = prop_title($l);
$photos = prop_photos($l);
$desc = prop_description($l);
$period = prop_period($l);
$specs = array_filter([
    ['bed', prop_beds($l['beds'] === null ? null : (int) $l['beds'])],
    ['bath', prop_baths($l['baths'] === null ? null : (int) $l['baths'])],
    ['size', prop_size($l['size_sqft'] === null ? null : (int) $l['size_sqft'])],
], fn($s) => $s[1] !== '');
$summary = money((int) $l['price']) . ($period !== '' ? ' ' . strip_tags($period) : '') . ' · '
    . implode(' · ', array_map(fn($s) => $s[1], $specs)) . ' · ' . prop_community($l);

$page = [
    'key' => 'property',
    'title' => str_replace('{title}', $title, strip_tags(t('prop.meta.title'))),
    'desc' => $desc !== '' ? mb_strimwidth(preg_replace('/\s+/u', ' ', $desc), 0, 158, '…') : $summary,
    'crumbs' => [[t('nav.properties'), '/properties'], [e(mb_strimwidth($title, 0, 46, '…')), PATH]],
    'robots' => $preview ? 'noindex, nofollow' : '',
    'og_image' => $l['cover'] !== '' ? absolute_url(prop_cover($l, 'lg')) : '',
    'scripts' => ['properties.js'],
];
require __DIR__ . '/inc/head.php';

$similar = [];
try { $similar = prop_similar($l, 3); } catch (Throwable $e) { }

// Simple view count for the admin panel (search engine bots and previews not counted)
if (!$gone && !$preview && !preg_match('/bot|crawl|spider|slurp|preview|facebookexternalhit|whatsapp/i', $_SERVER['HTTP_USER_AGENT'] ?? 'bot')) {
    try { db()->prepare('UPDATE listings SET views = views + 1 WHERE id = ?')->execute([(int) $l['id']]); } catch (Throwable $e) { }
}

if ($gone): ?>
    <section class="page-hero page-hero-plain not-found" aria-labelledby="page-title">
      <span class="watermark" lang="ar" aria-hidden="true">عقارات</span>
      <div class="wrap">
        <div class="page-hero-copy">
          <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span dir="auto"><?= e($title) ?></span></p>
          <h1 id="page-title" class="page-title" data-split><?= t('prop.gone.title') ?></h1>
          <p class="page-lead"><?= t('prop.gone.lead') ?></p>
          <ul class="nf-links">
            <li><a class="btn btn-gold" href="<?= url('/properties') . ($l['purpose'] === 'rent' ? '?for=rent' : '') ?>"><span><?= t('prop.allProps') ?></span><?= arrow() ?></a></li>
          </ul>
        </div>
      </div>
    </section>
<?php else: ?>
    <section class="prop-hero" aria-labelledby="page-title">
      <div class="wrap">
<?php if ($preview): ?>
        <p class="prop-preview" role="status"><?= t('prop.preview') ?></p>
<?php endif; ?>
<?php if ($photos): ?>
        <div class="pg pg-n<?= min(5, count($photos)) ?>" id="gallery" data-photos="<?= e(json_encode($photos, JSON_UNESCAPED_SLASHES)) ?>" aria-label="<?= tp('prop.galleryAria') ?>" role="group">
<?php foreach (array_slice($photos, 0, 5) as $i => $src): ?>
          <button class="pg-item<?= $i === 0 ? ' pg-main' : '' ?>" type="button" data-i="<?= $i ?>" aria-label="<?= e(str_replace(['{n}', '{total}'], [(string) ($i + 1), (string) count($photos)], strip_tags(t('prop.photoOf')))) ?>">
            <img src="<?= e($i === 0 ? $src : prop_photos($l, 'md')[$i]) ?>" alt="" decoding="async"<?= $i === 0 ? ' fetchpriority="high"' : ' loading="lazy"' ?>>
          </button>
<?php endforeach; ?>
<?php if (count($photos) > 1): ?>
          <button class="pg-all" type="button" data-i="0"><?= icon('grid') ?><span><?= e(str_replace('{n}', (string) count($photos), strip_tags(t('prop.allPhotos')))) ?></span></button>
<?php endif; ?>
        </div>
<?php else: ?>
        <div class="pg pg-none"><span><img src="<?= asset('img/mark-light.webp') ?>" alt="" width="464" height="476"><?= t('prop.noPhoto') ?></span></div>
<?php endif; ?>

        <div class="prop-head">
          <div class="prop-head-copy">
            <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('props.type.' . $l['type']) ?> · <?= t('props.countFor.' . $l['purpose']) ?></span></p>
            <h1 id="page-title" class="prop-title" dir="auto"><?= e($title) ?></h1>
            <p class="prop-loc"><?= icon('pin') ?><span dir="auto"><?= e(prop_location($l) ?: prop_community($l)) ?></span></p>
          </div>
          <div class="prop-head-side">
            <p class="prop-price"><?= e(money((int) $l['price'])) ?><?php if ($period !== ''): ?> <small><?= $period ?></small><?php endif; ?></p>
<?php if ($specs): ?>
            <ul class="prop-specs">
<?php foreach ($specs as [$sIcon, $sText]): ?>
              <li><?= icon($sIcon) ?><?= e($sText) ?></li>
<?php endforeach; ?>
            </ul>
<?php endif; ?>
            <a class="btn btn-gold magnetic" href="#request"><span><?= t('prop.req.cta') ?></span><?= arrow() ?></a>
          </div>
        </div>
      </div>
    </section>

    <section class="section prop-main">
      <div class="wrap prop-layout">
        <div class="prop-content">
          <section class="prop-block" aria-labelledby="facts-title">
            <h2 class="area-h" id="facts-title"><span class="roof" aria-hidden="true"></span><?= t('prop.facts') ?></h2>
            <dl class="prop-facts">
              <div><dt><?= t('prop.f.type') ?></dt><dd><?= t('props.type.' . $l['type']) ?></dd></div>
              <div><dt><?= t('prop.f.purpose') ?></dt><dd><?= t($l['purpose'] === 'rent' ? 'prop.rent' : 'prop.sale') ?></dd></div>
<?php if ($l['beds'] !== null): ?>
              <div><dt><?= t('prop.f.beds') ?></dt><dd><?= (int) $l['beds'] === 0 ? t('props.studio') : (int) $l['beds'] ?></dd></div>
<?php endif; ?>
<?php if ($l['baths'] !== null && (int) $l['baths'] > 0): ?>
              <div><dt><?= t('prop.f.baths') ?></dt><dd><?= (int) $l['baths'] ?></dd></div>
<?php endif; ?>
<?php if ($l['size_sqft']): ?>
              <div><dt><?= t('prop.f.size') ?></dt><dd><?= e(prop_size((int) $l['size_sqft'])) ?> <span class="prop-sub">(<?= number_format((int) $l['size_sqft'] / 10.7639) ?> m²)</span></dd></div>
<?php endif; ?>
<?php if ($l['completion'] !== ''): ?>
              <div><dt><?= t('prop.f.completion') ?></dt><dd><?= t('props.' . $l['completion']) ?></dd></div>
<?php endif; ?>
<?php if ($l['furnishing'] !== ''): ?>
              <div><dt><?= t('prop.f.furnishing') ?></dt><dd><?= t('props.' . $l['furnishing']) ?></dd></div>
<?php endif; ?>
<?php if ($l['permit'] !== ''): ?>
              <div><dt><?= t('prop.f.permit') ?></dt><dd dir="ltr"><?= e($l['permit']) ?></dd></div>
<?php endif; ?>
<?php if ($l['reference'] !== ''): ?>
              <div><dt><?= t('prop.f.ref') ?></dt><dd dir="ltr"><?= e($l['reference']) ?></dd></div>
<?php endif; ?>
<?php if ((int) $l['listed_at'] > 0): ?>
              <div><dt><?= t('prop.f.listed') ?></dt><dd><?= e(dubai_date((int) $l['listed_at'])) ?></dd></div>
<?php endif; ?>
            </dl>
          </section>

<?php if ($desc !== ''): ?>
          <section class="prop-block" aria-labelledby="about-title">
            <h2 class="area-h" id="about-title"><span class="roof" aria-hidden="true"></span><?= t('prop.about') ?></h2>
            <div class="prop-desc" id="prop-desc" dir="auto">
<?php foreach (preg_split("/\n{2,}/", $desc) as $para): ?>
              <p><?= nl2br(e($para)) ?></p>
<?php endforeach; ?>
            </div>
            <button class="prop-more" type="button" aria-expanded="false" aria-controls="prop-desc" hidden data-more="<?= tp('prop.more') ?>" data-less="<?= tp('prop.less') ?>"><?= t('prop.more') ?></button>
          </section>
<?php endif; ?>

<?php $amen = json_decode($l['amenities'] ?: '[]', true) ?: []; if ($amen): ?>
          <section class="prop-block" aria-labelledby="amen-title">
            <h2 class="area-h" id="amen-title"><span class="roof" aria-hidden="true"></span><?= t('prop.amenities') ?></h2>
            <ul class="prop-amen" dir="auto">
<?php foreach ($amen as $a): if (!is_string($a)) continue; ?>
              <li><?= e(amenity_label($a)) ?></li>
<?php endforeach; ?>
            </ul>
          </section>
<?php endif; ?>

          <section class="prop-block" aria-labelledby="loc-title">
            <h2 class="area-h" id="loc-title"><span class="roof" aria-hidden="true"></span><?= t('prop.location') ?></h2>
            <p class="prop-loc-text" dir="auto"><?= e(prop_location($l) ?: prop_community($l)) ?></p>
<?php if ($l['area_slug'] !== '' && !empty(areas()[$l['area_slug']]['guide'])): ?>
            <a class="link-arrow" href="<?= url('/areas/' . $l['area_slug']) ?>"><span><?= str_replace('{name}', strip_tags(t('area.' . areas()[$l['area_slug']]['key'])), t('area.cta')) ?></span><?= arrow() ?></a>
<?php endif; ?>
<?php if ($l['lat'] !== null && $l['lng'] !== null):
    $lat = (float) $l['lat']; $lng = (float) $l['lng'];
    $bbox = implode(',', [round($lng - 0.012, 5), round($lat - 0.007, 5), round($lng + 0.012, 5), round($lat + 0.007, 5)]); ?>
            <div class="prop-map">
              <iframe title="<?= tp('prop.mapTitle') ?>" src="https://www.openstreetmap.org/export/embed.html?bbox=<?= e($bbox) ?>&amp;layer=mapnik&amp;marker=<?= $lat ?>,<?= $lng ?>" loading="lazy" referrerpolicy="no-referrer"></iframe>
            </div>
            <a class="link-arrow" href="https://www.google.com/maps/search/?api=1&amp;query=<?= $lat ?>,<?= $lng ?>" target="_blank" rel="noopener noreferrer"><span><?= t('prop.map') ?></span><?= icon('out') ?></a>
<?php endif; ?>
          </section>

        </div>

        <aside class="prop-aside" id="request" aria-labelledby="req-title">
          <div class="req-card">
            <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('prop.req.eyebrow') ?></span></p>
            <h2 class="req-title" id="req-title"><?= t('prop.req.title') ?></h2>
            <p class="req-lead"><?= t('prop.req.lead') ?></p>
<?php $sent = $_GET['sent'] ?? ''; ?>
            <form class="enquiry req-form" id="request-form" action="<?= SITE_BASE ?>/request.php" method="post" novalidate>
              <input type="hidden" name="listing_id" value="<?= (int) $l['id'] ?>">
              <input type="hidden" name="lang" value="<?= LANG ?>">
              <input type="hidden" name="ts" value="<?= time() ?>">
              <fieldset class="req-intent">
                <legend><?= t('prop.req.intent') ?></legend>
<?php foreach (['viewing', 'info', 'offer'] as $i => $intent): ?>
                <label class="pf-chip pf-chip-dark"><input type="radio" name="intent" value="<?= $intent ?>"<?= $i === 0 ? ' checked' : '' ?>><span><?= t('prop.req.' . $intent) ?></span></label>
<?php endforeach; ?>
              </fieldset>
              <div class="field">
                <label for="r-name"><?= t('form.name') ?></label>
                <input id="r-name" name="name" type="text" autocomplete="name" placeholder="<?= tp('form.namePh') ?>" required maxlength="100">
              </div>
              <div class="field">
                <label for="r-phone"><?= t('prop.req.phone') ?></label>
                <input id="r-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+971 …" maxlength="30" dir="ltr">
              </div>
              <div class="field">
                <label for="r-email"><?= t('prop.req.emailOpt') ?></label>
                <input id="r-email" name="email" type="email" autocomplete="email" inputmode="email" placeholder="you@email.com" maxlength="150" dir="ltr">
              </div>
              <div class="field">
                <label for="r-pref"><?= t('prop.req.contact') ?></label>
                <select id="r-pref" name="contact_pref">
                  <option value="call"><?= t('prop.req.call') ?></option>
                  <option value="whatsapp"><?= t('prop.req.whatsapp') ?></option>
                  <option value="email"><?= t('prop.req.email') ?></option>
                </select>
              </div>
              <div class="field">
                <label for="r-msg"><?= t('prop.req.msg') ?></label>
                <textarea id="r-msg" name="message" rows="3" maxlength="2000" placeholder="<?= tp('prop.req.msgPh') ?>"></textarea>
              </div>
              <div class="hp" aria-hidden="true">
                <label for="r-website">Leave this empty</label>
                <input id="r-website" name="website" type="text" tabindex="-1" autocomplete="off">
              </div>
              <button class="btn btn-gold btn-lg req-submit" type="submit"><span><?= t('prop.req.submit') ?></span><?= arrow() ?></button>
              <p class="form-status<?= $sent === '1' ? ' is-success' : ($sent === '0' ? ' is-error' : '') ?>" id="request-status" role="status" aria-live="polite"><?= $sent === '1' ? t('prop.req.success') : ($sent === '0' ? t('prop.req.error') : '') ?></p>
              <p class="enquiry-note"><?= t('form.note') ?></p>
            </form>
          </div>
        </aside>
      </div>
    </section>

    <a class="prop-sticky" href="#request"><span class="prop-sticky-price"><?= e(money((int) $l['price'])) ?><?php if ($period !== ''): ?> <small><?= $period ?></small><?php endif; ?></span><span class="btn btn-gold btn-sm"><span><?= t('prop.req.cta') ?></span></span></a>

    <dialog class="lightbox" id="lightbox" aria-label="<?= tp('prop.galleryAria') ?>">
      <div class="lb-top">
        <p class="lb-count" aria-live="polite"></p>
        <button class="lb-btn lb-close" type="button" aria-label="<?= tp('prop.close') ?>"><?= icon('close') ?></button>
      </div>
      <figure class="lb-stage"><img alt="" referrerpolicy="no-referrer"></figure>
      <button class="lb-btn lb-prev" type="button" aria-label="<?= tp('prop.prevPhoto') ?>"><?= icon('prev') ?></button>
      <button class="lb-btn lb-next" type="button" aria-label="<?= tp('prop.nextPhoto') ?>"><?= icon('next') ?></button>
    </dialog>
<?php endif; ?>

<?php if ($similar): ?>
    <section class="section prop-similar" aria-labelledby="similar-title">
      <div class="wrap">
        <div class="section-head section-head-split">
          <h2 id="similar-title" data-split><?= t('prop.similar') ?></h2>
          <a class="link-arrow" href="<?= url('/properties') . ($l['purpose'] === 'rent' ? '?for=rent' : '') ?>"><span><?= t('prop.allProps') ?></span><?= arrow() ?></a>
        </div>
        <ul class="pgrid pgrid-3">
<?php foreach ($similar as $l) require __DIR__ . '/partials/prop-card.php'; ?>
        </ul>
      </div>
    </section>
<?php endif; ?>
<?php
if ($gone) require __DIR__ . '/partials/cta.php';
require __DIR__ . '/inc/foot.php';
