<?php
/**
 * Dark hero for inner pages. Expects $hero:
 *   eyebrow, title (may contain <em>), lead, image, w, h, alt, watermark (Arabic word), meta (optional html under lead)
 */
$hero += ['image' => null, 'watermark' => '', 'meta' => '', 'alt' => '', 'tagline' => ''];
?>
    <section class="page-hero<?= $hero['image'] ? '' : ' page-hero-plain' ?>" aria-labelledby="page-title">
<?php if ($hero['watermark']): ?>
      <span class="watermark" lang="ar" aria-hidden="true"><?= e($hero['watermark']) ?></span>
<?php endif; ?>
      <div class="wrap page-hero-grid">
        <div class="page-hero-copy">
          <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= $hero['eyebrow'] ?></span></p>
          <h1 id="page-title" class="page-title" data-split><?= $hero['title'] ?></h1>
<?php if ($hero['tagline']): ?>
          <p class="page-tagline"><?= $hero['tagline'] ?></p>
<?php endif; ?>
          <p class="page-lead"><?= $hero['lead'] ?></p>
          <?= $hero['meta'] ?>
        </div>
<?php if ($hero['image']): ?>
        <figure class="page-hero-media gable gable-reveal">
          <img src="<?= asset('img/' . $hero['image']) ?>" alt="<?= e($hero['alt']) ?>" width="<?= (int) $hero['w'] ?>" height="<?= (int) $hero['h'] ?>" fetchpriority="high">
        </figure>
<?php endif; ?>
      </div>
    </section>
