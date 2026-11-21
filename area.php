<?php
require __DIR__ . '/inc/bootstrap.php';
$slug = (string) ($_GET['a'] ?? '');
$all = areas();
if (!isset($all[$slug]) || empty($all[$slug]['guide'])) render_404();
$a = $all[$slug];
$k = $a['key'];
$name = t('area.' . $k);
$arNames = require __DIR__ . '/lang/ar.php';

$page = [
    'key' => 'area',
    'title' => str_replace('{name}', strip_tags($name), strip_tags(t('meta.area.title'))),
    'desc' => strip_tags(t('area.' . $k . '.intro')),
    'image' => $a['img'],
    'crumbs' => [[t('nav.areas'), '/areas'], [$name, '/areas/' . $slug]],
];
require __DIR__ . '/inc/head.php';

$hero = [
    'eyebrow' => t('area.eyebrow') . ' · ' . t('area.type.' . $a['type']),
    'title' => $name,
    'tagline' => t('area.' . $k . '.title'),
    'lead' => t('area.' . $k . '.intro'),
    'image' => $a['img'], 'w' => 900, 'h' => 1200,
    'alt' => strip_tags(t('area.' . $k . 'Alt')),
    'watermark' => $arNames['area.' . $k],
    'meta' => '<div class="page-hero-actions"><a class="btn btn-gold btn-lg magnetic" href="' . url('/contact') . '?area=' . urlencode($a['value']) . '"><span>' . e(str_replace('{name}', strip_tags($name), strip_tags(t('area.cta')))) . '</span>' . arrow() . '</a></div>',
];
require __DIR__ . '/partials/page-hero.php';
?>

    <section class="section area-facts" aria-label="<?= e(strip_tags($name)) ?>">
      <div class="wrap area-facts-grid">
        <div class="area-life rv">
          <h2 class="area-h"><span class="roof" aria-hidden="true"></span><?= t('area.lifeTitle') ?></h2>
          <ol class="life-list">
<?php for ($n = 1; $n <= 3; $n++): ?>
            <li><span class="life-n">0<?= $n ?></span><span><?= t('area.' . $k . '.life' . $n) ?></span></li>
<?php endfor; ?>
          </ol>
        </div>
        <dl class="area-cards">
          <div class="area-card rv"><dt><?= t('area.homesTitle') ?></dt><dd><?= t('area.' . $k . '.homes') ?></dd></div>
          <div class="area-card rv"><dt><?= t('area.goodTitle') ?></dt><dd><?= t('area.' . $k . '.good') ?></dd></div>
          <div class="area-card area-card-dark rv"><dt><?= t('area.moveTitle') ?></dt><dd><?= t('area.' . $k . '.move') ?></dd></div>
        </dl>
        <p class="area-note"><?= t('area.note') ?></p>
      </div>
    </section>

    <section class="section guides guides-more" aria-labelledby="more-title">
      <div class="wrap">
        <div class="section-head section-head-split">
          <h2 id="more-title" data-split><?= t('area.others') ?></h2>
          <a class="link-arrow" href="<?= url('/areas') ?>#line"><span><?= t('line.eyebrow') ?></span><?= arrow() ?></a>
        </div>
<?php $exclude = $slug; $limit = 3; require __DIR__ . '/partials/guide-cards.php'; ?>
      </div>
    </section>

<?php
$ctaQuery = '?area=' . urlencode($a['value']);
require __DIR__ . '/partials/cta.php';
require __DIR__ . '/inc/foot.php';
