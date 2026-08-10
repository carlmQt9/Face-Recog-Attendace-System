# Quick Reference Guide

## 🎯 What's New (v2.0)

| Old | New | Benefit |
|-----|-----|---------|
| AM/PM sessions | In/Out sessions | More intuitive |
| Fixed time ranges | Any time allowed | More flexibility |
| Manual absent marking | Automatic | Saves time |
| No late tracking | Auto late detection | Accurate records |
| No status badges | Color badges | Easy to see |
| No auto scan mode | Auto selection | Reduces clicks |
| No camera flip | Flip button | Better positioning |

---

## 🚀 Quick Start

### Create a Session
1. Go to **My Sessions**
2. Enter Subject & Section
3. Select Camera
4. Choose **📥 In** or **📤 Out** (not AM/PM!)
5. Set schedule (optional - any time)
6. Click **Start**

### Open Camera
1. Session automatically sets scan mode
2. Click **Flip** if needed (local devices only)
3. Start scanning
4. Status badges show Present/Absent/Late

### Check Results
- **Live Session**: See all students' status
- **Camera Roster**: See who scanned in
- **Student History**: See all attendance records

---

## ❓ Common Questions

### Q: How do I know if it's AM or PM?
**A**: Look at the schedule time:
- 08:00 → 🌅 AM
- 14:00 → 🌇 PM

### Q: Can I set morning session at 2 PM?
**A**: Yes! Set session type to "📥 In" and time to 14:00. It shows as "📥 In (🌇 PM)".

### Q: What's the difference between In and Out?
**A**: 
- "📥 In" = Time In (attendance arrival)
- "📤 Out" = Time Out (attendance departure)

### Q: Are absent students marked automatically?
**A**: Yes! When morning session ends, unmarked students become Absent.

### Q: Can a student change from Absent to Late?
**A**: Yes! If they show up in afternoon session, status changes to Late.

### Q: What does Flip button do?
**A**: Flips camera horizontally for better view. Only appears for local device cameras.

### Q: How do I know the scan type is selected?
**A**: The In/Out button will be highlighted automatically. You'll see:
- "In" button highlighted → Time In mode
- "Out" button highlighted → Time Out mode

### Q: Can I manually change In/Out after opening camera?
**A**: Yes, click the In/Out buttons anytime to change mode.

### Q: What if both students scan at exact same time?
**A**: System records both with timestamps. No conflicts.

---

## 🎨 Status Colors

| Status | Color | Meaning |
|--------|-------|---------|
| Present | 🟢 Green | Attended morning session |
| Absent | 🔴 Red | Didn't attend morning session |
| Late | 🟡 Yellow | Attended afternoon after absence |

---

## ⚡ Common Tasks

### Task: Create morning session
1. Choose "📥 In"
2. Set time 08:00 - 09:00
3. Displays as "📥 In (🌅 AM)"

### Task: Create afternoon session  
1. Choose "📤 Out"
2. Set time 13:00 - 14:00
3. Displays as "📤 Out (🌇 PM)"

### Task: Check absent students
1. Open Live Session view
2. Look for 🔴 Red Absent badges
3. These auto-marked when session ended

### Task: Check late arrivals
1. Open Live Session or History
2. Look for 🟡 Yellow Late badges
3. These auto-updated when student appeared

### Task: Flip camera
1. Click Flip button (⇄)
2. Camera flips horizontally
3. Click again to flip back

---

## 🔧 Troubleshooting

| Issue | Solution |
|-------|----------|
| In/Out button not highlighted | Reload page - should auto-select |
| Flip button not visible | Camera is not local device |
| Absent students not marked | Session type must be "📥 In" |
| Late status not updating | Student must scan during afternoon |
| Status badges not showing | Clear browser cache |
| QR scan not working | Try different camera angle |

---

## 📱 On Mobile

- Session creation: Works on mobile
- Camera view: Full screen mode recommended
- Roster: Swipe to see all columns
- Flip camera: Works on mobile too

---

## 🔐 Security Notes

- All data encrypted in transit (HTTPS)
- CSRF protection active
- Rate limiting on API endpoints
- No sensitive data in logs

---

## ⚙️ System Limits

- Max students per session: Unlimited
- Max sessions per day: Unlimited
- Max cameras: Limited by hardware
- Max schedule duration: 24 hours

---

## 📊 Reports

### Session Summary
- Total students
- Present count
- Absent count
- Late count

### Student Record
- Date, time in, time out
- Status (Present/Absent/Late)
- Duration
- Scan method (face/QR/manual)

---

## 🎯 Tips & Tricks

1. **Set schedule for auto-end**: Session automatically ends at scheduled time
2. **Use Flip for better lighting**: Adjust camera angle without switching
3. **QR code fastest**: Scan QR codes for quick attendance
4. **Manual mode for issues**: If auto-scan has trouble, use manual mode
5. **Check history often**: See patterns in student attendance

---

## 🆘 Emergency

### Session stuck?
- Refresh page
- If still stuck, end session manually

### Camera not working?
- Check permissions
- Try different browser
- Restart camera

### Data lost?
- Check attendance history
- Contact admin if needed

---

## 📞 Support

**Quick Issues**: Check troubleshooting section  
**Can't find feature**: Check START_HERE.md  
**Technical details**: Read IMPLEMENTATION_COMPLETE.md  
**Verification**: See FINAL_VERIFICATION.md

---

## ✨ Remember

- ✅ In/Out is the new AM/PM
- ✅ Schedule determines AM/PM label
- ✅ No time restrictions anymore
- ✅ Scan type auto-selects
- ✅ Absent marked automatically
- ✅ Late detected automatically
- ✅ Status shows at a glance

---

**Version**: 2.0  
**Last Updated**: August 10, 2026  
**Status**: Ready to Use ✅
