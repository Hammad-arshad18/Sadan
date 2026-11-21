<?php
/**
 * The enquiry form, posted to send.php. Pre-selects from $form or the address bar
 * (?interest=Buying&property_type=a+villa&area=Dubai+Marina), so links and the finder work without JavaScript.
 */
$form = ($form ?? []) + ['interest' => '', 'property_type' => '', 'area' => '', 'id' => 'contact-form'];
$interests = ['Buying' => 'opt.buy', 'Selling' => 'opt.sell', 'Renting' => 'opt.rent', 'Property management' => 'opt.manageLong'];
$types = ['a property' => 'opt.typeAny', 'an apartment' => 'opt.typeApt', 'a villa' => 'opt.typeVilla', 'a townhouse' => 'opt.typeTown', 'a penthouse' => 'opt.typePent', 'an office or shop' => 'opt.typeOffice'];
$areaOpts = ['anywhere in Dubai' => 'opt.areaAny'];
foreach (['downtown-dubai', 'dubai-marina', 'palm-jumeirah', 'business-bay', 'dubai-creek-harbour', 'jumeirah', 'jumeirah-village-circle', 'dubai-hills-estate'] as $eSlug) {
    $areaOpts[areas()[$eSlug]['value']] = 'area.' . areas()[$eSlug]['key'];
}
$areaOpts['another area'] = 'opt.areaOther';

$pick = function (string $name, array $allowed) use ($form): string {
    $v = isset($_GET[$name]) ? (string) $_GET[$name] : $form[$name];
    return array_key_exists($v, $allowed) ? $v : '';
};
$sel = ['interest' => $pick('interest', $interests), 'property_type' => $pick('property_type', $types), 'area' => $pick('area', $areaOpts)];
$options = function (array $opts, string $selected): string {
    $html = '';
    foreach ($opts as $value => $key) {
        $html .= '<option value="' . e($value) . '"' . ($value === $selected ? ' selected' : '') . '>' . t($key) . '</option>';
    }
    return $html;
};
$sent = $_GET['sent'] ?? '';
?>
          <form class="enquiry" id="<?= e($form['id']) ?>" action="<?= SITE_BASE ?>/send.php" method="post" novalidate>
            <input type="hidden" name="lang" value="<?= LANG ?>">
            <input type="hidden" name="back" value="<?= e(url(PATH)) ?>">
            <input type="hidden" name="ts" value="<?= time() ?>">
            <div class="enquiry-grid">
              <div class="field">
                <label for="f-name"><?= t('form.name') ?></label>
                <input id="f-name" name="name" type="text" autocomplete="name" placeholder="<?= tp('form.namePh') ?>" required maxlength="100">
              </div>
              <div class="field">
                <label for="f-email"><?= t('form.email') ?></label>
                <input id="f-email" name="email" type="email" autocomplete="email" inputmode="email" placeholder="you@email.com" required maxlength="150" dir="ltr">
              </div>
              <div class="field">
                <label for="f-phone"><?= t('form.phone') ?></label>
                <input id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+971 …" maxlength="30" dir="ltr">
              </div>
              <div class="field">
                <label for="f-interest"><?= t('form.interest') ?></label>
                <select id="f-interest" name="interest" required><?= $options($interests, $sel['interest']) ?></select>
              </div>
              <div class="field">
                <label for="f-type"><?= t('form.type') ?></label>
                <select id="f-type" name="property_type"><?= $options($types, $sel['property_type']) ?></select>
              </div>
              <div class="field">
                <label for="f-area"><?= t('form.area') ?></label>
                <select id="f-area" name="area"><?= $options($areaOpts, $sel['area']) ?></select>
              </div>
              <div class="field field-full">
                <label for="f-message"><?= t('form.message') ?></label>
                <textarea id="f-message" name="message" rows="4" maxlength="3000" placeholder="<?= tp('form.messagePh') ?>"></textarea>
              </div>
            </div>

            <div class="hp" aria-hidden="true">
              <label for="f-website">Leave this empty</label>
              <input id="f-website" name="website" type="text" tabindex="-1" autocomplete="off">
            </div>

            <div class="enquiry-foot">
              <button class="btn btn-gold btn-lg magnetic" type="submit"><span><?= t('form.submit') ?></span><?= arrow() ?></button>
              <p class="form-status<?= $sent === '1' ? ' is-success' : ($sent === '0' ? ' is-error' : '') ?>" id="form-status" role="status" aria-live="polite"><?= $sent === '1' ? t('form.success') : ($sent === '0' ? t('form.error') : '') ?></p>
            </div>
            <p class="enquiry-note"><?= t('form.note') ?></p>
          </form>
