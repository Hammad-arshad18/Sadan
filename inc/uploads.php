<?php
/**
 * Property photos uploaded in the admin panel. Every photo is opened and saved again
 * as WebP by this code, so only real images reach the uploads folder, phone photos are
 * turned the right way up, and hidden data in the file (such as GPS position) is dropped.
 * Two sizes are kept: <path>-lg.webp (gallery) and <path>-md.webp (cards).
 */
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const PHOTO_MAX_BYTES = 20 * 1024 * 1024;
const PHOTO_MAX_PIXELS = 60_000_000;
const PHOTO_SIZES = ['lg' => [2000, 82], 'md' => [900, 78]];
const PHOTO_MAX_PER_LISTING = 40;

function upload_root(): string
{
    $dir = rtrim(UPLOAD_DIR, '/');
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        throw new RuntimeException('The uploads folder could not be created. Check that PHP can write to the website folder.');
    }
    // Belt and braces: the main .htaccess also refuses scripts here
    if (!is_file($dir . '/.htaccess')) {
        @file_put_contents($dir . '/.htaccess', "Options -Indexes\n<FilesMatch \"\\.(?i:php\\d?|phtml|phar|pht|shtml|cgi|pl|py|sh)$\">\n  Require all denied\n</FilesMatch>\n<IfModule mod_headers.c>\n  Header set X-Content-Type-Options \"nosniff\"\n</IfModule>\n");
    }
    return $dir;
}

/**
 * Saves one uploaded file ($_FILES entry). Returns the photos row.
 * Throws RuntimeException with a message that can be shown to the admin.
 */
function photo_store(array $file): array
{
    $err = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($err === UPLOAD_ERR_INI_SIZE || $err === UPLOAD_ERR_FORM_SIZE) throw new RuntimeException('This photo is too large for the server. Try one under ' . ini_get('upload_max_filesize') . '.');
    if ($err !== UPLOAD_ERR_OK || !is_uploaded_file((string) ($file['tmp_name'] ?? ''))) throw new RuntimeException('The photo did not arrive. Please try again.');
    $tmp = (string) $file['tmp_name'];
    if (filesize($tmp) > PHOTO_MAX_BYTES) throw new RuntimeException('Photos can be up to 20 MB.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($tmp) ?: '';
    if (in_array($mime, ['image/heic', 'image/heif'], true)) throw new RuntimeException('iPhone HEIC photos can’t be read here. On the iPhone, set Camera → Formats → Most Compatible, or export the photo as JPEG.');
    if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/avif', 'image/gif'], true)) throw new RuntimeException('Only JPEG, PNG, WebP or AVIF photos can be used.');

    $info = @getimagesize($tmp);
    if (!$info || $info[0] < 1 || $info[1] < 1) throw new RuntimeException('This file is not a photo that can be read.');
    if ($info[0] * $info[1] > PHOTO_MAX_PIXELS) throw new RuntimeException('This photo has too many pixels. Please resize it below 60 megapixels.');
    if ($info[0] < 400 || $info[1] < 300) throw new RuntimeException('This photo is very small (' . $info[0] . '×' . $info[1] . '). Use one at least 1200 pixels wide so it looks sharp.');

    @ini_set('memory_limit', '768M');
    @set_time_limit(60);
    $img = match ($mime) {
        'image/jpeg' => @imagecreatefromjpeg($tmp),
        'image/png' => @imagecreatefrompng($tmp),
        'image/webp' => @imagecreatefromwebp($tmp),
        'image/avif' => function_exists('imagecreatefromavif') ? @imagecreatefromavif($tmp) : false,
        'image/gif' => @imagecreatefromgif($tmp),
    };
    if (!$img) throw new RuntimeException('This photo could not be opened. Try saving it again as JPEG.');

    // Phone photos are often stored sideways with a note saying which way is up
    if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
        $exif = @exif_read_data($tmp);
        $o = (int) ($exif['Orientation'] ?? 1);
        $rot = [3 => 180, 6 => -90, 8 => 90][$o] ?? 0;
        if ($rot) { $r = imagerotate($img, $rot, 0); if ($r) { imagedestroy($img); $img = $r; } }
    }

    $root = upload_root();
    $rel = 'uploads/properties/' . gmdate('Y/m') . '/' . bin2hex(random_bytes(10));
    $dir = dirname($root . '/' . substr($rel, strlen('uploads/')));
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) throw new RuntimeException('The uploads folder could not be written to.');

    $w = imagesx($img);
    $h = imagesy($img);
    $bytes = 0;
    $big = [0, 0];
    foreach (PHOTO_SIZES as $size => [$max, $quality]) {
        $scale = min(1, $max / max($w, $h));
        $nw = max(1, (int) round($w * $scale));
        $nh = max(1, (int) round($h * $scale));
        $out = imagecreatetruecolor($nw, $nh);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255)); // transparent PNGs on white
        imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        $file = $root . '/' . substr($rel, strlen('uploads/')) . '-' . $size . '.webp';
        if (!imagewebp($out, $file, $quality)) { imagedestroy($out); throw new RuntimeException('The photo could not be saved.'); }
        imagedestroy($out);
        @chmod($file, 0644);
        $bytes += (int) filesize($file);
        if ($size === 'lg') $big = [$nw, $nh];
    }
    imagedestroy($img);

    $row = ['path' => $rel, 'width' => $big[0], 'height' => $big[1], 'bytes' => $bytes, 'created_at' => time()];
    db()->prepare('INSERT INTO photos (listing_id, path, width, height, bytes, created_at) VALUES (NULL, ?, ?, ?, ?, ?)')
        ->execute(array_values($row));
    $row['id'] = (int) db()->lastInsertId();
    return $row;
}

/** Removes a photo's files and its row. */
function photo_delete(string $path): void
{
    if (!preg_match('#^uploads/properties/\d{4}/\d{2}/[a-f0-9]{20}$#', $path)) return;
    foreach (array_keys(PHOTO_SIZES) as $size) {
        @unlink(rtrim(UPLOAD_DIR, '/') . '/' . substr($path, strlen('uploads/')) . '-' . $size . '.webp');
    }
    db()->prepare('DELETE FROM photos WHERE path = ?')->execute([$path]);
}

/**
 * Attaches the chosen photos to a listing, in order, and deletes the ones that were
 * removed from it. Only photos uploaded here (and not used by another listing) are accepted.
 * Returns the ordered list of paths.
 */
function photos_attach(int $listingId, array $paths): array
{
    $db = db();
    $keep = [];
    $check = $db->prepare('SELECT listing_id FROM photos WHERE path = ?');
    foreach ($paths as $p) {
        if (!is_string($p) || in_array($p, $keep, true)) continue;
        $check->execute([$p]);
        $owner = $check->fetchColumn();
        if ($owner === false) continue;
        if ($owner !== null && (int) $owner !== $listingId) continue;
        $keep[] = $p;
        if (count($keep) >= PHOTO_MAX_PER_LISTING) break;
    }
    $st = $db->prepare('SELECT path FROM photos WHERE listing_id = ?');
    $st->execute([$listingId]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $old) {
        if (!in_array($old, $keep, true)) photo_delete($old);
    }
    if ($keep) {
        $in = implode(',', array_fill(0, count($keep), '?'));
        $db->prepare("UPDATE photos SET listing_id = ? WHERE path IN ($in)")->execute(array_merge([$listingId], $keep));
    }
    return $keep;
}

/** Photos uploaded but never saved with a property (form abandoned) are removed after a day. */
function photos_cleanup(): void
{
    $st = db()->prepare('SELECT path FROM photos WHERE listing_id IS NULL AND created_at < ? LIMIT 200');
    $st->execute([time() - 86400]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $p) photo_delete($p);
}

/** Deletes every photo of a listing (when the listing itself is deleted). */
function photos_delete_for(int $listingId): void
{
    $st = db()->prepare('SELECT path FROM photos WHERE listing_id = ?');
    $st->execute([$listingId]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $p) photo_delete($p);
}
