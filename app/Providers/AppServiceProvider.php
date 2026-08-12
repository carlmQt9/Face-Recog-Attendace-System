<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Set timezone explicitly to ensure accurate timestamps
        date_default_timezone_set(config('app.timezone', 'Asia/Manila'));
        
        // Use Bootstrap 5 pagination — clean text arrows, no broken SVG icons
        Paginator::useBootstrapFive();

        // Register a Blade directive and global helper for face/snapshot image URLs.
        // Saves to public/faces/ so no symlink is needed — works on localhost AND InfinityFree.
        Blade::directive('faceUrl', function ($expression) {
            return "<?php echo \App\Providers\AppServiceProvider::faceImageUrl($expression); ?>";
        });
    }

    /**
     * Resolve a stored face/snapshot path to a public URL.
     *
     * All images are stored directly in public/storage/ subfolders.
     * No symlink, no Storage facade — just asset() URLs.
     *
     * Path stored in DB              → Physical location                      → URL served
     * ----------------------------     -------------------------------------     -----------------------------
     * face-photos/foo.jpg            → public/storage/face-photos/foo.jpg    → /storage/face-photos/foo.jpg
     * time-in-photos/foo.jpg         → public/storage/time-in-photos/...     → /storage/time-in-photos/...
     * time-out-photos/foo.jpg        → public/storage/time-out-photos/...    → /storage/time-out-photos/...
     *
     * Legacy paths (old public/faces, public/snapshots) still work via asset().
     */
    public static function faceImageUrl(?string $path): string
    {
        if (empty($path)) return '';

        // Already an absolute URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // New storage paths — served via asset('storage/...')
        if (
            str_starts_with($path, 'face-photos/')     ||
            str_starts_with($path, 'time-in-photos/')  ||
            str_starts_with($path, 'time-out-photos/')
        ) {
            return asset('storage/' . ltrim($path, '/'));
        }

        // Legacy: old public/faces or public/snapshots paths
        if (
            str_starts_with($path, 'faces/')     ||
            str_starts_with($path, 'snapshots/') ||
            str_starts_with($path, 'uploads/')
        ) {
            return asset(ltrim($path, '/'));
        }

        // Fallback
        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Check if the image file exists on disk.
     */
    public static function faceImageExists(?string $path): bool
    {
        if (empty($path)) return false;

        // New storage paths
        if (
            str_starts_with($path, 'face-photos/')     ||
            str_starts_with($path, 'time-in-photos/')  ||
            str_starts_with($path, 'time-out-photos/')
        ) {
            return file_exists(public_path('storage/' . $path));
        }

        // Legacy public/ paths
        if (
            str_starts_with($path, 'faces/')     ||
            str_starts_with($path, 'snapshots/') ||
            str_starts_with($path, 'uploads/')
        ) {
            return file_exists(public_path($path));
        }

        return file_exists(public_path('storage/' . $path));
    }
}
