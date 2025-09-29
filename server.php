<?php
// Router script for PHP built-in server
// Usage: php -S 127.0.0.1:8082 QuikAPI/server.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$docRoot = __DIR__;
$file = $docRoot . $uri;

// Serve existing files directly
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    return false;
}

// Fallback to index.php for all requests
require __DIR__ . '/index.php';
