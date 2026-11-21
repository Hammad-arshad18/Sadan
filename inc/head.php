<?php
/**
 * Top of every page: <head>, header, menus. Expects $page:
 *   key    current page id (home, buy, sell, rent, manage, areas, area, about, contact, 404)
 *   title  plain-text <title>
 *   desc   meta description
 *   image  optional share image (file in assets/img)
 *   crumbs optional [[label, path], ...] for the breadcrumb trail
 */
$page += ['image' => 'hero.webp', 'crumbs' => [], 'desc' => strip_tags(t('meta.desc')), 'robots' => '', 'og_image' => ''];
$other = LANG === 'ar' ? 'en' : 'ar';
$here = PATH;
$isCurrent = function (string $path) use ($here): string {
    return rtrim($here, '/') === rtrim($path, '/') ? ' aria-current="page"' : '';
};
$svcActive = in_array($page['key'], array_keys(services()), true);
?><!doctype html>
<html lang="<?= LANG ?>" dir="<?= DIR ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= e($page['title']) ?></title>
  <meta name="description" content="<?= e($page['desc']) ?>">
  <meta name="theme-color" content="#08172B">
<?php if ($page['robots']): ?>
  <meta name="robots" content="<?= e($page['robots']) ?>">
<?php endif; ?>
  <link rel="canonical" href="<?= e(absolute_url(url($here))) ?>">
  <link rel="alternate" hreflang="en" href="<?= e(absolute_url(url($here, 'en'))) ?>">
  <link rel="alternate" hreflang="ar" href="<?= e(absolute_url(url($here, 'ar'))) ?>">
  <link rel="alternate" hreflang="x-default" href="<?= e(absolute_url(url($here, 'en'))) ?>">
  <meta property="og:type" content="website">
  <meta property="og:locale" content="<?= LANG === 'ar' ? 'ar_AE' : 'en_GB' ?>">
  <meta property="og:title" content="<?= e($page['title']) ?>">
  <meta property="og:description" content="<?= e($page['desc']) ?>">
  <meta property="og:image" content="<?= e($page['og_image'] !== '' ? $page['og_image'] : absolute_url(SITE_BASE . '/assets/img/' . $page['image'])) ?>">
  <link rel="icon" href="<?= asset('img/favicon.png') ?>" type="image/png">
  <link rel="apple-touch-icon" href="<?= asset('img/apple-touch-icon.png') ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600&family=Instrument+Serif:ital@0;1&family=IBM+Plex+Sans+Arabic:wght@400;500;600&family=Noto+Naskh+Arabic:wght@500;600;700&display=swap">
  <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
  <script>
    (function (r) {
      r.classList.add('js');
      try {
        var calm = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (!calm && !sessionStorage.getItem('sadan-intro')) r.classList.add('has-intro');
      } catch (e) {}
    })(document.documentElement);
  </script>
  <script type="application/ld+json">
  <?= json_encode([
      '@context' => 'https://schema.org',
      '@type' => 'RealEstateAgent',
      'name' => 'Sadan Real Estate L.L.C',
      'alternateName' => 'سدن العقارية',
      'email' => SITE_EMAIL,
      'url' => absolute_url(url('/')),
      'image' => absolute_url(SITE_BASE . '/assets/img/hero.webp'),
      'logo' => absolute_url(SITE_BASE . '/assets/img/logo-stack.webp'),
      'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Dubai', 'addressCountry' => 'AE'],
      'areaServed' => 'Dubai, United Arab Emirates',
      'knowsLanguage' => ['en', 'ar'],
  ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>

  </script>
<?php if ($page['crumbs']): ?>
  <script type="application/ld+json">
  <?php
    $items = [['@type' => 'ListItem', 'position' => 1, 'name' => strip_tags(t('nav.home')), 'item' => absolute_url(url('/'))]];
    foreach ($page['crumbs'] as $i => [$label, $path]) {
        $items[] = ['@type' => 'ListItem', 'position' => $i + 2, 'name' => strip_tags($label), 'item' => absolute_url(url($path))];
    }
    echo json_encode(['@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  ?>

  </script>
<?php endif; ?>
</head>
<body data-page="<?= e($page['key']) ?>">
  <a class="skip-link" href="#main"><?= t('skip') ?></a>

  <div class="intro" id="intro" aria-hidden="true">
    <div class="intro-inner">
      <img class="intro-mark" src="<?= asset('img/mark-light.webp') ?>" alt="" width="464" height="476">
      <div class="intro-word"><span>Sadan</span><span lang="ar">سدن</span></div>
      <div class="intro-count"><span id="intro-num">00</span></div>
    </div>
  </div>

  <div class="cursor" id="cursor" aria-hidden="true"><span class="cursor-label" id="cursor-label"></span></div>

  <header class="site-header is-over" id="top">
    <div class="wrap header-bar">
      <a class="brand" href="<?= url('/') ?>" aria-label="<?= tp('nav.home') ?>, Sadan Real Estate">
        <img class="brand-light" src="<?= asset('img/logo-light.webp') ?>" alt="" width="1400" height="288">
        <img class="brand-dark" src="<?= asset('img/logo.webp') ?>" alt="Sadan Real Estate سدن العقارية" width="1400" height="288">
      </a>

      <nav class="nav" aria-label="<?= tp('nav.aria') ?>">
        <div class="nav-item">
          <button class="nav-trigger" type="button" aria-expanded="false" aria-controls="mega"<?= $svcActive ? ' data-current="true"' : '' ?>>
            <?= t('nav.services') ?><svg class="i-chev" aria-hidden="true" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5"/></svg>
          </button>
          <div class="mega" id="mega" hidden>
            <div class="wrap mega-inner">
              <p class="mega-title"><span class="roof" aria-hidden="true"></span><?= t('nav.servicesMenu') ?></p>
              <ul class="mega-grid">
      <?php foreach (services() as $hSlug => $hS): ?>
                <li>
                  <a class="mega-card" href="<?= url('/' . $hSlug) ?>"<?= $isCurrent('/' . $hSlug) ?>>
                    <span class="mega-img gable"><img src="<?= asset('img/' . $hS['img']) ?>" alt="" width="<?= $hS['w'] ?>" height="<?= $hS['h'] ?>" loading="lazy"></span>
                    <span class="mega-num"><?= $hS['num'] ?></span>
                    <strong><?= t('svc.' . $hSlug) ?></strong>
                    <span class="mega-short"><?= t('svc.' . $hSlug . '.short') ?></span>
                  </a>
                </li>
      <?php endforeach; ?>
              </ul>
            </div>
          </div>
        </div>
<?php if (props_on()): ?>
        <a href="<?= url('/properties') ?>"<?= strpos($here, '/properties') === 0 ? ' aria-current="page"' : '' ?>><?= t('nav.properties') ?></a>
<?php endif; ?>
        <a href="<?= url('/areas') ?>"<?= strpos($here, '/areas') === 0 ? ' aria-current="page"' : '' ?>><?= t('nav.areas') ?></a>
        <a href="<?= url('/about') ?>"<?= $isCurrent('/about') ?>><?= t('nav.about') ?></a>
        <a href="<?= url('/contact') ?>"<?= $isCurrent('/contact') ?>><?= t('nav.contact') ?></a>
      </nav>

      <div class="header-actions">
        <div class="lang" aria-label="Language / اللغة" role="group">
          <a class="lang-btn" href="<?= url($here, 'en') ?>" hreflang="en" lang="en"<?= LANG === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
          <a class="lang-btn" href="<?= url($here, 'ar') ?>" hreflang="ar" lang="ar"<?= LANG === 'ar' ? ' aria-current="true"' : '' ?>>عربي</a>
        </div>
        <a class="btn btn-solid btn-sm header-cta magnetic" href="<?= url('/contact') ?>"><span><?= t('nav.cta') ?></span></a>
        <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu">
          <span class="sr-only"><?= t('nav.menu') ?></span>
          <span class="menu-lines" aria-hidden="true"><span></span><span></span></span>
        </button>
      </div>
    </div>

  </header>

  <div class="menu" id="menu" hidden>
    <nav class="menu-nav" aria-label="<?= tp('nav.menu') ?>">
      <a href="<?= url('/') ?>"<?= $isCurrent('/') ?>><span class="menu-num">00</span><span><?= t('nav.home') ?></span></a>
<?php foreach (services() as $hSlug => $hS): ?>
      <a href="<?= url('/' . $hSlug) ?>"<?= $isCurrent('/' . $hSlug) ?>><span class="menu-num"><?= $hS['num'] ?></span><span><?= t('svc.' . $hSlug) ?></span></a>
<?php endforeach; ?>
<?php $mN = 5; if (props_on()): ?>
      <a href="<?= url('/properties') ?>"<?= $isCurrent('/properties') ?>><span class="menu-num"><?= sprintf('%02d', $mN++) ?></span><span><?= t('nav.properties') ?></span></a>
<?php endif; ?>
      <a href="<?= url('/areas') ?>"<?= $isCurrent('/areas') ?>><span class="menu-num"><?= sprintf('%02d', $mN++) ?></span><span><?= t('nav.areas') ?></span></a>
      <a href="<?= url('/about') ?>"<?= $isCurrent('/about') ?>><span class="menu-num"><?= sprintf('%02d', $mN++) ?></span><span><?= t('nav.about') ?></span></a>
      <a href="<?= url('/contact') ?>"<?= $isCurrent('/contact') ?>><span class="menu-num"><?= sprintf('%02d', $mN++) ?></span><span><?= t('nav.contact') ?></span></a>
    </nav>
    <div class="menu-foot">
      <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>
      <span><?= t('footer.place') ?></span>
    </div>
  </div>

  <main id="main">
<?php if ($page['crumbs']): ?>
    <nav class="crumbs wrap" aria-label="<?= tp('crumb.aria') ?>">
      <ol>
        <li><a href="<?= url('/') ?>"><?= t('nav.home') ?></a></li>
<?php foreach ($page['crumbs'] as $i => [$label, $path]): ?>
        <li><?php if ($i === count($page['crumbs']) - 1): ?><span aria-current="page"><?= strip_tags($label) ?></span><?php else: ?><a href="<?= url($path) ?>"><?= strip_tags($label) ?></a><?php endif; ?></li>
<?php endforeach; ?>
      </ol>
    </nav>
<?php endif; ?>
