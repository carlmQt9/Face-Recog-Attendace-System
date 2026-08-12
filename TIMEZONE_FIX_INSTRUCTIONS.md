# Timezone Fix Instructions

## Changes Made

### 1. Added Timezone to .env File
```env
APP_TIMEZONE=Asia/Manila
DB_TIMEZONE=+08:00
```

### 2. Updated AppServiceProvider
Added explicit timezone setting at boot time to ensure PHP uses the correct timezone.

### 3. Updated Database Configuration
Added timezone configuration for MySQL connection to ensure database timestamps are stored with correct timezone offset.

## How to Apply These Changes

### Step 1: Clear Configuration Cache
Run this command in your terminal (from the project root):

```bash
php artisan config:clear
```

Or on Windows cmd:
```cmd
cd c:\xampp\htdocs\FACE RECOGNITION BASED ATTENDANCE SYSTEM
php artisan config:clear
```

### Step 2: Clear Application Cache
```bash
php artisan cache:clear
```

### Step 3: Restart Your Web Server
If using XAMPP:
1. Open XAMPP Control Panel
2. Stop Apache
3. Start Apache

### Step 4: Test the Timezone

#### Option A: Using Tinker (Recommended)
```bash
php artisan tinker
```

Then in tinker, run:
```php
echo now();
echo date('Y-m-d H:i:s');
echo config('app.timezone');
```

Expected output should show current Philippine time (UTC+8).

#### Option B: Create a Test File
Create a file `public/test-time.php`:

```php
<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "PHP Timezone: " . date_default_timezone_get() . "\n";
echo "Laravel Timezone: " . config('app.timezone') . "\n";
echo "Current Time (PHP): " . date('Y-m-d H:i:s') . "\n";
echo "Current Time (Laravel): " . now() . "\n";
echo "Current Time (Carbon): " . \Carbon\Carbon::now() . "\n";
```

Visit: `http://localhost/FACE%20RECOGNITION%20BASED%20ATTENDANCE%20SYSTEM/public/test-time.php`

### Step 5: Verify Database Timezone

Run this SQL query in phpMyAdmin:

```sql
SELECT @@global.time_zone, @@session.time_zone;
```

If both show 'SYSTEM', your MySQL is using system timezone.

To set MySQL timezone to Philippine time:

```sql
SET GLOBAL time_zone = '+08:00';
SET SESSION time_zone = '+08:00';
```

Or permanently in `my.ini` (MySQL config file in XAMPP):

```ini
[mysqld]
default-time-zone='+08:00'
```

Then restart MySQL service in XAMPP.

## Verify Attendance Times

After applying changes:

1. **Clear Browser Cache** (Ctrl+Shift+Delete)
2. **Open Scanner Page**
3. **Scan a Student**
4. **Check the Time Recorded**

The time should now show the correct Philippine time (PST - UTC+8).

## Troubleshooting

### If Time is Still Wrong:

#### Check 1: PHP Timezone
Create `public/phpinfo.php`:
```php
<?php
phpinfo();
```

Visit it and search for "date.timezone". It should show "Asia/Manila".

If not, edit `php.ini` in XAMPP:
```ini
date.timezone = Asia/Manila
```

Restart Apache.

#### Check 2: Database Records
Check existing attendance records in database:

```sql
SELECT id, student_id, arrived_at, time_out, created_at 
FROM attendance_records 
ORDER BY id DESC 
LIMIT 10;
```

Old records will have wrong time - this is normal.
New records (after fix) should have correct time.

#### Check 3: Server Time
In terminal:
```bash
php -r "echo date('Y-m-d H:i:s');"
```

Should show current Philippine time.

#### Check 4: Laravel Config Cache
Sometimes config is cached. Clear it:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Understanding the Times in Your Screenshot

In your screenshot:
- **OUT 08:11 PM** - This was recorded BEFORE the fix
- **IN 05:04 AM** - This was recorded BEFORE the fix

These old records are stored in database with wrong timezone.

**Option 1: Leave them as is** (they're historical data)

**Option 2: Update old records** (if you want to correct them):

```sql
-- This updates all times by adding 8 hours (adjust based on your actual offset)
UPDATE attendance_records 
SET arrived_at = DATE_ADD(arrived_at, INTERVAL 8 HOUR),
    time_out = IF(time_out IS NOT NULL, DATE_ADD(time_out, INTERVAL 8 HOUR), NULL),
    created_at = DATE_ADD(created_at, INTERVAL 8 HOUR),
    updated_at = DATE_ADD(updated_at, INTERVAL 8 HOUR);
```

**WARNING**: Only run this ONCE and backup your database first!

## Expected Behavior After Fix

- ✅ New attendance records show correct Philippine time
- ✅ Camera clock shows correct Philippine time  
- ✅ Email notifications sent at correct time
- ✅ Auto-end sessions trigger at correct time
- ✅ Reports show correct timestamps

## Technical Details

### Philippine Time (PST)
- **Timezone**: Asia/Manila
- **UTC Offset**: +08:00
- **No DST**: Philippines doesn't observe daylight saving time

### How Laravel Handles Time:
1. `now()` - Returns Carbon instance with app timezone
2. `Carbon::now()` - Same as above
3. `created_at`, `updated_at` - Auto-cast to Carbon with app timezone
4. Database stores in UTC or specified timezone
5. Laravel converts on retrieval

### Key Files Modified:
1. `.env` - Added APP_TIMEZONE and DB_TIMEZONE
2. `app/Providers/AppServiceProvider.php` - Added timezone setting
3. `config/database.php` - Added MySQL timezone configuration

## Summary

The timezone is now properly configured at multiple levels:
1. ✅ PHP level (date_default_timezone_set)
2. ✅ Laravel application level (APP_TIMEZONE)
3. ✅ Database connection level (DB_TIMEZONE)

After clearing cache and restarting server, all new timestamps should be accurate Philippine time.

## Date
August 12, 2026
