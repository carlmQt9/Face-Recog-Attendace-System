# InfinityFree Deployment Guide

## Files Created
✅ `index.php` - Created in root directory
✅ `.htaccess` - Created in root directory

## What Changed
The paths in `index.php` have been updated from:
- `__DIR__.'/../storage'` → `__DIR__.'/storage'`
- `__DIR__.'/../vendor'` → `__DIR__.'/vendor'`
- `__DIR__.'/../bootstrap'` → `__DIR__.'/bootstrap'`

This allows Laravel to run from the root directory instead of the `public` folder.

## Deployment Steps for InfinityFree

### Step 1: Prepare Your Files
Upload ALL files and folders to InfinityFree's `htdocs` folder:
- app/
- bootstrap/
- config/
- database/
- public/ (keep this folder, it contains assets)
- resources/
- routes/
- storage/
- vendor/
- .htaccess (root level - IMPORTANT!)
- index.php (root level - IMPORTANT!)
- artisan
- composer.json
- composer.lock
- .env (configure this - see Step 3)

### Step 2: Update .env File
Update your `.env` file with InfinityFree database credentials:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-domain.infinityfreeapp.com

DB_CONNECTION=mysql
DB_HOST=sql###.infinityfree.com
DB_PORT=3306
DB_DATABASE=epiz_#######_dbname
DB_USERNAME=epiz_#######
DB_PASSWORD=your_database_password
```

### Step 3: Set Permissions
On InfinityFree, set these folder permissions to **0755**:
- storage/
- storage/framework/
- storage/framework/cache/
- storage/framework/sessions/
- storage/framework/views/
- storage/logs/
- bootstrap/cache/

### Step 4: Important Security Note
⚠️ Since all files are now in the web root, add this to the TOP of your root `.htaccess`:

```apache
# Deny access to sensitive folders
<IfModule mod_rewrite.c>
    RewriteRule ^(app|bootstrap|config|database|routes|storage|vendor)/.*$ - [F,L]
</IfModule>
```

### Step 5: Asset URLs
Your assets in the `public` folder will now be accessed as:
- `public/css/styles.css`
- `public/js/script.js`
- `public/images/logo.png`

Laravel's `asset()` helper should automatically handle this correctly.

### Step 6: Clear Cache
After uploading, run these commands via SSH (if available) or create a `clear-cache.php` file:

**Option A: SSH**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Option B: Create clear-cache.php in root**
```php
<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('config:cache');
$kernel->call('route:cache');
$kernel->call('view:cache');
echo "Cache cleared successfully!";
```

Then visit: `http://your-domain.infinityfreeapp.com/clear-cache.php`

### Troubleshooting

**403 Forbidden Error:**
- Check that `index.php` and `.htaccess` are in the root `htdocs` folder
- Verify `.htaccess` file is not corrupted
- Check file permissions (755 for folders, 644 for files)

**500 Internal Server Error:**
- Check `storage` and `bootstrap/cache` folder permissions
- Verify `.env` file exists and has correct database credentials
- Check error logs in cPanel

**Assets Not Loading:**
- Update asset paths in blade templates to use `{{ asset('public/path/to/file') }}`
- Or move contents of `public` folder to root (but keep the folder structure)

**Database Connection Error:**
- Verify database credentials in `.env`
- Create database in InfinityFree cPanel if not already created
- Import your database SQL file via phpMyAdmin

## InfinityFree Limitations
⚠️ Be aware of these InfinityFree limitations:
- No SSH access (on free plan)
- Limited PHP execution time
- No Laravel Queue workers
- No scheduled tasks (cron jobs require upgrade)
- Face recognition may be resource-intensive

## Alternative: Move Public Assets
For better organization, you can move only the public assets:
1. Move everything from `public/` folder to root except `index.php`
2. Keep `index.php` in root as created
3. Update asset paths in your blade templates

Good luck with your deployment! 🚀
