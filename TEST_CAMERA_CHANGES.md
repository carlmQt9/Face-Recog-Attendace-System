# Testing Camera Default Changes

## Quick Test Steps

### 1. Clear Browser Cache
Clear your mobile browser cache to ensure new changes are loaded:
- **Chrome:** Settings → Privacy → Clear browsing data
- **Safari:** Settings → Safari → Clear History and Website Data

### 2. Test Scanner Camera
1. Open the attendance scanner on your phone
2. **Expected:** Should open with rear camera (if available)
3. **Expected:** Camera view should NOT be mirrored/inverted
4. **Expected:** You should see the natural view (like looking through the camera normally)

### 3. Test Camera Switching
1. Click the camera switch button (🔄 or arrow button)
2. Switch to front camera
3. **Expected:** Front camera should be mirrored (like a selfie/mirror)
4. Switch back to rear camera  
5. **Expected:** Rear camera should be natural (not mirrored)

### 4. Test Face Registration
1. Go to Admin → Face Registration
2. Click on a student to register their face
3. **Expected:** Should open with rear camera by default
4. **Expected:** Camera view should NOT be mirrored

## What to Look For

### ✅ Correct Behavior
- Scanner opens with rear camera (back camera facing away from you)
- Rear camera shows natural, non-mirrored view
- Front camera (when switched) shows mirrored view like a selfie
- Face scanning still works correctly
- Text and objects appear in correct orientation

### ❌ Issues to Report
- Still opens with front camera by default
- Rear camera shows mirrored/inverted view
- Text appears backwards
- Face scanning doesn't work
- Camera switching causes errors

## Device-Specific Notes

### Android Phones
- Should default to main rear camera
- Camera switching should work between front and rear
- Natural rear view, mirrored front view

### iPhone/iPad
- Should default to main rear camera  
- Camera switching should work
- Natural rear view, mirrored front view

### Tablets
- May have different camera arrangements
- Should still prefer rear camera if available
- Switching should work correctly

## Troubleshooting

### Issue: Still opens with front camera
**Possible causes:**
- Browser cache not cleared
- Device only has front camera
- Browser permissions

**Solution:**
1. Clear browser cache completely
2. Reload the page
3. Check camera permissions in browser settings

### Issue: View is still mirrored with rear camera
**Possible causes:**
- Browser cache not cleared
- CSS not updated

**Solution:**
1. Hard refresh the page (Ctrl+F5 or Cmd+Shift+R)
2. Clear browser cache
3. Try in incognito/private mode

### Issue: Camera switching doesn't work
**Possible causes:**
- Device has only one camera
- Browser permissions issues
- JavaScript errors

**Solution:**
1. Check browser console for errors
2. Ensure camera permissions are granted
3. Try refreshing the page

## Success Criteria
- ✅ Rear camera opens by default
- ✅ Rear camera shows natural (non-mirrored) view  
- ✅ Front camera shows mirrored view when switched
- ✅ Face scanning works correctly
- ✅ Camera switching works without errors
- ✅ Consistent behavior across scanner and face registration

## Need Help?
If you encounter issues:
1. Take a screenshot of the problem
2. Check the browser console for error messages
3. Note which device and browser you're using
4. Test in a different browser to see if it's browser-specific