<?php
// Simple test - just check current directory
echo "<h1>Directory Test</h1>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Faces dir exists: " . (is_dir(__DIR__ . '/faces') ? 'YES' : 'NO') . "</p>";

if (is_dir(__DIR__ . '/faces')) {
    $files = glob(__DIR__ . '/faces/*.jpg');
    echo "<p>Files in faces: " . count($files) . "</p>";
    if (count($files) > 0) {
        echo "<p>First file: " . basename($files[0]) . "</p>";
        echo "<img src='/faces/" . basename($files[0]) . "' style='max-width:200px'>";
    }
} else {
    // Try to create it
    if (mkdir(__DIR__ . '/faces', 0755, true)) {
        echo "<p>Created faces directory</p>";
    } else {
        echo "<p>Could not create faces directory</p>";
    }
}

// Check if we can detect the issue
echo "<hr>";
echo "<h2>URL Test</h2>";
echo "<p>Your domain: " . $_SERVER['HTTP_HOST'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Try to access an image directly
if (file_exists(__DIR__ . '/dmcmes-logo.png')) {
    echo "<p>Logo exists - trying to display:</p>";
    echo "<img src='/dmcmes-logo.png' style='max-width:100px'>";
}
?>
