<?php
if (!defined('LANG')) {
    require __DIR__ . '/inc/bootstrap.php';
    http_response_code(404);
}
$page = ['key' => '404', 'title' => strip_tags(t('404.meta')), 'desc' => strip_tags(t('404.lead'))];
require __DIR__ . '/inc/head.php';
?>
    <section class="page-hero page-hero-plain not-found" aria-labelledby="page-title">
      <span class="watermark" aria-hidden="true">404</span>
      <div class="wrap">
        <div class="page-hero-copy">
          <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span>404</span></p>
          <h1 id="page-title" class="page-title" data-split><?= t('404.title') ?></h1>
          <p class="page-lead"><?= t('404.lead') ?></p>
          <ul class="nf-links">
            <li><a class="btn btn-gold" href="<?= url('/') ?>"><span><?= t('nav.home') ?></span><?= arrow() ?></a></li>
<?php foreach (services() as $slug => $s): ?>
            <li><a class="btn btn-ghost-light" href="<?= url('/' . $slug) ?>"><span><?= t('svc.' . $slug) ?></span></a></li>
<?php endforeach; ?>
            <li><a class="btn btn-ghost-light" href="<?= url('/areas') ?>"><span><?= t('nav.areas') ?></span></a></li>
            <li><a class="btn btn-ghost-light" href="<?= url('/contact') ?>"><span><?= t('nav.contact') ?></span></a></li>
          </ul>
        </div>
      </div>
    </section>
<?php require __DIR__ . '/inc/foot.php';
