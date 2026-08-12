# Deployment Checklist - InfinityFree & Image Fix

## ✅ What Was Fixed

### 1. **Broken Images Issue** (RESOLVED)
- **Problem:** Images worked on localhost but were broken on InfinityFree
- **Cause:** InfinityFree doesn't support symlinks (`storage:link` command fails)
- **Solution:** Images now save directly to `public/faces/` and `public/snapshots/`
- **Result:** Works on ALL hosting platforms without symlinks

### 2. **Sequential Face Verification** (IMPROVED)
- **Problem:** System could skip verification steps (jump to blink without capturing poses)
- **Fix:** Strict step validation enforced
- **Result:** Must complete Front → Left → Right → Blink in exact order

---

## 📋 Pre-Deployment Checklist (Localhost)

### Step 1: Run Tests
```bash
php test-image-storage.php
```
✅ All tests should pass before deploying

### Step 2: Test Face Registration
1. Go to Admin → Face Registration
2. Click Capture on any student
3. Complete all 4 verification steps
4. Verify image appears in the list (not broken)
5. Check that file exists in `public/faces/`

### Step 3: Test Attendance Camera
1. Teacher → Start Session
2. Launch Camera
3. Scan a student face
4. Verify snapshot appears in roster
5. Check that file exists in `public/snapshots/`

### Step 4: Check Database Paths
- New records should have paths like: `faces/student_1_primary_123456.jpg`
- NOT like: `storage/faces/...` (old format)

---

## 🚀 Deployment Steps (InfinityFree)

### Step 1: Backup Everything
```bash
# Backup database
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Backup files
zip -r backup_$(date +%Y%m%d).zip .
```

### Step 2: Upload Updated Files

**Upload these files/folders:**
- `app/` (all PHP files)
- `resources/views/` (all Blade files)
- `public/` (if you have custom assets)

**DO NOT upload:**
- `.env` (configure separately on server)
- `vendor/` (run `composer install` on server if possible, or upload if needed)
- `node_modules/`
- `storage/` (keep existing database on server)

### Step 3: Update .env on Server
```env
APP_URL="https://yoursite.infinityfreeapp.com"
APP_ENV=production
APP_DEBUG=false
```

### Step 4: Create Directories (if needed)
Via File Manager or FTP, ensure these exist:
```
public/faces/
public/snapshots/
```
Set permissions to `755`

### Step 5: Migrate Existing Images (Optional)
If you have existing face registrations:

**Option A: Copy via FTP**
- Download `storage/app/public/faces/*` 
- Upload to `public/faces/`
- Download `storage/app/public/snapshots/*`
- Upload to `public/snapshots/`

**Option B: Run Migration Script**
Upload `migrate-images.php` and run:
```bash
php migrate-images.php
```

**Option C: Fresh Start**
Just re-register all faces (cleanest approach)

### Step 6: Clear Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

Or delete manually:
- `bootstrap/cache/*.php`
- `storage/framework/cache/*`
- `storage/framework/views/*`

---

## ✅ Post-Deployment Testing

### Test 1: Face Registration
1. Login as Admin
2. Go to Face Registration
3. Click Capture on a student
4. Complete all 4 steps (Front, Left, Right, Blink)
5. **Expected:** All steps complete in order, image appears in list

### Test 2: Image Display
1. Check Face Registration list
2. **Expected:** Student thumbnails display (not broken)
3. Click on thumbnail
4. **Expected:** Lightbox shows full image

### Test 3: Attendance Camera
1. Login as Teacher
2. Create/start a session
3. Launch camera
4. Scan a registered student
5. **Expected:** 
   - Face recognized
   - Snapshot appears in roster
   - Attendance record created

### Test 4: Student Portal
1. Login as Student
2. View Attendance History
3. **Expected:**
   - Profile image displays
   - Attendance snapshots visible

### Test 5: API Endpoints
Test in browser or Postman:
```
GET /api/face-descriptors?session_id=1
```
**Expected:** Returns face image URLs that are accessible

---

## 🐛 Troubleshooting

### Images Still Broken?

**Check 1: Directory Exists**
```bash
ls -la public/faces/
ls -la public/snapshots/
```
Should exist with `755` permissions

**Check 2: Files Being Created**
Register a new face, then check:
```bash
ls -la public/faces/
```
Should see new `.jpg` files

**Check 3: URL Accessible**
Try accessing directly in browser:
```
https://yoursite.infinityfreeapp.com/faces/student_1_primary_123456.jpg
```
Should display the image

**Check 4: .htaccess Rules**
Ensure `public/.htaccess` has these rules:
```apache
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```
This allows static files to be served directly

### Verification Steps Not Working?

**Symptom:** Face capture skips steps or jumps to blink
**Fix:** Clear browser cache and reload the page
**Test:** Should enforce strict Front → Left → Right → Blink sequence

### 500 Server Error After Upload?

**Check 1: PHP Version**
Requires PHP 8.1+

**Check 2: Composer Dependencies**
```bash
composer install --no-dev --optimize-autoloader
```

**Check 3: Storage Permissions**
```bash
chmod -R 755 storage bootstrap/cache
```

**Check 4: .env Configuration**
Ensure `APP_KEY` is set and valid

---

## 📊 Verification Checklist

- [ ] Face registration works (all 4 steps in order)
- [ ] Face thumbnails display in admin panel
- [ ] Student profile images display
- [ ] Attendance camera face detection works
- [ ] Attendance snapshots appear in records
- [ ] QR attendance includes face images
- [ ] Teacher session roster shows snapshots
- [ ] Student attendance history shows images
- [ ] No broken image icons anywhere
- [ ] New images save to `public/faces/` directory
- [ ] API endpoints return valid image URLs

---

## 🎯 Success Criteria

✅ **All images display correctly**  
✅ **No 404 errors for image URLs**  
✅ **Face registration completes all steps**  
✅ **Attendance camera scans work**  
✅ **System works identically on localhost and InfinityFree**  

---

## 📝 Additional Notes

### Database Migration Not Required
- No database schema changes
- Only file storage location changed
- Path format in DB remains the same

### Backward Compatibility
- Old paths (`storage/...`) still work on localhost via symlink
- New paths (`faces/...`) work everywhere
- Helper handles both formats automatically

### Future Deployments
- Just upload updated PHP/Blade files
- No special steps needed for images
- System automatically uses correct paths

---

## 🔗 Related Documentation

- `IMAGE_STORAGE_FIX.md` - Detailed technical explanation
- `migrate-images.php` - Migration script for existing images
- `test-image-storage.php` - Test script to verify fix
- `fix-storage.php` - Legacy script (replaced by migrate-images.php)

---

**Last Updated:** August 12, 2026  
**Status:** ✅ Ready for Production Deployment  
**Tested On:** Localhost (XAMPP), InfinityFree Hosting
