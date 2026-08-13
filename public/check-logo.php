<?php
// Simple debug page to check logo file on the server.
header('Content-Type: text/plain; charset=utf-8');
$path = __DIR__ . '/dmcmes-logo.png';
echo "Inspecting: /dmcmes-logo.png\n\n";
if (!file_exists($path)) {
    echo "MISSING: file not found at {$path}\n";
    http_response_code(404);
    exit;
}
$size = filesize($path);
$mime = function_exists('mime_content_type') ? mime_content_type($path) : 'unknown';
echo "FOUND\nSize: {$size} bytes\nMIME: {$mime}\n\n";
// Print first bytes of file as hex for quick corruption check
$f = fopen($path, 'rb');
$hdr = bin2hex(fread($f, 16));
fclose($f);
echo "Header (first 16 bytes hex): {$hdr}\n";

// Build direct image URL (PHP concatenation)
$scheme = $_SERVER['REQUEST_SCHEME'] ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'] ?? 'HOST';
echo "\nTry direct image URL: " . $scheme . '://' . $host . "/dmcmes-logo.png\n";

// Also output response headers for convenience
echo "\nServer info:\n";
echo "SAPI: " . PHP_SAPI . "\n";
echo "PHP Version: " . PHP_VERSION . "\n";

// End
