<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && file_exists(__DIR__.'/public'.$uri)) {
    return false;
}

// Pages request /public/css and /public/js. The built-in server's root is
// already the public directory, so map that prefix back onto the real file.
if (preg_match('#^/public/(.+)$#', $uri, $assetMatch)) {
    $publicRoot = realpath(__DIR__.'/public');
    $asset = realpath(__DIR__.'/public/'.$assetMatch[1]);
    $publicPrefix = $publicRoot ? strtolower(str_replace('\\', '/', $publicRoot)).'/' : '';
    $assetPath = $asset ? strtolower(str_replace('\\', '/', $asset)) : '';
    if ($publicRoot && $asset && is_file($asset) && strpos($assetPath, $publicPrefix) === 0) {
        $types = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];
        $extension = strtolower(pathinfo($asset, PATHINFO_EXTENSION));
        if (isset($types[$extension])) {
            header('Content-Type: '.$types[$extension]);
        }
        header('Content-Length: '.filesize($asset));
        readfile($asset);

        return true;
    }
}

require_once __DIR__.'/public/index.php';
