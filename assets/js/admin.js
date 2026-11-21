/* Sadan admin panel: menus, toasts, list layout, and the property editor
   (photo uploads and ordering, map pin, live checklist, unsaved-changes bar). */
(function () {
  'use strict';
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var body = document.body;

  /* ---------- phone menu ---------- */
  var openBtn = $('[data-nav-open]');
  var scrim = $('.scrim');
  function nav(open) {
    body.classList.toggle('nav-open', open);
    if (scrim) scrim.hidden = !open;
    if (openBtn) openBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) { var f = $('.side a, .side button'); if (f) f.focus(); } else if (openBtn) { openBtn.focus(); }
  }
  if (openBtn) openBtn.addEventListener('click', function () { nav(true); });
  $$('[data-nav-close]').forEach(function (el) { el.addEventListener('click', function () { nav(false); }); });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      if (body.classList.contains('nav-open')) nav(false);
      $$('details.menu[open]').forEach(function (d) { d.open = false; });
    }
    // "/" jumps to search
    if (e.key === '/' && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
      var q = $('#top-q');
      if (q && q.offsetParent) { e.preventDefault(); q.focus(); }
    }
  });

  /* ---------- top bar line once scrolled ---------- */
  var top = $('.top');
  if (top) {
    var onScroll = function () { top.classList.toggle('is-stuck', window.scrollY > 4); };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  /* ---------- toasts ---------- */
  $$('[data-toast]').forEach(function (t) {
    var close = function () { t.classList.add('is-out'); setTimeout(function () { t.remove(); }, 300); };
    var btn = $('[data-toast-close]', t);
    if (btn) btn.addEventListener('click', close);
    var timer = setTimeout(close, t.classList.contains('toast-error') ? 12000 : 5000);
    t.addEventListener('mouseenter', function () { clearTimeout(timer); });
  });

  /* ---------- confirm before deleting ---------- */
  document.addEventListener('submit', function (e) {
    var msg = (e.submitter && e.submitter.getAttribute('data-confirm')) || e.target.getAttribute('data-confirm');
    if (msg && !window.confirm(msg)) e.preventDefault();
  });

  /* ---------- row menus: one open at a time ---------- */
  document.addEventListener('click', function (e) {
    $$('details.menu[open]').forEach(function (d) { if (!d.contains(e.target)) d.open = false; });
  });

  /* ---------- filters apply on change ---------- */
  $$('form[data-autosubmit] select').forEach(function (s) {
    s.addEventListener('change', function () { s.form.submit(); });
  });

  /* ---------- cards / list layout ---------- */
  $$('[data-view]').forEach(function (b) {
    b.addEventListener('click', function () {
      var v = b.getAttribute('data-view');
      document.cookie = 'admin_view=' + v + ';path=/admin;max-age=31536000;samesite=strict';
      var list = $('[data-plist]');
      if (list) { list.classList.remove('plist-grid', 'plist-list'); list.classList.add('plist-' + v); }
      $$('[data-view]').forEach(function (x) { var on = x === b; x.classList.toggle('is-on', on); x.setAttribute('aria-pressed', on ? 'true' : 'false'); });
    });
  });

  /* ---------- copy a link ---------- */
  $$('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var text = b.getAttribute('data-copy');
      var done = function () { var old = b.innerHTML; b.textContent = 'Copied. It works until the home is published.'; setTimeout(function () { b.innerHTML = old; }, 2600); };
      if (navigator.clipboard) navigator.clipboard.writeText(text).then(done, function () { window.prompt('Copy this link:', text); });
      else window.prompt('Copy this link:', text);
    });
  });

  /* ======================================================================
     Property editor
     ====================================================================== */
  var form = $('[data-editor]');
  if (!form) return;
  var cfgEl = $('#admin-cfg');
  var cfg = cfgEl ? JSON.parse(cfgEl.textContent) : {};

  /* ----- unsaved changes ----- */
  var dirty = false;
  var savebar = $('[data-savebar]');
  function markDirty() {
    if (dirty) return;
    dirty = true;
    if (savebar) savebar.classList.add('is-on');
  }
  form.addEventListener('input', markDirty);
  form.addEventListener('change', markDirty);
  var uploading = 0;
  form.addEventListener('submit', function (e) {
    if (uploading > 0) {
      e.preventDefault();
      window.alert('Photos are still uploading. Please wait a moment, then save.');
      return;
    }
    dirty = false;
    $$('button[type="submit"]', form).concat($$('button[form="listing-form"]')).forEach(function (b) { b.disabled = true; });
  });
  window.addEventListener('beforeunload', function (e) {
    if (dirty || uploading) { e.preventDefault(); e.returnValue = ''; }
  });

  /* ----- sale / rent fields ----- */
  function purpose() { var r = $('input[name="purpose"]:checked', form); return r ? r.value : 'sale'; }
  function syncPurpose() {
    var rent = purpose() === 'rent';
    $$('[data-rent-only]', form).forEach(function (el) { el.hidden = !rent; });
    $$('[data-sale-only]', form).forEach(function (el) { el.hidden = rent; });
    numbers();
  }
  $$('input[name="purpose"]', form).forEach(function (r) { r.addEventListener('change', syncPurpose); });

  /* ----- price with thousands separators, size in m², price per sq ft ----- */
  var price = $('[data-money]', form);
  var size = $('[data-size]', form);
  var sqm = $('[data-sqm]', form);
  var ppsf = $('[data-ppsf]', form);
  var digits = function (s) { return (s || '').replace(/\D/g, ''); };
  var fmt = function (n) { return n.replace(/\B(?=(\d{3})+(?!\d))/g, ','); };
  if (price) {
    price.addEventListener('input', function () {
      var pos = price.selectionStart || 0;
      var before = digits(price.value.slice(0, pos)).length;
      var d = digits(price.value).replace(/^0+(?=\d)/, '').slice(0, 13);
      price.value = d ? fmt(d) : '';
      var i = 0, seen = 0;
      while (i < price.value.length && seen < before) { if (/\d/.test(price.value[i])) seen++; i++; }
      try { price.setSelectionRange(i, i); } catch (err) { /* not focused */ }
      numbers();
    });
  }
  if (size) size.addEventListener('input', function () { size.value = digits(size.value).slice(0, 8); numbers(); });
  function numbers() {
    var s = parseInt(digits(size && size.value), 10) || 0;
    var p = parseInt(digits(price && price.value), 10) || 0;
    if (sqm) sqm.textContent = s ? '≈ ' + fmt(String(Math.round(s / 10.7639))) + ' m²' : '';
    if (ppsf) {
      if (s && p && purpose() === 'sale') ppsf.textContent = 'AED ' + fmt(String(Math.round(p / s))) + ' per sq ft';
      else if (s && p) {
        var per = ($('select[name="rent_period"]', form) || {}).value || 'yearly';
        var year = p * ({ monthly: 12, weekly: 52, daily: 365 }[per] || 1);
        ppsf.textContent = 'AED ' + fmt(String(Math.round(year / s))) + ' per sq ft a year';
      } else ppsf.textContent = '';
    }
  }
  var rp = $('select[name="rent_period"]', form);
  if (rp) rp.addEventListener('change', numbers);

  /* ----- status dot ----- */
  var status = $('[data-status]', form);
  var dot = $('[data-status-dot]', form);
  if (status && dot) status.addEventListener('change', function () { dot.className = 'dot dot-' + status.value; });

  /* ----- checklist ----- */
  var list = $('[data-photo-list]', form);
  function checks() {
    var val = function (k) { var el = $('[data-check-src="' + k + '"]', form); return el ? el.value.trim() : ''; };
    var state = {
      photos: list ? $$('.photo:not(.is-uploading)', list).length > 0 : false,
      title: val('title') !== '',
      price: parseInt(digits(val('price')), 10) > 0,
      community: val('community') !== '',
      description: val('description').length >= 40,
      permit: val('permit') !== '',
      arabic: val('arabic') !== ''
    };
    $$('[data-check]', form).forEach(function (li) { li.classList.toggle('is-done', !!state[li.getAttribute('data-check')]); });
    var count = $('[data-photo-count]', form);
    if (count && list) count.textContent = $$('.photo', list).length + ' / ' + (form.querySelector('[data-uploader]').getAttribute('data-max') || 40);
  }
  form.addEventListener('input', checks);

  /* ----- photos ----- */
  var uploader = $('[data-uploader]', form);
  var fileInput = $('[data-file]', form);
  var drop = $('[data-drop]', form);
  var errors = $('[data-upload-errors]', form);
  var max = uploader ? parseInt(uploader.getAttribute('data-max'), 10) || 40 : 40;
  var tools = list && list.querySelector('.photo-tools') ? list.querySelector('.photo-tools').outerHTML : null;
  // Tool buttons for photos added on this page (same markup as the server's)
  var toolsHtml = tools || '<span class="photo-tools">' +
    '<button type="button" class="icon-btn" data-move="-1" title="Move earlier"><svg class="ai" aria-hidden="true" viewBox="0 0 24 24"><path d="m15 18-6-6 6-6"/></svg><span class="sr-only">Move earlier</span></button>' +
    '<button type="button" class="icon-btn" data-move="1" title="Move later"><svg class="ai" aria-hidden="true" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg><span class="sr-only">Move later</span></button>' +
    '<button type="button" class="icon-btn" data-remove title="Remove"><svg class="ai" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg><span class="sr-only">Remove photo</span></button></span>';

  function showError(name, msg) {
    if (!errors) return;
    var li = document.createElement('li');
    li.textContent = (name ? name + ': ' : '') + msg;
    errors.appendChild(li);
    setTimeout(function () { li.remove(); }, 15000);
  }

  var queue = [];
  var active = 0;
  function addFiles(files) {
    files = Array.prototype.slice.call(files || []);
    var room = max - $$('.photo', list).length;
    if (files.length > room) {
      showError('', 'Only ' + max + ' photos per property. ' + (files.length - Math.max(room, 0)) + ' were left out.');
      files = files.slice(0, Math.max(room, 0));
    }
    files.forEach(function (file) {
      if (!/^image\//.test(file.type) && !/\.(jpe?g|png|webp|avif|heic|heif)$/i.test(file.name)) { showError(file.name, 'This is not a photo.'); return; }
      if (file.size > 60 * 1024 * 1024) { showError(file.name, 'This file is too large to be a photo (over 60 MB).'); return; }
      var li = document.createElement('li');
      li.className = 'photo is-uploading';
      var img = document.createElement('img');
      img.alt = '';
      try { img.src = URL.createObjectURL(file); } catch (err) { /* preview not possible */ }
      li.appendChild(img);
      li.insertAdjacentHTML('beforeend', '<span class="photo-cover">Cover</span><span class="photo-progress"><i></i></span>');
      list.appendChild(li);
      queue.push({ file: file, li: li });
      uploading++;
    });
    checks();
    pump();
  }
  function pump() {
    while (active < 2 && queue.length) send(queue.shift());
  }
  /* Big photos are resized in the browser first (longest side 2400 px, high-quality JPEG):
     uploads are faster, stay under the server's size limit, and Safari can turn iPhone
     HEIC photos into JPEG this way. Small JPEG/PNG/WebP files are sent as they are. */
  var MAX_SIDE = 2400;
  function shrink(file) {
    var small = file.size <= 1.5 * 1024 * 1024 && /^image\/(jpeg|png|webp)$/.test(file.type);
    if (small || !window.createImageBitmap) return Promise.resolve({ blob: file, name: file.name });
    return createImageBitmap(file, { imageOrientation: 'from-image' }).then(function (bmp) {
      var scale = Math.min(1, MAX_SIDE / Math.max(bmp.width, bmp.height));
      var w = Math.round(bmp.width * scale), h = Math.round(bmp.height * scale);
      var canvas = document.createElement('canvas');
      canvas.width = w; canvas.height = h;
      var ctx = canvas.getContext('2d');
      ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, w, h);
      ctx.imageSmoothingQuality = 'high';
      ctx.drawImage(bmp, 0, 0, w, h);
      if (bmp.close) bmp.close();
      return new Promise(function (resolve) {
        canvas.toBlob(function (blob) {
          resolve(blob ? { blob: blob, name: file.name.replace(/\.[a-z0-9]+$/i, '') + '.jpg' } : { blob: file, name: file.name });
        }, 'image/jpeg', 0.9);
      });
    }).catch(function () { return { blob: file, name: file.name }; });
  }
  function send(job) {
    active++;
    shrink(job.file).then(function (out) { upload(job, out); });
  }
  function upload(job, out) {
    var fd = new FormData();
    fd.append('do', 'upload');
    fd.append('csrf', cfg.csrf || '');
    fd.append('photo', out.blob, out.name);
    var xhr = new XMLHttpRequest();
    xhr.open('POST', cfg.endpoint || location.pathname);
    xhr.setRequestHeader('X-Requested-With', 'fetch');
    xhr.upload.onprogress = function (e) {
      if (e.lengthComputable) { var bar = job.li.querySelector('.photo-progress i'); if (bar) bar.style.width = Math.round(e.loaded / e.total * 95) + '%'; }
    };
    var finish = function (res) {
      active--; uploading--;
      var img = job.li.querySelector('img');
      if (res && res.ok) {
        job.li.classList.remove('is-uploading');
        job.li.setAttribute('draggable', 'true');
        job.li.setAttribute('data-path', res.path);
        var pr = job.li.querySelector('.photo-progress'); if (pr) pr.remove();
        var old = img.src;
        img.src = res.thumb;
        img.onload = function () { if (old.indexOf('blob:') === 0) URL.revokeObjectURL(old); };
        var input = document.createElement('input');
        input.type = 'hidden'; input.name = 'photos[]'; input.value = res.path;
        job.li.appendChild(input);
        job.li.insertAdjacentHTML('beforeend', toolsHtml);
        markDirty();
      } else {
        showError(job.file.name, (res && res.error) || 'The upload failed. Please try again.');
        if (img && img.src.indexOf('blob:') === 0) URL.revokeObjectURL(img.src);
        job.li.remove();
      }
      checks();
      pump();
    };
    xhr.onload = function () { var r = null; try { r = JSON.parse(xhr.responseText); } catch (err) { /* not JSON */ } finish(r); };
    xhr.onerror = function () { finish({ ok: false, error: 'No connection. Check the internet and try again.' }); };
    xhr.send(fd);
  }

  if (fileInput) {
    fileInput.addEventListener('change', function () {
      addFiles(fileInput.files);
      fileInput.value = ''; // uploaded already; don't send them again with the form
    });
  }
  if (drop) {
    ['dragenter', 'dragover'].forEach(function (t) {
      drop.addEventListener(t, function (e) { if (e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') > -1) { e.preventDefault(); drop.classList.add('is-over'); } });
    });
    ['dragleave', 'drop'].forEach(function (t) { drop.addEventListener(t, function () { drop.classList.remove('is-over'); }); });
    drop.addEventListener('drop', function (e) { e.preventDefault(); addFiles(e.dataTransfer.files); });
  }
  // Files dropped anywhere on the photos card
  if (uploader) {
    uploader.addEventListener('dragover', function (e) { if (e.dataTransfer && Array.prototype.indexOf.call(e.dataTransfer.types, 'Files') > -1) e.preventDefault(); });
    uploader.addEventListener('drop', function (e) { if (e.dataTransfer && e.dataTransfer.files.length && !drop.contains(e.target)) { e.preventDefault(); addFiles(e.dataTransfer.files); } });
  }

  // Move and remove buttons
  if (list) {
    list.addEventListener('click', function (e) {
      var btn = e.target.closest('button');
      if (!btn) return;
      var li = btn.closest('.photo');
      if (btn.hasAttribute('data-remove')) {
        var next = li.nextElementSibling || li.previousElementSibling;
        li.remove();
        markDirty(); checks();
        if (next) { var b = next.querySelector('[data-remove]'); if (b) b.focus(); }
        return;
      }
      var dir = parseInt(btn.getAttribute('data-move'), 10);
      if (dir < 0 && li.previousElementSibling) list.insertBefore(li, li.previousElementSibling);
      if (dir > 0 && li.nextElementSibling) list.insertBefore(li.nextElementSibling, li);
      btn.focus();
      markDirty();
    });

    // Drag to reorder (mouse)
    var dragging = null;
    list.addEventListener('dragstart', function (e) {
      var li = e.target.closest('.photo');
      if (!li || li.classList.contains('is-uploading')) return;
      dragging = li;
      li.classList.add('is-dragging');
      e.dataTransfer.effectAllowed = 'move';
      try { e.dataTransfer.setData('text/plain', li.getAttribute('data-path')); } catch (err) { /* old browsers */ }
    });
    list.addEventListener('dragover', function (e) {
      if (!dragging) return;
      e.preventDefault();
      var over = e.target.closest('.photo');
      $$('.photo.is-target', list).forEach(function (x) { if (x !== over) x.classList.remove('is-target'); });
      if (!over || over === dragging) return;
      over.classList.add('is-target');
      var r = over.getBoundingClientRect();
      var after = (e.clientX - r.left) > r.width / 2;
      list.insertBefore(dragging, after ? over.nextSibling : over);
    });
    list.addEventListener('dragend', function () {
      if (!dragging) return;
      dragging.classList.remove('is-dragging');
      $$('.photo.is-target', list).forEach(function (x) { x.classList.remove('is-target'); });
      dragging = null;
      markDirty();
    });
  }

  /* ----- map pin (OpenStreetMap via Leaflet) ----- */
  var mapEl = $('[data-map]', form);
  var latIn = $('input[data-lat]', form);
  var lngIn = $('input[data-lng]', form);
  if (mapEl && window.L) {
    var L = window.L;
    var lat = parseFloat(mapEl.getAttribute('data-lat'));
    var lng = parseFloat(mapEl.getAttribute('data-lng'));
    var has = !isNaN(lat) && !isNaN(lng);
    var map = L.map(mapEl, { scrollWheelZoom: false, attributionControl: true }).setView(has ? [lat, lng] : [25.13, 55.2], has ? 15 : 11);
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors' }).addTo(map);
    mapEl.classList.add('is-ready');
    var icon = L.divIcon({ className: 'map-pin', html: '<span></span>', iconSize: [28, 36], iconAnchor: [14, 34] });
    var marker = null;
    var place = function (ll, pan) {
      if (!marker) {
        marker = L.marker(ll, { draggable: true, icon: icon, keyboard: true, title: 'Property location' }).addTo(map);
        marker.on('dragend', function () { write(marker.getLatLng()); });
      } else marker.setLatLng(ll);
      if (pan) map.setView(ll, Math.max(map.getZoom(), 15));
    };
    var write = function (ll) {
      latIn.value = ll.lat.toFixed(6);
      lngIn.value = ll.lng.toFixed(6);
      markDirty();
    };
    if (has) place([lat, lng], false);
    map.on('click', function (e) { place(e.latlng, false); write(e.latlng); });
    map.on('focus', function () { map.scrollWheelZoom.enable(); });
    map.on('blur', function () { map.scrollWheelZoom.disable(); });
    var typed = function () {
      var a = parseFloat(latIn.value), b = parseFloat(lngIn.value);
      if (!isNaN(a) && !isNaN(b) && Math.abs(a) <= 90 && Math.abs(b) <= 180) place([a, b], true);
    };
    latIn.addEventListener('change', typed);
    lngIn.addEventListener('change', typed);
    var clear = $('[data-map-clear]', form);
    if (clear) clear.addEventListener('click', function () {
      if (marker) { map.removeLayer(marker); marker = null; }
      latIn.value = ''; lngIn.value = '';
      markDirty();
    });
    var find = $('[data-map-find]', form);
    if (find) find.addEventListener('click', function () {
      var c = ($('[name="community"]', form) || {}).value || '';
      var b = ($('[name="location"]', form) || {}).value || '';
      var q = [b, c, 'Dubai'].filter(Boolean).join(', ');
      if (!c && !b) { window.alert('Type the community first.'); return; }
      find.disabled = true;
      fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&countrycodes=ae&q=' + encodeURIComponent(q), { headers: { 'Accept-Language': 'en' } })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (res && res[0]) {
            var ll = L.latLng(parseFloat(res[0].lat), parseFloat(res[0].lon));
            map.setView(ll, 15);
            if (!marker) { place(ll, false); write(ll); }
          } else window.alert('That place was not found on the map. Click the map to place the pin yourself.');
        })
        .catch(function () { window.alert('The map search is not available right now. Click the map to place the pin.'); })
        .then(function () { find.disabled = false; });
    });
    // The map needs a size check when its card becomes visible
    setTimeout(function () { map.invalidateSize(); }, 300);
  }

  syncPurpose();
  checks();
  numbers();
})();
