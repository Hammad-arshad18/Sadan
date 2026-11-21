<?php
/** Grid of neighbourhood guide cards. Optional $exclude (slug) and $limit. */
$exclude = $exclude ?? '';
$limit = $limit ?? 6;
$shown = 0;
?>
        <ul class="guide-grid">
<?php foreach ($GLOBALS['SITE']['guide_order'] as $gSlug):
    if ($gSlug === $exclude || $shown >= $limit) continue;
    $gA = areas()[$gSlug]; $shown++; ?>
          <li class="guide rv">
            <a href="<?= url('/areas/' . $gSlug) ?>" data-cursor="<?= tp('home.guide') ?>">
              <span class="guide-img gable"><img src="<?= asset('img/' . $gA['img']) ?>" alt="" width="900" height="1200" loading="lazy"></span>
              <span class="guide-type"><?= t('area.type.' . $gA['type']) ?></span>
              <strong><?= t('area.' . $gA['key']) ?></strong>
              <span class="guide-desc"><?= t('area.' . $gA['key'] . 'Desc') ?></span>
              <span class="guide-more"><?= t('home.guide') ?><?= arrow() ?></span>
            </a>
          </li>
<?php endforeach; ?>
        </ul>
