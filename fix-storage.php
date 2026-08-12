<?php
/**
 * InfinityFree Storage Fix Script
 * This script copies face images from storage/app/public to public/uploads
 * Run this once after uploading to InfinityFree hosting
 */

// Create directories if they don't exist
if (!file_exists('public/uploads/faces')) {
    mkdir('public/uploads/faces', 0755, true);
}

if (!file_exists('public/uploads/snapshots')) {
    mkdir('public/uploads/snapshots', 0755, true);
}

$copied = 0;
$errors = 0;

// Copy face images
if (file_exists('storage/app/public/faces')) {
    $faceFiles = glob('storage/app/public/faces/*');
    foreach ($faceFiles as $file) {
        $filename = basename($file);
        $dest = 'public/uploads/faces/' . $filename;
        if (copy($file, $dest)) {
            $copied++;
            echo "Copied: faces/{$filename}\n";
        } else {
            $errors++;
            echo "Error copying: faces/{$filename}\n";
        }
    }
}

// Copy snapshots
if (file_exists('storage/app/public/snapshots')) {
    $snapshotFiles = glob('storage/app/public/snapshots/*');
    foreach ($snapshotFiles as $file) {
        $filename = basename($file);
        $dest = 'public/uploads/snapshots/' . $filename;
        if (copy($file, $dest)) {
            $copied++;
            echo "Copied: snapshots/{$filename}\n";
        } else {
            $errors++;
            echo "Error copying: snapshots/{$filename}\n";
        }
    }
}

echo "\nSummary:\n";
echo "Files copied: {$copied}\n";
echo "Errors: {$errors}\n";
echo "\nInfinityFree storage fix completed!\n";
echo "You can delete this file after running it.\n";
?>