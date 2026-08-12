<?php
/**
 * Cache Clear Script (for InfinityFree without SSH)
 * Access this once: yoursite.com/clear-cache.php
 * Then DELETE this file for security!
 */

// Security: Only allow from specific IP or remove after use
// Uncomment and set your IP:
// $allowed_ip = 'YOUR.IP.ADDRESS.HERE';
// if ($_SERVER['REMOTE_ADDR'] !== $allowed_ip) die('Access denied');

echo "<h1>Laravel Cache Clear</h1>";

$base = dirname(__DIR__);

// Clear config cache
$config_cache = $base . '/bootstrap/cache/config.php';
if (file_exists($config_cache)) {
    unlink($config_cache);
    echo "<p>✓ Config cache cleared</p>";
}

// Clear route cache
$routes_cache = $base . '/bootstrap/cache/routes-v7.php';
if (file_exists($routes_cache)) {
    unlink($routes_cache);
    echo "<p>✓ Routes cache cleared</p>";
}

// Clear compiled views
$views_path = $base . '/storage/framework/views';
if (is_dir($views_path)) {
    $files = glob($views_path . '/*');
    foreach ($files as $file) {
        if (is_file($file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            unlink($file);
        }
    }
    echo "<p>✓ Views cache cleared (" . count($files) . " files)</p>";
}

// Clear application cache
$cache_path = $base . '/storage/framework/cache/data';
if (is_dir($cache_path)) {
    $files = glob($cache_path . '/*/*');
    $count = 0;
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
            $count++;
        }
    }
    echo "<p>✓ Application cache cleared ($count files)</p>";
}

echo "<hr>";
echo "<p><strong>All caches cleared!</strong></p>";
echo "<p style='color:red;'><strong>IMPORTANT: Delete this file now for security!</strong></p>";

// Show current paths for debugging
echo "<hr>";
echo "<h2>Path Information:</h2>";
echo "<p>Base path: $base</p>";
echo "<p>Faces folder: " . $base . "/public/faces</p>";
echo "<p>Faces exists: " . (is_dir($base . '/public/faces') ? 'YES' : 'NO') . "</p>";

if (is_dir($base . '/public/faces')) {
    $images = glob($base . '/public/faces/*.jpg');
    echo "<p>Images in faces: " . count($images) . "</p>";
}
?>
