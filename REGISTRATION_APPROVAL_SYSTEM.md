# Registration & Approval System - Implementation Guide

## Overview
A complete registration system has been added where users can self-register, and administrators must approve accounts before users can access the system (except students who are auto-approved).

## Files Created/Modified

### 1. Database Migration
**File:** `database/migrations/2026_10_12_000001_add_approval_fields_to_users_table.php`
- Adds `is_approved` (boolean) field
- Adds `approved_at` (timestamp) field
- Adds `approved_by` (foreign key to users table) field

### 2. Registration Controller
**File:** `app/Http/Controllers/Auth/RegisterController.php`
- Handles user registration
- Auto-approves students
- Creates Student/Teacher records based on role
- Redirects with appropriate success messages

### 3. Updated Models

#### User Model (`app/Models/User.php`)
- Added fillable fields: `is_approved`, `approved_at`, `approved_by`
- Added casts for approval fields
- Added `approvedBy()` relationship

### 4. Updated Controllers

#### LoginController (`app/Http/Controllers/Auth/LoginController.php`)
- Added approval check during login
- Prevents unapproved users from logging in
- Shows appropriate error message

#### UserController (`app/Http/Controllers/Admin/UserController.php`)
- Added `approve(User $user)` method - approves single user
- Added `approveAll()` method - approves all pending users
- Added `reject(User $user)` method - rejects and deletes pending registration

### 5. Views

#### Registration View (`resources/views/auth/register.blade.php`)
- Clean, modern registration form
- Role selection (Student, Teacher, Admin)
- Dynamic form fields based on role
- Student-specific fields (Student ID, School Year, Company)
- Password confirmation
- Info message about approval requirements

#### Updated Login View (`resources/views/auth/login.blade.php`)
- Added "Don't have an account? Register here" link

#### Updated Landing Page (`resources/views/landing.blade.php`)
- Changed "Get Started" button to "Register"

### 6. Routes (`routes/web.php`)
- Added `GET /register` - show registration form
- Added `POST /register` - process registration
- Added `POST admin/users/{user}/approve` - approve single user
- Added `POST admin/users/approve-all` - approve all pending users
- Added `POST admin/users/{user}/reject` - reject user registration

## How to Setup

### 1. Run Migration
```bash
php artisan migrate
```

This will add the approval fields to your users table.

### 2. Update Existing Users (Optional)
If you have existing users in the database, you may want to approve them automatically:

```sql
UPDATE users SET is_approved = 1, approved_at = NOW() WHERE is_approved IS NULL OR is_approved = 0;
```

## How It Works

### Registration Flow

#### For Students:
1. User fills out registration form
2. Selects "Student" role
3. Enters Student ID, School Year, and optionally Company
4. Submits form
5. **Auto-approved** - can log in immediately
6. Student record created in database
7. Redirected to login page with success message

#### For Teachers & Admins:
1. User fills out registration form
2. Selects "Teacher" or "Admin" role
3. Submits form
4. `is_approved = false` by default
5. Teacher record created (if teacher)
6. Redirected to login page with "pending approval" message
7. **Cannot log in** until admin approves
8. If they try to log in, they see: "Your account is pending admin approval"

### Admin Approval Flow

#### In Admin Dashboard (User Management):

**To approve users, you need to add approval buttons to the admin users view:**

1. Admin sees list of all users
2. Pending users show with special badge/indicator
3. Admin can:
   - **Approve individual user:** Click "Approve" button
   - **Approve all pending:** Click "Approve All Pending" button
   - **Reject user:** Click "Reject" button (deletes the user)

4. When approved:
   - `is_approved` set to `true`
   - `approved_at` set to current timestamp
   - `approved_by` set to admin's user ID
   - User can now log in

## Admin UI Updates Needed

You need to update `resources/views/admin/users/index.blade.php` to add:

### 1. Status Badge
Add approval status badge in the user list:
```blade
@if(!$user->is_approved)
    <span class="badge bg-warning">Pending Approval</span>
@else
    <span class="badge bg-success">Approved</span>
@endif
```

### 2. Approve Buttons
Add buttons for pending users:
```blade
@if(!$user->is_approved)
    <form action="{{ route('admin.users.approve', $user) }}" method="POST" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-success" title="Approve">
            <i class="bi bi-check-circle-fill"></i> Approve
        </button>
    </form>
    <form action="{{ route('admin.users.reject', $user) }}" method="POST" class="d-inline">
        @csrf
        <button class="btn btn-sm btn-danger" title="Reject">
            <i class="bi bi-x-circle-fill"></i> Reject
        </button>
    </form>
@endif
```

### 3. Approve All Button
Add at the top of the page:
```blade
@if($users->where('is_approved', false)->count() > 0)
    <form action="{{ route('admin.users.approve-all') }}" method="POST" class="d-inline">
        @csrf
        <button class="btn btn-success">
            <i class="bi bi-check-circle-fill"></i> Approve All Pending
        </button>
    </form>
@endif
```

## Testing

### Test Student Registration (Auto-Approved):
1. Go to `/register`
2. Fill form with role "Student"
3. Enter Student ID and School Year
4. Submit
5. Should redirect to login with success message
6. Log in immediately ✓

### Test Teacher/Admin Registration (Pending):
1. Go to `/register`
2. Fill form with role "Teacher" or "Admin"
3. Submit
4. Should redirect to login with "pending approval" message
5. Try to log in - should see error: "Your account is pending admin approval" ✓
6. Admin logs in
7. Admin goes to User Management
8. Admin clicks "Approve" for the user
9. User can now log in ✓

## Security Notes

- Passwords are hashed using Laravel's Hash::make()
- Email must be unique
- Password must be at least 8 characters
- Students are auto-approved to reduce friction
- Teachers and Admins require manual approval for security
- Login check prevents unapproved users from accessing the system

## Notification (Future Enhancement)

You can add email notifications when:
- User registers (welcome email)
- User is approved (approval email)
- User is rejected (rejection email)

Use Laravel's Mail and Notifications for this.

## Summary

✅ Registration form created
✅ Auto-approval for students
✅ Manual approval required for teachers and admins
✅ Login check for approval status
✅ Admin approval/reject endpoints
✅ Database migration for approval fields
✅ Landing page updated with register link
✅ Login page updated with register link

**Next Step:** Run the migration and update the admin users view to show approval buttons!
