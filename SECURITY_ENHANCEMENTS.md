# Security Enhancements Documentation

This document outlines the security enhancements implemented in the AuthController and related components.

## Overview

The following security features have been implemented to strengthen user authentication and protect against common attacks:

1. **Login Attempt Tracking** - Monitor failed login attempts
2. **Account Lockout Mechanism** - Temporary lockout after multiple failed attempts
3. **Rate Limiting** - Limit registration and login attempts per IP address
4. **Strong Password Validation** - Enforce complex password requirements
5. **Email Verification** - Require email verification before account access
6. **Input Sanitization** - Strip and trim all user inputs
7. **CSRF Protection** - Laravel's default CSRF token validation
8. **Activity Logging** - Log all authentication events

---

## 1. Login Attempt Tracking

### Model: `LoginAttempt`
**Location:** `app/Models/LoginAttempt.php`

**Table Fields:**
- `id` - Primary key
- `email` - Unique email address (indexed)
- `attempts` - Number of failed login attempts
- `last_attempt_at` - Timestamp of the last failed attempt
- `locked_until` - Timestamp when the lockout expires (indexed)
- `created_at` / `updated_at` - Timestamps

**Methods:**
- `isLocked()` - Check if account is currently locked
- `remainingLockoutTime()` - Get remaining lockout time in minutes
- `reset()` - Reset attempts and clear lockout
- `incrementAttempt()` - Increment failed attempts and apply lockout if needed

### Lockout Rules:
- **Threshold:** 5 failed attempts trigger a lockout
- **Duration:** 15 minutes lockout period
- **Automatic Reset:** Counter resets on successful login

### Migration
**File:** `database/migrations/2026_01_15_create_login_attempts_table.php`

---

## 2. Account Lockout Mechanism

### User Model Enhancement
**Location:** `app/Models/User.php`

**New Fields:**
- `email_verified_at` - Timestamp when email was verified
- `is_locked` - Boolean flag for manual account locking
- `locked_until` - Timestamp when manual lock expires

**New Methods:**
- `hasVerifiedEmail()` - Check if email is verified
- `markEmailAsVerified()` - Mark email as verified
- `isAccountLocked()` - Check if account is manually locked

### Migration
**File:** `database/migrations/2026_01_15_add_security_fields_to_users_table.php`

---

## 3. Rate Limiting

### Registration Rate Limiting
- **Limit:** 5 registrations per IP address
- **Window:** 1 hour
- **Error Message:** "Registration rate limited - try again later"
- **Implementation:** Uses `Illuminate\Cache\RateLimiter`

### Login Rate Limiting
- **Limit:** 10 login attempts per IP address
- **Window:** 1 minute
- **Additional Lockout:** Automatic lockout after 5 failed attempts
- **Implementation:** Uses `Illuminate\Cache\RateLimiter`

### Configuration
```php
const REGISTRATION_LIMIT = 5;
const REGISTRATION_DURATION = 3600; // 1 hour
const LOGIN_ATTEMPTS_LIMIT = 10;
const LOCKOUT_DURATION = 900; // 15 minutes
```

---

## 4. Strong Password Validation

### Password Requirements
- **Minimum Length:** 8 characters
- **Uppercase Letter:** At least one (A-Z)
- **Number:** At least one (0-9)
- **Special Character:** At least one (!@#$%^&*)

### Regex Pattern
```
^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])(?=.{8,}).*$
```

### Validation Rule
```php
private function passwordRules()
{
    return 'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])(?=.{8,}).*$/';
}
```

### Validation Message
```
Password must contain at least one uppercase letter, one number, and one special character (!@#$%^&*).
```

---

## 5. Email Verification

### Mailable Class
**Location:** `app/Mail/VerifyEmail.php`

**Features:**
- Generates temporary signed URL valid for 24 hours
- Uses SHA1 hash of email for additional verification
- Sends HTML-formatted verification email

### Verification Email Template
**Location:** `resources/views/emails/verify-email.blade.php`

**Contains:**
- User's name
- Click-to-verify button
- Full verification URL as fallback
- 24-hour expiration notice

### Email Verification Flow

#### 1. Registration
1. User registers with email and password
2. User account created with `email_verified_at = null`
3. Verification email sent automatically
4. User cannot access protected routes until email is verified

#### 2. Verification
1. User clicks the verification link in email
2. Signed route verifies the signature (24-hour expiration)
3. Email hash is validated against user email
4. `email_verified_at` timestamp is set
5. User can now access protected routes

#### 3. Resend Verification
1. User can request email resend
2. New verification link is generated
3. Email is sent again

### Routes
```php
// Verify email with signed URL
GET /email/verify/{id}/{hash}
Name: verify.email
Middleware: signed

// Resend verification email
POST /email/resend-verification
Name: resend.verification
```

---

## 6. Input Sanitization

All user inputs are sanitized using:
- `trim()` - Remove whitespace
- `strip_tags()` - Remove HTML/PHP tags

**Applied to:**
- Email addresses
- Names
- All form inputs

**Code Example:**
```php
$credentials['email'] = trim(strip_tags($credentials['email']));
```

---

## 7. CSRF Protection

Laravel's built-in CSRF protection is active by default:
- CSRF token validated on all POST, PUT, PATCH, DELETE requests
- Token regenerated after login
- Token in session invalidated after logout

---

## 8. Activity Logging

### Logged Events

#### User Registration
```
Event: user.registered
Description: User {email} registered with role {role}
Subject: User model instance
```

#### Email Verification
```
Event: user.email_verified
Description: Email verified for user {email}
Subject: User model instance
```

### Middleware Components

#### EmailVerificationMiddleware
**Location:** `app/Http/Middleware/EmailVerificationMiddleware.php`

Checks if user has verified email and redirects to verification notice if not.

#### AccountLockoutMiddleware
**Location:** `app/Http/Middleware/AccountLockoutMiddleware.php`

Checks if user's account is locked and prevents access to protected resources.

---

## AuthController Methods

### `login(Request $request)`
1. Check global rate limiting (10 attempts per minute)
2. Validate email and password
3. Check LoginAttempt lockout status
4. Check email verification status
5. Check manual account lock status
6. Attempt authentication
7. Reset attempts on success / increment on failure

**Error Messages:**
- "Too many login attempts. Please try again later."
- "Too many login attempts. Account locked for X minutes."
- "Please verify your email before logging in."
- "Your account is temporarily locked. Please contact support."
- "Invalid credentials."

### `register(Request $request)`
1. Check registration rate limiting (5 per hour per IP)
2. Validate input data including strong password
3. Sanitize inputs
4. Check admin code if admin role
5. Create user account
6. Send verification email
7. Log activity
8. Redirect to login with success message

**Error Messages:**
- "Registration rate limited - try again later"
- "Password must contain uppercase, number, special character"
- "Invalid authorization code."

### `verifyEmail($id, $hash)`
1. Find user by ID
2. Verify email hash matches
3. Check signed URL signature (24-hour expiration)
4. Mark email as verified
5. Log activity
6. Redirect to dashboard with success message

### `resendVerificationEmail(Request $request)`
1. Validate email exists
2. Check if already verified
3. Send verification email
4. Return success message

---

## Configuration

### Mail Configuration
**File:** `.env`

```env
MAIL_MAILER=log          # Use 'log' for development, 'smtp' for production
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="Scholarship System"
```

For development, emails are logged to `storage/logs/laravel.log`

### Admin Registration Code
**File:** `.env`

```env
ADMIN_REGISTRATION_CODE=your-secret-admin-code
```

---

## Usage Examples

### Registering with Strong Password
```
POST /register
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePass123!",
    "password_confirmation": "SecurePass123!",
    "role": "faculty",
    "admin_code": ""
}
```

### Valid Passwords
- `SecurePass123!`
- `MyPassword@2024`
- `Strong#Pass99`

### Invalid Passwords
- `password123` - No uppercase, no special char
- `PASSWORD123` - No special character
- `Pass!123` - Too short (7 chars)
- `Abc123!` - Too short (7 chars)

### Logging In After Lockout
After 5 failed attempts, user receives:
```
Too many login attempts. Account locked for 15 minutes.
```

User must wait 15 minutes before attempting again.

---

## Testing the Features

### Test Registration Rate Limiting
```bash
# Register 6 users from same IP within 1 hour
# 6th registration will be rate limited
```

### Test Login Lockout
```bash
# Attempt login 5 times with wrong password
# Account locks for 15 minutes
# 6th attempt shows lockout message
```

### Test Email Verification
```bash
# After registration, check email
# Click verification link
# Verified email grants access to dashboard
# Email only valid for 24 hours
```

### Test Strong Password Validation
```bash
# Try registering with weak passwords
# See validation errors for each requirement
```

---

## Security Best Practices

1. **Use HTTPS in Production** - All credentials transmitted over HTTPS
2. **Keep Keys Secret** - Store `APP_KEY` in `.env` (not in version control)
3. **Monitor Activity Logs** - Review `ActivityLog` table for suspicious patterns
4. **Enable Rate Limiting** - Already implemented, monitor for attacks
5. **Update Dependencies** - Keep Laravel and packages up to date
6. **Use Strong Email** - Configure SMTP for production email delivery
7. **Implement 2FA** - Consider adding two-factor authentication
8. **Session Security** - Configure session security in `config/session.php`

---

## Migration Checklist

Before deploying to production:

- [ ] Run migrations: `php artisan migrate`
- [ ] Configure mail driver (SMTP)
- [ ] Update `MAIL_FROM_ADDRESS` in `.env`
- [ ] Set strong `ADMIN_REGISTRATION_CODE`
- [ ] Enable HTTPS/SSL
- [ ] Configure trusted proxies
- [ ] Set `APP_ENV=production`
- [ ] Disable `APP_DEBUG=false`
- [ ] Monitor activity logs regularly
- [ ] Set up email backup (in case of delivery failures)
- [ ] Test email verification flow end-to-end
- [ ] Test login lockout mechanism
- [ ] Monitor rate limiting in production

---

## Support

For issues or questions regarding security features:

1. Check application logs: `storage/logs/laravel.log`
2. Review activity logs in database: `activity_logs` table
3. Verify LoginAttempt records: `login_attempts` table
4. Check email configuration if verification emails not sending

---

## Changelog

**v1.0 - Initial Security Implementation**
- Login attempt tracking with LoginAttempt model
- 5-failed-attempt auto-lockout (15 minutes)
- Registration rate limiting (5 per hour per IP)
- Strong password validation (8+ chars, uppercase, number, special)
- Email verification requirement
- Input sanitization (trim, strip_tags)
- Activity logging for auth events
- Middleware for email verification and account lockout checks
