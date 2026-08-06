<?php
/**
 * Classy PHP Directory
 * https://github.com/ma-tt/classy-php-directory
 * By ma-tt
 *
 * DESCRIPTION
 * Drop this one file into any folder and open it in a browser. No install,
 * no other files, no vendor folder. You get a clean file listing with
 * sorting, pagination, dark mode, per-file-type icons, zip downloads, and
 * checksums. The icon font and body font load from cdnjs/Google Fonts so
 * this file can stay small; everything else (HTML, CSS, JS) is inline.
 *
 * SECURITY
 * - Hiding a file from the listing (see `hidden_files` below) does not
 *   block direct access to it, and there's no .htaccess shipped alongside
 *   this file by default. If this folder has secrets in it (.env, .git,
 *   etc.), grab the optional .htaccess from the repo this came from, or
 *   lock it down yourself. Example Apache rule:
 *     <FilesMatch "^\.">
 *         Require all denied
 *     </FilesMatch>
 *   Nginx equivalent: a `location ~ /\.` deny-all block.
 * - There's no login built in. Anyone with the URL can browse and
 *   download. Put it behind HTTP auth or somewhere private if that
 *   folder isn't meant to be public.
 *
 * CONFIG
 * Everything you'd want to change lives in the $CL_CONFIG block right
 * below this comment. No other file to touch.
 */

// ============================== CONFIG ==============================
$CL_CONFIG = array(

    // Shown in the page title and header.
    'title' => 'File Browser',

    // Max entries per page before older ones move to page 2, 3, etc.
    // 0 disables pagination and lists everything on one page.
    'items_per_page' => 250,

    // Hide dotfiles (.env, .git, .htaccess, ...) from the listing.
    // NOTE: this only hides them from this page. See the security note up top.
    'hide_dot_files' => true,

    // Glob patterns (matched against filename and relative path) to hide
    // from the listing, in addition to dotfiles above.
    'hidden_files' => array(
        'robots.txt',
        'favicon.*',
        'error_log',
    ),

    // List folders before files.
    'list_folders_first' => true,

    // Allow visitors to download the current folder (or a subfolder) as a
    // single zip file.
    'zip_enabled' => true,
    'zip_compression_level' => 0, // 0 = store (fast, no compression), 1-9 = deflate level
    'zip_size_limit' => 1073741824, // 1 GB of uncompressed input, 0 = no limit

    // Allow visitors to compute MD5/SHA1 checksums of individual files.
    'hash_enabled' => true,
    'hash_size_limit' => 268435456, // 256 MB, files larger than this won't be hashed
);
// ============================ END CONFIG =============================

// Never let this file zip/list/hash itself, on top of whatever's configured above.
$CL_CONFIG['hidden_files'][] = basename(__FILE__);
if ($CL_CONFIG['hide_dot_files']) {
    $CL_CONFIG['hidden_files'][] = '.*';
    $CL_CONFIG['hidden_files'][] = '*/.*';
}

// ---- security bootstrap ----
ini_set('open_basedir', getcwd());
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-Robots-Tag: noindex, nofollow');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com https://fonts.googleapis.com; font-src 'self' https://cdnjs.cloudflare.com https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; object-src 'none'; frame-ancestors 'self'");

// Bootstrap Icons, pinned + SRI-verified. The one external request this file makes
// besides the Google Fonts stylesheet below.
const CL_ICONS_CSS_URL = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css';
const CL_ICONS_CSS_SRI = 'sha512-dPXYcDub/aeb08c63jRq/k6GaKccl256JQy/AnOq7CAnEZ9FzSL9wSbcZkMp4R26vBsMLFYH4kQ67/bbV8XaCQ==';

// Extension -> icon mapping. Covers everything the app used to ship per-file-type icons for.
$CL_ICONS = array(
    '7z' => 'bi-file-earmark-zip', 'bz' => 'bi-file-earmark-zip', 'gz' => 'bi-file-earmark-zip',
    'rar' => 'bi-file-earmark-zip', 'tar' => 'bi-file-earmark-zip', 'zip' => 'bi-file-earmark-zip',
    'aac' => 'bi-filetype-aac', 'flac' => 'bi-file-earmark-music', 'mid' => 'bi-file-earmark-music',
    'midi' => 'bi-file-earmark-music', 'mp3' => 'bi-filetype-mp3', 'ogg' => 'bi-file-earmark-music',
    'wma' => 'bi-file-earmark-music', 'wav' => 'bi-filetype-wav',
    'c' => 'bi-file-earmark-code', 'class' => 'bi-file-earmark-code', 'cpp' => 'bi-file-earmark-code',
    'css' => 'bi-filetype-css', 'erb' => 'bi-file-earmark-code', 'htm' => 'bi-filetype-html',
    'html' => 'bi-filetype-html', 'java' => 'bi-filetype-java', 'js' => 'bi-filetype-js',
    'php' => 'bi-filetype-php', 'pl' => 'bi-file-earmark-code', 'py' => 'bi-filetype-py',
    'rb' => 'bi-filetype-rb', 'xhtml' => 'bi-filetype-html', 'xml' => 'bi-filetype-xml',
    'accdb' => 'bi-database-fill', 'db' => 'bi-database-fill', 'dbf' => 'bi-database-fill',
    'mdb' => 'bi-database-fill', 'pdb' => 'bi-database-fill', 'sql' => 'bi-filetype-sql',
    'csv' => 'bi-filetype-csv', 'doc' => 'bi-filetype-doc', 'docx' => 'bi-filetype-docx',
    'odt' => 'bi-file-earmark-richtext', 'pdf' => 'bi-filetype-pdf', 'xls' => 'bi-filetype-xls',
    'xlsx' => 'bi-filetype-xlsx',
    'app' => 'bi-file-earmark-binary', 'bat' => 'bi-terminal', 'com' => 'bi-file-earmark-binary',
    'exe' => 'bi-filetype-exe', 'jar' => 'bi-file-earmark-binary', 'msi' => 'bi-file-earmark-binary',
    'vb' => 'bi-file-earmark-binary',
    'eot' => 'bi-file-earmark-font', 'otf' => 'bi-filetype-otf', 'ttf' => 'bi-filetype-ttf',
    'woff' => 'bi-filetype-woff',
    'gam' => 'bi-controller', 'nes' => 'bi-controller', 'rom' => 'bi-controller', 'sav' => 'bi-floppy',
    'bmp' => 'bi-filetype-bmp', 'gif' => 'bi-filetype-gif', 'jpg' => 'bi-filetype-jpg',
    'jpeg' => 'bi-filetype-jpg', 'png' => 'bi-filetype-png', 'psd' => 'bi-filetype-psd',
    'tga' => 'bi-file-earmark-image', 'tif' => 'bi-filetype-tiff',
    'box' => 'bi-archive', 'deb' => 'bi-archive', 'rpm' => 'bi-archive',
    'cmd' => 'bi-terminal', 'sh' => 'bi-filetype-sh',
    'cfg' => 'bi-file-earmark-text', 'ini' => 'bi-file-earmark-text', 'log' => 'bi-file-earmark-text',
    'md' => 'bi-filetype-md', 'rtf' => 'bi-file-earmark-richtext', 'txt' => 'bi-filetype-txt',
    'ai' => 'bi-filetype-ai', 'drw' => 'bi-file-earmark-image', 'eps' => 'bi-file-earmark-image',
    'ps' => 'bi-file-earmark-image', 'svg' => 'bi-filetype-svg',
    'avi' => 'bi-file-earmark-play', 'flv' => 'bi-file-earmark-play', 'mkv' => 'bi-file-earmark-play',
    'mov' => 'bi-filetype-mov', 'mp4' => 'bi-filetype-mp4', 'mpg' => 'bi-file-earmark-play',
    'ogv' => 'bi-file-earmark-play', 'webm' => 'bi-file-earmark-play', 'wmv' => 'bi-file-earmark-play',
    'swf' => 'bi-file-earmark-play',
    'bak' => 'bi-floppy', 'msg' => 'bi-envelope',
    'blank' => 'bi-file-earmark',
);

// ================================================================
// Helpers
// ================================================================

function cl_base_url() {
    $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
    return strtok($uri, '?');
}

/** True if any '/'-separated segment of $path is exactly '..' (a real "notes..bak" file is fine). */
function cl_has_dotdot_segment($path) {
    foreach (explode('/', $path) as $segment) {
        if ($segment === '..') {
            return true;
        }
    }
    return false;
}

/** Validates a directory param. Returns the cleaned path, or null if invalid/inaccessible. */
function cl_sanitize_dir($dir, $config) {
    if ($dir === null || $dir === '') {
        return '.';
    }
    if (strpos($dir, "\0") !== false) {
        return null;
    }
    while (strpos($dir, '//') !== false) {
        $dir = str_replace('//', '/', $dir);
    }
    if (substr($dir, -1) === '/') {
        $dir = substr($dir, 0, -1);
    }
    if ($dir === '' || $dir === '.') {
        return '.';
    }
    if (cl_has_dotdot_segment($dir) || strpos($dir, '<') !== false
        || strpos($dir, '>') !== false || strpos($dir, '/') === 0) {
        return null;
    }
    if (!file_exists($dir) || !is_dir($dir)) {
        return null;
    }
    if (cl_is_hidden($dir, $config)) {
        return null;
    }
    return $dir;
}

/** Validates a file param (for ?zip= and ?hash=). Same character checks, no existence check yet. */
function cl_sanitize_file_param($raw) {
    if ($raw === null || $raw === '') {
        return null;
    }
    if (strpos($raw, "\0") !== false || cl_has_dotdot_segment($raw)
        || strpos($raw, '<') !== false || strpos($raw, '>') !== false || strpos($raw, '/') === 0) {
        return null;
    }
    return $raw;
}

function cl_is_hidden($relativePath, $config) {
    $variants = array($relativePath, ltrim($relativePath, './'), basename($relativePath));
    foreach ($config['hidden_files'] as $pattern) {
        if ($pattern === '') {
            continue;
        }
        foreach ($variants as $v) {
            if ($v !== '' && fnmatch($pattern, $v)) {
                return true;
            }
        }
    }
    return false;
}

function cl_human_size($bytes) {
    if ($bytes === false || $bytes === null) {
        return '-';
    }
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    $factor = (int) floor((strlen((string) $bytes) - 1) / 3);
    $factor = max(0, min($factor, count($sizes) - 1));
    return sprintf('%.2f', $bytes / pow(1024, $factor)) . $sizes[$factor];
}

function cl_icon_for($filename, $isDir, $icons) {
    if ($isDir) {
        return 'bi-folder-fill';
    }
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return isset($icons[$ext]) ? $icons[$ext] : $icons['blank'];
}

/** Reads $dir and returns a name-keyed array of entries (including a pinned '..' if applicable). */
function cl_read_directory($dir, $config, $icons) {
    $entries = array();
    $files = scandir($dir);

    foreach ($files as $file) {
        if ($file === '.') {
            continue;
        }
        if ($file === '..' && $dir === '.') {
            continue;
        }

        $relativePath = ($dir === '.') ? $file : $dir . '/' . $file;
        $realPath = realpath($relativePath);
        if ($realPath === false) {
            continue;
        }
        $isDir = is_dir($realPath);

        if ($file === '..') {
            $parts = explode('/', $dir);
            array_pop($parts);
            $parentDir = implode('/', $parts);
            $entries['..'] = array(
                'name' => '..',
                'is_dir' => true,
                'is_parent' => true,
                'size' => -1,
                'size_display' => '-',
                'mtime' => (($mt = @filemtime($realPath)) !== false) ? $mt : 0,
                'url' => '?' . ($parentDir !== '' ? 'dir=' . rawurlencode($parentDir) : ''),
                'icon' => 'bi-arrow-90deg-up',
            );
            continue;
        }

        if (cl_is_hidden($relativePath, $config)) {
            continue;
        }

        $bytes = $isDir ? false : @filesize($realPath);
        $urlPath = implode('/', array_map('rawurlencode', explode('/', $relativePath)));

        $entries[$file] = array(
            'name' => $file,
            'is_dir' => $isDir,
            'is_parent' => false,
            'size' => $isDir ? -1 : ($bytes === false ? -1 : $bytes),
            'size_display' => $isDir ? '-' : cl_human_size($bytes),
            'mtime' => (($mt = @filemtime($realPath)) !== false) ? $mt : 0,
            'url' => $isDir ? ('?dir=' . $urlPath) : $urlPath,
            'icon' => cl_icon_for($file, $isDir, $icons),
        );
    }

    return $entries;
}

/** Sorts entries by key/order, optionally grouping folders first. Parent row (if any) ends up at index 0. */
function cl_sort_entries($entries, $sortKey, $order, $foldersFirst) {
    $parent = null;
    if (isset($entries['..'])) {
        $parent = $entries['..'];
        unset($entries['..']);
    }

    $items = array_values($entries);
    usort($items, function ($a, $b) use ($sortKey) {
        switch ($sortKey) {
            case 'size':
                return $a['size'] <=> $b['size'];
            case 'date':
                return $a['mtime'] <=> $b['mtime'];
            default:
                return strnatcasecmp($a['name'], $b['name']);
        }
    });

    if ($order === 'desc') {
        $items = array_reverse($items);
    }

    if ($foldersFirst) {
        $folders = array_values(array_filter($items, function ($e) { return $e['is_dir']; }));
        $files = array_values(array_filter($items, function ($e) { return !$e['is_dir']; }));
        $items = array_merge($folders, $files);
    }

    if ($parent !== null) {
        array_unshift($items, $parent);
    }

    return $items;
}

function cl_breadcrumbs($dir, $baseUrl) {
    $crumbs = array(array('url' => $baseUrl, 'label' => 'Home'));
    if ($dir === '.' || $dir === '') {
        return $crumbs;
    }
    $accum = '';
    foreach (explode('/', $dir) as $part) {
        $accum = ($accum === '') ? $part : $accum . '/' . $part;
        $crumbs[] = array('url' => $baseUrl . '?dir=' . rawurlencode($accum), 'label' => $part);
    }
    return $crumbs;
}

function cl_sort_link($key, $sortKey, $order, $dir) {
    $nextOrder = ($sortKey === $key && $order === 'asc') ? 'desc' : 'asc';
    $params = array('sort' => $key, 'order' => $nextOrder);
    if ($dir !== '.') {
        $params['dir'] = $dir;
    }
    return '?' . http_build_query($params);
}

function cl_page_link($page, $dir, $sortKey, $order) {
    $params = array();
    if ($dir !== '.') {
        $params['dir'] = $dir;
    }
    if ($sortKey !== 'name') {
        $params['sort'] = $sortKey;
    }
    if ($order !== 'asc') {
        $params['order'] = $order;
    }
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

function cl_zip_directory($dir, $config) {
    if (!class_exists('ZipArchive')) {
        http_response_code(500);
        die('Zip support (the PHP zip extension) is not available on this server.');
    }

    $name = ($dir === '.') ? 'Home' : basename($dir);
    $tmp = tempnam(sys_get_temp_dir(), 'classylite') . '.zip';

    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE) !== true) {
        http_response_code(500);
        die('Could not create the zip archive.');
    }

    // Resolved once so every file's real path can be checked against it below.
    // A symlink inside $dir that points outside of it must not get pulled into the zip.
    $root = realpath($dir);
    if ($root === false) {
        unlink($tmp);
        http_response_code(500);
        die('Could not resolve the target folder.');
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    $level = (int) $config['zip_compression_level'];
    $sizeLimit = (int) $config['zip_size_limit'];
    $hasFiles = false;
    $totalBytes = 0;

    foreach ($iterator as $file) {
        // Use the iterator's own sub-path rather than stripping $dir as a substring.
        // Stripping '.' from every path would eat every '.' in every filename/extension.
        $relativePath = $iterator->getSubPathname();
        $relativeForMatch = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);

        if (cl_is_hidden($relativeForMatch, $config)) {
            continue;
        }

        $realFile = realpath($file->getPathname());
        if ($realFile === false || strpos($realFile, $root . DIRECTORY_SEPARATOR) !== 0) {
            continue;
        }

        $bytes = @filesize($realFile);
        if ($bytes !== false) {
            $totalBytes += $bytes;
        }
        if ($sizeLimit > 0 && $totalBytes > $sizeLimit) {
            $zip->close();
            unlink($tmp);
            http_response_code(413);
            die('This folder is too large to zip (over the configured size limit).');
        }

        $zip->addFile($realFile, $relativePath);
        if ($level <= 0) {
            $zip->setCompressionName($relativePath, ZipArchive::CM_STORE);
        } else {
            $zip->setCompressionName($relativePath, ZipArchive::CM_DEFLATE, min($level, 9));
        }
        $hasFiles = true;
    }

    $zip->close();

    if (!$hasFiles) {
        unlink($tmp);
        http_response_code(404);
        die('Nothing to zip in this folder.');
    }

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $name . '.zip"');
    header('Content-Length: ' . filesize($tmp));
    readfile($tmp);
    unlink($tmp);
}

function cl_file_hash($relPath, $config) {
    $result = array('md5' => null, 'sha1' => null, 'size' => null);

    $real = realpath($relPath);
    if ($real === false || !is_file($real)) {
        return $result;
    }
    if (cl_is_hidden($relPath, $config)) {
        return $result;
    }
    $cwd = getcwd();
    if (strpos($real, $cwd) !== 0) {
        return $result;
    }

    $size = filesize($real);
    $result['size'] = $size === false ? null : $size;
    if ($size !== false && $size > $config['hash_size_limit']) {
        $result['md5'] = '[ File size exceeds threshold ]';
        $result['sha1'] = '[ File size exceeds threshold ]';
        return $result;
    }

    // One read pass feeding both hashers, instead of reading the file twice.
    $md5Ctx = hash_init('md5');
    $sha1Ctx = hash_init('sha1');
    $handle = fopen($real, 'rb');
    if ($handle !== false) {
        while (!feof($handle)) {
            $chunk = fread($handle, 1048576);
            hash_update($md5Ctx, $chunk);
            hash_update($sha1Ctx, $chunk);
        }
        fclose($handle);
        $result['md5'] = hash_final($md5Ctx);
        $result['sha1'] = hash_final($sha1Ctx);
    }

    return $result;
}

// ================================================================
// Request routing
// ================================================================

$CL_BASE = cl_base_url();

if (isset($_GET['hash'])) {
    header('Content-Type: application/json; charset=utf-8');
    if (!$CL_CONFIG['hash_enabled']) {
        http_response_code(403);
        echo json_encode(array('md5' => null, 'sha1' => null, 'size' => null));
        exit;
    }
    $file = cl_sanitize_file_param($_GET['hash']);
    if ($file === null) {
        http_response_code(400);
        echo json_encode(array('md5' => null, 'sha1' => null, 'size' => null));
        exit;
    }
    echo json_encode(cl_file_hash($file, $CL_CONFIG));
    exit;
}

if (isset($_GET['zip'])) {
    if (!$CL_CONFIG['zip_enabled']) {
        http_response_code(403);
        die('Zip downloads are disabled.');
    }
    $zipDir = cl_sanitize_dir($_GET['zip'], $CL_CONFIG);
    if ($zipDir === null) {
        http_response_code(400);
        die('Invalid path.');
    }
    cl_zip_directory($zipDir, $CL_CONFIG);
    exit;
}

// ---- listing mode ----
$dir = cl_sanitize_dir(isset($_GET['dir']) ? $_GET['dir'] : '.', $CL_CONFIG);
$invalidPath = ($dir === null);
if ($invalidPath) {
    $dir = '.';
}

$sortKey = isset($_GET['sort']) && in_array($_GET['sort'], array('name', 'size', 'date'), true) ? $_GET['sort'] : 'name';
$order = (isset($_GET['order']) && $_GET['order'] === 'desc') ? 'desc' : 'asc';

$entries = cl_read_directory($dir, $CL_CONFIG, $CL_ICONS);
$sorted = cl_sort_entries($entries, $sortKey, $order, $CL_CONFIG['list_folders_first']);

$parentRow = null;
if (!empty($sorted) && $sorted[0]['is_parent']) {
    $parentRow = array_shift($sorted);
}

$perPage = (int) $CL_CONFIG['items_per_page'];
$total = count($sorted);
$totalPages = $perPage > 0 ? max(1, (int) ceil($total / $perPage)) : 1;

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) {
    $page = 1;
} elseif ($page > $totalPages) {
    $page = $totalPages;
}

$pageItems = ($perPage > 0 && $total > $perPage)
    ? array_slice($sorted, ($page - 1) * $perPage, $perPage)
    : $sorted;

if ($parentRow !== null) {
    array_unshift($pageItems, $parentRow);
}

$breadcrumbs = cl_breadcrumbs($dir, $CL_BASE);
$pageTitle = $CL_CONFIG['title'] . ' — /' . ($dir === '.' ? '' : $dir);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<link rel="icon" href="data:image/svg+xml,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20viewBox%3D%220%200%2024%2024%22%3E%3Cpath%20d%3D%22M10%204H4c-1.1%200-2%20.9-2%202v12c0%201.1.9%202%202%202h16c1.1%200%202-.9%202-2V8c0-1.1-.9-2-2-2h-8l-2-2z%22%20fill%3D%22%23707070%22/%3E%3C/svg%3E">
<link rel="stylesheet" href="<?php echo CL_ICONS_CSS_URL; ?>" integrity="<?php echo CL_ICONS_CSS_SRI; ?>" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<!-- Google Fonts serves a UA-tailored stylesheet, so it can't be SRI-pinned like the icon font above. -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap">
<script>
(function () {
    var t = localStorage.getItem('cl-theme');
    if (t === 'light' || t === 'dark') {
        document.documentElement.setAttribute('data-theme', t);
    }
})();
</script>
<style>
:root {
    --bg: #f8f8f7;
    --fg: #1a1a1a;
    --muted: #6b6b6b;
    --border: #e2e2e0;
    --hover: #eeeeec;
}
@media (prefers-color-scheme: dark) {
    :root { --bg: #141414; --fg: #f0f0ee; --muted: #9a9a97; --border: #2c2c2a; --hover: #1e1e1c; }
}
:root[data-theme="light"] { --bg: #f8f8f7; --fg: #1a1a1a; --muted: #6b6b6b; --border: #e2e2e0; --hover: #eeeeec; }
:root[data-theme="dark"] { --bg: #141414; --fg: #f0f0ee; --muted: #9a9a97; --border: #2c2c2a; --hover: #1e1e1c; }

* { box-sizing: border-box; }
body {
    background: var(--bg);
    color: var(--fg);
    font-family: 'Roboto', -apple-system, "Segoe UI", Helvetica, Arial, sans-serif;
    margin: 0;
    padding: 1.5rem;
    transition: background-color 0.15s ease, color 0.15s ease;
}
a { color: var(--fg); text-decoration: none; }
a:hover { text-decoration: underline; }
.cl-wrap { max-width: 60rem; margin: 0 auto; }
.cl-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
.cl-header h1 { font-size: 1.3rem; font-weight: 500; letter-spacing: 0.01em; margin: 0; }
.cl-breadcrumbs { color: var(--muted); font-size: 0.9rem; margin-bottom: 1rem; }
.cl-breadcrumbs a { color: var(--muted); }
.cl-breadcrumbs a:hover { color: var(--fg); }
.cl-actions { display: flex; gap: 0.5rem; }
button.cl-btn {
    background: transparent;
    color: var(--fg);
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 0.45rem 0.75rem;
    font-family: inherit;
    font-size: 0.88rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.1s ease, border-color 0.1s ease;
}
button.cl-btn:hover { background: var(--hover); border-color: var(--muted); }
.cl-table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.9rem; table-layout: auto; }
th, td { text-align: left; padding: 0.5rem 0.6rem; border-bottom: 1px solid var(--border); }
th { color: var(--muted); font-weight: normal; white-space: nowrap; }
th a { color: var(--muted); }
th a:hover { color: var(--fg); }
tr:hover td { background: var(--hover); }
td.cl-size, td.cl-date, th.cl-size, th.cl-date { white-space: nowrap; color: var(--muted); }
/* Name column takes all remaining space and truncates instead of wrapping/squeezing its neighbors. */
td.cl-name-cell { width: 100%; max-width: 0; padding: 0; }
/* The whole cell is the link (not just the text), so folder rows / ".." are a much bigger, faster click target. */
.cl-name {
    display: flex; align-items: center; gap: 0.5rem; min-width: 0;
    padding: 0.5rem 0.6rem; color: inherit; text-decoration: none;
}
.cl-name i { color: var(--muted); flex: none; }
.cl-name-text { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; min-width: 0; }
th.cl-actions-col, td.cl-actions-cell { white-space: nowrap; width: 1%; }
/* min-height keeps rows with no action buttons (the ".." row) the same height as every other row. */
.cl-row-actions { display: flex; gap: 0.25rem; justify-content: flex-end; min-height: 1.9rem; }
.cl-icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 1.9rem; height: 1.9rem;
    background: none; border: 1px solid transparent; border-radius: 6px;
    color: var(--muted); cursor: pointer; text-decoration: none; flex: none;
    transition: background-color 0.1s ease, border-color 0.1s ease, color 0.1s ease;
}
.cl-icon-btn:hover { color: var(--fg); border-color: var(--border); background: var(--hover); text-decoration: none; }
.sr-only {
    position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px;
    overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; border: 0;
}
a:focus-visible, button:focus-visible {
    outline: 2px solid var(--fg); outline-offset: 2px;
}
@media (max-width: 640px) {
    .cl-date-col { display: none; }
}
@media (max-width: 480px) {
    .cl-size-col { display: none; }
}
.cl-pager { display: flex; gap: 0.4rem; justify-content: center; margin-top: 1.25rem; flex-wrap: wrap; }
.cl-pager a, .cl-pager span {
    border: 1px solid var(--border);
    border-radius: 6px;
    padding: 0.3rem 0.65rem;
    font-size: 0.85rem;
}
.cl-pager span.disabled { color: var(--muted); }
.cl-pager span.current { background: var(--fg); color: var(--bg); }
.cl-alert { border-left: 3px solid var(--fg); padding: 0.6rem 0.9rem; margin-bottom: 1rem; font-size: 0.9rem; }
.cl-footer { margin-top: 2rem; color: var(--muted); font-size: 0.8rem; text-align: center; }
.cl-footer a { color: inherit; text-decoration: underline; }
.cl-footer a:hover { color: var(--fg); }
.cl-modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    display: flex; align-items: center; justify-content: center; padding: 1rem;
}
.cl-modal-overlay[hidden] { display: none; }
.cl-modal {
    background: var(--bg); color: var(--fg); border: 1px solid var(--border);
    border-radius: 8px; padding: 1.25rem; max-width: 32rem; width: 100%;
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.25);
}
.cl-modal h2 { margin-top: 0; font-size: 1.05rem; font-weight: 500; }
.cl-modal code { word-break: break-all; }
.cl-modal .cl-btn { margin-top: 0.75rem; }
</style>
</head>
<body>
<div class="cl-wrap">
    <div class="cl-header">
        <h1><?php echo htmlspecialchars($CL_CONFIG['title']); ?></h1>
        <div class="cl-actions">
            <?php if ($CL_CONFIG['zip_enabled']): ?>
            <a href="?<?php echo http_build_query(array('zip' => $dir)); ?>">
                <button type="button" class="cl-btn" aria-label="Download this folder as a zip file">
                    <i class="bi bi-file-earmark-zip" aria-hidden="true"></i> Download zip
                </button>
            </a>
            <?php endif; ?>
            <button type="button" class="cl-btn" id="cl-theme-toggle" aria-label="Toggle dark mode">
                <i class="bi bi-circle-half" aria-hidden="true"></i> Theme
            </button>
        </div>
    </div>

    <div class="cl-breadcrumbs">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php if ($i > 0): ?> / <?php endif; ?>
            <a href="<?php echo htmlspecialchars($crumb['url']); ?>"><?php echo htmlspecialchars($crumb['label']); ?></a>
        <?php endforeach; ?>
    </div>

    <?php if ($invalidPath): ?>
    <div class="cl-alert"><strong>Notice:</strong> That path was invalid or inaccessible. Showing the home folder instead.</div>
    <?php endif; ?>

    <div class="cl-table-wrap">
    <table>
        <thead>
            <tr>
                <th><a href="<?php echo cl_sort_link('name', $sortKey, $order, $dir); ?>">Name<?php echo $sortKey === 'name' ? ($order === 'asc' ? ' ▲' : ' ▼') : ''; ?></a></th>
                <th class="cl-size cl-size-col"><a href="<?php echo cl_sort_link('size', $sortKey, $order, $dir); ?>">Size<?php echo $sortKey === 'size' ? ($order === 'asc' ? ' ▲' : ' ▼') : ''; ?></a></th>
                <th class="cl-date cl-date-col"><a href="<?php echo cl_sort_link('date', $sortKey, $order, $dir); ?>">Modified<?php echo $sortKey === 'date' ? ($order === 'asc' ? ' ▲' : ' ▼') : ''; ?></a></th>
                <th class="cl-actions-col"><span class="sr-only">Actions</span></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($pageItems)): ?>
            <tr><td colspan="4" style="color:var(--muted);">This folder is empty.</td></tr>
            <?php endif; ?>
            <?php foreach ($pageItems as $entry):
                $entryRelPath = ($dir === '.') ? $entry['name'] : $dir . '/' . $entry['name'];
            ?>
            <tr>
                <td class="cl-name-cell">
                    <a class="cl-name" href="<?php echo htmlspecialchars($entry['url']); ?>" title="<?php echo htmlspecialchars($entry['name']); ?>">
                        <i class="bi <?php echo htmlspecialchars($entry['icon']); ?>" aria-hidden="true"></i>
                        <span class="cl-name-text"><?php echo htmlspecialchars($entry['name']); ?></span>
                    </a>
                </td>
                <td class="cl-size cl-size-col"><?php echo htmlspecialchars($entry['size_display']); ?></td>
                <td class="cl-date cl-date-col"><?php echo $entry['mtime'] ? htmlspecialchars(date('Y-m-d H:i', $entry['mtime'])) : '-'; ?></td>
                <td class="cl-actions-cell">
                    <div class="cl-row-actions">
                        <?php if ($entry['is_parent']): ?>
                            <?php // no actions on the ".." row ?>
                        <?php elseif ($entry['is_dir']): ?>
                            <?php if ($CL_CONFIG['zip_enabled']): ?>
                            <a class="cl-icon-btn" href="?<?php echo http_build_query(array('zip' => $entryRelPath)); ?>" aria-label="Download <?php echo htmlspecialchars($entry['name']); ?> as a zip file">
                                <i class="bi bi-file-earmark-zip" aria-hidden="true"></i>
                            </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($CL_CONFIG['hash_enabled']): ?>
                            <button type="button" class="cl-icon-btn cl-hash-btn" data-path="<?php echo htmlspecialchars($entryRelPath); ?>" aria-label="Show checksums for <?php echo htmlspecialchars($entry['name']); ?>">
                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                            </button>
                            <?php endif; ?>
                            <a class="cl-icon-btn" href="<?php echo htmlspecialchars($entry['url']); ?>" download aria-label="Download <?php echo htmlspecialchars($entry['name']); ?>">
                                <i class="bi bi-download" aria-hidden="true"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="cl-pager" aria-label="Pagination">
        <?php if ($page > 1): ?>
            <a href="<?php echo cl_page_link($page - 1, $dir, $sortKey, $order); ?>">Prev</a>
        <?php else: ?>
            <span class="disabled">Prev</span>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p === $page): ?>
                <span class="current"><?php echo $p; ?></span>
            <?php else: ?>
                <a href="<?php echo cl_page_link($p, $dir, $sortKey, $order); ?>"><?php echo $p; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="<?php echo cl_page_link($page + 1, $dir, $sortKey, $order); ?>">Next</a>
        <?php else: ?>
            <span class="disabled">Next</span>
        <?php endif; ?>
    </nav>
    <?php endif; ?>

    <div class="cl-footer">
        <a href="https://github.com/ma-tt/classy-php-directory" target="_blank" rel="noopener noreferrer">Classy PHP Directory</a>
        — single-file directory listing
    </div>
</div>

<div class="cl-modal-overlay" id="cl-modal" hidden>
    <div class="cl-modal" role="dialog" aria-modal="true" aria-labelledby="cl-modal-title">
        <h2 id="cl-modal-title">File checksums</h2>
        <div id="cl-modal-body">Computing&hellip;</div>
        <button type="button" class="cl-btn" id="cl-modal-close">Close</button>
    </div>
</div>

<script>
document.getElementById('cl-theme-toggle').addEventListener('click', function () {
    var current = document.documentElement.getAttribute('data-theme')
        || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
    var next = current === 'dark' ? 'light' : 'dark';
    document.documentElement.setAttribute('data-theme', next);
    localStorage.setItem('cl-theme', next);
});

var modal = document.getElementById('cl-modal');
var modalBody = document.getElementById('cl-modal-body');

document.querySelectorAll('.cl-hash-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var path = btn.getAttribute('data-path');
        modalBody.textContent = 'Computing…';
        modal.hidden = false;
        fetch('?hash=' + encodeURIComponent(path))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                modalBody.innerHTML = '';
                var addRow = function (label, val) {
                    var p = document.createElement('p');
                    var strong = document.createElement('strong');
                    strong.textContent = label + ': ';
                    var code = document.createElement('code');
                    code.textContent = (val === null || val === undefined) ? 'unavailable' : val;
                    p.appendChild(strong);
                    p.appendChild(code);
                    modalBody.appendChild(p);
                };
                addRow('Size', data.size === null ? 'unavailable' : data.size + ' bytes');
                addRow('MD5', data.md5);
                addRow('SHA1', data.sha1);
            })
            .catch(function () {
                modalBody.textContent = 'Failed to compute checksums.';
            });
    });
});

document.getElementById('cl-modal-close').addEventListener('click', function () {
    modal.hidden = true;
});
modal.addEventListener('click', function (e) {
    if (e.target === modal) {
        modal.hidden = true;
    }
});
</script>
</body>
</html>
