/* ==========================================================================
   Sadan Real Estate — interaction engine (v8, multi-page)
   Plain JavaScript. Every feature checks that its elements exist, so the same
   file runs on every page. Text comes from the server (window.SADAN).
   Lenis (smooth scrolling) is optional; without it the page scrolls natively.
   ========================================================================== */
(function () {
  'use strict';

  var CFG = window.SADAN || { lang: 'en', whatsapp: '', send: '/send.php', t: {} };
  var t = function (k) { return CFG.t[k] != null ? CFG.t[k] : k; };
  var root = document.documentElement;
  var motion = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var wideMQ = window.matchMedia('(min-width: 900px)');
  var rtl = root.dir === 'rtl';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var clamp = function (v, a, b) { return Math.max(a, Math.min(b, v)); };
  var nf = new Intl.NumberFormat(CFG.lang === 'ar' ? 'ar-AE-u-nu-latn' : 'en-AE', { maximumFractionDigits: 0 });

  if (!motion) root.classList.add('no-motion');

  /* ---------------------------------------------------------------------
     Smooth scrolling (same-page links only; other links navigate normally
     so the roof-shaped page transition can play)
     --------------------------------------------------------------------- */
  var lenis = (motion && window.Lenis) ? new window.Lenis({ lerp: 0.09, smoothWheel: true }) : null;
  var header = $('#top');
  var scrollToEl = function (el, cb) {
    if (!el) return;
    var top = el === document.body;
    if (lenis) lenis.scrollTo(top ? 0 : el, { offset: top ? 0 : -header.offsetHeight + 1, duration: 1.3, onComplete: cb });
    else { if (top) window.scrollTo({ top: 0, behavior: motion ? 'smooth' : 'auto' }); else el.scrollIntoView({ behavior: motion ? 'smooth' : 'auto' }); if (cb) setTimeout(cb, motion ? 700 : 0); }
  };
  document.addEventListener('click', function (e) {
    var a = e.target.closest('a[href^="#"]');
    if (!a || a.classList.contains('skip-link')) return;
    var id = a.getAttribute('href');
    var target = id === '#top' ? document.body : (id.length > 1 ? document.querySelector(id) : null);
    if (!target) return;
    e.preventDefault();
    if (menuOpen) setMenu(false);
    scrollToEl(target, function () {
      var f = target.querySelector && target.querySelector('input, select, textarea');
      if (target.id === 'enquire' && f) f.focus({ preventScroll: true });
    });
  });

  /* ---------------------------------------------------------------------
     Words: rise animation for headings, read-along fill for statements
     --------------------------------------------------------------------- */
  var wrapWords = function (el, make) {
    var i = 0;
    (function walk(node) {
      Array.prototype.slice.call(node.childNodes).forEach(function (ch) {
        if (ch.nodeType === 3) {
          var frag = document.createDocumentFragment();
          ch.textContent.split(/(\s+)/).forEach(function (part) {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(part)); return; }
            frag.appendChild(make(part, i++));
          });
          node.replaceChild(frag, ch);
        } else if (ch.nodeType === 1) walk(ch);
      });
    })(el);
  };
  $$('[data-split]').forEach(function (el) {
    el.setAttribute('aria-label', el.textContent.replace(/\s+/g, ' ').trim());
    wrapWords(el, function (word, i) {
      var w = document.createElement('span'); w.className = 'w'; w.setAttribute('aria-hidden', 'true');
      var wi = document.createElement('span'); wi.className = 'wi'; wi.style.setProperty('--wi', i); wi.textContent = word;
      w.appendChild(wi); return w;
    });
  });
  var fills = $$('[data-fill]').map(function (el) {
    wrapWords(el, function (word) { var s = document.createElement('span'); s.className = 'fill-w'; s.textContent = word; return s; });
    var words = $$('.fill-w', el);
    if (!motion) words.forEach(function (w) { w.classList.add('is-lit'); });
    return { el: el, words: words, lit: -1 };
  });

  /* ---------------------------------------------------------------------
     Dubai clock
     --------------------------------------------------------------------- */
  var clockFmt = null;
  try { clockFmt = new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Dubai', hour: '2-digit', minute: '2-digit' }); } catch (e) {}
  var renderClock = function () { if (clockFmt) { var s = clockFmt.format(new Date()); $$('.js-clock').forEach(function (el) { el.textContent = s; }); } };
  renderClock(); setInterval(renderClock, 15000);

  /* ---------------------------------------------------------------------
     Header, services mega menu, full-screen menu
     --------------------------------------------------------------------- */
  var menu = $('#menu'), menuBtn = $('.menu-btn'), menuOpen = false;
  var mega = $('#mega'), megaBtn = $('.nav-trigger'), megaOpen = false, megaTimer;
  var darkZones = $$('.hero, .page-hero, .prop-hero, .areas, .contact, .cta-band, .site-footer, .card-3, .card-4');
  var lastY = window.scrollY;

  var updateHeader = function (y) {
    var probe = header.querySelector('.header-bar').offsetHeight / 2;
    var over = !megaOpen && (menuOpen || darkZones.some(function (z) { var r = z.getBoundingClientRect(); return r.top <= probe && r.bottom >= probe; }));
    header.classList.toggle('is-over', over);
    header.classList.toggle('is-solid', (y > 40 || megaOpen) && !menuOpen);
    if (!menuOpen && !megaOpen && !header.contains(document.activeElement)) {
      if (y > lastY + 2 && y > window.innerHeight * 0.6) header.classList.add('is-hidden');
      else if (y < lastY - 2 || y < 80) header.classList.remove('is-hidden');
    } else header.classList.remove('is-hidden');
    lastY = y;
  };

  var setMega = function (open) {
    if (!mega || open === megaOpen) return;
    megaOpen = open;
    megaBtn.setAttribute('aria-expanded', String(open));
    header.classList.toggle('mega-open', open);
    if (open) { mega.hidden = false; requestAnimationFrame(function () { mega.classList.add('is-open'); }); }
    else { mega.classList.remove('is-open'); setTimeout(function () { if (!megaOpen) mega.hidden = true; }, motion ? 400 : 0); }
    updateHeader(window.scrollY);
  };
  if (mega && megaBtn) {
    megaBtn.addEventListener('click', function () { setMega(!megaOpen); });
    if (finePointer) {
      var navItem = megaBtn.parentNode;
      [navItem, mega].forEach(function (el) {
        el.addEventListener('mouseenter', function () { clearTimeout(megaTimer); megaTimer = setTimeout(function () { setMega(true); }, 80); });
        el.addEventListener('mouseleave', function () { clearTimeout(megaTimer); megaTimer = setTimeout(function () { setMega(false); }, 220); });
      });
    }
    document.addEventListener('click', function (e) { if (megaOpen && !header.contains(e.target)) setMega(false); });
    header.addEventListener('focusout', function (e) { if (megaOpen && !header.contains(e.relatedTarget)) setMega(false); });
  }

  var setMenu = function (open) {
    menuOpen = open;
    menuBtn.setAttribute('aria-expanded', String(open));
    if (open) {
      menu.hidden = false;
      requestAnimationFrame(function () { menu.classList.add('is-open'); });
      if (lenis) lenis.stop(); else document.body.style.overflow = 'hidden';
      setTimeout(function () { var a = $('a', menu); if (a) a.focus({ preventScroll: true }); }, 350);
    } else {
      menu.classList.remove('is-open');
      if (lenis) lenis.start(); else document.body.style.overflow = '';
      setTimeout(function () { if (!menuOpen) menu.hidden = true; }, motion ? 800 : 0);
    }
    updateHeader(window.scrollY);
  };
  if (menuBtn) menuBtn.addEventListener('click', function () { setMenu(!menuOpen); });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    if (menuOpen) { setMenu(false); menuBtn.focus(); }
    if (megaOpen) { setMega(false); megaBtn.focus(); }
  });
  wideMQ.addEventListener('change', function () { if (menuOpen && window.innerWidth > 980) setMenu(false); });

  /* ---------------------------------------------------------------------
     Home hero slideshow
     --------------------------------------------------------------------- */
  var hero = $('.hero');
  var slides = $$('.hero-slide'), bars = $$('.hero-bars span');
  var slideNum = $('#slide-num'), slideCap = $('#slide-cap');
  var caps = []; try { caps = JSON.parse(slideCap ? slideCap.getAttribute('data-caps') : '[]'); } catch (e) {}
  var SLIDE_MS = 6500, slideIdx = 0, slideTimer = null, heroVisible = true;
  root.style.setProperty('--slide-ms', SLIDE_MS + 'ms');
  var showSlide = function (n) {
    if (!slides.length) return;
    slideIdx = (n + slides.length) % slides.length;
    slides.forEach(function (s, i) { s.classList.toggle('is-on', i === slideIdx); if (i === slideIdx) { var im = $('img', s); if (im) im.loading = 'eager'; } });
    bars.forEach(function (b, i) { b.classList.remove('is-on'); b.classList.toggle('is-done', i < slideIdx); });
    void (bars[slideIdx] && bars[slideIdx].offsetWidth);
    if (bars[slideIdx]) bars[slideIdx].classList.add('is-on');
    if (slideNum) slideNum.textContent = String(slideIdx + 1).padStart(2, '0');
    if (slideCap && caps[slideIdx]) slideCap.textContent = caps[slideIdx];
  };
  var playSlides = function () { if (motion && slides.length > 1 && !slideTimer) slideTimer = setInterval(function () { showSlide(slideIdx + 1); }, SLIDE_MS); };
  var pauseSlides = function () { clearInterval(slideTimer); slideTimer = null; };
  document.addEventListener('visibilitychange', function () { if (document.hidden) pauseSlides(); else if (heroVisible) { showSlide(slideIdx); playSlides(); } });

  var startPage = function () {
    if (hero) { hero.classList.add('is-ready'); showSlide(0); playSlides(); }
    $$('.hero [data-split], .page-hero [data-split], .contact-page .contact-head [data-split]').forEach(function (el) { el.classList.add('is-in'); });
    $$('.page-hero').forEach(function (el) { el.classList.add('is-ready'); });
  };

  /* ---------------------------------------------------------------------
     Intro (first page of a visit only)
     --------------------------------------------------------------------- */
  var runIntro = function () {
    if (!root.classList.contains('has-intro')) { startPage(); return; }
    if (lenis) lenis.stop();
    var num = $('#intro-num'), t0 = performance.now(), dur = 1600;
    (function tick(now) {
      var p = clamp((now - t0) / dur, 0, 1);
      num.textContent = String(Math.round((1 - Math.pow(1 - p, 3)) * 100)).padStart(2, '0');
      if (p < 1) { requestAnimationFrame(tick); return; }
      root.classList.add('intro-done');
      try { sessionStorage.setItem('sadan-intro', '1'); } catch (e) {}
      setTimeout(startPage, 350);
      setTimeout(function () { root.classList.remove('has-intro', 'intro-done'); if (lenis) lenis.start(); }, 1150);
    })(t0);
  };

  /* ---------------------------------------------------------------------
     Marquee
     --------------------------------------------------------------------- */
  var marquee = $('#marquee'), mqX = 0, mqW = 0;
  var buildMarquee = function () {
    if (!marquee) return;
    $$('.is-clone', marquee).forEach(function (c) { c.remove(); });
    var g = $('.marquee-group', marquee); mqW = g.offsetWidth;
    if (!mqW) return;
    for (var i = 0, n = Math.ceil(window.innerWidth * 2 / mqW) + 1; i < n; i++) { var c = g.cloneNode(true); c.classList.add('is-clone'); marquee.appendChild(c); }
  };

  /* ---------------------------------------------------------------------
     Home areas: pinned horizontal scroll on wide screens
     --------------------------------------------------------------------- */
  var areas = $('#areas'), areasTrack = $('#areas-track'), areasBar = $('#areas-bar');
  var areaImgs = $$('.area-img img'), areaDist = 0, hScroll = false;
  var measureAreas = function () {
    if (!areas) return;
    hScroll = motion && wideMQ.matches;
    root.classList.toggle('h-scroll', hScroll);
    if (!hScroll) { areas.style.height = ''; return; }
    areaDist = Math.max(0, areasTrack.scrollWidth - window.innerWidth);
    areas.style.height = (areaDist + window.innerHeight) + 'px';
  };
  if (areas) {
    areas.addEventListener('focusin', function (e) {
      if (!hScroll) return;
      var item = e.target.closest('.area, .areas-end, .areas-intro');
      if (!item) return;
      var fromStart = rtl ? (areasTrack.offsetLeft + areasTrack.scrollWidth) - (item.offsetLeft + item.offsetWidth) : item.offsetLeft - areasTrack.offsetLeft;
      var top = areas.offsetTop + clamp((fromStart - 48) / Math.max(areaDist, 1), 0, 1) * areaDist;
      if (lenis) lenis.scrollTo(top, { immediate: true }); else window.scrollTo(0, top);
    });
  }

  /* ---------------------------------------------------------------------
     Reveal on enter + counters
     --------------------------------------------------------------------- */
  var counts = $$('.count');
  if (motion) counts.forEach(function (c) { c.textContent = '00'; });
  var runCount = function (el) {
    var to = +el.getAttribute('data-to'), t0 = performance.now();
    (function step(now) {
      var p = clamp((now - t0) / 1600, 0, 1);
      el.textContent = String(Math.round(to * (1 - Math.pow(1 - p, 4)))).padStart(2, '0');
      if (p < 1) requestAnimationFrame(step);
    })(t0);
  };
  var revealTargets = $$('.rv, .card, .area, .wordmark, .count, .gable-reveal, .guide, .line-sec, [data-split]').filter(function (el) {
    return !el.closest('.hero, .page-hero') && !(el.matches('[data-split]') && el.closest('.contact-page .contact-head'));
  });
  if (!motion || !('IntersectionObserver' in window)) {
    revealTargets.forEach(function (el) { el.classList.add('is-in'); });
    counts.forEach(function (c) { c.textContent = String(c.getAttribute('data-to')).padStart(2, '0'); });
  } else {
    $$('.facts, .principle-rows, .faq-list, .handle-grid, .journey-track, .guide-grid, .area-cards').forEach(function (g) {
      $$('.rv, .guide', g).forEach(function (el, i) { el.style.setProperty('--rv-d', (i * 90) + 'ms'); });
    });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        en.target.classList.add('is-in');
        if (en.target.classList.contains('count')) runCount(en.target);
        io.unobserve(en.target);
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.08 });
    revealTargets.forEach(function (el) { io.observe(el); });
  }

  /* ---------------------------------------------------------------------
     Cursor + magnetic buttons (mouse only)
     --------------------------------------------------------------------- */
  var cursor = $('#cursor'), cursorLabel = $('#cursor-label');
  var cx = -100, cy = -100, tx = -100, ty = -100;
  if (cursor && finePointer && motion) {
    window.addEventListener('pointermove', function (e) {
      tx = e.clientX; ty = e.clientY;
      cursor.classList.remove('is-hidden');
      var hit = e.target.closest && e.target.closest('[data-cursor]');
      cursor.classList.toggle('is-big', !!hit);
      if (hit) cursorLabel.textContent = hit.getAttribute('data-cursor');
    }, { passive: true });
    document.addEventListener('pointerleave', function () { cursor.classList.add('is-hidden'); });
    $$('.magnetic').forEach(function (b) {
      b.addEventListener('pointermove', function (e) {
        var r = b.getBoundingClientRect();
        b.style.setProperty('--mx', ((e.clientX - r.left - r.width / 2) * 0.22).toFixed(1) + 'px');
        b.style.setProperty('--my', ((e.clientY - r.top - r.height / 2) * 0.32).toFixed(1) + 'px');
      });
      b.addEventListener('pointerleave', function () { b.style.setProperty('--mx', '0px'); b.style.setProperty('--my', '0px'); });
    });
  } else if (cursor) cursor.remove();

  /* ---------------------------------------------------------------------
     The Sadan Line: filters
     --------------------------------------------------------------------- */
  var line = $('#sadan-line');
  if (line) {
    var chips = $$('.line-filters .chip');
    chips.forEach(function (chip) {
      chip.addEventListener('click', function () {
        var f = chip.getAttribute('data-filter');
        chips.forEach(function (c) { c.setAttribute('aria-pressed', String(c === chip)); });
        $$('.stop', line).forEach(function (s) { s.classList.toggle('is-dim', f !== 'all' && s.getAttribute('data-type') !== f); });
        line.setAttribute('data-filter', f);
      });
    });
  }

  /* ---------------------------------------------------------------------
     Calculators
     --------------------------------------------------------------------- */
  $$('form[data-calc]').forEach(function (form) {
    var kind = form.getAttribute('data-calc');
    var val = function (n) { var v = parseFloat(form.elements[n].value); return isFinite(v) && v > 0 ? v : 0; };
    var out = function (k, v) { var el = $('[data-out="' + k + '"]', form); if (el) el.textContent = nf.format(Math.max(0, Math.round(v))); };
    var calc = function () {
      if (kind === 'mortgage') {
        var price = val('price'), down = clamp(val('down'), 0, 100), rate = val('rate') / 100 / 12, n = Math.round(clamp(val('years'), 1, 40) * 12);
        var loan = price * (1 - down / 100);
        var pay = rate ? loan * rate / (1 - Math.pow(1 + rate, -n)) : loan / n;
        out('monthly', pay); out('loan', loan); out('downAmt', price - loan); out('interest', pay * n - loan);
      } else {
        var rent = val('rent'), costs = val('service') + val('maint') + rent * clamp(val('fee'), 0, 100) / 100;
        var net = rent - costs;
        out('net', net); out('month', net / 12); out('costs', costs);
        var bar = $('[data-out-bar="share"]', form);
        if (bar) bar.style.transform = 'scaleX(' + (rent ? clamp(net / rent, 0, 1) : 0).toFixed(3) + ')';
      }
    };
    form.addEventListener('input', calc);
    calc();
  });

  /* ---------------------------------------------------------------------
     Checklists (remembered on this device)
     --------------------------------------------------------------------- */
  $$('[data-checklist]').forEach(function (list) {
    var key = 'sadan-' + list.getAttribute('data-checklist');
    var boxes = $$('input[type="checkbox"]', list);
    var saved = {};
    try { saved = JSON.parse(localStorage.getItem(key) || '{}'); } catch (e) {}
    boxes.forEach(function (b) { b.checked = !!saved[b.name]; });
    var render = function () {
      var n = boxes.filter(function (b) { return b.checked; }).length;
      $('.check-count', list).textContent = t('tool.progress').replace('{n}', n).replace('{total}', boxes.length);
      $('.check-bar i', list).style.transform = 'scaleX(' + (n / boxes.length).toFixed(3) + ')';
      list.classList.toggle('is-complete', n === boxes.length);
    };
    list.addEventListener('change', function () {
      var state = {}; boxes.forEach(function (b) { state[b.name] = b.checked; });
      try { localStorage.setItem(key, JSON.stringify(state)); } catch (e) {}
      render();
    });
    render();
  });

  /* ---------------------------------------------------------------------
     Enquiry form (falls back to a normal post without JavaScript)
     --------------------------------------------------------------------- */
  var form = $('#contact-form');
  if (form) {
    var status = $('#form-status');
    var setStatus = function (msg, type) { status.textContent = msg; status.className = 'form-status' + (type ? ' is-' + type : ''); };
    var required = [{ field: form.elements.name, key: 'form.missingName' }, { field: form.elements.email, key: 'form.missingEmail' }];
    required.forEach(function (r) { r.field.addEventListener('input', function () { r.field.removeAttribute('aria-invalid'); }); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var missing = required.filter(function (r) {
        var bad = !r.field.value.trim() || !r.field.checkValidity();
        if (bad) r.field.setAttribute('aria-invalid', 'true'); else r.field.removeAttribute('aria-invalid');
        return bad;
      });
      if (missing.length) {
        setStatus(t('form.missing').replace('{fields}', missing.map(function (r) { return t(r.key); }).join(t('form.and'))), 'error');
        missing[0].field.focus();
        return;
      }
      var button = $('button[type="submit"]', form);
      button.disabled = true;
      setStatus(t('form.sending'));
      fetch(CFG.send, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
        .then(function (res) { return res.json().then(function (d) { return { ok: res.ok, data: d }; }); })
        .then(function (r) {
          if (r.ok && r.data.ok) { form.reset(); setStatus(t('form.success'), 'success'); }
          else setStatus((r.data && r.data.error) || t('form.error'), 'error');
        })
        .catch(function () { setStatus(t('form.error'), 'error'); })
        .finally(function () { button.disabled = false; });
    });
  }

  /* ---------------------------------------------------------------------
     One animation loop for everything tied to scroll
     --------------------------------------------------------------------- */
  var cards = $$('.card'), steps = $('.steps'), stepItems = steps ? $$('.step', steps) : [];
  var ownersSec = $('.owners'), ownersImg = $('.owners-media img'), heroSlides = $('.hero-slides');
  var watermarks = $$('.watermark');
  var prevT = performance.now(), prevY = window.scrollY, vel = 0;

  function frame(now) {
    if (lenis) lenis.raf(now);
    var dt = Math.min(64, now - prevT) / 1000; prevT = now;
    var y = window.scrollY, vh = window.innerHeight, vw = window.innerWidth;
    vel = vel * 0.85 + ((y - prevY) / Math.max(dt, 0.001)) * 0.15; prevY = y;
    updateHeader(y);

    if (steps) {
      var sr = steps.getBoundingClientRect();
      if (sr.bottom > -vh && sr.top < vh * 2) {
        steps.style.setProperty('--progress', clamp((vh * 0.6 - sr.top) / sr.height, 0, 1).toFixed(3));
        stepItems.forEach(function (li) { li.classList.toggle('is-reached', li.getBoundingClientRect().top + 36 < vh * 0.62); });
      }
    }

    if (motion) {
      if (hero) {
        var hr = hero.getBoundingClientRect(), vis = hr.bottom > 0;
        if (vis !== heroVisible) { heroVisible = vis; if (vis) playSlides(); else pauseSlides(); }
        if (vis) heroSlides.style.setProperty('--hy', (y * 0.28).toFixed(1) + 'px');
      }
      watermarks.forEach(function (w) { w.style.setProperty('--wy', (y * 0.18).toFixed(1) + 'px'); });

      if (mqW) {
        mqX += (60 + Math.min(Math.abs(vel) * 0.35, 900)) * dt * (rtl ? 1 : -1);
        if (mqX <= -mqW) mqX += mqW;
        if (mqX > 0) mqX -= mqW;
        marquee.style.transform = 'translate3d(' + mqX.toFixed(2) + 'px,0,0)';
      }

      fills.forEach(function (f) {
        var r = f.el.getBoundingClientRect();
        if (r.top > vh || r.bottom < 0) return;
        var lit = Math.round(clamp((vh * 0.82 - r.top) / (r.height + vh * 0.3), 0, 1) * f.words.length);
        if (lit !== f.lit) { f.words.forEach(function (w, i) { w.classList.toggle('is-lit', i < lit); }); f.lit = lit; }
      });

      if (wideMQ.matches) {
        for (var i = 0; i < cards.length; i++) {
          var k = 0, next = cards[i + 1];
          if (next) { var cr = cards[i].getBoundingClientRect(); k = clamp(1 - (next.getBoundingClientRect().top - cr.top) / cr.height, 0, 1); }
          cards[i].style.setProperty('--s', (1 - k * 0.07).toFixed(4));
          cards[i].style.setProperty('--dim', (k * 0.45).toFixed(3));
        }
      }

      if (hScroll && areaDist) {
        var ar = areas.getBoundingClientRect();
        if (ar.top < vh && ar.bottom > 0) {
          var ap = clamp(-ar.top / areaDist, 0, 1);
          areasTrack.style.setProperty('--tx', ((rtl ? 1 : -1) * ap * areaDist).toFixed(1) + 'px');
          areasBar.parentNode.style.setProperty('--ap', ap.toFixed(4));
          areaImgs.forEach(function (img) {
            var r = img.parentNode.getBoundingClientRect();
            if (r.right < -200 || r.left > vw + 200) return;
            img.style.setProperty('--px', (((r.left + r.width / 2) - vw / 2) * -0.06).toFixed(1) + 'px');
          });
        }
      }

      if (ownersSec) {
        var orr = ownersSec.getBoundingClientRect();
        if (orr.top < vh && orr.bottom > 0) {
          ownersImg.style.setProperty('--py', clamp(((orr.top + orr.height / 2) - vh / 2) * -0.12, -orr.height * 0.11, orr.height * 0.11).toFixed(1) + 'px');
        }
      }

      if (cursor && cursor.isConnected) {
        cx += (tx - cx) * 0.2; cy += (ty - cy) * 0.2;
        cursor.style.setProperty('--cx', cx.toFixed(1) + 'px');
        cursor.style.setProperty('--cy', cy.toFixed(1) + 'px');
      }
    }
    requestAnimationFrame(frame);
  }

  /* ---------------------------------------------------------------------
     Start
     --------------------------------------------------------------------- */
  var relayout = function () { buildMarquee(); measureAreas(); };
  relayout();
  window.addEventListener('resize', relayout);
  wideMQ.addEventListener('change', relayout);
  window.addEventListener('load', relayout);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(relayout);

  /* a sent form returns here with ?sent=1: bring it into view */
  if (/[?&]sent=/.test(location.search) && form) setTimeout(function () { form.scrollIntoView(); }, 50);

  runIntro();
  requestAnimationFrame(frame);
})();
