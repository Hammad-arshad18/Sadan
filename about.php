<?php
require __DIR__ . '/inc/bootstrap.php';
$page = [
    'key' => 'about',
    'title' => strip_tags(t('meta.about.title')),
    'desc' => strip_tags(t('about.lead')),
    'crumbs' => [[t('nav.about'), '/about']],
];
require __DIR__ . '/inc/head.php';

$hero = [
    'eyebrow' => t('about.eyebrow'),
    'title' => t('about.hero'),
    'lead' => t('about.lead'),
    'image' => 'hero.webp', 'w' => 2400, 'h' => 1602,
    'alt' => strip_tags(t('slide.1')),
    'watermark' => 'سدن',
];
require __DIR__ . '/partials/page-hero.php';
?>

    <!-- THE WORD -->
    <section class="section word" aria-labelledby="word-title">
      <div class="wrap word-layout">
        <p class="word-ar" lang="ar" aria-hidden="true" data-word>سِدانة</p>
        <div class="word-copy">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span id="word-title"><?= t('about.wordEyebrow') ?></span></p>
          <p class="word-text" data-fill><?= t('about.wordText') ?></p>
        </div>
      </div>
    </section>

    <!-- PRINCIPLES -->
    <section class="section principles-sec" aria-labelledby="principles-title">
      <div class="wrap">
        <div class="section-head">
          <h2 id="principles-title" data-split><?= t('about.principlesTitle') ?></h2>
        </div>
        <ol class="principle-rows">
<?php foreach (['I', 'II', 'III'] as $n => $roman): ?>
          <li class="principle-row rv">
            <span class="principle-num"><?= $roman ?></span>
            <h3><?= t('about.' . ($n + 1) . '.title') ?></h3>
            <p><?= t('about.' . ($n + 1) . '.text') ?></p>
          </li>
<?php endforeach; ?>
        </ol>
      </div>
    </section>

    <!-- SERVICES INDEX -->
    <section class="section svc-index-sec" aria-labelledby="what-title">
      <div class="wrap">
        <div class="section-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('services.eyebrow') ?></span></p>
          <h2 id="what-title" data-split><?= t('about.whatTitle') ?></h2>
        </div>
        <ul class="svc-index">
<?php foreach (services() as $slug => $s): ?>
          <li>
            <a href="<?= url('/' . $slug) ?>" class="svc-row" data-cursor="<?= tp('read.more') ?>">
              <span class="svc-row-num"><?= $s['num'] ?></span>
              <span class="svc-row-name"><?= t('svc.' . $slug) ?></span>
              <span class="svc-row-short"><?= t('svc.' . $slug . '.short') ?></span>
              <span class="svc-row-img gable" aria-hidden="true"><img src="<?= asset('img/' . $s['img']) ?>" alt="" width="<?= $s['w'] ?>" height="<?= $s['h'] ?>" loading="lazy"></span>
              <span class="svc-row-arrow" aria-hidden="true"><?= arrow() ?></span>
            </a>
          </li>
<?php endforeach; ?>
        </ul>
      </div>
    </section>

    <!-- LANGUAGE -->
    <section class="section lang-sec" aria-labelledby="lang-title">
      <div class="wrap lang-layout">
        <div class="lang-pair" aria-hidden="true"><span class="lang-pair-en">Hello</span><span class="lang-pair-ar" lang="ar">أهلاً</span></div>
        <div>
          <h2 id="lang-title" data-split><?= t('about.langTitle') ?></h2>
          <p class="lang-text"><?= t('about.langText') ?></p>
        </div>
      </div>
    </section>

<?php
require __DIR__ . '/partials/cta.php';
require __DIR__ . '/inc/foot.php';
