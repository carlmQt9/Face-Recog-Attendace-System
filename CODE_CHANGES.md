# Code Changes Summary

## 1. ClassSession Model (`app/Models/ClassSession.php`)

### Added Methods:

```php
// Check if session is currently active
public function isActive(): bool
{
    return $this->status === 'active';
}

// Mark absent students when morning session ends
public function markAbsentStudents(): int
{
    // Gets all teacher's students
    // Finds who didn't scan in
    // Creates absent records
}

// Update absent to late when student shows up in afternoon
public function updateAbsentToLate($studentId): bool
{
    // Finds absent record from morning session
    // Changes status to late
    // Updates marked_by field
}
```

---

## 2. ClassSessionController (`app/Http/Controllers/Teacher/ClassSessionController.php`)

### Modified Methods:

```php
// stop() - now marks absent students
public function stop(ClassSession $session)
{
    $session->update(['status' => 'ended', 'ended_at' => now()]);
    
    $absentCount = $session->markAbsentStudents(); // NEW
    
    return redirect()->route('teacher.sessions.index')
        ->with('success', "Class session ended. {$absentCount} students marked as absent.");
}

// checkSchedule() - marks absent on auto-end
public function checkSchedule(ClassSession $session)
{
    if ($session->isActive() && $session->scheduled_end && now()->format('H:i') >= $session->scheduled_end) {
        $session->update(['status' => 'ended', 'ended_at' => now()]);
        $session->camera->update(['is_active' => false]);
        
        $absentCount = $session->markAbsentStudents(); // NEW
        
        return response()->json([
            'auto_ended' => true,
            'absent_count' => $absentCount
        ]);
    }
}
```

---

## 3. FaceScanController (`app/Http/Controllers/Api/FaceScanController.php`)

### Modified handleTimeIn():

```php
$record = AttendanceRecord::create([
    // ... other fields ...
    'status' => 'present', // NEW - all time-in records start as present
    // ...
]);
```

### Modified handleTimeOut():

```php
private function handleTimeOut($student, $session, $camera, $request)
{
    // ... existing code ...
    
    // NEW: Check if student was absent in morning
    if (!$record) {
        if ($session && $session->session_type === 'afternoon_out') {
            $wasAbsent = $session->updateAbsentToLate($student->id); // NEW
            if ($wasAbsent) {
                // Create record with status='late'
            }
        }
    }
    
    // ... rest of method ...
}
```

---

## 4. AttendanceRecord Model (`app/Models/AttendanceRecord.php`)

### Updated fillable array:

```php
protected $fillable = [
    // ... existing fields ...
    'status', // NEW
    // ...
];
```

### Added casts:

```php
protected $casts = [
    // ... existing casts ...
    'status' => 'string', // NEW
];
```

### Added helper methods:

```php
// Get status badge HTML
public function statusBadge(): string
{
    return match($this->status ?? 'present') {
        'present' => '<span class="badge bg-success">Present</span>',
        'absent' => '<span class="badge bg-danger">Absent</span>',
        'late' => '<span class="badge bg-warning text-dark">Late</span>',
        default => '<span class="badge bg-secondary">Unknown</span>',
    };
}

// Get status color for UI
public function statusColor(): string
{
    return match($this->status ?? 'present') {
        'present' => 'success',
        'absent' => 'danger',
        'late' => 'warning',
        default => 'secondary',
    };
}
```

---

## 5. ManualAttendanceController (`app/Http/Controllers/Teacher/ManualAttendanceController.php`)

### Updated record creation:

```php
$record = AttendanceRecord::create([
    // ... existing fields ...
    'status' => 'present', // NEW
    // ...
]);
```

---

## 6. Session Index View (`resources/views/teacher/sessions/index.blade.php`)

### Removed time restrictions:

```javascript
// BEFORE: Had separate logic for AM (00:00-11:59) and PM (12:00-23:59)
// NOW: Both support full 24-hour range
function applyTimeRestrictions() {
    // Remove min/max restrictions
    startEl.min = '00:00'; startEl.max = '23:59';
    endEl.min = '00:00'; endEl.max = '23:59';
    
    // Just show informational hints, no time restrictions
}
```

---

## 7. Live Session View (`resources/views/teacher/sessions/live.blade.php`)

### Added Status column:

```blade
<thead>
    <tr>
        <!-- ... other headers ... -->
        <th>Status</th> <!-- NEW -->
        <!-- ... other headers ... -->
    </tr>
</thead>

<tbody>
    @forelse($attendance as $i => $record)
        <tr>
            <!-- ... other cells ... -->
            <td>
                @php
                    $statusBadgeClass = match($record->status ?? 'present') {
                        'present' => 'bg-success',
                        'absent' => 'bg-danger',
                        'late' => 'bg-warning text-dark',
                    };
                @endphp
                <span class="badge {{ $statusBadgeClass }}">
                    {{ ucfirst($record->status ?? 'present') }}
                </span>
            </td>
            <!-- ... other cells ... -->
        </tr>
    @endforelse
</tbody>
```

---

## 8. Camera View (`resources/views/teacher/sessions/camera.blade.php`)

### Updated roster item display:

```blade
<div class="roster-item">
    <!-- ... avatar ... -->
    <div>
        <div class="roster-name">{{ $record->student->user->name }}</div>
        <div style="display:flex;align-items:center;gap:4px;">
            <!-- NEW: Status badge -->
            <span style="background:{{ $statusClass }};color:{{ $statusColor }};">
                {{ $statusLabel }}
            </span>
            
            <!-- Time display -->
            <span>IN {{ $record->arrived_at->format('h:i A') }}</span>
            @if($record->time_out)
                <span>OUT {{ $record->time_out->format('h:i A') }}</span>
            @endif
        </div>
    </div>
</div>
```

---

## 9. Student History View (`resources/views/student/attendance-history.blade.php`)

### Added Status column:

```blade
<thead>
    <tr>
        <th>Photo</th>
        <th>Date</th>
        <th>Status</th> <!-- NEW -->
        <th>In</th>
        <th>Out</th>
        <th>Duration</th>
        <th>Location</th>
    </tr>
</thead>

<tbody>
    @forelse($records as $record)
        <tr>
            <!-- ... photo ... -->
            <td>
                <span class="badge {{ $statusBadgeClass }}">
                    {{ $statusLabel }}
                </span>
            </td>
            <!-- ... time fields ... -->
        </tr>
    @endforelse
</tbody>
```

---

## Data Flow Diagram

```
MORNING SESSION (session_type: morning_in)
├─ Student scans in
│  └─ FaceScanController::handleTimeIn()
│     └─ AttendanceRecord::create(['status' => 'present'])
│
└─ Session ends (manual or scheduled)
   └─ ClassSessionController::stop() or checkSchedule()
      └─ ClassSession::markAbsentStudents()
         └─ For each student not in attendance:
            └─ AttendanceRecord::create(['status' => 'absent'])

AFTERNOON SESSION (session_type: afternoon_out)
├─ Student scans in/out
│  └─ FaceScanController::handleTimeOut()
│     ├─ Find morning session
│     └─ If student marked absent:
│        └─ ClassSession::updateAbsentToLate()
│           └─ AttendanceRecord::update(['status' => 'late'])
│
└─ Results displayed in:
   ├─ Live Session View (with Status column)
   ├─ Camera View (roster panel)
   └─ Student History (with Status column)
```

---

## Key Implementation Details

1. **Status is immutable from outside** - Only system can change status through defined methods
2. **Auto-absent only on morning sessions** - Afternoon sessions don't trigger automatic absent marking
3. **Late only from absent** - Students can only become "late" if they were first marked "absent"
4. **Both timestamps preserved** - time_in and time_out both stored for complete record
5. **Backward compatible** - Old records default to 'present' status

