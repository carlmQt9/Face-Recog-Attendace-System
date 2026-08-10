# Testing Guide - New Features

## Setup & Prerequisites
- Application running on `http://127.0.0.1:8000`
- Teacher account created and logged in
- Students assigned to teacher
- Camera/device configured

---

## Test 1: AM/PM Time Restrictions Removed

### Steps:
1. Navigate to **Teacher > My Sessions** page
2. Click "Start a New Class Session"
3. Fill in Subject and Section
4. Select **"🌅 AM"** session type
5. Try to set schedule times:
   - Start: `14:30` (2:30 PM)
   - End: `15:30` (3:30 PM)

### Expected Result:
✅ Should accept the times without error (previously showed "Value must be 11:59 AM or earlier")

### Steps (continued):
6. Now select **"🌇 PM"** session type
7. Try to set schedule times:
   - Start: `08:00` (8:00 AM)
   - End: `09:00` (9:00 AM)

### Expected Result:
✅ Should accept the times (no restriction forcing PM times)

---

## Test 2: Morning Session Auto-Absent Marking

### Setup:
- Create/have at least 3 students under your class
- Students should NOT have logged in yet

### Steps:
1. Start a **morning session** (session_type: `morning_in`)
2. Have **Student A and B scan in** (use manual mark if needed)
3. **Do NOT scan Student C**
4. Wait a few seconds to ensure times settle
5. **End the session** (click "End" or wait for scheduled end time)

### Expected Result:
✅ System message: "Class session ended. 1 students marked as absent."
✅ View the session roster:
- Student A: Status = `Present` (✓)
- Student B: Status = `Present` (✓)
- Student C: Status = `Absent` (🚫)

### Verification:
- Check Live Session view
- Status badges should show:
  - Green "Present" for A and B
  - Red "Absent" for C

---

## Test 3: Late Arrival Detection

### Prerequisites:
- Completed Test 2 (morning session with Student C marked absent)

### Steps:
1. In the same day/session, start an **afternoon session** (session_type: `afternoon_out`)
2. Have **Student C scan in** (use time_out mode to simulate departure)

### Expected Result:
✅ Student C's attendance status changes from **Absent → Late**
✅ Camera/roster shows Student C with:
- Status badge: Yellow "Late"
- Time: IN [morning time], OUT [afternoon time]

### Verification in Live Roster:
- Student C should show:
  - Status: `Late`
  - Timestamps from both morning and afternoon

---

## Test 4: Student Attendance History Display

### Steps:
1. Log in as a **Student** (Student A, B, or C from previous tests)
2. Navigate to **My Attendance** page
3. Select current month and year

### Expected Result:
✅ Attendance table shows:
- New column: **Status** with colored badges
- Student A & B: Green "Present"
- Student C: Yellow "Late"

✅ **Most Recent** card shows:
- Status: `Present` or `Late`
- Time In and Time Out for both morning and afternoon

---

## Test 5: Manual Mark (Teacher)

### Steps:
1. Start any active session
2. Go to **Live Session** or **Camera** view
3. Find **Manual Mark** section (right side or bottom)
4. Select a student who hasn't scanned yet
5. Choose **"Time In"**
6. Click **Mark**

### Expected Result:
✅ Student appears in roster with:
- Status: `Present`
- Method: `Manual` (badge)
- Time: Current timestamp

---

## Test 6: Display on Live Camera View

### Steps:
1. Start a session
2. Go to **Camera** view
3. Have students scan in
4. View the **Live Roster** panel on the right

### Expected Result:
✅ Each roster item shows:
- Student name
- **Status badge** (Present/Absent/Late with color)
- Time In: `IN [time]`
- Time Out: `OUT [time]` (if applicable)
- Method badge (Face/Manual/QR)

### Example Display:
```
👤 John Smith
[Present] IN 8:15 AM        [Face]

👤 Jane Doe  
[Late] IN 7:50 AM OUT 5:00 PM  [Face]
```

---

## Test 7: Auto-End Schedule with Absent Marking

### Steps:
1. Start a **morning session** with scheduled end time in 1 minute
2. Have some students scan in
3. **Do NOT touch the session** - let it auto-end
4. Wait for the scheduled time to pass

### Expected Result:
✅ Session automatically ends
✅ Students who didn't scan are marked **Absent**
✅ Message shown in session history: "N students marked as absent"

---

## Test 8: Edge Cases

### Case 8a: Student scans in multiple times (same session)
**Steps**: Student A scans in, then scans again
**Expected**: Shows "already timed in" message, no duplicate record

### Case 8b: Time-out without time-in
**Steps**: Open afternoon session, student scans in time-out mode without morning time-in
**Expected**: Creates time-in record automatically, then adds time-out to it

### Case 8c: Afternoon session without prior morning session
**Steps**: Start only afternoon session, students scan
**Expected**: 
- Status = `Present`
- No "late" conversion (no prior morning session)

---

## Test 9: Database Verification

### Check Status Values:
```sql
SELECT id, student_id, status, scan_type, arrived_at, time_out
FROM attendance_records
ORDER BY created_at DESC
LIMIT 10;
```

### Expected Results:
- `status` should be one of: `present`, `absent`, `late`
- Records created by system have `marked_by = 'System - Auto Absent'`
- Records updated for late have `marked_by = 'System - Late Arrival'`

---

## Test 10: Concurrent Sessions

### Steps:
1. Start a morning session with Teacher A
2. **Start another** morning session with same teacher (different camera/class)
3. Check what happens to the first session

### Expected Behavior:
- First session should end
- Only one active session per teacher at a time
- Second session becomes the active one

---

## Common Issues & Solutions

### Issue: Status column doesn't show
**Solution**: 
1. Clear view cache: `php artisan view:clear`
2. Refresh browser page
3. Check if attendance records have status value (may need to run migration)

### Issue: Absent students not auto-marked
**Solution**:
1. Ensure session_type = `morning_in` (not afternoon_out)
2. Verify students are assigned to the teacher
3. Check that marked attendance records exist in DB
4. Session must be ended (manually or via scheduled end)

### Issue: Late status not updating
**Solution**:
1. Student must have been marked `absent` first
2. Afternoon session must exist on same day
3. Student must scan during afternoon session
4. Both morning and afternoon sessions must be for same teacher

### Issue: Times not appearing in history
**Solution**:
1. Ensure both `arrived_at` and `time_out` fields are populated
2. Check database: may need to update existing records
3. Verify camera records are being created properly

---

## Performance Notes

- **Auto-absent marking** runs when session ends (could affect ~50+ students)
- **Late detection** runs per scan (minimal impact)
- Database queries optimized with indexes on:
  - `student_id`
  - `class_session_id`
  - `status`

---

## Rollback Instructions

If you need to revert changes:

### Database:
```sql
-- Remove status column from attendance_records
ALTER TABLE attendance_records DROP COLUMN status;

-- Remove auto-created absent records
DELETE FROM attendance_records WHERE marked_by LIKE 'System%';
```

### Code:
Revert these files to previous version:
- `app/Models/ClassSession.php`
- `app/Http/Controllers/Teacher/ClassSessionController.php`
- `app/Http/Controllers/Api/FaceScanController.php`
- `resources/views/teacher/sessions/live.blade.php`
- `resources/views/teacher/sessions/camera.blade.php`
- `resources/views/student/attendance-history.blade.php`

---

## Sign-Off Checklist

When testing is complete, verify:

- [ ] Time restrictions removed (can set any time)
- [ ] Morning session marks absent students when ended
- [ ] Absent students show "Late" when they show up afternoon
- [ ] Status badges display in all views
- [ ] History shows both time-in and time-out
- [ ] Manual marks work correctly
- [ ] Database stores status correctly
- [ ] No errors in browser console
- [ ] No errors in server logs

