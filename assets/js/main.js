(function () {
  'use strict';

  var year = document.getElementById('year');
  if (year) year.textContent = new Date().getFullYear();

  /* ---- Hero: the four service words work as tabs ---- */
  var tabs = Array.prototype.slice.call(document.querySelectorAll('.word'));
  var panels = document.querySelectorAll('.panel');
  var photos = document.querySelectorAll('.gable-lg img');

  var select = function (tab, moveFocus) {
    var service = tab.getAttribute('data-service');
    tabs.forEach(function (t) {
      var on = t === tab;
      t.setAttribute('aria-selected', String(on));
      t.tabIndex = on ? 0 : -1;
    });
    panels.forEach(function (p) { p.classList.toggle('is-on', p.id === 'panel-' + service); });
    photos.forEach(function (img) { img.classList.toggle('is-on', img.getAttribute('data-photo') === service); });
    if (moveFocus) tab.focus();
  };

  if (tabs.length) {
    var hoverTimer;
    var canHover = window.matchMedia('(hover: hover)').matches;
    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { select(tab); });
      if (canHover) {
        tab.addEventListener('mouseenter', function () {
          clearTimeout(hoverTimer);
          hoverTimer = setTimeout(function () { select(tab); }, 90);
        });
        tab.addEventListener('mouseleave', function () { clearTimeout(hoverTimer); });
      }
      tab.addEventListener('keydown', function (e) {
        var next = null;
        if (e.key === 'ArrowDown' || e.key === 'ArrowRight') next = tabs[(i + 1) % tabs.length];
        if (e.key === 'ArrowUp' || e.key === 'ArrowLeft') next = tabs[(i - 1 + tabs.length) % tabs.length];
        if (e.key === 'Home') next = tabs[0];
        if (e.key === 'End') next = tabs[tabs.length - 1];
        if (next) { e.preventDefault(); select(next, true); }
      });
    });
    select(tabs[0]);
  }

  /* ---- Buttons that carry the chosen service into the form ---- */
  var interest = document.getElementById('f-interest');
  document.querySelectorAll('[data-interest]').forEach(function (link) {
    link.addEventListener('click', function () {
      if (interest) { interest.value = link.getAttribute('data-interest'); fitSelect(interest); }
      setTimeout(function () { document.getElementById('f-name').focus({ preventScroll: true }); }, 700);
    });
  });

  /* ---- Sentence form: each dropdown is only as wide as its chosen words ---- */
  var sizer = document.createElement('span');
  sizer.style.cssText = 'position:absolute;visibility:hidden;white-space:pre;left:-9999px;top:0';
  document.body.appendChild(sizer);
  var fitSelect = function (sel) {
    var cs = getComputedStyle(sel);
    sizer.style.font = cs.font;
    sizer.textContent = sel.options[sel.selectedIndex].text;
    sel.style.width = Math.ceil(sizer.getBoundingClientRect().width + parseFloat(cs.fontSize) * 1.25 + 12) + 'px';
  };
  var blanks = document.querySelectorAll('.blank select');
  var fitAll = function () { blanks.forEach(fitSelect); };
  blanks.forEach(function (sel) { sel.addEventListener('change', function () { fitSelect(sel); }); });
  fitAll();
  window.addEventListener('resize', fitAll);
  if (document.fonts && document.fonts.ready) document.fonts.ready.then(fitAll);

  /* ---- Contact form ---- */
  var form = document.getElementById('contact-form');
  var status = document.getElementById('form-status');
  if (!form) return;

  var EMAIL = 'info@sadanllc.com';
  var setStatus = function (msg, type) {
    status.textContent = msg;
    status.className = 'form-status' + (type ? ' is-' + type : '');
  };

  // Non-JS fallback lands back here with ?sent=1 or ?sent=0
  var params = new URLSearchParams(window.location.search);
  if (params.get('sent') === '1') setStatus('Enquiry sent. We will reply by email.', 'success');
  if (params.get('sent') === '0') setStatus('The enquiry was not sent. Email us at ' + EMAIL + ' instead.', 'error');

  var required = [
    { field: form.elements.name, problem: 'your name' },
    { field: form.elements.email, problem: 'a valid email address' }
  ];
  required.forEach(function (r) {
    r.field.addEventListener('input', function () { r.field.removeAttribute('aria-invalid'); });
  });

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    var missing = required.filter(function (r) {
      var bad = !r.field.value.trim() || !r.field.checkValidity();
      if (bad) r.field.setAttribute('aria-invalid', 'true'); else r.field.removeAttribute('aria-invalid');
      return bad;
    });
    if (missing.length) {
      setStatus('Add ' + missing.map(function (r) { return r.problem; }).join(' and ') + ' so we can reply.', 'error');
      missing[0].field.focus();
      return;
    }

    var button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    setStatus('Sending…');

    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
      .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
      .then(function (r) {
        if (r.ok && r.data.ok) {
          form.reset(); fitAll();
          setStatus('Enquiry sent. We will reply by email.', 'success');
        } else {
          setStatus((r.data && r.data.error) || 'The enquiry was not sent. Email us at ' + EMAIL + ' instead.', 'error');
        }
      })
      .catch(function () {
        setStatus('The enquiry was not sent. Email us at ' + EMAIL + ' instead.', 'error');
      })
      .finally(function () { button.disabled = false; });
  });
})();
