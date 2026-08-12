# 🚀 Deploy to InfinityFree - Complete Guide

## Current Status
Your diagnostic showed:
- ❌ Storage folders missing
- ❌ `.env` file missing
- ✅ Domain is accessible

## What You Need to Do (5 Simple Steps)

### Step 1: Upload the `.env` File

1. **Open** `.env.infinityfree` file in your project folder
2. **Update** the database credentials (lines 24-28):
   ```env
   DB_HOST=sql200.infinityfreeapp.com        ← Your InfinityFree MySQL host
   DB_DATABASE=if0_12345678_face_attendance  ← Your database name
   DB_USERNAME=if0_12345678                  ← Your database username
   DB_PASSWORD=YOUR_PASSWORD_HERE            ← Your database password
   ```
   
   **Where to find these?**
   - Log into InfinityFree Control Panel
   - Go to: **MySQL Databases**
   - Copy the host, database name, username, and password

3. **Rename** `.env.infinityfree` to `.env`
4. **Upload** the `.env` file to your InfinityFree **root folder** (same level as `artisan` file)
   - Use FileZilla or InfinityFree File Manager
   - Make sure it's in the root, NOT in `public/`

5. **Set permission** to **644**:
   - Right-click `.env` → Permissions → 644

### Step 2: Run the Auto-Setup Script

1. **Upload** `public/setup-infinityfree.php` to your server (it's already in your `public/` folder)

2. **Visit** in your browser:
   ```
   https://smartattendacedmcmes.freehosting.dev/setup-infinityfree.php
   ```

3. The script will **automatically**:
   - ✅ Create all storage folders
   - ✅ Set correct permissions
   - ✅ Verify `.env` settings
   - ✅ Clear all Laravel caches
   - ✅ Test URL generation

4. **Follow any instructions** shown on the page

5. **Delete the file immediately** after it finishes:
   - Delete `public/setup-infinityfree.php` from your server

### Step 3: Verify It Worked

1. **Visit** `https://smartattendacedmcmes.freehosting.dev/simple-check.php`

2. You should now see:
   - ✅ `face-photos/: EXISTS`
   - ✅ `time-in-photos/: EXISTS`
   - ✅ `time-out-photos/: EXISTS`
   - ✅ `.env file: EXISTS`
   - ✅ `APP_URL is correct!`

3. **Delete** `public/simple-check.php` from your server

### Step 4: Re-Capture All Faces

**IMPORTANT:** Images captured on localhost don't exist on InfinityFree!

1. Log into your InfinityFree site as **Admin**
2. Go to: **Admin → Face Registration**
3. For each student/teacher:
   - Click **"Capture"**
   - Complete the 4-step liveness verification
   - Click **"Save Face Registration"**

4. The images will now save to `public/storage/face-photos/` on InfinityFree and display correctly

### Step 5: Test Attendance Capture

1. Go to: **Teacher → Live Camera Session**
2. Start a session and scan a student's face
3. The attendance snapshot should now save and display (no broken image!)

---

## 🆘 Troubleshooting

### Problem: "500 Server Error"
**Fix:** Clear cache by visiting:
```
https://smartattendacedmcmes.freehosting.dev/clear-cache.php
```
Then delete the file.

### Problem: Images still broken after setup
**Check:**
1. Did you upload `.env` file? (must be in root folder)
2. Is `APP_URL=https://smartattendacedmcmes.freehosting.dev` in `.env`?
3. Did you re-capture faces ON InfinityFree (not localhost)?
4. Run `simple-check.php` to verify folders exist

### Problem: "Database connection error"
**Fix:** Update these in `.env`:
```env
DB_HOST=sql200.infinityfreeapp.com
DB_DATABASE=if0_xxxxx_database_name
DB_USERNAME=if0_xxxxx
DB_PASSWORD=your_password
```
Get these from InfinityFree Control Panel → MySQL Databases

### Problem: Can't create folders
**Fix:** 
1. Go to InfinityFree File Manager
2. Manually create:
   - `public/storage/` (if missing)
   - `public/storage/face-photos/`
   - `public/storage/time-in-photos/`
   - `public/storage/time-out-photos/`
3. Set all folders to permission **755**

---

## 📂 Expected Folder Structure on InfinityFree

```
htdocs/smartattendacedmcmes.freehosting.dev/
├── .env                          ← MUST exist (uploaded in Step 1)
├── artisan
├── composer.json
├── public/
│   ├── index.php
│   ├── .htaccess
│   └── storage/                  ← Created by setup script
│       ├── face-photos/          ← Registered face images
│       ├── time-in-photos/       ← Time-in snapshots
│       └── time-out-photos/      ← Time-out snapshots
├── app/
├── config/
├── database/
├── resources/
├── routes/
├── storage/
└── vendor/
```

---

## ✅ Success Checklist

Before you're done, verify:
- [ ] `.env` file exists in root folder with correct `APP_URL` and database credentials
- [ ] `public/storage/face-photos/` folder exists (permission 755)
- [ ] `public/storage/time-in-photos/` folder exists (permission 755)
- [ ] `public/storage/time-out-photos/` folder exists (permission 755)
- [ ] Setup script ran successfully (no errors)
- [ ] All diagnostic files deleted (`setup-infinityfree.php`, `simple-check.php`, etc.)
- [ ] All faces re-captured on InfinityFree
- [ ] Images display correctly (not broken)

---

## 🎯 Quick Commands Reference

If you have SSH access to InfinityFree (most don't), you can run:
```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Create folders
mkdir -p public/storage/{face-photos,time-in-photos,time-out-photos}
chmod -R 755 public/storage
```

Otherwise, use the `setup-infinityfree.php` script which does everything automatically.

---

**Need help?** Re-run `simple-check.php` to diagnose the current state, then follow the instructions shown.
