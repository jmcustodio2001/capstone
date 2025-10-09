# HR2ESS API Keys Configuration

## Generated API Keys

### Regular API Keys (Employee Operations)
```env
# Leave Management API Key
LEAVE_API_KEY=hr2ess_leave_api_2025_k7m9n2p4q6r8s1t3

# Attendance Management API Key  
ATTENDANCE_API_KEY=hr2ess_attend_api_2025_x5z8a2c4e6g9h1j3

# Claim Reimbursement API Key
CLAIM_API_KEY=hr2ess_claim_api_2025_b3d6f8h1k4m7n9p2
```

### Admin API Keys (Administrative Operations)
```env
# Leave Management Admin API Key
LEAVE_ADMIN_API_KEY=hr2ess_leave_admin_2025_w4y7z9a2c5e8f1g4

# Attendance Management Admin API Key
ATTENDANCE_ADMIN_API_KEY=hr2ess_attend_admin_2025_r6t9u2v5x8z1a4b7

# Claim Reimbursement Admin API Key
CLAIM_ADMIN_API_KEY=hr2ess_claim_admin_2025_l3m6n9p2q5r8s1t4
```

### Webhook Secret Key
```env
# Webhook Secret for Leave Status Updates
LEAVE_WEBHOOK_SECRET=hr2ess_webhook_secret_2025_j7k9l2m5n8p1q4r7
```

## Environment Configuration

Add these to your `.env` file:

```env
# Leave Management API Keys
LEAVE_API_KEY=hr2ess_leave_api_2025_k7m9n2p4q6r8s1t3
LEAVE_ADMIN_API_KEY=hr2ess_leave_admin_2025_w4y7z9a2c5e8f1g4
LEAVE_WEBHOOK_SECRET=hr2ess_webhook_secret_2025_j7k9l2m5n8p1q4r7

# Attendance Management API Keys  
ATTENDANCE_API_KEY=hr2ess_attend_api_2025_x5z8a2c4e6g9h1j3
ATTENDANCE_ADMIN_API_KEY=hr2ess_attend_admin_2025_r6t9u2v5x8z1a4b7

# Claim Reimbursement API Keys
CLAIM_API_KEY=hr2ess_claim_api_2025_b3d6f8h1k4m7n9p2
CLAIM_ADMIN_API_KEY=hr2ess_claim_admin_2025_l3m6n9p2q5r8s1t4
```

## API Key Usage

### For HR3 Integration:
- **Regular Operations**: Use the regular API keys for standard employee operations
- **Administrative Operations**: Use admin API keys for approval/rejection workflows
- **Webhook Notifications**: Use webhook secret for status update notifications

### Security Notes:
1. **Keep these keys secure** - Never commit them to version control
2. **Rotate regularly** - Change keys periodically for security
3. **Use HTTPS only** - All API calls must use secure connections
4. **Monitor usage** - Track API key usage for security auditing
5. **Separate environments** - Use different keys for development/staging/production

### Testing Configuration:
Update your `test_api_endpoints.php` file with these keys:

```php
$apiKeys = [
    'leave' => 'hr2ess_leave_api_2025_k7m9n2p4q6r8s1t3',
    'attendance' => 'hr2ess_attend_api_2025_x5z8a2c4e6g9h1j3', 
    'claims' => 'hr2ess_claim_api_2025_b3d6f8h1k4m7n9p2',
    'admin' => 'hr2ess_leave_admin_2025_w4y7z9a2c5e8f1g4'
];
```

## Rate Limiting
- **100 requests per minute** per API key
- **1000 requests per hour** per API key
- Rate limit headers included in responses

## Key Format
- **Prefix**: `hr2ess_` (identifies the system)
- **Module**: `leave_`, `attend_`, `claim_` (identifies the module)
- **Type**: `api_` or `admin_` (identifies permission level)
- **Year**: `2025_` (for key rotation tracking)
- **Random**: `32-character alphanumeric` (for security)

Generated on: 2025-01-09 00:25:37 +08:00
