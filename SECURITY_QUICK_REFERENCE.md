# Security Features - Quick Reference Guide

## At a Glance

This Scholarship System now includes enterprise-grade authentication security:

| Feature | Status | Details |
|---------|--------|---------|
| Login Attempt Tracking | ✅ Active | 5 failures = 15-min lockout |
| Rate Limiting | ✅ Active | 5 reg/hour, 10 login/min per IP |
| Strong Passwords | ✅ Required | 8+ chars, uppercase, number, special char |
| Email Verification | ✅ Required | 24-hour signed links |
| Input Sanitization | ✅ Active | trim() & strip_tags() on all inputs |
| Activity Logging | ✅ Active | All auth events logged |
| CSRF Protection | ✅ Active | Laravel default |
| Account Lockout | ✅ Available | Manual admin lockout with timed unlock |

---

## Implementation Files

### Models (2)
```
app/Models/LoginAttempt.php          ← Track failed login attempts
app/Models/User.php                  ← Updated with security fields
```

### Controllers (1)
```
app/Http/Controllers/AuthController.php  ← Enhanced with security features
```

### Mail (1)
```
app/Mail/VerifyEmail.php             ← Email verification message
```

### Middleware (2)
```
app/Http/Middleware/EmailVerificationMiddleware.php
app/Http/Middleware/AccountLockoutMiddleware.php
```

### Migrations (2)
```
database/migrations/2026_01_15_create_login_attempts_table.php
database/migrations/2026_01_15_add_security_fields_to_users_table.php
```

### Views (1)
```
resources/views/emails/verify-email.blade.php
```

### Routes (2 new)
```
GET  /email/verify/{id}/{hash}       ← Verify email
POST /email/resend-verification      ← Resend verification email
```

---

## Common Tasks

### Admin: Manually Lock User Account
```php
// In Laravel Tinker or Admin Panel
$user = User::find($userId);
$user->update([
    'is_locked' => true,
    'locked_until' => now()->addMinutes(30)  // or any duration
]);
```

### Admin: Unlock User Account
```php
$user = User::find($userId);
$user->update([
    'is_locked' => false,
    'locked_until' => null
]);
```

### Admin: Clear Failed Login Attempts
```php
$attempt = LoginAttempt::where('email', 'user@example.com')->first();
$attempt?->reset();
```

### Check User Email Verification Status
```php
$user = User::find($userId);
if ($user->hasVerifiedEmail()) {
    // Email verified
} else {
    // Email not verified
}
```

### View Activity Logs
```php
$logs = ActivityLog::where('event', 'user.registered')->get();
$logs = ActivityLog::where('subject_type', 'App\\Models\\User')->get();
```

---

## Database Tables

### login_attempts
```
Columns: id, email (unique), attempts, last_attempt_at, 
         locked_until, created_at, updated_at
Purpose: Track and enforce login attempt limits
```

### users (updated)
```
New Columns: email_verified_at, is_locked, locked_until
Purpose: Store email verification and manual lock status
```

### activity_logs
```
Columns: event, description, subject_type, subject_id, user_id
Purpose: Audit trail of authentication events
```

---

## Validation Rules

### Password
```
✓ Minimum 8 characters
✓ At least 1 uppercase letter (A-Z)
✓ At least 1 number (0-9)
✓ At least 1 special character (!@#$%^&*)
```

**Examples:**
- ✅ ValidPass123!
- ✅ MySecure#2024
- ✅ Strong@Pass99
- ❌ weakpass1 (no uppercase/special)
- ❌ NoNumbers! (no number)
- ❌ NoSpecial1 (no special char)
- ❌ Sh0rt! (too short)

---

## Rate Limiting

### Registration
- **Limit:** 5 registrations per hour
- **Scope:** Per IP address
- **Reset:** Automatically after 1 hour

### Login
- **Limit:** 10 login attempts per minute
- **Scope:** Per IP address
- **Automatic Lockout:** 5 failed attempts = 15 minute account lockout

---

## Email Verification Flow

1. **User Registers** → Email sent immediately
2. **User Clicks Link** → Email verified (takes <1 second)
3. **24-Hour Window** → Link valid for 24 hours
4. **Can Resend** → User can request new link anytime
5. **Required to Login** → Cannot access system without verification

---

## Error Messages Users Might See

### Registration
- `"Registration rate limited. Please try again later."` → Too many registrations from this IP
- `"Password must contain at least one uppercase letter, one number, and one special character (!@#$%^&*)."` → Weak password

### Login
- `"Too many login attempts. Please try again later."` → Global rate limit exceeded
- `"Too many login attempts. Account locked for 15 minutes."` → Account locked due to failures
- `"Please verify your email before logging in."` → Email not verified
- `"Your account is temporarily locked. Please contact support."` → Admin locked account
- `"Invalid credentials."` → Wrong email or password

### Email Verification
- `"Verification link has expired."` → Link older than 24 hours
- `"Email already verified."` → Already verified, no need to reverify
- `"Invalid verification link."` → Link was tampered with

---

## Configuration (.env)

```env
# Mail Driver (development = log, production = smtp)
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Scholarship System"

# Admin registration code (for new admin accounts)
ADMIN_REGISTRATION_CODE=your-secret-admin-code
```

---

## Security Highlights

✅ **Passwords are hashed** with bcrypt (Laravel default)
✅ **Rate limiting** prevents brute force attacks
✅ **Account lockout** after 5 failed attempts
✅ **Email verification** prevents fake accounts
✅ **Input sanitization** prevents injection attacks
✅ **CSRF protection** on all forms
✅ **Signed URLs** for email verification (can't be forged)
✅ **Activity logging** for audit trails
✅ **Session security** with token regeneration

---

## Testing (Quick)

### Test Strong Password
```
Register with: password123
Result: ❌ FAIL - No uppercase/special char
```

### Test Email Verification
```
1. Register successfully
2. Check logs: storage/logs/laravel.log
3. Find "Verification" email (contains URL)
4. Visit URL in browser
5. Should see "Email verified successfully!"
```

### Test Login Lockout
```
1. Enter wrong password 5 times
2. Account locks for 15 minutes
3. 6th attempt shows: "Account locked for 15 minutes"
4. Wait 15 min or admin resets
5. Can login again
```

---

## Troubleshooting

### "Registration rate limited"
→ This IP registered 5 times in last hour. Wait 1 hour or use different IP.

### "Email verification link expired"
→ Link valid for 24 hours. Request new one via "Resend verification email" link.

### "Account locked for 15 minutes"
→ 5 failed login attempts. Wait 15 minutes or contact admin to unlock.

### "Please verify your email before logging in"
→ Check email inbox (including spam). Click verification link.

### Email not arriving
→ Check `storage/logs/laravel.log` for email content in development mode.

---

## Admin Commands

### View Failed Login Attempts
```bash
php artisan tinker
> LoginAttempt::where('attempts', '>', 0)->get()
```

### Reset Specific User's Attempts
```bash
php artisan tinker
> LoginAttempt::where('email', 'user@example.com')->delete()
```

### Check Email Verification Status
```bash
php artisan tinker
> User::where('email', 'user@example.com')->first()->email_verified_at
```

### View Recent Activity Logs
```bash
php artisan tinker
> ActivityLog::latest()->limit(20)->get()
```

---

## Key Constants

Located in `AuthController.php`:

```php
REGISTRATION_LIMIT = 5              // Registrations per hour
LOGIN_ATTEMPTS_LIMIT = 10           // Login attempts per minute (IP-based)
LOCKOUT_DURATION = 900              // 15 minutes (seconds)
REGISTRATION_DURATION = 3600        // 1 hour (seconds)
```

---

## What's Happening Behind the Scenes

### When User Registers
1. ✓ Validate input and password strength
2. ✓ Check registration rate limit
3. ✓ Sanitize name and email
4. ✓ Create user account
5. ✓ Increment rate limit counter
6. ✓ Send verification email
7. ✓ Log activity event
8. ✓ Redirect to login

### When User Logs In
1. ✓ Check global rate limit
2. ✓ Check LoginAttempt lockout
3. ✓ Check email verification status
4. ✓ Check manual account lock
5. ✓ Attempt authentication
6. ✓ Success → Clear rate limit, reset attempts, regenerate session
7. ✓ Failure → Increment attempts, lock if >5, log event

### When User Verifies Email
1. ✓ Validate user exists
2. ✓ Check email hash
3. ✓ Verify signed URL signature
4. ✓ Mark as verified
5. ✓ Log activity
6. ✓ Grant access to protected routes

---

## Next Steps for Admins

1. ✅ Review `SECURITY_ENHANCEMENTS.md` for full documentation
2. ✅ Test all features in development
3. ✅ Configure mail driver for production
4. ✅ Monitor `login_attempts` table
5. ✅ Review `activity_logs` regularly
6. ✅ Set up alerts for suspicious patterns
7. ✅ Educate users about password requirements
8. ✅ Implement 2FA (future enhancement)

---

**Status:** ✅ Ready for Production

All security features are implemented, tested, and documented.
