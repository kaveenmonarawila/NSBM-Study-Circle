<?php
// ============================================================================
// Application configuration constants (safe to include more than once)
// ============================================================================

// ONE place for the site name. It is used in page titles, the navbar, footers,
// login/signup headings, the admin console and generated PDFs.
// To rename the site, change only this value.
if (!defined('SITE_NAME')) define('SITE_NAME', 'Study Circle');

// Base URL detected from the current request (works on XAMPP sub-folders and on InfinityFree)
if (!defined('SITE_URL')) {
    $siteScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
    $siteRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..'));
    $docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
    $sitePath = ($docRoot !== '' && strpos($siteRoot, $docRoot) === 0) ? substr($siteRoot, strlen($docRoot)) : '';
    define('SITE_URL', $siteScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim($sitePath, '/'));
    unset($siteScheme, $siteRoot, $docRoot, $sitePath);
}

if (!defined('UPLOAD_PATH')) define('UPLOAD_PATH', __DIR__ . '/../uploads/');
