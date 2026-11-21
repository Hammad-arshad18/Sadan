<?php
/** FAQ block. Expects $faqTitle (html) and $faqs = [[question, answer], ...]. */
?>
    <section class="section faq" aria-labelledby="faq-title">
      <div class="wrap faq-layout">
        <div class="faq-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('faq.eyebrow') ?></span></p>
          <h2 id="faq-title" data-split><?= $faqTitle ?></h2>
          <p class="faq-more"><span><?= t('faq.more') ?></span> <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
        </div>
        <div class="faq-list">
<?php foreach ($faqs as $i => [$q, $a]): ?>
          <details class="rv">
            <summary><span class="faq-n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><span class="faq-q"><?= $q ?></span><span class="plus" aria-hidden="true"></span></summary>
            <div class="faq-a"><p><?= $a ?></p></div>
          </details>
<?php endforeach; ?>
        </div>
      </div>
    </section>
<?php
// FAQ rich results for search engines
$faqLd = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => []];
foreach ($faqs as [$q, $a]) {
    $faqLd['mainEntity'][] = ['@type' => 'Question', 'name' => strip_tags($q), 'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags($a)]];
}
?>
    <script type="application/ld+json"><?= json_encode($faqLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
