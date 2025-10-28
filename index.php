<?php
/**
 * Redireccionador principal
 * Redirige a Server app/public/index.php
 */

// Obtener la URI solicitada
$request_uri = $_SERVER['REQUEST_URI'];

// Si es la raíz, redirigir al login
if ($request_uri === '/' || $request_uri === '/index.php') {
    header('Location: /Server%20app/public/index.php/login');
    exit;
}

// Para cualquier otra ruta, redirigir al index.php de Laravel
$target = '/Server%20app/public/index.php' . $request_uri;
header('Location: ' . $target);
exit;
