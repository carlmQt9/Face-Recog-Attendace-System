<?php
/**
 * Image Migration Script
 * 
 * Moves existing images from storage/app/public/ to public/
 * Run this ONCE after deploying the new code.
 * 
 * Usage: php migrate-images.php
 */

$migrated = 0;
$errors = 0;
$skipped = 0;

echo "Face Recognition System - Image Migration Tool\n";
echo "==============================================\n\n";

// Create directories if they don't exist
$directories = ['public/faces', 'public/snapshots'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        echo "✓ Created directory: $dir\n";
    }
}

echo "\n";

// Migrate face images
if (is_dir('storage/app/public/faces')) {
    echo "Migrating face images...\n";
    $files = glob('storage/app/public/faces/*');
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        
        $filename = basename($file);
        $dest = 'public/faces/' . $filename;
        
        if (file_exists($dest)) {
            echo "⊘ Skipped (exists): $filename\n";
            $skipped++;
            continue;
        }
        
        if (copy($file, $dest)) {
            echo "✓ Migrated: $filename\n";
            $migrated++;
        } else {
            echo "✗ Failed: $filename\n";
            $errors++;
        }
    }
}

echo "\n";

// Migrate snapshot images
if (is_dir('storage/app/public/snapshots')) {
    echo "Migrating snapshots...\n";
    $files = glob('storage/app/public/snapshots/*');
    foreach ($files as $file) {
        if (!is_file($file)) continue;
        
        $filename = basename($file);
        $dest = 'public/snapshots/' . $filename;
        
        if (file_exists($dest)) {
            echo "⊘ Skipped (exists): $filename\n";
            $skipped++;
            continue;
        }
        
        if (copy($file, $dest)) {
            echo "✓ Migrated: $filename\n";
            $migrated++;
        } else {
            echo "✗ Failed: $filename\n";
            $errors++;
        }
    }
}

echo "\n";
echo "==============================================\n";
echo "Migration Summary:\n";
echo "  Migrated: $migrated files\n";
echo "  Skipped:  $skipped files (already exist)\n";
echo "  Errors:   $errors files\n";
echo "\n";

if ($errors === 0) {
    echo "✓ Migration completed successfully!\n";
    echo "\nNext steps:\n";
    echo "1. Test face registration on your site\n";
    echo "2. Verify images display correctly\n";
    echo "3. If everything works, you can delete storage/app/public/faces/ and snapshots/\n";
} else {
    echo "✗ Migration completed with errors.\n";
    echo "Please check file permissions and try again.\n";
}

echo "\n";
?>
