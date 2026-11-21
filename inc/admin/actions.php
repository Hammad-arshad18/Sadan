<?php
/**
 * Admin panel: what each form does when it is sent (POST). Every action ends with a
 * redirect (or a JSON answer for the photo uploader).
 */
declare(strict_types=1);

function admin_action(string $do): void
{
    $db = db();
    // Menu buttons send one value: "action|id|value"
    if ($do === '' && is_string($_POST['act'] ?? null) && preg_match('/^([a-z-]+)\|(\d+)\|([a-z0-9]*)$/', $_POST['act'], $m)) {
        [$do, $_POST['id'], $_POST['status'], $_POST['value']] = [$m[1], $m[2], $m[3], $m[3]];
    }
    $id = (int) ($_POST['id'] ?? 0);
    $back = is_string($_POST['back'] ?? null) && preg_match('/^[a-z0-9=&_%\-\[\]]*$/i', $_POST['back']) ? $_POST['back'] : '';

    switch ($do) {
        case 'logout':
            $_SESSION = [];
            session_destroy();
            header('Location: ' . ADMIN);
            exit;

        /* ----- photos ----- */
        case 'upload':
            try {
                photos_cleanup();
                $row = photo_store($_FILES['photo'] ?? []);
                json_out(['ok' => true, 'path' => $row['path'], 'thumb' => photo_url($row['path'], 'md'), 'width' => $row['width'], 'height' => $row['height']]);
            } catch (RuntimeException $e) {
                json_out(['ok' => false, 'error' => $e->getMessage()], 422);
            } catch (Throwable $e) {
                error_log('Sadan upload: ' . $e->getMessage());
                json_out(['ok' => false, 'error' => 'The photo could not be saved. Please try again.'], 500);
            }

        /* ----- properties ----- */
        case 'listing-save':
            [$newId, $msg, $kind] = listing_save($id);
            go('p=property&id=' . $newId, $msg, $kind);

        case 'listing-status':
            $status = array_key_exists(post('status'), PROP_STATUSES) ? post('status') : 'draft';
            $l = prop_get($id);
            if (!$l) go('p=properties', 'That property no longer exists.', 'error');
            if ($status === 'published' && ($missing = publish_missing($l))) {
                go($back ?: 'p=property&id=' . $id, 'Not published yet. Still needed: ' . implode(', ', $missing) . '.', 'error');
            }
            $db->prepare('UPDATE listings SET status = ?, listed_at = IF(? = \'published\' AND listed_at = 0, ?, listed_at), updated_at = ? WHERE id = ?')
                ->execute([$status, $status, time(), time(), $id]);
            go($back ?: 'p=property&id=' . $id, '“' . listing_title($l) . '” is now ' . strtolower(PROP_STATUSES[$status]) . '.');

        case 'listing-feature':
            $on = post('value') === '1' ? 1 : 0;
            $db->prepare('UPDATE listings SET featured = ? WHERE id = ?')->execute([$on, $id]);
            go($back ?: 'p=properties', $on ? 'Marked as a Sadan pick: it shows first on the website.' : 'No longer a Sadan pick.');

        case 'listing-duplicate':
            $l = prop_get($id);
            if (!$l) go('p=properties', 'That property no longer exists.', 'error');
            unset($l['id']);
            $now = time();
            $l = array_merge($l, ['status' => 'draft', 'featured' => 0, 'title' => trim($l['title'] . ' (copy)'), 'cover' => '', 'photos' => '[]',
                'reference' => next_reference(), 'permit' => '', 'views' => 0, 'listed_at' => 0, 'created_at' => $now, 'updated_at' => $now]);
            $cols = array_keys($l);
            $db->prepare('INSERT INTO listings (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')')
                ->execute(array_values($l));
            go('p=property&id=' . (int) $db->lastInsertId(), 'Copied as a new draft. Add its photos and permit number, then publish.');

        case 'listing-delete':
            $l = prop_get($id);
            if ($l) {
                photos_delete_for($id);
                $db->prepare('DELETE FROM listings WHERE id = ?')->execute([$id]);
            }
            go('p=properties', $l ? '“' . listing_title($l) . '” was deleted.' : 'Deleted.');

        /* ----- requests ----- */
        case 'request-save':
            $status = array_key_exists(post('status'), STATUSES) ? post('status') : 'new';
            $db->prepare('UPDATE requests SET status = ?, note = ?, updated_at = ? WHERE id = ?')->execute([$status, post('note', 4000), time(), $id]);
            go('p=request&id=' . $id, 'Saved.');

        case 'request-status':
            $status = array_key_exists(post('status'), STATUSES) ? post('status') : 'new';
            $db->prepare('UPDATE requests SET status = ?, updated_at = ? WHERE id = ?')->execute([$status, time(), $id]);
            go($back ?: 'p=request&id=' . $id, 'Marked as “' . STATUSES[$status] . '”.');

        case 'request-delete':
            $db->prepare('DELETE FROM requests WHERE id = ?')->execute([$id]);
            go('p=requests', 'Request deleted.');

        /* ----- settings ----- */
        case 'settings-notify':
            $to = post('notify_email', 200);
            if ($to !== '' && !filter_var($to, FILTER_VALIDATE_EMAIL)) go('p=settings', 'That email address does not look right.', 'error');
            setting_set('notify_email', $to);
            setting_set('default_agent', post('default_agent', 120));
            setting_set('ref_prefix', strtoupper(preg_replace('/[^A-Za-z0-9]/', '', post('ref_prefix', 8))) ?: 'SAD');
            go('p=settings', 'Settings saved.');

        case 'settings-site':
            $on = post('props_public') === '1';
            setting_set('props_public', $on ? '1' : '0');
            go($back ?: 'p=settings',
                $on ? 'The Properties page is now shown on the website.' : 'The Properties page is now hidden from the website. Your properties are kept and you can show it again anytime.');

        case 'settings-password':
            if (!password_verify(post('current_password', 200), setting('admin_hash'))) go('p=settings#password', 'Your current password is not right.', 'error');
            if (mb_strlen(post('new_password', 200)) < 10) go('p=settings#password', 'Use at least 10 characters for the new password.', 'error');
            if (post('new_password', 200) !== post('confirm_password', 200)) go('p=settings#password', 'The two new passwords do not match.', 'error');
            setting_set('admin_hash', password_hash(post('new_password', 200), PASSWORD_DEFAULT));
            $_SESSION['admin_v'] = substr(setting('admin_hash'), -16);
            session_regenerate_id(true);
            go('p=settings', 'Password changed. Other signed-in devices are signed out.');
    }
}

/** Next suggested reference number, e.g. SAD-0042. */
function next_reference(): string
{
    $prefix = setting('ref_prefix', 'SAD') ?: 'SAD';
    $n = (int) db()->query('SELECT COALESCE(MAX(id), 0) + 1 FROM listings')->fetchColumn();
    return $prefix . '-' . str_pad((string) $n, 4, '0', STR_PAD_LEFT);
}

/** What a listing still needs before it can go on the website (empty when ready). */
function publish_missing(array $l): array
{
    $m = [];
    if (trim((string) $l['title']) === '') $m[] = 'a title';
    if ((int) $l['price'] <= 0) $m[] = 'a price';
    if (trim((string) $l['community']) === '') $m[] = 'the community';
    if (!prop_photo_paths($l)) $m[] = 'at least one photo';
    return $m;
}

/**
 * Saves the property form. Always keeps the work: if something needed for publishing
 * is missing, it is saved as a draft and the message says what to add.
 * Returns [id, message, kind].
 */
function listing_save(int $id): array
{
    $db = db();
    $old = $id ? prop_get($id) : null;
    if ($id && !$old) go('p=properties', 'That property no longer exists.', 'error');

    $purpose = post('purpose') === 'rent' ? 'rent' : 'sale';
    $period = $purpose === 'rent' ? (in_array(post('rent_period'), RENT_PERIODS, true) ? post('rent_period') : 'yearly') : '';
    $price = (int) min(999999999999, (int) preg_replace('/\D/', '', post('price', 20)));
    $num = function (string $k, int $max): ?int {
        $v = post($k, 12);
        if ($v === '') return null;
        $n = (int) preg_replace('/\D/', '', $v);
        return min($max, $n);
    };
    $coord = function (string $k, float $lim): ?float {
        $v = str_replace(',', '.', post($k, 20));
        return is_numeric($v) && abs((float) $v) <= $lim ? round((float) $v, 6) : null;
    };
    $beds = post('beds', 6) === 'studio' ? 0 : $num('beds', 20);
    $community = post('community', 120);
    $location = post('location', 200);

    $amen = array_values(array_intersect(post_list('amenities'), AMENITIES));
    foreach (preg_split('/[,\n]+/u', post('amenities_other', 1000)) as $a) {
        $a = mb_substr(trim($a), 0, 60);
        if ($a !== '' && !in_array($a, $amen, true)) $amen[] = $a;
    }

    $status = array_key_exists(post('status'), PROP_STATUSES) ? post('status') : 'draft';
    $now = time();
    $n = [
        'purpose' => $purpose,
        'type' => in_array(post('type'), PROP_TYPES, true) ? post('type') : 'apartment',
        'title' => post('title', 200),
        'title_ar' => post('title_ar', 200),
        'description' => post('description', 10000),
        'description_ar' => post('description_ar', 10000),
        'price' => $price,
        'rent_period' => $period,
        'price_year' => yearly_price($price, $purpose, $period),
        'beds' => $beds,
        'baths' => $num('baths', 20),
        'size_sqft' => $num('size_sqft', 10000000),
        'community' => $community,
        'community_ar' => post('community_ar', 120),
        'community_key' => slugify($community),
        'area_slug' => match_area_slug($community, $location),
        'location' => $location,
        'location_ar' => post('location_ar', 200),
        'lat' => $coord('lat', 90),
        'lng' => $coord('lng', 180),
        'completion' => $purpose === 'sale' && in_array(post('completion'), ['ready', 'offplan'], true) ? post('completion') : '',
        'furnishing' => in_array(post('furnishing'), ['furnished', 'unfurnished', 'partly'], true) ? post('furnishing') : '',
        'amenities' => json_encode($amen, JSON_UNESCAPED_UNICODE),
        'agent' => post('agent', 120),
        'permit' => post('permit', 60),
        'reference' => post('reference', 60),
        'featured' => empty($_POST['featured']) ? 0 : 1,
        'updated_at' => $now,
    ];
    if ($n['lat'] === null || $n['lng'] === null) { $n['lat'] = null; $n['lng'] = null; }

    $db->beginTransaction();
    try {
        if ($id) {
            $set = implode(', ', array_map(fn($f) => "$f = ?", array_keys($n)));
            $db->prepare("UPDATE listings SET $set WHERE id = ?")->execute(array_merge(array_values($n), [$id]));
        } else {
            $n += ['status' => 'draft', 'cover' => '', 'photos' => '[]', 'created_at' => $now, 'listed_at' => 0];
            if ($n['reference'] === '') $n['reference'] = next_reference();
            $cols = array_keys($n);
            $db->prepare('INSERT INTO listings (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')')
                ->execute(array_values($n));
            $id = (int) $db->lastInsertId();
        }

        // Photos: the ones already uploaded by the page's script, plus any sent with the form (no-script fallback)
        $paths = post_list('photos');
        $failed = [];
        if (!empty($_FILES['new_photos']['name']) && is_array($_FILES['new_photos']['name'])) {
            foreach (array_keys($_FILES['new_photos']['name']) as $i) {
                if (($_FILES['new_photos']['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
                $f = ['name' => $_FILES['new_photos']['name'][$i], 'tmp_name' => $_FILES['new_photos']['tmp_name'][$i], 'error' => $_FILES['new_photos']['error'][$i], 'size' => $_FILES['new_photos']['size'][$i]];
                try { $paths[] = photo_store($f)['path']; } catch (RuntimeException $e) { $failed[] = $e->getMessage(); }
            }
        }
        $paths = photos_attach($id, $paths);
        $db->prepare('UPDATE listings SET photos = ?, cover = ? WHERE id = ?')
            ->execute([json_encode($paths, JSON_UNESCAPED_SLASHES), $paths[0] ?? '', $id]);

        // Status last, so the publish check sees the saved photos
        $saved = prop_get($id);
        $missing = $status === 'published' ? publish_missing($saved) : [];
        if ($missing) $status = $old && $old['status'] !== 'published' ? $old['status'] : 'draft';
        $db->prepare('UPDATE listings SET status = ?, listed_at = IF(? = \'published\' AND listed_at = 0, ?, listed_at) WHERE id = ?')
            ->execute([$status, $status, $now, $id]);
        $db->commit();
    } catch (Throwable $e) {
        $db->rollBack();
        error_log('Sadan listing save: ' . $e->getMessage());
        return [$id, 'The property could not be saved: ' . $e->getMessage(), 'error'];
    }

    if ($missing) return [$id, 'Saved as a draft. To publish it, add ' . implode(', ', $missing) . '.', 'error'];
    if ($failed) return [$id, 'Saved, but some photos were skipped: ' . implode(' ', array_unique($failed)), 'error'];
    if ($status === 'published' && (!$old || $old['status'] !== 'published')) return [$id, 'Published. It is now on the website.', 'ok'];
    return [$id, $old ? 'Changes saved.' : 'Property created.', 'ok'];
}
