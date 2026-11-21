<?php
/**
 * The Sadan Line: the twelve communities laid out west to east like a metro map.
 * Waterfront stops sit above the line, inland communities below, city stops on it.
 */
?>
    <section class="section line-sec" id="line" aria-labelledby="line-title">
      <div class="wrap">
        <div class="line-head">
          <div>
            <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('line.eyebrow') ?></span></p>
            <h2 id="line-title" data-split><?= t('line.title') ?></h2>
          </div>
          <div class="line-aside">
            <p><?= t('line.lead') ?></p>
            <div class="line-filters" role="group" aria-label="<?= tp('line.filter') ?>">
              <button type="button" class="chip" data-filter="all" aria-pressed="true"><?= t('line.all') ?></button>
              <button type="button" class="chip chip-coast" data-filter="coast" aria-pressed="false"><?= t('line.coast') ?></button>
              <button type="button" class="chip chip-city" data-filter="city" aria-pressed="false"><?= t('line.city') ?></button>
              <button type="button" class="chip chip-community" data-filter="community" aria-pressed="false"><?= t('line.community') ?></button>
            </div>
          </div>
        </div>
      </div>

      <div class="line-scroll" tabindex="0" role="region" aria-label="<?= tp('line.eyebrow') ?>">
        <div class="line-ends" aria-hidden="true"><span><?= t('line.west') ?></span><span><?= t('line.east') ?></span></div>
        <ol class="line" id="sadan-line">
<?php $lN = 0; $below = 0; foreach (areas() as $lSlug => $lA):
    $lN++;
    $deep = false;
    if ($lA['type'] !== 'coast') { $below++; $deep = $below % 2 === 0; }
    $lGuide = !empty($lA['guide']);
    $lName = t('area.' . $lA['key']); ?>
          <li class="stop stop-<?= $lA['type'] ?><?= $lGuide ? ' has-guide' : '' ?><?= $deep ? ' stop-deep' : '' ?>" data-type="<?= $lA['type'] ?>" style="--n:<?= $lN ?>">
<?php if ($lGuide): ?>
            <a class="stop-link" href="<?= url('/areas/' . $lSlug) ?>">
              <span class="stop-dot" aria-hidden="true"></span>
              <span class="stop-label"><span class="stop-name"><?= $lName ?></span><span class="stop-type"><?= t('area.type.' . $lA['type']) ?> · <?= t('line.guide') ?></span></span>
              <span class="stop-peek gable" aria-hidden="true"><img src="<?= asset('img/' . $lA['img']) ?>" alt="" width="900" height="1200" loading="lazy"></span>
            </a>
<?php else: ?>
            <span class="stop-link">
              <span class="stop-dot" aria-hidden="true"></span>
              <span class="stop-label"><span class="stop-name"><?= $lName ?></span><span class="stop-type"><?= t('area.type.' . $lA['type']) ?></span></span>
            </span>
<?php endif; ?>
          </li>
<?php endforeach; ?>
        </ol>
      </div>
    </section>
