<?php
/**
 * Image Storage Debug Script
 * Upload this to public/ and access via: yoursite.com/debug-images.php
 */

echo "<!DOCTYPE html><html><head><title>Image Storage Debug</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#0f0;}";
echo ".success{color:#0f0;} .error{color:#f00;} .warning{color:#ff0;}</style></head><body>";
echo "<h1>🔍 Image Storage Diagnostic</h1>";
echo "<hr>";

// Test 1: Check if faces directory exists
echo "<h2>Test 1: Directory Check</h2>";
$facesDir = __DIR__ . '/faces';
$snapshotsDir = __DIR__ . '/snapshots';

if (is_dir($facesDir)) {
    echo "<p class='success'>✓ faces/ directory exists</p>";
    echo "<p>Path: $facesDir</p>";
    
    // List files
    $files = glob($facesDir . '/*.jpg');
    echo "<p>Files found: " . count($files) . "</p>";
    if (count($files) > 0) {
        echo "<ul>";
        foreach (array_slice($files, 0, 5) as $file) {
            $filename = basename($file);
            $size = filesize($file);
            echo "<li>$filename (" . round($size/1024, 2) . " KB)</li>";
        }
        echo "</ul>";
    }
} else {
    echo "<p class='error'>✗ faces/ directory DOES NOT exist</p>";
    echo "<p>Attempting to create...</p>";
    if (mkdir($facesDir, 0755, true)) {
        echo "<p class='success'>✓ Created successfully</p>";
    } else {
        echo "<p class='error'>✗ Failed to create (check permissions)</p>";
    }
}

if (is_dir($snapshotsDir)) {
    echo "<p class='success'>✓ snapshots/ directory exists</p>";
} else {
    echo "<p class='warning'>⚠ snapshots/ directory does not exist</p>";
    mkdir($snapshotsDir, 0755, true);
}

echo "<hr>";

// Test 2: Check URL generation
echo "<h2>Test 2: URL Generation</h2>";

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'unknown';
$baseUrl = $protocol . $host;

echo "<p>Protocol: $protocol</p>";
echo "<p>Host: $host</p>";
echo "<p>Base URL: $baseUrl</p>";

if (count($files ?? []) > 0) {
    $testFile = basename($files[0]);
    $testUrl = $baseUrl . '/faces/' . $testFile;
    echo "<p>Test Image URL: <a href='$testUrl' target='_blank'>$testUrl</a></p>";
    
    // Test if accessible
    echo "<h3>Test Image Display:</h3>";
    echo "<img src='/faces/$testFile' alt='Test' style='max-width:200px;border:2px solid #0f0;' onerror=\"this.style.border='2px solid #f00';this.alt='FAILED TO LOAD'\">";
}

echo "<hr>";

// Test 3: Check Laravel paths
echo "<h2>Test 3: Laravel Detection</h2>";
$laravelPublic = dirname(__DIR__);
echo "<p>Project root: $laravelPublic</p>";

if (file_exists($laravelPublic . '/vendor/autoload.php')) {
    echo "<p class='success'>✓ Laravel installation detected</p>";
    
    require_once $laravelPublic . '/vendor/autoload.php';
    $app = require_once $laravelPublic . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "<p>APP_URL: " . config('app.url') . "</p>";
    echo "<p>APP_ENV: " . config('app.env') . "</p>";
    
    // Test helper function
    if (method_exists(\App\Providers\AppServiceProvider::class, 'faceImageUrl')) {
        $testPath = 'faces/test.jpg';
        $url = \App\Providers\AppServiceProvider::faceImageUrl($testPath);
        echo "<p class='success'>✓ Helper function exists</p>";
        echo "<p>Sample URL output: $url</p>";
    } else {
        echo "<p class='error'>✗ Helper function not found</p>";
    }
    
    // Check database
    try {
        $students = \App\Models\Student::where('face_registered', true)
            ->whereNotNull('face_encoding')
            ->limit(3)
            ->get();
        
        echo "<p class='success'>✓ Database connection OK</p>";
        echo "<p>Registered students: " . $students->count() . "</p>";
        
        if ($students->count() > 0) {
            echo "<h3>Sample Student Data:</h3>";
            foreach ($students as $student) {
                $path = $student->face_encoding;
                $url = \App\Providers\AppServiceProvider::faceImageUrl($path);
                $exists = \App\Providers\AppServiceProvider::faceImageExists($path);
                
                echo "<div style='border:1px solid #0f0;padding:10px;margin:10px 0;'>";
                echo "<p><strong>" . $student->user->name . "</strong></p>";
                echo "<p>DB Path: $path</p>";
                echo "<p>Generated URL: $url</p>";
                echo "<p>File exists: " . ($exists ? '✓ YES' : '✗ NO') . "</p>";
                if ($exists) {
                    $fullPath = public_path($path);
                    if (file_exists($fullPath)) {
                        echo "<p>File size: " . round(filesize($fullPath)/1024, 2) . " KB</p>";
                        echo "<p><img src='$url' alt='Face' style='max-width:100px;border:2px solid #0f0;' onerror=\"this.style.border='2px solid #f00'\"></p>";
                    }
                }
                echo "</div>";
            }
        }
        
    } catch (\Exception $e) {
        echo "<p class='error'>✗ Database error: " . $e->getMessage() . "</p>";
    }
    
} else {
    echo "<p class='error'>✗ Laravel not detected</p>";
}

echo "<hr>";
echo "<p><em>Generated: " . date('Y-m-d H:i:s') . "</em></p>";
echo "</body></html>";
?>
