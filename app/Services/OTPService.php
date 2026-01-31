<?php

namespace App\Services;

use App\Models\Employee;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OTPService
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);
        $this->configureSMTP();
    }

    /**
     * Configure SMTP settings
     */
    private function configureSMTP()
    {
        try {
            // Validate SMTP configuration - use config() instead of env() for better caching
            $mailUsername = config('mail.mailers.smtp.username') ?: env('MAIL_USERNAME');
            $mailPassword = config('mail.mailers.smtp.password') ?: env('MAIL_PASSWORD');

            if (!$mailUsername || !$mailPassword) {
                Log::error('SMTP Configuration Missing', [
                    'mail_username_set' => !empty($mailUsername),
                    'mail_password_set' => !empty($mailPassword),
                    'mail_mailer' => env('MAIL_MAILER'),
                    'config_username' => config('mail.mailers.smtp.username'),
                    'env_username' => env('MAIL_USERNAME')
                ]);
                throw new \Exception('MAIL_USERNAME and MAIL_PASSWORD are required for SMTP');
            }

            // Server settings
            $this->mail->isSMTP();
            $this->mail->Host       = env('MAIL_HOST', 'smtp.gmail.com');
            $this->mail->SMTPAuth   = true;
            $this->mail->Username   = $mailUsername;
            $this->mail->Password   = $mailPassword;

            // Handle different encryption types properly
            $encryption = env('MAIL_ENCRYPTION', 'tls');
            if ($encryption === 'ssl') {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // For port 465
            } else {
                $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // For port 587
            }

            $this->mail->Port       = env('MAIL_PORT', 587);

            // Additional SSL/TLS options for better compatibility
            if ($encryption === 'ssl') {
                // For Gmail port 465 (SSL)
                $this->mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );
            }

            // Enable verbose debug output for troubleshooting (temporarily disabled)
            $this->mail->SMTPDebug = 0; // Set to 2 for detailed debugging, 0 to disable
            $this->mail->Debugoutput = function($str, $level) {
                Log::info("SMTP Debug: " . $str);
            };

            // Recipients
            $fromAddress = config('mail.from.address', 'noreply@example.com');
            $fromName = config('mail.from.name', 'Jetlouge Travels');
            $this->mail->setFrom($fromAddress, $fromName);

            // Content
            $this->mail->isHTML(true);
            $this->mail->CharSet = 'UTF-8';

            // Log configuration for debugging
            Log::info('SMTP Configuration:', [
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'username' => $mailUsername,
                'password_length' => strlen($mailPassword),
                'encryption' => env('MAIL_ENCRYPTION'),
                'from_address' => env('MAIL_FROM_ADDRESS'),
                'smtp_secure_setting' => $this->mail->SMTPSecure,
                'smtp_options_set' => isset($this->mail->SMTPOptions)
            ]);

        } catch (Exception $e) {
            Log::error('SMTP Configuration Error: ' . $e->getMessage());
            throw new \Exception('Email configuration failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate a 6-digit OTP code
     */
    public function generateOTP(): string
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP to employee email
     */
    public function sendOTP(Employee $employee): array
    {
        try {
            // DEVELOPMENT MODE: Only use development mode if explicitly enabled
            // Check if OTP_BYPASS_EMAIL is explicitly set to true
            if (env('OTP_BYPASS_EMAIL', false) === true) {
                Log::info('OTP Development Mode Triggered - OTP_BYPASS_EMAIL=true', [
                    'employee_id' => $employee->employee_id,
                    'mail_mailer' => env('MAIL_MAILER'),
                    'otp_bypass_email' => env('OTP_BYPASS_EMAIL', false)
                ]);
                return $this->handleDevelopmentMode($employee);
            }

            // If MAIL_MAILER is 'log', warn but still try to send email for testing
            if (env('MAIL_MAILER') === 'log') {
                Log::warning('MAIL_MAILER is set to log - emails will be logged instead of sent', [
                    'employee_id' => $employee->employee_id,
                    'mail_mailer' => env('MAIL_MAILER')
                ]);
                // Continue with normal flow to attempt email sending
            }

            // Check rate limiting (max 1 OTP per 30 seconds for testing)
            if ($this->isRateLimited($employee)) {
                $timeSinceLastOTP = Carbon::now()->diffInSeconds($employee->last_otp_sent_at);
                $waitTimeSeconds = max(0, 30 - $timeSinceLastOTP);

                return [
                    'success' => false,
                    'message' => "Too many OTP requests. Please wait {$waitTimeSeconds} more seconds before requesting again.",
                    'rate_limited' => true,
                    'wait_time' => $waitTimeSeconds
                ];
            }

            // Generate new OTP
            $otpCode = $this->generateOTP();
            $expiresAt = Carbon::now()->addMinutes(2); // OTP expires in 2 minutes

            // Update employee with OTP details
            $employee->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
                'otp_attempts' => 0,
                'last_otp_sent_at' => Carbon::now(),
                'otp_verified' => false
            ]);

            // Send email
            $emailSent = $this->sendOTPEmail($employee, $otpCode);

            if ($emailSent) {
                Log::info('OTP sent successfully', [
                    'employee_id' => $employee->employee_id,
                    'email' => $employee->email,
                    'expires_at' => $expiresAt->toDateTimeString()
                ]);

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully to your email address.',
                    'expires_at' => $expiresAt->toDateTimeString()
                ];
            } else {
                Log::error('Failed to send OTP email - PHPMailer returned false', [
                    'employee_id' => $employee->employee_id,
                    'email' => $employee->email,
                    'smtp_host' => env('MAIL_HOST'),
                    'smtp_port' => env('MAIL_PORT'),
                    'smtp_username' => env('MAIL_USERNAME'),
                    'smtp_from' => env('MAIL_FROM_ADDRESS')
                ]);

                return [
                    'success' => false,
                    'message' => 'Failed to send OTP email. Please check your email configuration and try again.',
                    'email_error' => true
                ];
            }

        } catch (\Exception $e) {
            Log::error('OTP Send Error: ' . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while sending OTP. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Handle development mode - bypass email sending
     */
    private function handleDevelopmentMode(Employee $employee): array
    {
        // Generate a fixed OTP for development
        $otpCode = env('OTP_DEV_CODE', '123456');
        $expiresAt = Carbon::now()->addMinutes(10);

        // Update employee with OTP details
        $employee->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => $expiresAt,
            'otp_attempts' => 0,
            'last_otp_sent_at' => Carbon::now(),
            'otp_verified' => false
        ]);

        Log::info('OTP Development Mode - Email bypassed', [
            'employee_id' => $employee->employee_id,
            'email' => $employee->email,
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt->toDateTimeString(),
            'mail_mailer' => env('MAIL_MAILER'),
            'otp_bypass_email' => env('OTP_BYPASS_EMAIL', false)
        ]);

        return [
            'success' => true,
            'message' => "OTP sent successfully to your email address. [DEV MODE: Use code {$otpCode}] - Development mode is enabled via OTP_BYPASS_EMAIL=true",
            'expires_at' => $expiresAt->toDateTimeString(),
            'dev_mode' => true,
            'dev_otp' => $otpCode,
            'dev_reason' => 'OTP_BYPASS_EMAIL=true'
        ];
    }

    /**
     * Send OTP to external employee (session-based, no DB persistence)
     */
    public function sendOTPExternal(Employee $employee, string $otpCode): bool
    {
        return $this->sendOTPEmail($employee, $otpCode);
    }

    /**
     * Send OTP email using PHPMailer
     */
    private function sendOTPEmail(Employee $employee, string $otpCode): bool
    {
        try {
            Log::info('Starting OTP email send process', [
                'employee_id' => $employee->employee_id,
                'email' => $employee->email,
                'smtp_host' => $this->mail->Host,
                'smtp_port' => $this->mail->Port,
                'smtp_secure' => $this->mail->SMTPSecure
            ]);

            // Clear any previous recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            // Add recipient
            $this->mail->addAddress($employee->email, $employee->first_name . ' ' . $employee->last_name);

            // Email content
            $this->mail->Subject = 'HR2ESS Login Verification Code';
            $this->mail->Body = $this->getOTPEmailTemplate($employee, $otpCode);
            $this->mail->AltBody = $this->getOTPEmailPlainText($employee, $otpCode);

            Log::info('Email content prepared, attempting to send...');

            // Send email
            $result = $this->mail->send();

            Log::info('OTP Email send result', [
                'employee_id' => $employee->employee_id,
                'email' => $employee->email,
                'result' => $result,
                'success' => $result ? 'YES' : 'NO'
            ]);

            return $result;

        } catch (Exception $e) {
            Log::error('PHPMailer Error: ' . $e->getMessage(), [
                'employee_id' => $employee->employee_id,
                'email' => $employee->email,
                'error_code' => $e->getCode(),
                'smtp_host' => env('MAIL_HOST'),
                'smtp_port' => env('MAIL_PORT'),
                'smtp_username' => env('MAIL_USERNAME'),
                'smtp_from_address' => env('MAIL_FROM_ADDRESS'),
                'smtp_encryption' => env('MAIL_ENCRYPTION'),
                'detailed_error' => $e->errorMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Also log to console for immediate debugging
            error_log("OTP Email Error: " . $e->getMessage());
            error_log("SMTP Config - Host: " . env('MAIL_HOST') . ", Port: " . env('MAIL_PORT'));
            error_log("SMTP Auth - Username: " . env('MAIL_USERNAME') . ", From: " . env('MAIL_FROM_ADDRESS'));

            return false;
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOTP(Employee $employee, string $otpCode): array
    {
        try {
            $response = ['success' => false];

            if (!$employee->otp_code || !$employee->otp_expires_at) {
                $response += [
                    'message' => 'No OTP found. Please request a new OTP.',
                    'no_otp' => true
                ];
            } elseif (Carbon::now()->isAfter($employee->otp_expires_at)) {
                $employee->update([
                    'otp_code' => null,
                    'otp_expires_at' => null,
                    'otp_attempts' => 0
                ]);
                $response += [
                    'message' => 'OTP has expired. Please request a new OTP.',
                    'expired' => true
                ];
            } elseif ($employee->otp_attempts >= 5) {
                $employee->update([
                    'otp_code' => null,
                    'otp_expires_at' => null,
                    'otp_attempts' => 0
                ]);
                $response += [
                    'message' => 'Too many failed attempts. Please request a new OTP.',
                    'max_attempts' => true
                ];
            } elseif ($employee->otp_code === $otpCode) {
                $employee->update([
                    'otp_code' => null,
                    'otp_expires_at' => null,
                    'otp_attempts' => 0,
                    'otp_verified' => true,
                    'email_verified_at' => Carbon::now()
                ]);

                Log::info('OTP verified successfully', [
                    'employee_id' => $employee->employee_id,
                    'email' => $employee->email
                ]);

                $response = [
                    'success' => true,
                    'message' => 'OTP verified successfully.',
                    'verified' => true
                ];
            } else {
                $employee->increment('otp_attempts');
                $remainingAttempts = 5 - $employee->otp_attempts;

                Log::warning('Incorrect OTP attempt', [
                    'employee_id' => $employee->employee_id,
                    'attempts' => $employee->otp_attempts,
                    'remaining' => $remainingAttempts
                ]);

                $response += [
                    'message' => "Incorrect OTP. You have {$remainingAttempts} attempts remaining.",
                    'incorrect' => true,
                    'remaining_attempts' => $remainingAttempts
                ];
            }

            return $response;

        } catch (\Exception $e) {
            Log::error('OTP Verification Error: ' . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred during OTP verification. Please try again.',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if employee is rate limited for OTP requests
     */
    private function isRateLimited(Employee $employee): bool
    {
        // TEMPORARILY DISABLED FOR TESTING - ALWAYS ALLOW OTP REQUESTS
        return false;

        if (!$employee->last_otp_sent_at) {
            return false;
        }

        $timeSinceLastOTP = Carbon::now()->diffInSeconds($employee->last_otp_sent_at);

        // Allow new OTP if more than 30 seconds have passed (for testing - change back to 120 seconds for production)
        if ($timeSinceLastOTP < 30) {
            Log::info('Rate limit triggered', [
                'employee_id' => $employee->employee_id,
                'last_otp_sent' => $employee->last_otp_sent_at,
                'minutes_since_last' => $timeSinceLastOTP
            ]);
            return true;
        }

        return false;
    }

    /**
     * Reset rate limit for testing purposes
     */
    public function resetRateLimit(Employee $employee): void
    {
        $employee->update([
            'last_otp_sent_at' => null,
            'otp_code' => null,
            'otp_expires_at' => null,
            'otp_attempts' => 0
        ]);

        Log::info('Rate limit reset for employee', [
            'employee_id' => $employee->employee_id
        ]);
    }

    /**
     * Get HTML email template for OTP
     */
    private function getOTPEmailTemplate(Employee $employee, string $otpCode): string
    {
        $companyName = env('APP_NAME', 'Jetlouge Travels');
        $employeeName = $employee->first_name . ' ' . $employee->last_name;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Login Verification Code</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px; }
                .otp-code { background: #fff; border: 2px dashed #667eea; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px; }
                .otp-number { font-size: 32px; font-weight: bold; color: #667eea; letter-spacing: 8px; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Login Verification</h1>
                    <p>Secure access to your {$companyName} account</p>
                </div>
                <div class='content'>
                    <h2>Hello, {$employeeName}!</h2>
                    <p>We received a login request for your {$companyName} Jetlouge Travels account. To complete your login, please use the verification code below:</p>

                    <div class='otp-code'>
                        <p style='margin: 0; font-size: 14px; color: #666;'>Your Verification Code</p>
                        <div class='otp-number'>{$otpCode}</div>
                        <p style='margin: 0; font-size: 12px; color: #666;'>Valid for 2 minutes</p>
                    </div>

                    <div class='warning'>
                        <strong>⚠️ Security Notice:</strong>
                        <ul style='margin: 10px 0;'>
                            <li>This code expires in <strong>2 minutes</strong></li>
                            <li>Never share this code with anyone</li>
                            <li>If you didn't request this login, please contact your administrator immediately</li>
                            <li>This code can only be used once</li>
                        </ul>
                    </div>

                    <p><strong>Login Details:</strong></p>
                    <ul>
                        <li><strong>Time:</strong> " . Carbon::now()->format('F j, Y g:i A T') . "</li>
                        <li><strong>Email:</strong> {$employee->email}</li>
                        <li><strong>Employee ID:</strong> {$employee->employee_id}</li>
                    </ul>
                </div>
                <div class='footer'>
                    <p>This is an automated message from {$companyName}. Please do not reply to this email.</p>
                    <p>If you have any questions, please contact your system administrator.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get plain text version of OTP email
     */
    private function getOTPEmailPlainText(Employee $employee, string $otpCode): string
    {
        $companyName = env('APP_NAME', 'Jetlouge Travels');
        $employeeName = $employee->first_name . ' ' . $employee->last_name;

        return "
LOGIN VERIFICATION CODE

Hello {$employeeName},

We received a login request for your {$companyName} Jetlouge Travels account.

Your verification code is: {$otpCode}

This code will expire in 2 minutes.

SECURITY NOTICE:
- Never share this code with anyone
- If you didn't request this login, contact your administrator immediately
- This code can only be used once

Login Details:
- Time: " . Carbon::now()->format('F j, Y g:i A T') . "
- Email: {$employee->email}
- Employee ID: {$employee->employee_id}

This is an automated message from {$companyName}.
        ";
    }

    /**
     * Send OTP to admin email (similar to employee but for admin users)
     */
    public function sendAdminOTP($adminUser, string $otpCode): array
    {
        try {
            Log::info("=== OTP SERVICE ADMIN EMAIL START ===");
            Log::info("Attempting to send admin OTP email to: {$adminUser->email}");
            Log::info("Admin user object: " . json_encode($adminUser));
            Log::info("OTP Code: {$otpCode}");
            
            // Check SMTP configuration
            Log::info("SMTP Configuration Check:", [
                'host' => $this->mail->Host,
                'port' => $this->mail->Port,
                'username' => $this->mail->Username,
                'smtp_secure' => $this->mail->SMTPSecure,
                'smtp_auth' => $this->mail->SMTPAuth
            ]);
            
            // Clear any previous recipients
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            // Add recipient
            $this->mail->addAddress($adminUser->email, $adminUser->first_name);
            Log::info("Added recipient: {$adminUser->email} ({$adminUser->first_name})");

            // Email content for admin
            $this->mail->Subject = 'HR2ESS Admin Login - Verification Code';
            $this->mail->Body = $this->getAdminOTPEmailTemplate($adminUser, $otpCode);
            $this->mail->AltBody = $this->getAdminOTPEmailPlainText($adminUser, $otpCode);

            Log::info('Admin email content prepared, attempting to send...');
            Log::info('Email subject: ' . $this->mail->Subject);

            // Send email
            $result = $this->mail->send();

            Log::info('Admin OTP Email send result', [
                'admin_email' => $adminUser->email,
                'result' => $result,
                'success' => $result ? 'YES' : 'NO',
                'smtp_debug' => 'Check SMTP logs above'
            ]);

            if ($result) {
                Log::info("✅ Admin OTP email sent successfully!");
                Log::info("=== OTP SERVICE ADMIN EMAIL END (SUCCESS) ===");
                return [
                    'success' => true,
                    'message' => 'Admin OTP sent successfully to your email address.'
                ];
            } else {
                Log::error("❌ PHPMailer returned false - email not sent");
                Log::info("=== OTP SERVICE ADMIN EMAIL END (FAILED) ===");
                return [
                    'success' => false,
                    'message' => 'Failed to send admin OTP email - PHPMailer returned false.'
                ];
            }

        } catch (Exception $e) {
            Log::error('❌ Admin PHPMailer Exception: ' . $e->getMessage(), [
                'admin_email' => $adminUser->email,
                'error_code' => $e->getCode(),
                'detailed_error' => method_exists($e, 'errorMessage') ? $e->errorMessage() : 'No detailed error',
                'trace' => $e->getTraceAsString()
            ]);
            
            Log::info("=== OTP SERVICE ADMIN EMAIL END (EXCEPTION) ===");

            return [
                'success' => false,
                'message' => 'Failed to send admin OTP email: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get HTML template for admin OTP email
     */
    private function getAdminOTPEmailTemplate($adminUser, string $otpCode): string
    {
        $companyName = env('APP_NAME', 'Jetlouge Travels');
        $adminName = $adminUser->first_name;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Admin Login Verification</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>{$companyName}</h1>
                <p style='color: #f0f0f0; margin: 10px 0 0 0; font-size: 16px;'>Admin Portal - Login Verification</p>
            </div>
            
            <div style='background: #f9f9f9; padding: 30px; border-radius: 10px; border-left: 5px solid #667eea;'>
                <h2 style='color: #333; margin-top: 0;'>Hello {$adminName},</h2>
                
                <p>We received an admin login request for your {$companyName} account.</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0; border: 2px dashed #667eea;'>
                    <p style='margin: 0 0 10px 0; font-size: 16px; color: #666;'>Your Admin Verification Code:</p>
                    <h1 style='font-size: 36px; color: #667eea; margin: 10px 0; letter-spacing: 8px; font-family: monospace;'>{$otpCode}</h1>
                    <p style='margin: 10px 0 0 0; font-size: 14px; color: #999;'>This code expires in 2 minutes</p>
                </div>
                
                <div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107; margin: 20px 0;'>
                    <h3 style='color: #856404; margin: 0 0 10px 0; font-size: 16px;'>🔒 Security Notice:</h3>
                    <ul style='margin: 0; padding-left: 20px; color: #856404;'>
                        <li>Never share this code with anyone</li>
                        <li>If you didn't request this login, contact IT immediately</li>
                        <li>This code can only be used once</li>
                        <li>Admin access requires additional security verification</li>
                    </ul>
                </div>
                
                <div style='background: #e9ecef; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                    <h4 style='margin: 0 0 10px 0; color: #495057;'>Login Details:</h4>
                    <p style='margin: 5px 0; color: #6c757d;'><strong>Time:</strong> " . Carbon::now()->format('F j, Y g:i A T') . "</p>
                    <p style='margin: 5px 0; color: #6c757d;'><strong>Email:</strong> {$adminUser->email}</p>
                    <p style='margin: 5px 0; color: #6c757d;'><strong>Access Level:</strong> Administrator</p>
                </div>
                
                <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; font-size: 14px; color: #6c757d;'>
                    <p>This is an automated security message from {$companyName} Admin System.</p>
                    <p>Please do not reply to this email. For support, contact your system administrator.</p>
                </div>
            </div>
        </body>
        </html>";
    }

    /**
     * Get plain text version of admin OTP email
     */
    private function getAdminOTPEmailPlainText($adminUser, string $otpCode): string
    {
        $companyName = env('APP_NAME', 'Jetlouge Travels');
        $adminName = $adminUser->first_name;

        return "
ADMIN LOGIN VERIFICATION CODE

Hello {$adminName},

We received an admin login request for your {$companyName} account.

Your admin verification code is: {$otpCode}

This code will expire in 2 minutes.

SECURITY NOTICE:
- Never share this code with anyone
- If you didn't request this login, contact IT immediately
- This code can only be used once
- Admin access requires additional security verification

Login Details:
- Time: " . Carbon::now()->format('F j, Y g:i A T') . "
- Email: {$adminUser->email}
- Access Level: Administrator

This is an automated security message from {$companyName} Admin System.
        ";
    }
}
