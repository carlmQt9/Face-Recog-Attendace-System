# InfinityFree Deployment Guide — Image Storage Fix

## The Problem

InfinityFree **does not support symbolic links** (`public/storage` → `storage/app/public/`). This breaks the standard Laravel storage pattern used by most tutorials and other projects.

## The Solution

This project now uses a **symlink-free storage pattern**:
- Images are saved **directly into `public/storage/`** subfolders
- No `php artisan storage:link` command needed
- Works identically on localhost and InfinityFree

## File Structure on InfinityFree

```
htdocs/                               ← your InfinityFree web root
├── public/                           ← Laravel's public folder (entire contents go here)
│   ├── index.php                     ← Laravel entry point
│   ├── .htaccess                     ← Laravel routing rules
│   └── storage/                      ← PHYSICAL folder (not a symlink!)
│       ├── face-photos/              ← registered face images
│       ├── time-in-photos/           ← attendance time-in snapshots
│       └── time-out-photos/          ← attendance time-out snapshots
├── app/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
└── vendor/
```

## Steps to Deploy on InfinityFree

### 1. Upload All Files

Upload the entire project to your InfinityFree account using FTP (FileZilla recommended):
- Upload everything **except** `node_modules/` (too large, not needed)
- Place files in `/htdocs/yourdomain.com/` or wherever your account root is

### 2. Move `public/` Contents to Web Root

InfinityFree serves files from `htdocs/` as the web root. You need to either:

**Option A: Move public contents up** (recommended)
```
htdocs/
├── index.php         ← moved from public/index.php
├── .htaccess         ← moved from public/.htaccess
├── storage/          ← moved from public/storage/
│   ├── face-photos/
│   ├── time-in-photos/
│   └── time-out-photos/
├── css/
├── js/
└── (rest of files stay in subdirectories)
```

After moving, edit `index.php` line 16-17 to fix the paths:
```php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
```

**Option B: Point domain to public folder**
In InfinityFree control panel → Domain Settings → set document root to `/htdocs/yourdomain.com/public/`

### 3. Create the Storage Folders

In InfinityFree File Manager, create these folders if they don't exist:
```
public/storage/face-photos/         (permission: 755)
public/storage/time-in-photos/      (permission: 755)
public/storage/time-out-photos/     (permission: 755)
```

Set folder permissions to **755** (read/write for owner, read for others).

### 4. Update `.env` on InfinityFree

Edit `.env` on the server and set:

```env
APP_URL=https://yourdomain.infinityfreeapp.com
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=sqlXXX.infinityfreeapp.com
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

# ... rest of your config
```

**Critical:** `APP_URL` must be your exact InfinityFree domain — no `/public` suffix, no trailing slash.

### 5. Run Setup Commands

SSH into your InfinityFree account (if available) or use the online file manager terminal:

```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
```

If you don't have SSH access, create a temporary `setup.php` in your web root:

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');

echo "Clearing config cache...\n";
$kernel->call('config:clear');

echo "Clearing cache...\n";
$kernel->call('cache:clear');

echo "Running migrations...\n";
$kernel->call('migrate', ['--force' => true]);

echo "Setup complete!";
```

Visit `https://yourdomain.com/setup.php`, then **delete the file immediately** after running.

### 6. Re-Capture All Face Photos on Live Site

**Important:** Images captured on localhost are saved locally — they won't transfer automatically to InfinityFree.

After deploying, you must **re-register every student/teacher face** on the live site:
1. Go to Admin → Face Registration
2. Click "Capture" for each person
3. Complete the liveness verification
4. Save the registration

The images will now save directly into `public/storage/face-photos/` on InfinityFree and display correctly.

### 7. Test Image Display

Visit the face registration page and attendance history. All images should now show up correctly (not broken).

If images are still broken:
1. Check `APP_URL` in `.env` matches your domain exactly
2. Verify folders exist: `public/storage/face-photos/`, `time-in-photos/`, `time-out-photos/`
3. Check folder permissions are `755`
4. Run `php artisan config:clear` again
5. Visit `https://yourdomain.com/debug-images.php` to diagnose

## Troubleshooting

### Images Still Broken

**Check the generated URL:**
Right-click on a broken image → "Copy Image Address"

If you see:
```
http://localhost/FACE%20RECOGNITION%20.../storage/face-photos/foo.jpg
```
→ You forgot to update `APP_URL` in `.env` on InfinityFree.

If you see:
```
https://yourdomain.com/storage/face-photos/foo.jpg
```
→ The folder doesn't exist or has wrong permissions.

### "Class 'Storage' not found"

Run `composer install` on InfinityFree (or upload the full `vendor/` folder from localhost).

### Permission Denied Errors

Set folder permissions to `755`:
```bash
chmod -R 755 public/storage
```

Or use InfinityFree File Manager → right-click folder → Permissions → 755.

### Database Connection Failed

Check `.env` database credentials match your InfinityFree MySQL settings (found in control panel → MySQL Databases).

## Summary of Changes Made

| Component | What Changed |
|-----------|--------------|
| `config/filesystems.php` | Added `public_direct` disk that writes directly into `public/storage/` |
| `FaceRegistrationController.php` | Now uses `Storage::disk('public_direct')` instead of `file_put_contents` |
| `FaceScanController.php` | Now saves to `time-in-photos/` and `time-out-photos/` via `public_direct` disk |
| `AppServiceProvider.php` | `faceImageUrl()` routes new paths through `Storage::disk('public_direct')->url()` |

This pattern **does not require symlinks**, so it works on InfinityFree, shared hosting, and localhost identically.

## Need More Help?

Run the diagnostic script:
```
https://yourdomain.com/debug-images.php
```

It will show:
- Whether folders exist and are writable
- What URLs are being generated
- Whether `APP_URL` matches the actual domain
- Sample images from the database

**Delete `debug-images.php` after troubleshooting** (it exposes system info).
