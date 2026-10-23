<?php

declare(strict_types=1);

/**
 * Router para el servidor embebido de PHP:
 *   php -S localhost:9981 -t public public/router.php
 *
 * Apache no lo usa: ahí el ruteo lo resuelve public/.htaccess.
 */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === false || $path === null || $path === '') {
    $path = '/';
}

$target = __DIR__ . $path;

// Archivos reales (assets, media, favicon): los sirve el servidor embebido.
if ($path !== '/' && is_file($target)) {
    return false;
}

// API: entry point de Slim.
if (preg_match('#^/api(?:/|$)#', $path) === 1) {
    require __DIR__ . '/index.php';

    return true;
}

// Admin: su propio index.html para cualquier ruta del cliente.
if (preg_match('#^/admin(?:/|$)#', $path) === 1) {
    $adminIndex = __DIR__ . '/admin/index.html';

    if (is_file($adminIndex)) {
        header('Content-Type: text/html; charset=utf-8');
        readfile($adminIndex);

        return true;
    }
}

// Sitio público: fallback de la SPA.
$siteIndex = __DIR__ . '/index.html';

if (is_file($siteIndex)) {
    header('Content-Type: text/html; charset=utf-8');
    readfile($siteIndex);

    return true;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo 'Not found.';

return true;
