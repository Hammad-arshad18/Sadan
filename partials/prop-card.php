<?php
/** One property card. Expects $l (a listings row). */
$cTitle = prop_title($l);
$cSpecs = array_filter([
    ['bed', prop_beds($l['beds'] === null ? null : (int) $l['beds'])],
    ['bath', prop_baths($l['baths'] === null ? null : (int) $l['baths'])],
    ['size', prop_size($l['size_sqft'] === null ? null : (int) $l['size_sqft'])],
], fn($s) => $s[1] !== '');
$cPhotos = count(prop_photos($l));
?>
          <li class="pcard">
            <a class="pcard-link" href="<?= e(prop_url($l)) ?>">
              <span class="pcard-media<?= $l['cover'] === '' ? ' is-empty' : '' ?>">
<?php if ($l['cover'] !== ''): ?>
                <img src="<?= e(prop_cover($l, 'md')) ?>" alt="" loading="lazy" decoding="async" width="900" height="600">
<?php endif; ?>
                <span class="pcard-tags">
                  <span class="pcard-tag"><?= t('props.type.' . $l['type']) ?></span>
<?php if ($l['completion'] === 'offplan'): ?>
                  <span class="pcard-tag pcard-tag-gold"><?= t('props.offplan') ?></span>
<?php endif; ?>
<?php if ((int) $l['featured']): ?>
                  <span class="pcard-tag pcard-tag-gold"><?= t('props.featured') ?></span>
<?php endif; ?>
                </span>
<?php if ($cPhotos > 1): ?>
                <span class="pcard-count"><?= icon('camera') ?><?= $cPhotos ?></span>
<?php endif; ?>
              </span>
              <span class="pcard-body">
                <span class="pcard-price"><strong><?= e(money((int) $l['price'])) ?></strong><?php if (prop_period($l) !== ''): ?> <small><?= prop_period($l) ?></small><?php endif; ?></span>
                <span class="pcard-title" dir="auto"><?= e($cTitle) ?></span>
                <span class="pcard-loc"><?= icon('pin') ?><span dir="auto"><?= e(prop_location($l) ?: prop_community($l)) ?></span></span>
<?php if ($cSpecs): ?>
                <span class="pcard-specs">
<?php foreach ($cSpecs as [$cIcon, $cText]): ?>
                  <span><?= icon($cIcon) ?><?= e($cText) ?></span>
<?php endforeach; ?>
                </span>
<?php endif; ?>
              </span>
            </a>
          </li>
