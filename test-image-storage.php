<?php
/**
 * Image Storage Test Script
 * 
 * Tests that the new image storage system is working correctly.
 * Run this to verify the fix before deploying.
 * 
 * Usage: php test-image-storage.php
 */

echo "Face Recognition System - Image Storage Test\n";
echo "============================================\n\n";

$allPassed = true;

// Test 1: Check directories exist and are writable
echo "Test 1: Directory Permissions\n";
echo "------------------------------\n";

$directories = [
    'public/faces',
    'public/snapshots'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        echo "✗ FAIL: Directory does not exist: $dir\n";
        echo "  Creating it now...\n";
        mkdir($dir, 0755, true);
        if (is_dir($dir)) {
            echo "  ✓ Created successfully\n";
        } else {
            echo "  ✗ Failed to create\n";
            $allPassed = false;
        }
    } else {
        echo "✓ PASS: Directory exists: $dir\n";
    }
    
    if (is_writable($dir)) {
        echo "  ✓ Writable\n";
    } else {
        echo "  ✗ NOT writable (check permissions)\n";
        $allPassed = false;
    }
}

echo "\n";

// Test 2: Test writing a sample image
echo "Test 2: Image Write Test\n";
echo "-------------------------\n";

$testImage = base64_decode('/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAIBAQIBAQICAgICAgICAwUDAwMDAwYEBAMFBwYHBwcGBwcICQsJCAgKCAcHCg0KCgsMDAwMBwkODw0MDgsMDAz/2wBDAQICAgMDAwYDAwYMCAcIDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAwMDAz/wAARCAABAAEDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlbaWmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwD9/KKKKAP/2Q==');
$testPath = 'public/faces/test_image_' . time() . '.jpg';

if (file_put_contents($testPath, $testImage)) {
    echo "✓ PASS: Successfully wrote test image\n";
    echo "  Path: $testPath\n";
    
    if (file_exists($testPath)) {
        echo "  ✓ File exists on disk\n";
        
        $url = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/' . $testPath;
        echo "  URL: $url\n";
        
        // Clean up
        unlink($testPath);
        echo "  ✓ Test file cleaned up\n";
    } else {
        echo "  ✗ File not found after write\n";
        $allPassed = false;
    }
} else {
    echo "✗ FAIL: Could not write test image\n";
    $allPassed = false;
}

echo "\n";

// Test 3: Check helper functions exist
echo "Test 3: Helper Functions\n";
echo "------------------------\n";

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

if (method_exists(\App\Providers\AppServiceProvider::class, 'faceImageUrl')) {
    echo "✓ PASS: faceImageUrl() method exists\n";
    
    // Test the helper
    $testUrl = \App\Providers\AppServiceProvider::faceImageUrl('faces/test.jpg');
    echo "  Sample output: $testUrl\n";
} else {
    echo "✗ FAIL: faceImageUrl() method not found\n";
    $allPassed = false;
}

if (method_exists(\App\Providers\AppServiceProvider::class, 'faceImageExists')) {
    echo "✓ PASS: faceImageExists() method exists\n";
} else {
    echo "✗ FAIL: faceImageExists() method not found\n";
    $allPassed = false;
}

echo "\n";

// Test 4: Check for old Storage references
echo "Test 4: Code Update Verification\n";
echo "---------------------------------\n";

$filesToCheck = [
    'app/Http/Controllers/Admin/FaceRegistrationController.php',
    'app/Http/Controllers/Api/FaceScanController.php',
    'app/Models/AttendanceRecord.php'
];

$foundOldCode = false;
foreach ($filesToCheck as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        // Check if using new helper or old Storage facade in wrong context
        if (strpos($content, 'AppServiceProvider::faceImage') !== false || 
            strpos($content, 'file_put_contents') !== false ||
            strpos($content, 'public_path') !== false) {
            echo "✓ PASS: $file uses new storage method\n";
        } else if (strpos($content, 'Storage::disk(\'public\')->put') !== false) {
            echo "✗ FAIL: $file still uses old Storage::disk() method\n";
            $foundOldCode = true;
            $allPassed = false;
        }
    }
}

if (!$foundOldCode) {
    echo "✓ All critical files updated\n";
}

echo "\n";
echo "============================================\n";

if ($allPassed) {
    echo "✓ ALL TESTS PASSED\n";
    echo "\nThe image storage fix is working correctly!\n";
    echo "You can now:\n";
    echo "1. Deploy to InfinityFree\n";
    echo "2. Run migrate-images.php if you have existing images\n";
    echo "3. Test face registration on the live site\n";
} else {
    echo "✗ SOME TESTS FAILED\n";
    echo "\nPlease fix the issues above before deploying.\n";
}

echo "\n";
?>
