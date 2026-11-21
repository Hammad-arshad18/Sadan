<?php
/** Closing call to action, dark, runs into the footer. Optional $ctaQuery (e.g. '?interest=Buying'). */
$ctaQuery = $ctaQuery ?? '';
?>
    <section class="cta-band" aria-labelledby="cta-title">
      <div class="wrap">
        <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('cta.eyebrow') ?></span></p>
        <h2 class="cta-title" id="cta-title" data-split><?= t('cta.title') ?></h2>
        <div class="cta-row">
          <p class="cta-lead"><?= t('cta.lead') ?></p>
          <div class="cta-actions">
            <a class="btn btn-gold btn-lg magnetic" href="<?= url('/contact') . e($ctaQuery) ?>"><span><?= t('cta.button') ?></span><?= arrow() ?></a>
            <a class="contact-mail" href="mailto:<?= SITE_EMAIL ?>" data-cursor="<?= tp('cursor.email') ?>"><?= SITE_EMAIL ?></a>
          </div>
        </div>
      </div>
    </section>
