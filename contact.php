<?php
require __DIR__ . '/inc/bootstrap.php';
$page = [
    'key' => 'contact',
    'title' => strip_tags(t('meta.contact.title')),
    'desc' => strip_tags(t('contact.lead')),
    'crumbs' => [[t('nav.contact'), '/contact']],
];
require __DIR__ . '/inc/head.php';
?>

    <section class="contact contact-page" id="contact" aria-labelledby="contact-title">
      <span class="watermark" lang="ar" aria-hidden="true">تواصل</span>
      <div class="wrap">
        <div class="contact-head">
          <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('contactp.eyebrow') ?></span></p>
          <h1 class="contact-title" id="contact-title" data-split><?= t('contact.title') ?></h1>
        </div>

        <div class="contact-layout">
          <div class="contact-copy">
            <p class="contact-lead"><?= t('contact.lead') ?></p>
            <a class="contact-mail" href="mailto:<?= SITE_EMAIL ?>" data-cursor="<?= tp('cursor.email') ?>"><?= SITE_EMAIL ?></a>
            <dl class="contact-direct">
              <div><dt><?= t('contact.office') ?></dt><dd><?= t('footer.place') ?></dd></div>
<?php if (WHATSAPP !== ''): ?>
              <div><dt>WhatsApp</dt><dd><a href="https://wa.me/<?= e(preg_replace('/\D/', '', WHATSAPP)) ?>" target="_blank" rel="noopener"><?= t('contact.waLink') ?></a></dd></div>
<?php endif; ?>
            </dl>
            <div class="next-steps">
              <h2 class="next-steps-title"><?= t('contactp.nextTitle') ?></h2>
              <ol>
<?php for ($n = 1; $n <= 3; $n++): ?>
                <li><span class="next-n">0<?= $n ?></span><span><?= t('contactp.n' . $n) ?></span></li>
<?php endfor; ?>
              </ol>
            </div>
          </div>
<?php require __DIR__ . '/partials/enquiry-form.php'; ?>
        </div>
      </div>
    </section>

<?php
$faqTitle = t('faq.title');
$faqs = [];
for ($n = 1; $n <= 5; $n++) $faqs[] = [t('faq.' . $n . '.q'), t('faq.' . $n . '.a')];
require __DIR__ . '/partials/faq.php';
require __DIR__ . '/inc/foot.php';
