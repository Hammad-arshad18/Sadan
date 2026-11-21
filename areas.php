<?php
require __DIR__ . '/inc/bootstrap.php';
$page = [
    'key' => 'areas',
    'title' => strip_tags(t('meta.areas.title')),
    'desc' => strip_tags(t('meta.areas.desc')),
    'image' => 'area-marina.webp',
    'crumbs' => [[t('nav.areas'), '/areas']],
];
require __DIR__ . '/inc/head.php';

$hero = [
    'eyebrow' => t('areas.eyebrow'),
    'title' => t('areas.page.title'),
    'lead' => t('areas.lead'),
    'image' => 'area-marina.webp', 'w' => 900, 'h' => 1200,
    'alt' => strip_tags(t('area.marinaAlt')),
    'watermark' => 'دبي',
];
require __DIR__ . '/partials/page-hero.php';
require __DIR__ . '/partials/line-map.php';
?>

    <section class="section guides" aria-labelledby="guides-title">
      <div class="wrap">
        <div class="section-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('area.eyebrow') ?></span></p>
          <h2 id="guides-title" data-split><?= t('areas.guidesTitle') ?></h2>
        </div>
<?php require __DIR__ . '/partials/guide-cards.php'; ?>
      </div>
    </section>

<?php
require __DIR__ . '/partials/cta.php';
require __DIR__ . '/inc/foot.php';
