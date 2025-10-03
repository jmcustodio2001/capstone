# HR2ESS OTP Authentication Setup Guide

## Overview
This guide will help you set up the OTP (One-Time Password) authentication system for the HR2ESS employee login portal using PHP Mailer.

## Features Implemented
- ✅ Two-factor authentication with email OTP
- ✅ Secure 6-digit verification codes
- ✅ 10-minute expiration time for OTP codes
- ✅ Rate limiting (max 3 OTPs per 15 minutes)
- ✅ Attempt limiting (max 5 verification attempts)
- ✅ Beautiful email templates with HTML and plain text versions
- ✅ Real-time countdown timer
- ✅ Auto-submit when 6 digits entered
- ✅ Resend OTP functionality
- ✅ Session management and security
- ✅ Comprehensive error handling and logging

## Installation Steps

### 1. Install PHPMailer
Run the following command to install PHPMailer:
```bash
composer install
```

### 2. Run Database Migration
Execute the migration to add OTP fields to the employees table:
```bash
php artisan migrate
```

### 3. Configure Email Settings
Copy `.env.example` to `.env` if you haven't already, then update the email configuration:

#### For Gmail (Recommended):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="HR2ESS System"
```

**Important for Gmail:**
1. Enable 2-Factor Authentication on your Gmail account
2. Generate an "App Password" (not your regular password)
3. Use the App Password in the `MAIL_PASSWORD` field

#### For Outlook/Hotmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.live.com
MAIL_PORT=587
MAIL_USERNAME=your-email@outlook.com
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@outlook.com"
MAIL_FROM_NAME="HR2ESS System"
```

#### For Yahoo Mail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mail.yahoo.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yahoo.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@yahoo.com"
MAIL_FROM_NAME="HR2ESS System"
```

### 4. Clear Configuration Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## How It Works

### Login Process:
1. **Step 1**: Employee enters email and password
2. **Step 2**: System verifies credentials
3. **Step 3**: If valid, OTP is generated and sent via email
4. **Step 4**: Employee enters 6-digit OTP code
5. **Step 5**: System verifies OTP and completes login

### Security Features:
- **Rate Limiting**: Maximum 3 OTP requests per 15 minutes
- **Attempt Limiting**: Maximum 5 verification attempts per OTP
- **Time Expiration**: OTP expires after 10 minutes
- **Session Security**: OTP verification tied to user session
- **Password Verification**: Password must be correct before OTP is sent

## File Structure

### New Files Created:
- `app/Services/OTPService.php` - Core OTP functionality
- `database/migrations/2025_01_22_000000_add_otp_fields_to_employees_table.php` - Database migration
- `OTP_SETUP_GUIDE.md` - This setup guide

### Modified Files:
- `app/Models/Employee.php` - Added OTP fields
- `app/Http/Controllers/EmployeeController.php` - Added OTP methods
- `resources/views/employee_ess_modules/employee_login.blade.php` - Enhanced UI
- `routes/web.php` - Added OTP routes
- `composer.json` - Added PHPMailer dependency
- `.env.example` - Updated email configuration

## API Endpoints

### New Routes Added:
- `POST /employee/login` - Enhanced login with OTP
- `POST /employee/verify-otp` - Verify OTP code
- `POST /employee/resend-otp` - Resend OTP code

## Testing the System

### 1. Test Email Configuration:
Create a test route to verify email sending:
```php
Route::get('/test-email', function() {
    try {
        $otpService = new \App\Services\OTPService();
        $employee = \App\Models\Employee::first();
        $result = $otpService->sendOTP($employee);
        return response()->json($result);
    } catch (Exception $e) {
        return response()->json(['error' => $e->getMessage()]);
    }
});
```

### 2. Test Login Flow:
1. Navigate to `/employee/login`
2. Enter valid employee credentials
3. Check email for OTP code
4. Enter OTP code to complete login

## Troubleshooting

### Common Issues:

#### 1. "SMTP Error: Could not authenticate"
- **Solution**: Check email credentials, enable 2FA for Gmail and use App Password

#### 2. "Connection timed out"
- **Solution**: Check firewall settings, try different SMTP ports (465 for SSL)

#### 3. "OTP not received"
- **Solution**: Check spam folder, verify email address, check Laravel logs

#### 4. "Class 'PHPMailer\PHPMailer\PHPMailer' not found"
- **Solution**: Run `composer install` to install dependencies

### Debugging:
- Check Laravel logs: `storage/logs/laravel.log`
- Enable debug mode: `APP_DEBUG=true` in `.env`
- Test SMTP connection with telnet: `telnet smtp.gmail.com 587`

## Security Considerations

### Best Practices Implemented:
- ✅ OTP codes are cryptographically secure (random_int)
- ✅ Codes expire after 10 minutes
- ✅ Rate limiting prevents spam
- ✅ Attempt limiting prevents brute force
- ✅ Session-based verification prevents replay attacks
- ✅ Comprehensive logging for audit trails
- ✅ Input validation and sanitization
- ✅ CSRF protection on all forms

### Additional Security Recommendations:
- Use HTTPS in production
- Implement IP-based rate limiting
- Monitor failed login attempts
- Regular security audits
- Keep dependencies updated

## Production Deployment

### Before Going Live:
1. Set `APP_DEBUG=false` in production
2. Use a dedicated SMTP service (SendGrid, Mailgun, etc.)
3. Configure proper SSL certificates
4. Set up monitoring and alerting
5. Test thoroughly with real email addresses
6. Configure backup SMTP servers

## Support

For issues or questions:
1. Check Laravel logs first
2. Verify email configuration
3. Test with different email providers
4. Check firewall and network settings
5. Review this guide for common solutions

---

**Note**: This OTP system enhances security significantly. Ensure all employees are informed about the new login process and have access to their registered email addresses.
