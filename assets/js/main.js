(function () {
  'use strict';

  var year = document.getElementById('year');
  if (year) year.textContent = new Date().getFullYear();

  /* ---- i18n: English / Arabic ---- */
  var I18N = {
    en: {
      'meta.title': 'Sadan Real Estate | Buy, sell, rent and manage property in Dubai',
      'meta.desc': 'Sadan Real Estate is a Dubai brokerage. We help you buy, sell, rent and manage property across the city, with one point of contact from first viewing to handover.',
      'skip': 'Skip to content',
      'nav.areas': 'Areas',
      'nav.steps': 'How it works',
      'nav.about': 'About',
      'nav.cta': 'Start an enquiry',
      'hero.intro': 'Sadan is a property brokerage in Dubai. <span>What brings you here?</span>',
      'hero.servicesAria': 'Our services',
      'hero.buy': 'Buy',
      'hero.sell': 'Sell',
      'hero.rent': 'Rent',
      'hero.manage': 'Manage',
      'buy.text': 'Tell us how you want to live, or what return you\'re after. We shortlist homes that fit, arrange viewings around your schedule and negotiate on your side until the transfer is done.',
      'buy.li1': 'Requirement brief',
      'buy.li2': 'Shortlist and viewings',
      'buy.li3': 'Offer and negotiation',
      'buy.li4': 'Transfer support',
      'buy.cta': 'Start a buying enquiry',
      'sell.text': 'We price on recent comparable sales, present the property properly and qualify buyers before they visit, so your time goes to serious offers.',
      'sell.li1': 'Market valuation',
      'sell.li2': 'Photography and listing',
      'sell.li3': 'Qualified viewings',
      'sell.li4': 'Negotiation and handover',
      'sell.cta': 'Ask for a valuation',
      'rent.text': 'Looking for a home, or a tenant for yours? We match the two and handle the paperwork from the tenancy contract to Ejari.',
      'rent.li1': 'Property search',
      'rent.li2': 'Tenant screening',
      'rent.li3': 'Tenancy contract and Ejari',
      'rent.li4': 'Renewals',
      'rent.cta': 'Start a rental enquiry',
      'manage.text': 'Live abroad, or short on time? We act as your local team for tenants, rent, repairs and inspections, with a clear statement for every payment.',
      'manage.li1': 'Rent collection',
      'manage.li2': 'Maintenance and repairs',
      'manage.li3': 'Move-in and move-out inspections',
      'manage.li4': 'Owner statements',
      'manage.cta': 'Talk about your property',
      'areas.title': 'The neighbourhoods we work in',
      'areas.lead': 'Every community in Dubai has its own prices, pace and rental demand. We\'ll tell you plainly which ones suit your plans.',
      'areas.aria': 'Dubai neighbourhoods',
      'areas.downtown': 'Downtown Dubai',
      'areas.downtownDesc': 'City-centre living around the Burj Khalifa',
      'areas.marina': 'Dubai Marina',
      'areas.marinaDesc': 'Waterfront apartments and a long promenade',
      'areas.palm': 'Palm Jumeirah',
      'areas.palmDesc': 'Beachfront villas and apartments on the island',
      'areas.bay': 'Business Bay',
      'areas.bayDesc': 'Canal-side towers next to Downtown',
      'areas.creek': 'Dubai Creek Harbour',
      'areas.creekDesc': 'New waterfront homes with skyline views',
      'areas.jumeirah': 'Jumeirah',
      'areas.jumeirahDesc': 'Beachside villas and low-rise streets',
      'areas.also': 'We also work in Jumeirah Village Circle, Dubai Hills Estate, Jumeirah Lake Towers, Arabian Ranches, Al Barsha and Al Furjan.',
      'steps.title': 'From your first message to the keys, in four steps',
      'steps.1.title': 'Tell us what you need',
      'steps.1.text': 'A short message about your goals, budget and timing. There\'s no obligation.',
      'steps.2.title': 'We do the groundwork',
      'steps.2.text': 'Shortlists, valuations or tenant checks, based on what the market is really doing.',
      'steps.3.title': 'Viewings and offers',
      'steps.3.text': 'We arrange viewings, advise on the numbers and negotiate in your interest.',
      'steps.4.title': 'Paperwork to keys',
      'steps.4.text': 'Contracts, registrations and handover, one step at a time, with you kept informed.',
      'about.title': 'We look after your property the way an owner would, whether it\'s a first apartment or a whole portfolio.',
      'about.1.title': 'Straight answers',
      'about.1.text': 'Honest pricing advice, even when it isn\'t what you hoped to hear. We\'d rather earn your trust than a quick deal.',
      'about.2.title': 'One point of contact',
      'about.2.text': 'You deal with the same person from start to finish, so nothing gets lost between teams.',
      'about.3.title': 'Local and responsive',
      'about.3.text': 'We know Dubai\'s communities, procedures and paperwork, and we answer when you need us.',
      'contact.eyebrow': 'Get in touch',
      'contact.title': 'Tell us what you need',
      'contact.lead': 'Share a few details and we’ll reply with next steps. No obligation, just a clear conversation about your plans in Dubai.',
      'contact.prefer': 'Prefer email?',
      'form.name': 'Full name',
      'form.namePh': 'Your name',
      'form.email': 'Email',
      'form.emailPh': 'you@email.com',
      'form.phone': 'Phone <em>Optional</em>',
      'form.phonePh': '+971 …',
      'form.interest': 'I’m looking to',
      'form.buy': 'Buy',
      'form.sell': 'Sell',
      'form.rent': 'Rent',
      'form.manage': 'Property management',
      'form.type': 'Property type',
      'form.typeAny': 'Any property',
      'form.typeApt': 'Apartment',
      'form.typeVilla': 'Villa',
      'form.typeTown': 'Townhouse',
      'form.typePent': 'Penthouse',
      'form.typeOffice': 'Office or shop',
      'form.area': 'Preferred area',
      'form.areaAny': 'Anywhere in Dubai',
      'form.areaOther': 'Another area',
      'form.message': 'Anything else we should know? <em>Optional</em>',
      'form.messagePh': 'Budget, timing, number of bedrooms…',
      'form.submit': 'Send enquiry',
      'form.note': 'Your details are only used to reply to you.',
      'form.sending': 'Sending…',
      'form.success': 'Enquiry sent. We will reply by email.',
      'form.error': 'The enquiry was not sent. Email us at info@sadanllc.com instead.',
      'form.missing': 'Add {fields} so we can reply.',
      'form.missingName': 'your name',
      'form.missingEmail': 'a valid email address',
      'footer.aria': 'Footer',
      'footer.services': 'Services',
      'footer.contact': 'Contact',
      'footer.place': 'Dubai, United Arab Emirates',
      'footer.legal': 'Sadan Real Estate L.L.C'
    },
    ar: {
      'meta.title': 'سدن العقارية | شراء وبيع وتأجير وإدارة العقارات في دبي',
      'meta.desc': 'سدن العقارية وساطة عقارية في دبي. نساعدك على شراء وبيع وتأجير وإدارة العقارات في أنحاء المدينة، بنقطة تواصل واحدة من أول معاينة حتى التسليم.',
      'skip': 'تخطَّ إلى المحتوى',
      'nav.areas': 'المناطق',
      'nav.steps': 'كيف نعمل',
      'nav.about': 'من نحن',
      'nav.cta': 'ابدأ استفسارك',
      'hero.intro': 'سدن وساطة عقارية في دبي. <span>ما الذي تبحث عنه؟</span>',
      'hero.servicesAria': 'خدماتنا',
      'hero.buy': 'شراء',
      'hero.sell': 'بيع',
      'hero.rent': 'تأجير',
      'hero.manage': 'إدارة',
      'buy.text': 'أخبرنا كيف تريد أن تعيش، أو ما العائد الذي تسعى إليه. نختار لك عقارات مناسبة، ننسّق المعاينات وفق جدولك، ونتفاوض لصالحك حتى إتمام النقل.',
      'buy.li1': 'تحديد المتطلبات',
      'buy.li2': 'قائمة مختصرة ومعاينات',
      'buy.li3': 'العرض والتفاوض',
      'buy.li4': 'دعم إجراءات النقل',
      'buy.cta': 'ابدأ استفسار الشراء',
      'sell.text': 'نسعّر وفق صفقات مقارنة حديثة، نعرض العقار بالشكل الصحيح، ونؤهّل المشترين قبل الزيارة حتى يذهب وقتك للعروض الجادة.',
      'sell.li1': 'تقييم السوق',
      'sell.li2': 'التصوير والإعلان',
      'sell.li3': 'معاينات مؤهّلة',
      'sell.li4': 'التفاوض والتسليم',
      'sell.cta': 'اطلب تقييماً',
      'rent.text': 'تبحث عن منزل، أو عن مستأجر لعقارك؟ نوفّق بينهما ونتولى الأوراق من عقد الإيجار حتى إيجاري.',
      'rent.li1': 'البحث عن عقار',
      'rent.li2': 'فرز المستأجرين',
      'rent.li3': 'عقد الإيجار وإيجاري',
      'rent.li4': 'التجديدات',
      'rent.cta': 'ابدأ استفسار التأجير',
      'manage.text': 'تعيش في الخارج أو ينقصك الوقت؟ نكون فريقك المحلي للمستأجرين والإيجار والصيانة والمعاينات، مع كشف واضح لكل دفعة.',
      'manage.li1': 'تحصيل الإيجار',
      'manage.li2': 'الصيانة والإصلاحات',
      'manage.li3': 'معاينات الدخول والخروج',
      'manage.li4': 'كشوف المالك',
      'manage.cta': 'تحدث عن عقارك',
      'areas.title': 'المناطق التي نعمل فيها',
      'areas.lead': 'لكل مجتمع في دبي أسعاره وإيقاعه وطلبه الإيجاري. نخبرك بصراحة أيها يناسب خططك.',
      'areas.aria': 'أحياء دبي',
      'areas.downtown': 'داون تاون دبي',
      'areas.downtownDesc': 'حياة وسط المدينة حول برج خليفة',
      'areas.marina': 'دبي مارينا',
      'areas.marinaDesc': 'شقق على الواجهة البحرية وممشى طويل',
      'areas.palm': 'نخلة جميرا',
      'areas.palmDesc': 'فلل وشقق على الشاطئ في الجزيرة',
      'areas.bay': 'الخليج التجاري',
      'areas.bayDesc': 'أبراج على القناة بجوار داون تاون',
      'areas.creek': 'خور دبي هاربور',
      'areas.creekDesc': 'منازل جديدة على الواجهة بإطلالات على الأفق',
      'areas.jumeirah': 'جميرا',
      'areas.jumeirahDesc': 'فلل شاطئية وشوارع منخفضة الارتفاع',
      'areas.also': 'نعمل أيضاً في جميرا فيليدج سيركل ودبي هيلز إستيت وأبراج بحيرات جميرا والعربية رانشز والبرشاء والفرجان.',
      'steps.title': 'من أول رسالة إلى تسليم المفاتيح، في أربع خطوات',
      'steps.1.title': 'أخبرنا بما تحتاجه',
      'steps.1.text': 'رسالة قصيرة عن أهدافك وميزانيتك وتوقيتك. بلا أي التزام.',
      'steps.2.title': 'نجهّز الأساس',
      'steps.2.text': 'قوائم مختصرة أو تقييمات أو فحص مستأجرين، وفق واقع السوق.',
      'steps.3.title': 'المعاينات والعروض',
      'steps.3.text': 'ننسّق المعاينات وننصحك بالأرقام ونتفاوض لمصلحتك.',
      'steps.4.title': 'الأوراق حتى المفاتيح',
      'steps.4.text': 'العقود والتسجيل والتسليم، خطوة بخطوة مع إبقائك على اطلاع.',
      'about.title': 'نعتني بعقارك كما يفعل المالك، سواء كان شقة أولى أو محفظة كاملة.',
      'about.1.title': 'إجابات مباشرة',
      'about.1.text': 'نصيحة تسعير صادقة، حتى حين لا تكون ما تمنيت سماعه. نفضّل كسب ثقتك على صفقة سريعة.',
      'about.2.title': 'نقطة تواصل واحدة',
      'about.2.text': 'تتعامل مع الشخص نفسه من البداية إلى النهاية، فلا يضيع شيء بين الفرق.',
      'about.3.title': 'محليون وسريعو الاستجابة',
      'about.3.text': 'نعرف مجتمعات دبي وإجراءاتها وأوراقها، ونرد حين تحتاجنا.',
      'contact.eyebrow': 'تواصل معنا',
      'contact.title': 'أخبرنا بما تحتاجه',
      'contact.lead': 'شاركنا بعض التفاصيل وسنرد عليك بالخطوات التالية. بلا التزام، فقط حوار واضح حول خططك في دبي.',
      'contact.prefer': 'تفضّل البريد؟',
      'form.name': 'الاسم الكامل',
      'form.namePh': 'اسمك',
      'form.email': 'البريد الإلكتروني',
      'form.emailPh': 'you@email.com',
      'form.phone': 'الهاتف <em>اختياري</em>',
      'form.phonePh': '+971 …',
      'form.interest': 'أرغب في',
      'form.buy': 'شراء',
      'form.sell': 'بيع',
      'form.rent': 'تأجير',
      'form.manage': 'إدارة عقارات',
      'form.type': 'نوع العقار',
      'form.typeAny': 'أي عقار',
      'form.typeApt': 'شقة',
      'form.typeVilla': 'فيلا',
      'form.typeTown': 'تاون هاوس',
      'form.typePent': 'بنتهاوس',
      'form.typeOffice': 'مكتب أو محل',
      'form.area': 'المنطقة المفضلة',
      'form.areaAny': 'أي منطقة في دبي',
      'form.areaOther': 'منطقة أخرى',
      'form.message': 'أي تفاصيل إضافية؟ <em>اختياري</em>',
      'form.messagePh': 'الميزانية، التوقيت، عدد الغرف…',
      'form.submit': 'إرسال الاستفسار',
      'form.note': 'نستخدم بياناتك فقط للرد عليك.',
      'form.sending': 'جارٍ الإرسال…',
      'form.success': 'تم إرسال الاستفسار. سنرد عبر البريد الإلكتروني.',
      'form.error': 'لم يُرسل الاستفسار. راسلنا على info@sadanllc.com بدلاً من ذلك.',
      'form.missing': 'أضف {fields} حتى نتمكن من الرد.',
      'form.missingName': 'اسمك',
      'form.missingEmail': 'بريداً إلكترونياً صالحاً',
      'footer.aria': 'تذييل الصفحة',
      'footer.services': 'الخدمات',
      'footer.contact': 'تواصل',
      'footer.place': 'دبي، الإمارات العربية المتحدة',
      'footer.legal': 'سدن العقارية ذ.م.م'
    }
  };

  var currentLang = document.documentElement.lang === 'ar' ? 'ar' : 'en';

  var t = function (key) {
    var pack = I18N[currentLang] || I18N.en;
    return pack[key] != null ? pack[key] : (I18N.en[key] || key);
  };

  var applyLang = function (lang) {
    currentLang = lang === 'ar' ? 'ar' : 'en';
    var root = document.documentElement;
    root.lang = currentLang;
    root.dir = currentLang === 'ar' ? 'rtl' : 'ltr';
    root.classList.toggle('lang-ar', currentLang === 'ar');
    root.classList.toggle('lang-en', currentLang === 'en');

    try { localStorage.setItem('sadan-lang', currentLang); } catch (e) {}

    document.querySelectorAll('.lang-btn').forEach(function (btn) {
      var on = btn.getAttribute('data-lang') === currentLang;
      btn.classList.toggle('is-active', on);
      btn.setAttribute('aria-pressed', String(on));
    });

    document.querySelectorAll('[data-i18n]').forEach(function (el) {
      var key = el.getAttribute('data-i18n');
      var value = t(key);
      if (el.hasAttribute('data-i18n-html')) el.innerHTML = value;
      else el.textContent = value;
    });

    document.querySelectorAll('[data-i18n-placeholder]').forEach(function (el) {
      el.setAttribute('placeholder', t(el.getAttribute('data-i18n-placeholder')));
    });

    document.querySelectorAll('[data-i18n-aria]').forEach(function (el) {
      el.setAttribute('aria-label', t(el.getAttribute('data-i18n-aria')));
    });

    var title = t('meta.title');
    var desc = t('meta.desc');
    document.title = title;
    var metaDesc = document.querySelector('meta[name="description"]');
    if (metaDesc) metaDesc.setAttribute('content', desc);
    var ogTitle = document.querySelector('meta[property="og:title"]');
    if (ogTitle) ogTitle.setAttribute('content', currentLang === 'ar' ? 'سدن العقارية، دبي' : 'Sadan Real Estate, Dubai');
    var ogDesc = document.querySelector('meta[property="og:description"]');
    if (ogDesc) ogDesc.setAttribute('content', currentLang === 'ar' ? 'شراء وبيع وتأجير وإدارة العقارات في دبي.' : 'Buy, sell, rent and manage property across Dubai.');
  };

  document.querySelectorAll('.lang-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      applyLang(btn.getAttribute('data-lang'));
    });
  });

  applyLang(currentLang);

  /* ---- Sticky header glass on scroll ---- */
  var header = document.getElementById('top');
  if (header) {
    var syncHeader = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    syncHeader();
    window.addEventListener('scroll', syncHeader, { passive: true });
  }

  /* ---- Section reveal on scroll ---- */
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var reveals = document.querySelectorAll('.reveal');
  if (reveals.length) {
    if (reduceMotion || !('IntersectionObserver' in window)) {
      reveals.forEach(function (el) { el.classList.add('is-in'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-in');
            io.unobserve(entry.target);
          }
        });
      }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
      reveals.forEach(function (el) { io.observe(el); });
    }
  }

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
      if (interest) interest.value = link.getAttribute('data-interest');
      setTimeout(function () {
        var nameField = document.getElementById('f-name');
        if (nameField) nameField.focus({ preventScroll: true });
      }, 700);
    });
  });

  /* ---- Contact form ---- */
  var form = document.getElementById('contact-form');
  var status = document.getElementById('form-status');
  if (!form) return;

  var EMAIL = 'info@sadanllc.com';
  var setStatus = function (msg, type) {
    status.textContent = msg;
    status.className = 'form-status' + (type ? ' is-' + type : '');
  };

  var params = new URLSearchParams(window.location.search);
  if (params.get('sent') === '1') setStatus(t('form.success'), 'success');
  if (params.get('sent') === '0') setStatus(t('form.error'), 'error');

  var required = [
    { field: form.elements.name, problemKey: 'form.missingName' },
    { field: form.elements.email, problemKey: 'form.missingEmail' }
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
      var join = currentLang === 'ar' ? ' و' : ' and ';
      setStatus(t('form.missing').replace('{fields}', missing.map(function (r) { return t(r.problemKey); }).join(join)), 'error');
      missing[0].field.focus();
      return;
    }

    var button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    setStatus(t('form.sending'));

    fetch(form.action, { method: 'POST', body: new FormData(form), headers: { 'Accept': 'application/json' } })
      .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
      .then(function (r) {
        if (r.ok && r.data.ok) {
          form.reset();
          setStatus(t('form.success'), 'success');
        } else {
          setStatus((r.data && r.data.error) || t('form.error'), 'error');
        }
      })
      .catch(function () {
        setStatus(t('form.error'), 'error');
      })
      .finally(function () { button.disabled = false; });
  });
})();
