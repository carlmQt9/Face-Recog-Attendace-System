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
     * Handles both storage paths (legacy, need symlink) and the new
     * public/faces|public/snapshots paths (no symlink required).
     *
     * Path stored in DB   → URL served
     * -------------------   ------------------------------------------------
     * faces/foo.jpg        → /faces/foo.jpg   (new: file lives in public/)
     * snapshots/foo.jpg    → /snapshots/foo.jpg
     * uploads/faces/foo.jpg→ /uploads/faces/foo.jpg  (old fix-paths migration)
     *
     * Falls back to Storage::url() for any path that doesn't match the above,
     * so existing localhost records using the storage symlink still work.
     */
    public static function faceImageUrl(?string $path): string
    {
        if (empty($path)) return '';

        // Already an absolute URL
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // New-style: file lives directly in public/ — construct URL manually
        if (
            str_starts_with($path, 'faces/')      ||
            str_starts_with($path, 'snapshots/')  ||
            str_starts_with($path, 'uploads/')
        ) {
            // For InfinityFree and most hosting, we want just the domain + path
            // Get the scheme and host (http://example.com or https://example.com)
            if (app()->bound('request') && ($request = app('request'))) {
                $baseUrl = $request->getSchemeAndHttpHost();
            } else {
                // Fallback to config
                $baseUrl = rtrim(config('app.url'), '/');
            }
            
            // Simply append /faces/file.jpg to the base URL
            return $baseUrl . '/' . ltrim($path, '/');
        }

        // Legacy: file lives under storage/app/public/ — needs symlink.
        // Works on localhost; on InfinityFree the symlink may be missing.
        return \Illuminate\Support\Facades\Storage::url($path);
    }

    /**
     * Does the image file actually exist on disk?
     * Checks the new public/ location first, then falls back to storage disk.
     */
    public static function faceImageExists(?string $path): bool
    {
        if (empty($path)) return false;

        if (
            str_starts_with($path, 'faces/')     ||
            str_starts_with($path, 'snapshots/') ||
            str_starts_with($path, 'uploads/')
        ) {
            return file_exists(public_path($path));
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->exists($path);
    }
}
