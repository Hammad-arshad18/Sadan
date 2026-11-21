/* Sadan Real Estate — Properties pages: live filters, gallery, request form.
   Everything here is an enhancement: without JavaScript the filters submit as a normal
   form, links page through results and the request form posts to request.php. */
(function () {
  'use strict';
  var CFG = window.SADAN || { t: {} };
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var motion = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* Broken remote photos fall back to the navy placeholder */
  var markBroken = function (img) {
    var box = img.closest('.pcard-media, .pg-item');
    if (box) box.classList.add('is-empty');
    img.remove();
  };
  document.addEventListener('error', function (e) {
    var img = e.target;
    if (img && img.tagName === 'IMG' && img.closest('.pcard-media, .pg-item')) markBroken(img);
  }, true);
  $$('.pcard-media img, .pg-item img').forEach(function (img) { if (img.complete && img.naturalWidth === 0 && img.getAttribute('src')) markBroken(img); });

  /* ---------------------------------------------------------------------
     Filters: update results in place, keep the address bar shareable
     --------------------------------------------------------------------- */
  var form = $('#pf');
  if (form) {
    var results = $('#results');
    var busy = null;
    var queryFrom = function () {
      var params = new URLSearchParams();
      new FormData(form).forEach(function (v, k) {
        v = String(v).trim();
        if (v === '' || (k === 'for' && v === 'sale') || (k === 'sort' && v === 'recommended')) return;
        params.append(k, v);
      });
      var s = params.toString();
      return s ? '?' + s : '';
    };

    var swap = function (url, push, scroll) {
      if (busy) busy.abort();
      busy = 'AbortController' in window ? new AbortController() : null;
      results.setAttribute('aria-busy', 'true');
      results.classList.add('is-loading');
      fetch(url, { signal: busy ? busy.signal : undefined, headers: { 'X-Requested-With': 'fetch' } })
        .then(function (r) { return r.text(); })
        .then(function (html) {
          var doc = new DOMParser().parseFromString(html, 'text/html');
          var active = document.activeElement && document.activeElement.id;
          ['#results', '#pf-side-body', '#pf-area', '#pf-type'].forEach(function (sel) {
            var fresh = $(sel, doc), old = $(sel);
            if (fresh && old) old.replaceWith(fresh);
          });
          var badgeNew = $('.pf-toggle', doc), badgeOld = $('.pf-toggle');
          if (badgeNew && badgeOld) { var exp = badgeOld.getAttribute('aria-expanded'); badgeOld.innerHTML = badgeNew.innerHTML; badgeOld.setAttribute('aria-expanded', exp); }
          results = $('#results');
          if (active && document.getElementById(active) && active !== 'pf-q') document.getElementById(active).focus({ preventScroll: true });
          if (push) history.pushState({ pf: 1 }, '', url);
          else history.replaceState({ pf: 1 }, '', url);
          if (scroll) {
            var top = $('#props').getBoundingClientRect().top + window.scrollY - 90;
            window.scrollTo({ top: top, behavior: motion ? 'smooth' : 'auto' });
          }
        })
        .catch(function (err) { if (!err || err.name !== 'AbortError') window.location.href = url; })
        .finally(function () { if (results) { results.removeAttribute('aria-busy'); results.classList.remove('is-loading'); } });
    };

    var base = form.getAttribute('action');
    var update = function (scroll) { swap(base + queryFrom(), false, scroll); };

    form.addEventListener('change', function (e) {
      var el = e.target;
      if (el.name === 'q') return;
      if (el.name === 'for') {
        // prices and the completion/furnishing choice differ between buying and renting
        ['min', 'max'].forEach(function (n) { if (form.elements[n]) form.elements[n].value = ''; });
        $$('input[name="done"], input[name="furn"]', form).forEach(function (r) { r.checked = r.value === ''; });
      }
      update(false);
    });
    var qTimer;
    form.elements.q.addEventListener('input', function () { clearTimeout(qTimer); qTimer = setTimeout(function () { update(false); }, 450); });
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      clearTimeout(qTimer);
      setSide(false);
      update(true);
    });

    // Links inside results (remove-filter chips, clear all, pages) load in place
    form.addEventListener('click', function (e) {
      var a = e.target.closest('.props-active a, .pager a, .pf-reset, .props-empty .btn');
      if (!a || e.metaKey || e.ctrlKey || e.shiftKey) return;
      if (!a.href || a.href.indexOf(base) === -1) return;
      e.preventDefault();
      // keep the form in step with the link we followed
      var target = new URL(a.href, location.href);
      syncForm(target.searchParams);
      swap(target.pathname + target.search, true, !!a.closest('.pager'));
    });

    var syncForm = function (params) {
      $$('input[type="checkbox"], input[type="radio"]', form).forEach(function (el) {
        var vals = params.getAll(el.name);
        if (el.type === 'radio' && !vals.length) el.checked = el.value === '' || (el.name === 'for' && el.value === 'sale');
        else el.checked = vals.indexOf(el.value) !== -1;
      });
      $$('select, input[type="search"]', form).forEach(function (el) {
        var v = params.get(el.name);
        el.value = v === null ? (el.name === 'sort' ? 'recommended' : '') : v;
      });
    };

    window.addEventListener('popstate', function () {
      syncForm(new URLSearchParams(location.search));
      swap(location.pathname + location.search, false, false);
    });

    /* More filters: a drawer on phones and tablets */
    var side = $('#pf-side');
    var setSide = function (open) {
      var btn = $('.pf-toggle');
      if (!btn) return;
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      side.classList.toggle('is-open', open);
    };
    side.addEventListener('click', function (e) {
      if (e.target.closest('.pf-toggle')) setSide($('.pf-toggle').getAttribute('aria-expanded') !== 'true');
    });
  }

  /* ---------------------------------------------------------------------
     Gallery lightbox
     --------------------------------------------------------------------- */
  var gallery = $('#gallery'), box = $('#lightbox');
  if (gallery && box && typeof box.showModal === 'function') {
    var photos = [];
    try { photos = JSON.parse(gallery.getAttribute('data-photos') || '[]'); } catch (e) {}
    var idx = 0, img = $('.lb-stage img', box), count = $('.lb-count', box);
    var label = (CFG.t['prop.photoOf'] || '{n} / {total}');
    var show = function (i) {
      idx = (i + photos.length) % photos.length;
      img.src = photos[idx];
      count.textContent = label.replace('{n}', idx + 1).replace('{total}', photos.length);
      [idx + 1, idx - 1].forEach(function (n) { var p = new Image(); p.referrerPolicy = 'no-referrer'; p.src = photos[(n + photos.length) % photos.length]; });
    };
    gallery.addEventListener('click', function (e) {
      var b = e.target.closest('[data-i]');
      if (!b || !photos.length) return;
      show(parseInt(b.getAttribute('data-i'), 10) || 0);
      box.showModal();
      document.documentElement.classList.add('lb-open');
    });
    var rtl = document.documentElement.dir === 'rtl';
    $('.lb-prev', box).addEventListener('click', function () { show(idx + (rtl ? 1 : -1)); });
    $('.lb-next', box).addEventListener('click', function () { show(idx + (rtl ? -1 : 1)); });
    $('.lb-close', box).addEventListener('click', function () { box.close(); });
    box.addEventListener('close', function () { document.documentElement.classList.remove('lb-open'); });
    box.addEventListener('click', function (e) { if (e.target === box || e.target.classList.contains('lb-stage')) box.close(); });
    box.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') show(idx + (rtl ? 1 : -1));
      if (e.key === 'ArrowRight') show(idx + (rtl ? -1 : 1));
    });
    var sx = null;
    box.addEventListener('touchstart', function (e) { sx = e.touches[0].clientX; }, { passive: true });
    box.addEventListener('touchend', function (e) {
      if (sx === null) return;
      var dx = e.changedTouches[0].clientX - sx; sx = null;
      if (Math.abs(dx) > 40) show(idx + ((dx < 0) !== rtl ? 1 : -1));
    });
  }

  /* Long descriptions fold behind "Read more" */
  var desc = $('#prop-desc'), more = $('.prop-more');
  if (desc && more && desc.scrollHeight > 420) {
    desc.classList.add('is-clamped');
    more.hidden = false;
    more.addEventListener('click', function () {
      var open = more.getAttribute('aria-expanded') !== 'true';
      more.setAttribute('aria-expanded', open ? 'true' : 'false');
      desc.classList.toggle('is-clamped', !open);
      more.textContent = more.getAttribute(open ? 'data-less' : 'data-more');
    });
  }

  /* ---------------------------------------------------------------------
     Request form (falls back to a normal post)
     --------------------------------------------------------------------- */
  var req = $('#request-form');
  if (req) {
    var status = $('#request-status');
    var say = function (msg, type) { status.textContent = msg; status.className = 'form-status' + (type ? ' is-' + type : ''); };
    var nameF = req.elements.name, phoneF = req.elements.phone, mailF = req.elements.email;
    [nameF, phoneF, mailF].forEach(function (f) { f.addEventListener('input', function () { f.removeAttribute('aria-invalid'); }); });
    req.addEventListener('submit', function (e) {
      e.preventDefault();
      var missing = [];
      if (!nameF.value.trim()) { nameF.setAttribute('aria-invalid', 'true'); missing.push(nameF); }
      var phoneOk = phoneF.value.replace(/\D/g, '').length >= 7;
      var mailOk = mailF.value.trim() !== '' && mailF.checkValidity();
      if (!phoneOk && !mailOk) { phoneF.setAttribute('aria-invalid', 'true'); missing.push(phoneF); }
      if (missing.length) { say(CFG.t['prop.req.missing'], 'error'); missing[0].focus(); return; }
      var btn = $('button[type="submit"]', req);
      btn.disabled = true;
      say(CFG.t['form.sending'] || '…');
      fetch(req.action, { method: 'POST', body: new FormData(req), headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (r) {
          if (r.ok && r.data.ok) { req.reset(); say(CFG.t['prop.req.success'], 'success'); }
          else say((r.data && r.data.error) || CFG.t['prop.req.error'], 'error');
        })
        .catch(function () { say(CFG.t['prop.req.error'], 'error'); })
        .finally(function () { btn.disabled = false; });
    });
  }

  /* Phone bar: hide once the request card is on screen */
  var sticky = $('.prop-sticky'), card = $('#request');
  if (sticky && card && 'IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      sticky.classList.toggle('is-hidden', entries[0].isIntersecting);
    }, { rootMargin: '0px 0px -20% 0px' }).observe(card);
  }
})();
