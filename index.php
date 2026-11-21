<?php
require __DIR__ . '/inc/bootstrap.php';
$page = ['key' => 'home', 'title' => strip_tags(t('meta.home.title')), 'desc' => strip_tags(t('meta.desc'))];
require __DIR__ . '/inc/head.php';
$guides = $GLOBALS['SITE']['guide_order'];
?>

    <!-- HERO -->
    <section class="hero" aria-labelledby="hero-title">
      <div class="hero-slides" aria-hidden="true">
        <figure class="hero-slide is-on"><img src="<?= asset('img/hero.webp') ?>" alt="" width="2400" height="1602" fetchpriority="high"></figure>
        <figure class="hero-slide"><img src="<?= asset('img/svc-sell.webp') ?>" alt="" width="1400" height="1000" loading="lazy"></figure>
        <figure class="hero-slide"><img src="<?= asset('img/svc-buy.webp') ?>" alt="" width="1400" height="1000" loading="lazy"></figure>
      </div>
      <div class="hero-shade" aria-hidden="true"></div>

      <div class="wrap hero-inner">
        <p class="eyebrow eyebrow-light hero-eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('hero.eyebrow') ?></span></p>
        <h1 class="hero-title" id="hero-title" data-split><?= t('hero.title') ?></h1>

        <div class="hero-foot">
          <div class="hero-copy">
            <p class="hero-lead"><?= t('hero.lead') ?></p>
            <div class="hero-links">
              <a class="btn btn-gold btn-lg magnetic" href="<?= url('/contact') ?>"><span><?= t('hero.cta') ?></span><?= arrow() ?></a>
              <a class="btn btn-ghost-light btn-lg" href="#services"><span><?= t('hero.cta2') ?></span></a>
            </div>
          </div>
          <div class="hero-index" aria-hidden="true">
            <p class="hero-caption"><span class="hero-count"><span id="slide-num">01</span> / 03</span> <span id="slide-cap" data-caps="<?= e(json_encode([t('slide.1'), t('slide.2'), t('slide.3')], JSON_UNESCAPED_UNICODE)) ?>"><?= t('slide.1') ?></span></p>
            <div class="hero-bars"><span class="is-on"><i></i></span><span><i></i></span><span><i></i></span></div>
          </div>
        </div>
      </div>
      <a class="scroll-cue" href="#finder" aria-label="<?= tp('hero.scroll') ?>"><span></span></a>
    </section>

    <!-- FINDER: works without JavaScript (goes to the contact page with choices filled in) -->
    <div class="wrap finder-wrap" id="finder">
      <form class="finder" id="quick" action="<?= url('/contact') ?>" method="get" aria-label="<?= tp('quick.aria') ?>">
        <p class="finder-title"><?= t('quick.title') ?></p>
        <div class="finder-field">
          <label for="q-interest"><?= t('quick.service') ?></label>
          <select id="q-interest" name="interest">
            <option value="Buying"><?= t('opt.buy') ?></option>
            <option value="Selling"><?= t('opt.sell') ?></option>
            <option value="Renting"><?= t('opt.rent') ?></option>
            <option value="Property management"><?= t('opt.manage') ?></option>
          </select>
        </div>
        <div class="finder-field">
          <label for="q-type"><?= t('quick.type') ?></label>
          <select id="q-type" name="property_type">
            <option value="a property"><?= t('opt.typeAny') ?></option>
            <option value="an apartment"><?= t('opt.typeApt') ?></option>
            <option value="a villa"><?= t('opt.typeVilla') ?></option>
            <option value="a townhouse"><?= t('opt.typeTown') ?></option>
            <option value="a penthouse"><?= t('opt.typePent') ?></option>
            <option value="an office or shop"><?= t('opt.typeOffice') ?></option>
          </select>
        </div>
        <div class="finder-field">
          <label for="q-area"><?= t('quick.area') ?></label>
          <select id="q-area" name="area">
            <option value="anywhere in Dubai"><?= t('opt.areaAny') ?></option>
<?php foreach (['downtown-dubai', 'dubai-marina', 'palm-jumeirah', 'business-bay', 'dubai-creek-harbour', 'jumeirah', 'jumeirah-village-circle', 'dubai-hills-estate'] as $s): ?>
            <option value="<?= e(areas()[$s]['value']) ?>"><?= t('area.' . areas()[$s]['key']) ?></option>
<?php endforeach; ?>
            <option value="another area"><?= t('opt.areaOther') ?></option>
          </select>
        </div>
        <div class="finder-actions">
          <button class="btn btn-solid btn-lg" type="submit"><span><?= t('quick.go') ?></span><?= arrow() ?></button>
<?php if (WHATSAPP !== ''): ?>
          <a class="btn btn-whatsapp btn-lg" id="quick-wa" href="https://wa.me/<?= e(preg_replace('/\D/', '', WHATSAPP)) ?>" target="_blank" rel="noopener"><span><?= t('quick.wa') ?></span></a>
<?php endif; ?>
        </div>
      </form>
    </div>

    <!-- MARQUEE -->
    <div class="marquee" aria-hidden="true">
      <div class="marquee-track" id="marquee">
        <div class="marquee-group">
<?php foreach (['downtown', 'marina', 'palm', 'bay', 'creek', 'jumeirah', 'hills', 'jvc'] as $k): ?>
          <span><?= t('area.' . $k) ?></span><i class="roof"></i>
<?php endforeach; ?>
        </div>
      </div>
    </div>

    <!-- MANIFESTO -->
    <section class="section manifesto" aria-labelledby="manifesto-title">
      <div class="wrap">
        <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('manifesto.eyebrow') ?></span></p>
        <h2 class="manifesto-text" id="manifesto-title" data-fill><?= t('about.title') ?></h2>
        <a class="link-arrow" href="<?= url('/about') ?>"><span><?= t('home.storyLink') ?></span><?= arrow() ?></a>

        <dl class="facts">
          <div class="fact rv"><dt><span class="count" data-to="4">04</span></dt><dd><?= t('facts.services') ?></dd></div>
          <div class="fact rv"><dt><span class="count" data-to="12">12</span></dt><dd><?= t('facts.areas') ?></dd></div>
          <div class="fact rv"><dt><span class="count" data-to="2">02</span></dt><dd><?= t('facts.langs') ?></dd></div>
          <div class="fact rv"><dt><span class="count" data-to="1">01</span></dt><dd><?= t('facts.adviser') ?></dd></div>
        </dl>
      </div>
    </section>

    <!-- SERVICES: stacking cards -->
    <section class="section services" id="services" aria-labelledby="services-title">
      <div class="wrap">
        <div class="section-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('services.eyebrow') ?></span></p>
          <h2 id="services-title" data-split><?= t('services.title') ?></h2>
        </div>
        <ol class="stack">
<?php $i = 0; foreach (services() as $slug => $s): $i++; ?>
          <li class="card card-<?= $i ?>" style="--i:<?= $i - 1 ?>">
            <div class="card-media"><img src="<?= asset('img/' . $s['img']) ?>" alt="<?= tp('svc.' . $slug . 'Alt') ?>" width="<?= $s['w'] ?>" height="<?= $s['h'] ?>" loading="lazy"></div>
            <div class="card-body">
              <p class="card-num"><?= $s['num'] ?> <span>/ 04</span></p>
              <h3 class="card-title"><?= t('svc.' . $slug) ?></h3>
              <p class="card-text"><?= t($slug . '.text') ?></p>
              <ul class="ticks">
<?php for ($n = 1; $n <= 4; $n++): ?>
                <li><?= t($slug . '.li' . $n) ?></li>
<?php endfor; ?>
              </ul>
              <div class="card-actions">
                <a class="btn <?= $i > 2 ? 'btn-gold' : 'btn-solid' ?> magnetic" href="<?= url('/' . $slug) ?>"><span><?= str_replace('{name}', t('svc.lower.' . $slug), t('home.svcMore')) ?></span><?= arrow() ?></a>
                <a class="link-arrow" href="<?= url('/contact') ?>?interest=<?= urlencode($s['interest']) ?>"><span><?= t($slug . '.cta') ?></span></a>
              </div>
            </div>
          </li>
<?php endforeach; ?>
        </ol>
      </div>
    </section>

    <!-- AREAS: pinned horizontal gallery -->
    <section class="areas" id="areas" aria-labelledby="areas-title">
      <div class="areas-pin">
        <div class="areas-track" id="areas-track">
          <div class="areas-intro">
            <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('areas.eyebrow') ?></span></p>
            <h2 id="areas-title" data-split><?= t('areas.title') ?></h2>
            <p class="areas-lead"><?= t('areas.lead') ?></p>
            <p class="areas-hint" aria-hidden="true"><span><?= t('areas.hint') ?></span><?= arrow() ?></p>
          </div>
          <ul class="area-list" aria-label="<?= tp('areas.aria') ?>">
<?php foreach ($guides as $n => $slug): $a = areas()[$slug]; ?>
            <li class="area">
              <a href="<?= url('/areas/' . $slug) ?>" data-cursor="<?= tp('home.guide') ?>">
                <span class="area-img"><img src="<?= asset('img/' . $a['img']) ?>" alt="<?= tp('area.' . $a['key'] . 'Alt') ?>" width="900" height="1200" loading="lazy"></span>
                <span class="area-num"><?= str_pad((string) ($n + 1), 2, '0', STR_PAD_LEFT) ?></span>
                <span class="area-meta"><strong><?= t('area.' . $a['key']) ?></strong><span><?= t('area.' . $a['key'] . 'Desc') ?></span></span>
              </a>
            </li>
<?php endforeach; ?>
          </ul>
          <div class="areas-end">
            <p class="areas-end-label"><?= t('areas.alsoLabel') ?></p>
            <ul class="areas-also">
<?php foreach (['jvc', 'hills', 'jlt', 'ranches', 'barsha', 'furjan'] as $k): ?>
              <li><?= t('area.' . $k) ?></li>
<?php endforeach; ?>
            </ul>
            <a class="btn btn-gold magnetic" href="<?= url('/areas') ?>"><span><?= t('home.areasAll') ?></span><?= arrow() ?></a>
          </div>
        </div>
        <div class="areas-progress" aria-hidden="true"><span id="areas-bar"></span></div>
      </div>
    </section>

    <!-- PROCESS -->
    <section class="section process" id="process" aria-labelledby="process-title">
      <div class="wrap process-layout">
        <div class="process-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('process.eyebrow') ?></span></p>
          <h2 id="process-title" data-split><?= t('process.title') ?></h2>
          <p class="process-lead"><?= t('process.lead') ?></p>
          <a class="btn btn-solid magnetic" href="<?= url('/contact') ?>"><span><?= t('process.cta') ?></span><?= arrow() ?></a>
        </div>
        <ol class="steps">
<?php for ($n = 1; $n <= 4; $n++): ?>
          <li class="step">
            <span class="step-num">0<?= $n ?></span>
            <div><h3><?= t('steps.' . $n . '.title') ?></h3><p><?= t('steps.' . $n . '.text') ?></p></div>
          </li>
<?php endfor; ?>
        </ol>
      </div>
    </section>

    <!-- OWNERS -->
    <section class="owners" id="owners" aria-labelledby="owners-title">
      <div class="owners-media" aria-hidden="true"><img src="<?= asset('img/mgmt.webp') ?>" alt="" width="1600" height="1300" loading="lazy"></div>
      <div class="wrap owners-wrap">
        <div class="owners-card rv">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('owners.eyebrow') ?></span></p>
          <h2 id="owners-title"><?= t('owners.title') ?></h2>
          <p class="owners-lead"><?= t('owners.lead') ?></p>
          <dl class="owners-list">
<?php for ($n = 1; $n <= 4; $n++): ?>
            <div><dt><?= t('owners.' . $n . '.t') ?></dt><dd><?= t('owners.' . $n . '.d') ?></dd></div>
<?php endfor; ?>
          </dl>
          <a class="btn btn-solid magnetic" href="<?= url('/manage') ?>"><span><?= str_replace('{name}', t('svc.lower.manage'), t('home.svcMore')) ?></span><?= arrow() ?></a>
        </div>
      </div>
    </section>

<?php
$faqTitle = t('faq.title');
$faqs = [];
for ($n = 1; $n <= 5; $n++) $faqs[] = [t('faq.' . $n . '.q'), t('faq.' . $n . '.a')];
require __DIR__ . '/partials/faq.php';
require __DIR__ . '/partials/cta.php';
require __DIR__ . '/inc/foot.php';
