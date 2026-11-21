<?php
/**
 * Properties: homes for sale and rent that Sadan lists in the admin panel.
 * Filters live in the address bar, so every result page can be shared and works
 * without JavaScript.
 */
require __DIR__ . '/inc/bootstrap.php';
require __DIR__ . '/inc/listings.php';
if (!props_on()) render_404();

$f = prop_filters($_GET);
$res = ['items' => [], 'total' => 0, 'pages' => 1, 'page' => 1];
$facets = ['areas' => [], 'types' => []];
$live = 0;
$lastSync = 0;
try {
    $res = prop_search($f);
    $facets = prop_facets($f);
    $live = prop_total_live();
    $lastSync = prop_last_update();
} catch (Throwable $e) {
    error_log('Sadan properties: ' . $e->getMessage());
}
$f['page'] = $res['page'];

$isFiltered = prop_query($f) !== '';
$page = [
    'key' => 'properties',
    'title' => strip_tags(t('meta.props.title')),
    'desc' => strip_tags(t('meta.props.desc')),
    'image' => 'svc-buy.webp',
    'crumbs' => [[t('nav.properties'), '/properties']],
    'scripts' => ['properties.js'],
    'robots' => $isFiltered ? 'noindex, follow' : '',
];
require __DIR__ . '/inc/head.php';

$areaName = '';
foreach ($facets['areas'] as $a) {
    if ($a['k'] === $f['area']) $areaName = prop_community_name($a['name'], $a['name_ar'], $a['slug']);
}
$steps = PRICE_STEPS[$f['for']];
foreach (['min', 'max'] as $k) if ($f[$k] && !in_array($f[$k], $steps, true)) { $steps[] = $f[$k]; sort($steps); }
$sizeSteps = SIZE_STEPS;
foreach (['smin', 'smax'] as $k) if ($f[$k] && !in_array($f[$k], $sizeSteps, true)) { $sizeSteps[] = $f[$k]; sort($sizeSteps); }

$opt = fn(string $value, string $label, bool $sel) => '<option value="' . e($value) . '"' . ($sel ? ' selected' : '') . '>' . e($label) . '</option>';

// Active filters, each with a link that removes it
$active = [];
if ($f['q'] !== '') $active[] = ['“' . $f['q'] . '”', ['q' => '']];
if ($f['area'] !== '') $active[] = [$areaName ?: $f['area'], ['area' => '']];
if ($f['type'] !== '') $active[] = [strip_tags(t('props.type.' . $f['type'])), ['type' => '']];
foreach ($f['beds'] as $b) $active[] = [$b === 5 ? str_replace('{n}', strip_tags(t('props.bedsPlus')), strip_tags(t('props.bedsN'))) : prop_beds($b), ['beds' => array_values(array_diff($f['beds'], [$b]))]];
if ($f['baths']) $active[] = [str_replace('{n}', (string) $f['baths'], strip_tags(t('props.bathsPlus'))) . ' ' . strip_tags(t('props.baths')), ['baths' => 0]];
if ($f['min']) $active[] = ['≥ ' . money_short($f['min']), ['min' => 0]];
if ($f['max']) $active[] = ['≤ ' . money_short($f['max']), ['max' => 0]];
if ($f['smin']) $active[] = ['≥ ' . prop_size($f['smin']), ['smin' => 0]];
if ($f['smax']) $active[] = ['≤ ' . prop_size($f['smax']), ['smax' => 0]];
if ($f['done'] !== '') $active[] = [strip_tags(t('props.' . $f['done'])), ['done' => '']];
if ($f['furn'] !== '') $active[] = [strip_tags(t('props.' . $f['furn'])), ['furn' => '']];

$countText = str_replace('{n}', number_format($res['total']), strip_tags(t($res['total'] === 1 ? 'props.count1' : 'props.count')))
    . ' ' . strip_tags(t('props.countFor.' . $f['for']))
    . ($areaName !== '' ? ' ' . str_replace('{area}', $areaName, strip_tags(t('props.in'))) : '');
?>
    <form class="pf" id="pf" action="<?= url('/properties') ?>" method="get" role="search" aria-label="<?= tp('props.filters') ?>">
      <section class="page-hero page-hero-plain props-hero" aria-labelledby="page-title">
        <span class="watermark" lang="ar" aria-hidden="true">عقارات</span>
        <div class="wrap">
          <div class="page-hero-copy">
            <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('props.eyebrow') ?></span></p>
            <h1 id="page-title" class="page-title" data-split><?= t('props.title') ?></h1>
            <p class="page-lead"><?= t('props.lead') ?></p>
<?php if ($lastSync): ?>
            <p class="props-updated"><span class="clock-dot" aria-hidden="true"></span><?= e(str_replace('{time}', dubai_date($lastSync, true), strip_tags(t('props.updated')))) ?></p>
<?php endif; ?>
          </div>

          <div class="pf-bar">
            <fieldset class="pf-purpose">
              <legend class="sr-only"><?= t('props.purposeAria') ?></legend>
              <label><input type="radio" name="for" value="sale"<?= $f['for'] === 'sale' ? ' checked' : '' ?>><span><?= t('props.buy') ?></span></label>
              <label><input type="radio" name="for" value="rent"<?= $f['for'] === 'rent' ? ' checked' : '' ?>><span><?= t('props.rent') ?></span></label>
            </fieldset>
            <div class="pf-cell pf-q">
              <label for="pf-q"><?= t('props.searchLabel') ?></label>
              <span class="pf-q-input"><?= icon('search') ?><input id="pf-q" name="q" type="search" value="<?= e($f['q']) ?>" placeholder="<?= tp('props.searchPh') ?>" maxlength="80" autocomplete="off" enterkeyhint="search"></span>
            </div>
            <div class="pf-cell">
              <label for="pf-area"><?= t('props.area') ?></label>
              <select id="pf-area" name="area">
                <?= $opt('', strip_tags(t('props.areaAny')), $f['area'] === '') ?>
<?php foreach ($facets['areas'] as $a): ?>
                <?= $opt($a['k'], prop_community_name($a['name'], $a['name_ar'], $a['slug']) . ' (' . $a['n'] . ')', $a['k'] === $f['area']) ?>
<?php endforeach; ?>
<?php if ($f['area'] !== '' && $areaName === ''): ?>
                <?= $opt($f['area'], $f['area'] . ' (0)', true) ?>
<?php endif; ?>
              </select>
            </div>
            <div class="pf-cell">
              <label for="pf-type"><?= t('props.type') ?></label>
              <select id="pf-type" name="type">
                <?= $opt('', strip_tags(t('props.typeAny')), $f['type'] === '') ?>
<?php foreach (PROP_TYPES as $ty): if (empty($facets['types'][$ty]) && $f['type'] !== $ty) continue; ?>
                <?= $opt($ty, strip_tags(t('props.type.' . $ty)) . ' (' . (int) ($facets['types'][$ty] ?? 0) . ')', $f['type'] === $ty) ?>
<?php endforeach; ?>
              </select>
            </div>
            <button class="btn btn-solid btn-lg pf-go" type="submit"><?= icon('search') ?><span><?= t('props.search') ?></span></button>
          </div>
        </div>
      </section>

      <div class="wrap props-layout" id="props">
        <aside class="pf-side" id="pf-side" aria-labelledby="pf-side-title">
          <button class="pf-toggle" type="button" aria-expanded="false" aria-controls="pf-side-body">
            <?= icon('filter') ?><span id="pf-side-title"><?= t('props.moreFilters') ?></span>
<?php $moreCount = count($f['beds']) + ($f['baths'] ? 1 : 0) + ($f['min'] || $f['max'] ? 1 : 0) + ($f['smin'] || $f['smax'] ? 1 : 0) + ($f['done'] !== '' ? 1 : 0) + ($f['furn'] !== '' ? 1 : 0); ?>
            <?php if ($moreCount): ?><span class="pf-badge"><?= $moreCount ?></span><?php endif; ?>
          </button>
          <div class="pf-side-body" id="pf-side-body">
            <fieldset class="pf-group">
              <legend><?= t('props.beds') ?></legend>
              <div class="pf-chips">
<?php foreach ([0, 1, 2, 3, 4, 5] as $b): ?>
                <label class="pf-chip"><input type="checkbox" name="beds[]" value="<?= $b ?>"<?= in_array($b, $f['beds'], true) ? ' checked' : '' ?>><span><?= $b === 0 ? t('props.studio') : ($b === 5 ? t('props.bedsPlus') : $b) ?></span></label>
<?php endforeach; ?>
              </div>
            </fieldset>

            <fieldset class="pf-group">
              <legend><?= t('props.baths') ?></legend>
              <div class="pf-chips">
<?php foreach ([0, 1, 2, 3, 4] as $b): ?>
                <label class="pf-chip"><input type="radio" name="baths" value="<?= $b ?: '' ?>"<?= $f['baths'] === $b ? ' checked' : '' ?>><span><?= $b === 0 ? t('props.any') : str_replace('{n}', (string) $b, t('props.bathsPlus')) ?></span></label>
<?php endforeach; ?>
              </div>
            </fieldset>

            <fieldset class="pf-group">
              <legend><?= t($f['for'] === 'rent' ? 'props.priceRent' : 'props.price') ?></legend>
              <div class="pf-pair">
                <label class="sr-only" for="pf-min"><?= t('props.min') ?></label>
                <select id="pf-min" name="min">
                  <?= $opt('', strip_tags(t('props.noMin')), !$f['min']) ?>
<?php foreach ($steps as $s): ?>
                  <?= $opt((string) $s, money_short($s), $f['min'] === $s) ?>
<?php endforeach; ?>
                </select>
                <span aria-hidden="true">–</span>
                <label class="sr-only" for="pf-max"><?= t('props.max') ?></label>
                <select id="pf-max" name="max">
                  <?= $opt('', strip_tags(t('props.noMax')), !$f['max']) ?>
<?php foreach ($steps as $s): ?>
                  <?= $opt((string) $s, money_short($s), $f['max'] === $s) ?>
<?php endforeach; ?>
                </select>
              </div>
            </fieldset>

            <fieldset class="pf-group">
              <legend><?= t('props.size') ?></legend>
              <div class="pf-pair">
                <label class="sr-only" for="pf-smin"><?= t('props.min') ?></label>
                <select id="pf-smin" name="smin">
                  <?= $opt('', strip_tags(t('props.noMin')), !$f['smin']) ?>
<?php foreach ($sizeSteps as $s): ?>
                  <?= $opt((string) $s, number_format($s), $f['smin'] === $s) ?>
<?php endforeach; ?>
                </select>
                <span aria-hidden="true">–</span>
                <label class="sr-only" for="pf-smax"><?= t('props.max') ?></label>
                <select id="pf-smax" name="smax">
                  <?= $opt('', strip_tags(t('props.noMax')), !$f['smax']) ?>
<?php foreach ($sizeSteps as $s): ?>
                  <?= $opt((string) $s, number_format($s), $f['smax'] === $s) ?>
<?php endforeach; ?>
                </select>
              </div>
            </fieldset>

<?php if ($f['for'] === 'sale'): ?>
            <fieldset class="pf-group">
              <legend><?= t('props.completion') ?></legend>
              <div class="pf-chips">
<?php foreach (['' => 'props.any', 'ready' => 'props.ready', 'offplan' => 'props.offplan'] as $v => $k): ?>
                <label class="pf-chip"><input type="radio" name="done" value="<?= $v ?>"<?= $f['done'] === $v ? ' checked' : '' ?>><span><?= t($k) ?></span></label>
<?php endforeach; ?>
              </div>
            </fieldset>
<?php else: ?>
            <fieldset class="pf-group">
              <legend><?= t('props.furnishing') ?></legend>
              <div class="pf-chips">
<?php foreach (['' => 'props.any', 'furnished' => 'props.furnished', 'unfurnished' => 'props.unfurnished'] as $v => $k): ?>
                <label class="pf-chip"><input type="radio" name="furn" value="<?= $v ?>"<?= $f['furn'] === $v ? ' checked' : '' ?>><span><?= t($k) ?></span></label>
<?php endforeach; ?>
              </div>
            </fieldset>
<?php endif; ?>

            <div class="pf-side-actions">
              <button class="btn btn-solid pf-apply" type="submit"><span><?= t('props.apply') ?></span></button>
              <a class="pf-reset" href="<?= url('/properties') . e(prop_query(prop_filters(['for' => $f['for']]))) ?>"><?= t('props.reset') ?></a>
            </div>
          </div>
        </aside>

        <section class="props-results" id="results" aria-labelledby="results-title" data-total="<?= (int) $res['total'] ?>">
          <div class="props-head">
            <h2 class="props-count" id="results-title"><?= e($countText) ?></h2>
            <div class="props-sort">
              <label for="pf-sort"><?= t('props.sort') ?></label>
              <select id="pf-sort" name="sort">
<?php foreach (['recommended', 'newest', 'price-asc', 'price-desc', 'size-desc'] as $s): ?>
                <?= $opt($s, strip_tags(t('props.sort.' . $s)), $f['sort'] === $s) ?>
<?php endforeach; ?>
              </select>
            </div>
          </div>

<?php if ($active): ?>
          <ul class="props-active">
<?php foreach ($active as [$label, $change]): ?>
            <li><a href="<?= url('/properties') . e(prop_query($f, $change + ['page' => 1])) ?>" aria-label="<?= e(str_replace('{name}', $label, strip_tags(t('props.remove')))) ?>"><span dir="auto"><?= e($label) ?></span><?= icon('close') ?></a></li>
<?php endforeach; ?>
            <li><a class="props-clear" href="<?= url('/properties') . e(prop_query(prop_filters(['for' => $f['for']]))) ?>"><?= t('props.reset') ?></a></li>
          </ul>
<?php endif; ?>

<?php if ($res['items']): ?>
          <ul class="pgrid">
<?php foreach ($res['items'] as $l) require __DIR__ . '/partials/prop-card.php'; ?>
          </ul>

<?php if ($res['pages'] > 1): ?>
          <nav class="pager" aria-label="<?= tp('props.pagesAria') ?>">
<?php if ($res['page'] > 1): ?>
            <a class="pager-step" href="<?= url('/properties') . e(prop_query($f, ['page' => $res['page'] - 1])) ?>" rel="prev"><?= icon('prev') ?><span><?= t('props.prev') ?></span></a>
<?php endif; ?>
            <ol>
<?php
$window = array_unique(array_filter([1, $res['page'] - 1, $res['page'], $res['page'] + 1, $res['pages']], fn($n) => $n >= 1 && $n <= $res['pages']));
sort($window);
$prevN = 0;
foreach ($window as $n):
    if ($n - $prevN > 1): ?>
              <li class="pager-gap" aria-hidden="true">…</li>
<?php endif; $prevN = $n; ?>
              <li><?php if ($n === $res['page']): ?><span aria-current="page"><?= $n ?></span><?php else: ?><a href="<?= url('/properties') . e(prop_query($f, ['page' => $n])) ?>" aria-label="<?= e(str_replace(['{n}', '{total}'], [(string) $n, (string) $res['pages']], strip_tags(t('props.pageOf')))) ?>"><?= $n ?></a><?php endif; ?></li>
<?php endforeach; ?>
            </ol>
<?php if ($res['page'] < $res['pages']): ?>
            <a class="pager-step" href="<?= url('/properties') . e(prop_query($f, ['page' => $res['page'] + 1])) ?>" rel="next"><span><?= t('props.next') ?></span><?= icon('next') ?></a>
<?php endif; ?>
          </nav>
<?php endif; ?>

<?php else: ?>
          <div class="props-empty">
            <span class="roof roof-lg" aria-hidden="true"></span>
<?php if ($live === 0): ?>
            <h3><?= t('props.none.title') ?></h3>
            <p><?= t('props.none.text') ?></p>
            <a class="btn btn-solid magnetic" href="<?= url('/contact') ?>?interest=<?= $f['for'] === 'rent' ? 'Renting' : 'Buying' ?>"><span><?= t('props.ask') ?></span><?= arrow() ?></a>
<?php else: ?>
            <h3><?= t('props.empty.title') ?></h3>
            <p><?= t('props.empty.text') ?></p>
            <div class="props-empty-actions">
              <a class="btn btn-solid" href="<?= url('/properties') . e(prop_query(prop_filters(['for' => $f['for']]))) ?>"><span><?= t('props.reset') ?></span></a>
              <a class="link-arrow" href="<?= url('/contact') ?>?interest=<?= $f['for'] === 'rent' ? 'Renting' : 'Buying' ?>"><span><?= t('props.ask') ?></span><?= arrow() ?></a>
            </div>
<?php endif; ?>
          </div>
<?php endif; ?>

          <p class="props-notice"><?= t('props.notice') ?></p>
        </section>
      </div>
    </form>

<?php
$ctaQuery = '?interest=' . ($f['for'] === 'rent' ? 'Renting' : 'Buying');
require __DIR__ . '/partials/cta.php';
require __DIR__ . '/inc/foot.php';
