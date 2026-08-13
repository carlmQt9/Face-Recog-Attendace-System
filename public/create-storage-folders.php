<?php
/**
 * Create required storage folders and write placeholder images.
 * Visit this script on your InfinityFree site once after deploying.
 * WARNING: Remove this file after use.
 */
header('Content-Type: text/plain; charset=utf-8');
echo "Creating storage folders...\n";

$folders = [
    'public/storage/face-photos',
    'public/storage/time-in-photos',
    'public/storage/time-out-photos',
    'public/faces',
    'public/snapshots',
];

foreach ($folders as $f) {
    if (!is_dir($f)) {
        if (mkdir($f, 0755, true)) {
            echo "Created: {$f}\n";
        } else {
            echo "Failed to create: {$f}\n";
        }
    } else {
        echo "Exists: {$f}\n";
    }
}

// Create a tiny placeholder JPG for testing
$placeholder = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wCEAAkGBxAQEBUQEBUPFQ8VFRUVFRYVFRUVFRUVFhUWFhUVFRUYHSggGBolGxUVITEhJSkrLi4uFx8zODMtNygtLisBCgoKDg0OGhAQGy0mICYtLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLS0tLf/AABEIAJ8BPgMBIgACEQEDEQH/xAAbAAEAAgMBAQAAAAAAAAAAAAAABQYBAwQCB//EADgQAAIBAgMFBgQFBQEAAAAAAAECAwQRAAUSIRMxQVEiYXGRBhQjM0JSobHB0fAUIzRCYnLC8P/EABgBAAMBAQAAAAAAAAAAAAAAAAABAgME/8QAHhEBAQEBAAMBAQEAAAAAAAAAAQIREiExA0FRYfD/2gAMAwEAAhEDEQA/APuIAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAB//Z');

foreach (['public/storage/face-photos/test.jpg','public/storage/time-in-photos/test.jpg','public/storage/time-out-photos/test.jpg','public/faces/test.jpg','public/snapshots/test.jpg','public/donma logo.jpg'] as $p) {
    $dir = dirname($p);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (file_put_contents($p, $placeholder) !== false) {
        echo "Wrote: {$p} (" . filesize($p) . " bytes)\n";
    } else {
        echo "Failed to write: {$p}\n";
    }
}

echo "\nDone. You may now check /image-debug.php to see files. Remove this file when finished.\n";
