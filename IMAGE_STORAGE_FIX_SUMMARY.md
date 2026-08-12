# Image Storage Fix — Complete Summary

## Problem
Images were broken on InfinityFree because:
1. InfinityFree **blocks symbolic links** (`public/storage` → `storage/app/public/`)
2. The old code used `Storage::disk('public')` which requires a working symlink
3. `APP_URL` in `.env` was set to localhost, breaking URL generation on the live site

## Solution
Implemented a **symlink-free storage pattern** that works identically on localhost and InfinityFree:

### New Storage Structure
```
public/storage/                    ← PHYSICAL folder (not a symlink)
├── face-photos/                   ← registered face images
├── time-in-photos/                ← attendance time-in snapshots
└── time-out-photos/               ← attendance time-out snapshots
```

### Files Changed

| File | What Changed |
|------|-------------|
| `config/filesystems.php` | Added `public_direct` disk with `root => public_path('storage')` |
| `FaceRegistrationController.php` | Uses `Storage::disk('public_direct')->put('face-photos/...')` |
| `FaceScanController.php` | Uses `Storage::disk('public_direct')->put('time-in-photos/...')` or `time-out-photos/` |
| `AppServiceProvider.php` | Routes new paths through `Storage::disk('public_direct')->url()` |
| `INFINITYFREE_SETUP.md` | Full deployment guide created |
| `debug-images.php` | Updated to test new disk and URL generation |

### How It Works

**Localhost:**
- Files save to: `public/storage/face-photos/student_1_primary_xxx.jpg`
- Served as: `http://localhost/.../public/storage/face-photos/...`

**InfinityFree:**
- Files save to: `public/storage/face-photos/student_1_primary_xxx.jpg` (same!)
- Served as: `https://yourdomain.com/storage/face-photos/...`

No symlink, no difference between environments — just works.

## What You Need to Do on InfinityFree

### 1. Update `.env`
```env
APP_URL=https://yourdomain.infinityfreeapp.com
APP_ENV=production
APP_DEBUG=false
```

### 2. Create Folders
In InfinityFree File Manager:
```
public/storage/face-photos/         (permission: 755)
public/storage/time-in-photos/      (permission: 755)
public/storage/time-out-photos/     (permission: 755)
```

### 3. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

Or create a temporary `clear-cache.php`:
```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->call('config:clear');
$kernel->call('cache:clear');
echo "Cache cleared!";
```

Visit `https://yourdomain.com/clear-cache.php`, then **delete it**.

### 4. Re-Capture All Faces
**Critical:** Images from localhost don't automatically transfer to InfinityFree.

After deploying:
1. Go to Admin → Face Registration
2. Click "Capture" for each student/teacher
3. Complete the liveness verification
4. Images will now save correctly on InfinityFree

## Testing

### Run the Diagnostic
Visit: `https://yourdomain.com/debug-images.php`

It will tell you:
- ✓ Whether folders exist and are writable
- ✓ What URL is being generated
- ✓ Whether `APP_URL` matches the domain
- ✓ Sample images from database with file existence check

**Delete `debug-images.php` after testing** (exposes system info).

### Expected Results
After fixing:
- Face Registration page: Shows face thumbnails (not broken image icons)
- Attendance History: Shows time-in/time-out snapshots correctly
- Live Camera Session: Snapshots save and display in the attendance table

## Troubleshooting

### Still seeing broken images?

**1. Check the image URL**
Right-click broken image → "Copy Image Address"

If URL shows `http://localhost/...`:
→ You forgot to update `APP_URL` in `.env` on InfinityFree

If URL shows `https://yourdomain.com/storage/face-photos/...` but still broken:
→ Folder doesn't exist or has wrong permissions

**2. Check folder permissions**
In InfinityFree File Manager:
- Right-click `public/storage` → Permissions → 755
- Right-click each subfolder → Permissions → 755

**3. Clear config cache again**
```bash
php artisan config:clear
```

**4. Verify folder structure**
Make sure you have:
```
public/
└── storage/          ← not a symlink, real folder
    ├── face-photos/
    ├── time-in-photos/
    └── time-out-photos/
```

## Technical Details

### Why This Works on InfinityFree

**The `public_direct` disk:**
```php
'public_direct' => [
    'driver'     => 'local',
    'root'       => public_path('storage'),  // ← writes to public/storage/
    'url'        => env('APP_URL') . '/storage',
    'visibility' => 'public',
],
```

**Storage facade call:**
```php
Storage::disk('public_direct')->put('face-photos/foo.jpg', $data);
```
Writes to: `public/storage/face-photos/foo.jpg` (physical file, no symlink)

**URL generation:**
```php
Storage::disk('public_direct')->url('face-photos/foo.jpg');
```
Returns: `https://yourdomain.com/storage/face-photos/foo.jpg`

Since `public/` IS the web root on InfinityFree, this URL is directly accessible.

### Legacy Path Support

Old records with `faces/student_1.jpg` or `snapshots/student_1.jpg` still work:
- `AppServiceProvider::faceImageUrl()` detects these prefixes
- Routes them through `asset()` instead of Storage facade
- Works for localhost development where old images may still exist

## Summary

✅ **No symlinks required** — works on InfinityFree  
✅ **Same code for localhost and production** — no environment-specific hacks  
✅ **Backward compatible** — legacy paths still resolve  
✅ **Easy deployment** — just create folders and update `.env`  

The fix is complete. Just remember to **re-capture faces on the live site** after deploying!
