<?php
require __DIR__ . '/inc/bootstrap.php';
$slug = (string) ($_GET['s'] ?? '');
$all = services();
if (!isset($all[$slug])) render_404();
$s = $all[$slug];
$name = t('svc.' . $slug);
$lower = t('svc.lower.' . $slug);
$keys = array_keys($all);
$idx = array_search($slug, $keys, true);
$next = $keys[($idx + 1) % count($keys)];

$page = [
    'key' => $slug,
    'title' => str_replace('{name}', strip_tags(t('svc.meta.' . $slug)), strip_tags(t('meta.svc.title'))),
    'desc' => strip_tags(t($slug . '.text')),
    'image' => $s['img'],
    'crumbs' => [[$name, '/' . $slug]],
];
require __DIR__ . '/inc/head.php';

$hero = [
    'eyebrow' => str_replace('{num}', $s['num'], t('svc.page.eyebrow')) . ' · ' . $name,
    'title' => t('svc.' . $slug . '.title'),
    'lead' => t($slug . '.text'),
    'image' => $s['img'], 'w' => $s['w'], 'h' => $s['h'],
    'alt' => strip_tags(t('svc.' . $slug . 'Alt')),
    'watermark' => $s['ar_word'],
    'meta' => '<div class="page-hero-actions"><a class="btn btn-gold btn-lg magnetic" href="#enquire"><span>' . t($slug . '.cta') . '</span>' . arrow() . '</a></div>',
];
require __DIR__ . '/partials/page-hero.php';
?>

    <!-- WHAT WE HANDLE -->
    <section class="section handle" aria-labelledby="handle-title">
      <div class="wrap">
        <div class="section-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= $name ?></span></p>
          <h2 id="handle-title" data-split><?= t('svc.handleTitle') ?></h2>
        </div>
        <ol class="handle-grid">
<?php for ($n = 1; $n <= 4; $n++): ?>
          <li class="handle-item rv">
            <span class="handle-num">0<?= $n ?></span>
            <h3><?= t($slug . '.li' . $n) ?></h3>
            <p><?= t('svc.' . $slug . '.h' . $n . 'd') ?></p>
          </li>
<?php endfor; ?>
        </ol>
      </div>
    </section>

    <!-- STEPS: horizontal journey -->
    <section class="section journey" aria-labelledby="journey-title">
      <div class="wrap">
        <div class="section-head">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('process.eyebrow') ?></span></p>
          <h2 id="journey-title" data-split><?= t('svc.stepsTitle') ?></h2>
        </div>
        <ol class="journey-track">
<?php for ($n = 1; $n <= 4; $n++): ?>
          <li class="journey-step rv">
            <span class="journey-dot" aria-hidden="true"></span>
            <span class="journey-n">0<?= $n ?></span>
            <h3><?= t('svc.' . $slug . '.s' . $n . 't') ?></h3>
            <p><?= t('svc.' . $slug . '.s' . $n . 'd') ?></p>
          </li>
<?php endfor; ?>
        </ol>
      </div>
    </section>

    <!-- TOOL -->
    <section class="section tool" aria-labelledby="tool-title">
      <div class="wrap tool-layout">
<?php if ($s['tool'] === 'mortgage'): ?>
        <div class="tool-copy">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('tool.eyebrow') ?></span></p>
          <h2 id="tool-title" data-split><?= t('calc.title') ?></h2>
          <p class="tool-note"><?= t('calc.note') ?></p>
        </div>
        <form class="calc" data-calc="mortgage" onsubmit="return false">
          <div class="calc-fields">
            <label class="calc-field"><span><?= t('calc.price') ?></span><input type="number" name="price" value="2000000" min="0" step="10000" inputmode="numeric" dir="ltr"></label>
            <label class="calc-field"><span><?= t('calc.down') ?></span><input type="number" name="down" value="20" min="0" max="100" step="1" inputmode="decimal" dir="ltr"></label>
            <label class="calc-field"><span><?= t('calc.rate') ?></span><input type="number" name="rate" value="4.5" min="0" max="20" step="0.05" inputmode="decimal" dir="ltr"></label>
            <label class="calc-field"><span><?= t('calc.years') ?></span><input type="number" name="years" value="25" min="1" max="30" step="1" inputmode="numeric" dir="ltr"></label>
          </div>
          <output class="calc-result" aria-live="polite">
            <span class="calc-label"><?= t('calc.monthly') ?></span>
            <span class="calc-big"><span data-out="monthly">–</span> <small><?= t('tool.aed') ?></small></span>
            <span class="calc-rows">
              <span><?= t('calc.loan') ?> <b data-out="loan">–</b></span>
              <span><?= t('calc.downAmt') ?> <b data-out="downAmt">–</b></span>
              <span><?= t('calc.interest') ?> <b data-out="interest">–</b></span>
            </span>
          </output>
        </form>
<?php elseif ($s['tool'] === 'net-rent'): ?>
        <div class="tool-copy">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('tool.eyebrow') ?></span></p>
          <h2 id="tool-title" data-split><?= t('net.title') ?></h2>
          <p class="tool-note"><?= t('net.note') ?></p>
        </div>
        <form class="calc" data-calc="net" onsubmit="return false">
          <div class="calc-fields">
            <label class="calc-field"><span><?= t('net.rent') ?></span><input type="number" name="rent" value="120000" min="0" step="1000" inputmode="numeric" dir="ltr"></label>
            <label class="calc-field"><span><?= t('net.service') ?></span><input type="number" name="service" value="15000" min="0" step="500" inputmode="numeric" dir="ltr"></label>
            <label class="calc-field"><span><?= t('net.maint') ?></span><input type="number" name="maint" value="5000" min="0" step="500" inputmode="numeric" dir="ltr"></label>
            <label class="calc-field"><span><?= t('net.fee') ?></span><input type="number" name="fee" value="0" min="0" max="100" step="0.5" inputmode="decimal" dir="ltr"></label>
          </div>
          <output class="calc-result" aria-live="polite">
            <span class="calc-label"><?= t('net.year') ?></span>
            <span class="calc-big"><span data-out="net">–</span> <small><?= t('tool.aed') ?></small></span>
            <span class="calc-rows">
              <span><?= t('net.month') ?> <b data-out="month">–</b></span>
              <span><?= t('net.costs') ?> <b data-out="costs">–</b></span>
            </span>
            <span class="calc-bar" aria-hidden="true"><i data-out-bar="share"></i></span>
          </output>
        </form>
<?php else: $prefix = $s['tool'] === 'sell-check' ? 'sellcheck' : 'rentcheck'; ?>
        <div class="tool-copy">
          <p class="eyebrow"><span class="roof" aria-hidden="true"></span><span><?= t('tool.eyebrow') ?></span></p>
          <h2 id="tool-title" data-split><?= t($prefix . '.title') ?></h2>
          <p class="tool-note"><?= t($prefix . '.lead') ?></p>
        </div>
        <div class="checklist" data-checklist="<?= $prefix ?>">
          <p class="check-progress" aria-live="polite"><span class="check-count"><?= str_replace(['{n}', '{total}'], ['0', '6'], t('tool.progress')) ?></span><span class="check-bar" aria-hidden="true"><i></i></span></p>
          <ul>
<?php for ($n = 1; $n <= 6; $n++): ?>
            <li><label class="check"><input type="checkbox" name="c<?= $n ?>"><span class="check-box" aria-hidden="true"></span><span><?= t($prefix . '.' . $n) ?></span></label></li>
<?php endfor; ?>
          </ul>
          <p class="tool-saved"><?= t('tool.saved') ?></p>
        </div>
<?php endif; ?>
      </div>
    </section>

<?php
$faqTitle = str_replace('{name}', $lower, t('svc.faqTitle'));
$faqs = [];
for ($n = 1; $n <= 3; $n++) $faqs[] = [t('svc.' . $slug . '.f' . $n . 'q'), t('svc.' . $slug . '.f' . $n . 'a')];
require __DIR__ . '/partials/faq.php';
?>

    <!-- ENQUIRY -->
    <section class="contact" id="enquire" aria-labelledby="enquire-title">
      <div class="wrap">
        <div class="contact-head">
          <p class="eyebrow eyebrow-light"><span class="roof" aria-hidden="true"></span><span><?= t('cta.eyebrow') ?></span></p>
          <h2 class="contact-title" id="enquire-title" data-split><?= str_replace('{name}', $lower, t('svc.formTitle')) ?></h2>
        </div>
        <div class="contact-layout">
          <div class="contact-copy">
            <p class="contact-lead"><?= t('contact.lead') ?></p>
            <a class="contact-mail" href="mailto:<?= SITE_EMAIL ?>" data-cursor="<?= tp('cursor.email') ?>"><?= SITE_EMAIL ?></a>
          </div>
<?php $form = ['interest' => $s['interest']]; require __DIR__ . '/partials/enquiry-form.php'; ?>
        </div>
      </div>
    </section>

    <!-- NEXT SERVICE -->
    <a class="next-page" href="<?= url('/' . $next) ?>">
      <span class="wrap next-inner">
        <span class="next-label"><?= t('next.label') ?> · <?= $all[$next]['num'] ?></span>
        <span class="next-title"><?= t('svc.' . $next) ?></span>
        <span class="next-short"><?= t('svc.' . $next . '.short') ?></span>
        <span class="next-arrow" aria-hidden="true"><?= arrow() ?></span>
      </span>
    </a>
<?php require __DIR__ . '/inc/foot.php';
