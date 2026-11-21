<?php
/** Bottom of every page: closes <main>, footer, scripts. */
$jsText = [];
foreach (['form.missing', 'form.missingName', 'form.missingEmail', 'form.and', 'form.sending', 'form.success', 'form.error',
          'tool.progress', 'wa.text', 'wa.buy', 'wa.sell', 'wa.rent', 'wa.manage', 'cursor.enquire', 'cursor.email',
          'prop.req.missing', 'prop.req.success', 'prop.req.error', 'prop.photoOf'] as $k) {
    $jsText[$k] = t($k);
}
?>
  </main>

  <footer class="site-footer">
    <div class="wrap footer-grid">
      <div class="footer-brand">
        <a class="brand brand-foot" href="<?= url('/') ?>" aria-label="Sadan Real Estate">
          <img src="<?= asset('img/logo-light.webp') ?>" alt="Sadan Real Estate سدن العقارية" width="1400" height="288" loading="lazy">
        </a>
        <p><?= t('meta.desc') ?></p>
      </div>
      <div class="footer-col">
        <h2><?= t('footer.servicesH') ?></h2>
        <ul>
<?php foreach (services() as $fSlug => $fS): ?>
          <li><a href="<?= url('/' . $fSlug) ?>"><?= t('svc.' . $fSlug) ?></a></li>
<?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h2><?= t('footer.areasH') ?></h2>
        <ul>
<?php foreach ($GLOBALS['SITE']['guide_order'] as $fSlug): ?>
          <li><a href="<?= url('/areas/' . $fSlug) ?>"><?= t('area.' . areas()[$fSlug]['key']) ?></a></li>
<?php endforeach; ?>
        </ul>
      </div>
      <div class="footer-col">
        <h2><?= t('footer.company') ?></h2>
        <ul>
<?php if (props_on()): ?>
          <li><a href="<?= url('/properties') ?>"><?= t('nav.properties') ?></a></li>
<?php endif; ?>
          <li><a href="<?= url('/about') ?>"><?= t('nav.about') ?></a></li>
          <li><a href="<?= url('/areas') ?>"><?= t('nav.allAreas') ?></a></li>
          <li><a href="<?= url('/contact') ?>"><?= t('nav.contact') ?></a></li>
          <li><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></li>
          <li><span class="footer-muted"><?= t('footer.place') ?></span></li>
        </ul>
      </div>
    </div>

    <div class="wordmark" aria-hidden="true">
      <span class="wordmark-en">Sadan</span>
      <img class="wordmark-mark" src="<?= asset('img/mark-light.webp') ?>" alt="" width="464" height="476" loading="lazy">
      <span class="wordmark-ar" lang="ar">سدن</span>
    </div>

    <div class="wrap footer-legal">
      <p>© <?= date('Y') ?> <?= t('footer.legal') ?><?php if (RERA_ORN !== ''): ?> · <?= t('footer.orn') ?> <?= e(RERA_ORN) ?><?php endif; ?></p>
      <a href="#top" class="to-top"><span><?= t('footer.top') ?></span><svg aria-hidden="true" viewBox="0 0 20 20"><path d="M10 16V4M5 9l5-5 5 5"/></svg></a>
    </div>
  </footer>

  <script>window.SADAN = <?= json_encode(['lang' => LANG, 'whatsapp' => preg_replace('/\D/', '', WHATSAPP), 'send' => SITE_BASE . '/send.php', 't' => $jsText], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;</script>
  <script src="https://cdn.jsdelivr.net/npm/lenis@1.1.20/dist/lenis.min.js" defer></script>
  <script src="<?= asset('js/main.js') ?>" defer></script>
<?php foreach ($page['scripts'] ?? [] as $extraScript): ?>
  <script src="<?= asset('js/' . $extraScript) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
