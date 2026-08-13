<?php
// Simple image debug page — lists image files and sizes under public/storage and public
header('Content-Type: text/plain; charset=utf-8');
echo "Image debug report\n";
echo str_repeat('=',40) . "\n";

$folders = [
    'public/storage/face-photos',
    'public/storage/time-in-photos',
    'public/storage/time-out-photos',
    'public/faces',
    'public/snapshots',
];

foreach ($folders as $f) {
    echo "\nFolder: {$f}\n";
    if (!is_dir($f)) { echo "  MISSING\n"; continue; }
    $files = glob($f . '/*');
    if (empty($files)) { echo "  (empty)\n"; continue; }
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        $size = filesize($file);
        $url = '/' . str_replace('public/', '', $file);
        echo sprintf("  %s — %d bytes — %s\n", basename($file), $size, $url);
    }
}

// Also check root logo files
echo "\nLogo files in public/:\n";
$logos = glob('public/donma*');
foreach ($logos as $l) {
    echo sprintf("  %s — %d bytes — /%s\n", basename($l), filesize($l), basename($l));
}

echo "\nDone.\n";
