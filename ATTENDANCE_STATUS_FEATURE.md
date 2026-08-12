# Attendance Status Feature - Late Marking

## ✅ Feature Restored

### **What It Does**
Allows students to time out in the afternoon session even if they didn't time in during the morning session. The system automatically marks them as "late" for the morning session.

## 📋 How It Works

### **Scenario: Student Arrives Late**
1. **Morning Session (Time-In)**: Student doesn't show up - marked as "absent"
2. **Afternoon Session (Time-Out)**: Student scans to time out
3. **System Action**: 
   - Finds the morning "absent" record
   - Updates it to "late"
   - Creates a time-in record for the afternoon
   - Records the time-out

### **Result**
- Morning attendance: Status changed from "absent" to "late"
- Afternoon attendance: New record with time-in and time-out
- Student's late arrival is properly documented

## 🔧 Technical Implementation

### **1. FaceScanController - Time-Out Logic**
When a student scans for time-out without a time-in:

```php
// Check if afternoon session
if ($session->session_type === 'afternoon_out') {
    // Try to update morning absent to late
    $wasMarkedLate = $session->updateAbsentToLate($student->id);
}

// Create time-in record
$status = $wasMarkedLate ? 'late' : 'present';
$record = AttendanceRecord::create([
    'status' => $status,
    'method' => $wasMarkedLate ? 'system' : 'face_scan',
    'arrived_at' => now()->subMinutes(1),
]);
```

### **2. ClassSession Model - Update Absent to Late**
```php
public function updateAbsentToLate($studentId): bool
{
    // Find morning session
    $morningSession = self::where('teacher_id', $this->teacher_id)
        ->where('session_type', 'morning_in')
        ->whereDate('started_at', today())
        ->first();

    if ($morningSession) {
        // Find absent record
        $absentRecord = AttendanceRecord::where('class_session_id', $morningSession->id)
            ->where('student_id', $studentId)
            ->where('status', 'absent')
            ->first();

        if ($absentRecord) {
            // Update to late
            $absentRecord->update([
                'status' => 'late',
                'marked_by' => 'System - Late Arrival',
            ]);
            return true;
        }
    }

    return false;
}
```

## 📊 Status Flow

### **Normal Flow (On Time)**
```
Morning: Time In → Status: Present
Afternoon: Time Out → Record complete
```

### **Late Arrival Flow**
```
Morning: 
  - Initially: Status: Absent (auto-marked by system)
  - After afternoon timeout: Status: Late (updated by system)

Afternoon:
  - Auto time-in created (1 minute before time-out)
  - Time out recorded
  - Status: Late (or Present if wasn't absent in morning)
```

## 🎯 Use Cases

### **Case 1: Student Arrives Late to School**
- **Morning**: Absent (auto-marked when session ends)
- **Afternoon**: Scans to time out
- **Result**: Morning changed to "Late", afternoon time-out recorded

### **Case 2: Student Has Medical Appointment**
- **Morning**: Absent (auto-marked)
- **Returns**: Scans to time out
- **Result**: Properly marked as late, not fully absent

### **Case 3: Student Forgets Morning Time-In**
- **Morning**: Doesn't scan (marked absent)
- **Afternoon**: Scans to time out
- **Result**: System recognizes late arrival

## 📝 Attendance Record Fields

### **Fields Used for Late Marking**
```php
[
    'status' => 'late',                    // Changed from 'absent'
    'method' => 'system',                  // System-initiated
    'marked_by' => 'System - Late Arrival', // Audit trail
    'arrived_at' => <afternoon_time>,      // When they actually arrived
    'time_out' => <timeout_time>,          // When they left
]
```

## 🔔 Notifications

### **Parent Notifications**
- **Time-Out Email**: Includes status message if marked late
- **Message**: "Student timed out at X:XX (marked as late due to morning absence)"

### **Response Message**
```json
{
    "result": "success",
    "scan_type": "time_out",
    "status": "late",
    "message": "Student Name timed out at 3:00 PM (stayed 2h 30m) (marked as late due to morning absence)."
}
```

## 💡 Benefits

1. **Accurate Tracking**: Distinguishes between completely absent and late arrivals
2. **Flexible**: Allows time-out without requiring time-in
3. **Automated**: System handles status updates automatically
4. **Audit Trail**: `marked_by` field shows system actions
5. **Parent Communication**: Parents notified of late status

## ⚙️ Configuration

### **Session Types**
- `morning_in`: Time-in session (default status: present/absent/late)
- `afternoon_out`: Time-out session (triggers late marking logic)

### **System Settings**
- `cooldown_seconds`: Prevents duplicate scans (default: 5)
- Session scheduling determines when to auto-mark absent

## 🧪 Testing Scenarios

### **Test 1: Late Arrival**
1. Start morning session
2. Don't time in a student
3. End morning session (student marked absent)
4. Start afternoon session
5. Student scans to time out
6. **Expected**: Morning status = "late", afternoon time-out recorded

### **Test 2: On-Time Student**
1. Student times in during morning
2. Student times out during afternoon
3. **Expected**: Status = "present", normal flow

### **Test 3: Multiple Late Students**
1. Multiple students don't time in
2. All marked absent in morning
3. Some time out in afternoon
4. **Expected**: Only those who timed out are marked late

## 📈 Reports & Analytics

### **Status Categories**
- **Present**: Arrived on time, timed in
- **Late**: Arrived late (afternoon time-out triggered update)
- **Absent**: Didn't show up at all (no afternoon time-out)

### **Queries**
```php
// Find late arrivals
AttendanceRecord::where('status', 'late')
    ->whereDate('arrived_at', today())
    ->get();

// Find system-marked records
AttendanceRecord::where('method', 'system')
    ->whereNotNull('marked_by')
    ->get();
```

## ✅ Current Status

The feature is **ACTIVE** and working:
- ✅ Time-out without time-in is allowed
- ✅ Absent status automatically updates to late
- ✅ Afternoon time-out records properly
- ✅ Parent notifications include late status
- ✅ Audit trail maintained with `marked_by` field

This provides accurate attendance tracking that distinguishes between students who never showed up and those who arrived late!