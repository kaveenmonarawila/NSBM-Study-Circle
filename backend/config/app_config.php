<?php
// Application Configuration Constants
define('SITE_NAME', 'NSBM Student Circle');

// Base URL detected from the current request (works on XAMPP sub-folders and on InfinityFree)
$siteScheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https') ? 'https' : 'http';
$siteRoot = str_replace('\\', '/', realpath(__DIR__ . '/../..'));
$docRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$sitePath = ($docRoot !== '' && strpos($siteRoot, $docRoot) === 0) ? substr($siteRoot, strlen($docRoot)) : '';
define('SITE_URL', $siteScheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . rtrim($sitePath, '/'));

define('UPLOAD_PATH', __DIR__ . '/../uploads/');
?>
