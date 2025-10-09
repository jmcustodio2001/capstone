# HR2ESS API Documentation

## Overview

This comprehensive API provides HR management functionality for three core modules:

### 1. Leave Management
- Submit leave requests
- Check leave application status
- Approve/reject leave applications (Admin)
- Get employee leave balances
- Retrieve leave history
- Webhook notifications for status updates
- **HR3 Integration**: Automatic forwarding of leave data to `hr3.jetlougetravels-ph.com`

### 2. Attendance Time Logs
- Retrieve employee attendance logs
- Create new attendance entries
- Update attendance records (Admin)
- Generate attendance summaries
- Filter by date ranges and status

### 3. Claim Reimbursement
- Submit reimbursement claims
- Track claim status
- Approve/reject/process claims (Admin)
- Upload receipt files
- Generate claim summaries

## Base URLs
```
Leave Management:     https://your-domain.com/api/v1/leave
Attendance Logs:      https://your-domain.com/api/v1/attendance  
Claim Reimbursement:  https://your-domain.com/api/v1/claims
```

## Authentication

All API endpoints require authentication via API keys:
- **Regular API Key**: For employee-related operations
- **Admin API Key**: For administrative operations
- **Webhook Secret**: For webhook registrations

### API Keys Configuration
Add these to your `.env` file:
```env
# Leave Management API Keys
LEAVE_API_KEY=your_leave_api_key_here
LEAVE_ADMIN_API_KEY=your_leave_admin_api_key_here
LEAVE_WEBHOOK_SECRET=your_webhook_secret_here

# Attendance Management API Keys  
ATTENDANCE_API_KEY=your_attendance_api_key_here
ATTENDANCE_ADMIN_API_KEY=your_attendance_admin_api_key_here

# Claim Reimbursement API Keys
CLAIM_API_KEY=your_claim_api_key_here
CLAIM_ADMIN_API_KEY=your_claim_admin_api_key_here
```

## Endpoints

---

# LEAVE MANAGEMENT API

### 1. Submit Leave Request

**POST** `/submit`

Submit a new leave request for an employee.

#### Request Body
```json
{
    "employee_id": "EMP001",
    "leave_type": "Vacation",
    "leave_days": 5,
    "start_date": "2025-01-15",
    "end_date": "2025-01-19",
    "reason": "Family vacation",
    "contact_info": "john.doe@email.com",
    "api_key": "your_api_key_here"
}
```

#### Parameters
- `employee_id` (string, required): Employee ID
- `leave_type` (string, required): One of: "Vacation", "Sick", "Emergency"
- `leave_days` (integer, required): Number of days (1-365)
- `start_date` (date, required): Start date (YYYY-MM-DD, today or future)
- `end_date` (date, required): End date (YYYY-MM-DD, >= start_date)
- `reason` (string, required): Reason for leave (max 500 chars)
- `contact_info` (string, optional): Contact information (max 255 chars)
- `api_key` (string, required): Valid API key

#### Response
```json
{
    "success": true,
    "message": "Leave application submitted successfully",
    "data": {
        "leave_id": "LV20250001",
        "application_id": 123,
        "status": "Pending",
        "employee_id": "EMP001",
        "leave_type": "Vacation",
        "days_requested": 5,
        "start_date": "2025-01-15",
        "end_date": "2025-01-19",
        "submitted_at": "2025-01-10T10:30:00.000000Z",
        "remaining_balance": 10
    }
}
```

### 2. Get Leave Status

**GET** `/status/{leaveId}?api_key=your_api_key_here`

Get the current status of a leave application.

#### Parameters
- `leaveId` (string): Leave ID or Application ID
- `api_key` (query parameter, required): Valid API key

#### Response
```json
{
    "success": true,
    "data": {
        "leave_id": "LV20250001",
        "application_id": 123,
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "leave_type": "Vacation",
        "days_requested": 5,
        "start_date": "2025-01-15",
        "end_date": "2025-01-19",
        "reason": "Family vacation",
        "status": "Approved",
        "submitted_at": "2025-01-10T10:30:00.000000Z",
        "approved_by": "HR Manager",
        "approved_date": "2025-01-11T14:20:00.000000Z",
        "remarks": "Approved for vacation"
    }
}
```

### 3. Update Leave Status (Admin Only)

**PUT** `/status/{leaveId}`

Approve or reject a leave application.

#### Request Body
```json
{
    "status": "Approved",
    "approved_by": "HR Manager",
    "remarks": "Approved for vacation",
    "admin_api_key": "your_admin_api_key_here"
}
```

#### Parameters
- `status` (string, required): "Approved" or "Rejected"
- `approved_by` (string, required): Name of approver (max 255 chars)
- `remarks` (string, optional): Additional remarks (max 1000 chars)
- `admin_api_key` (string, required): Valid admin API key

#### Response
```json
{
    "success": true,
    "message": "Leave application Approved successfully",
    "data": {
        "leave_id": "LV20250001",
        "application_id": 123,
        "status": "Approved",
        "approved_by": "HR Manager",
        "approved_date": "2025-01-11T14:20:00.000000Z",
        "remarks": "Approved for vacation",
        "new_balance": 10
    }
}
```

### 4. Get Leave Balance

**GET** `/balance/{employeeId}?api_key=your_api_key_here`

Get current leave balances for an employee.

#### Parameters
- `employeeId` (string): Employee ID
- `api_key` (query parameter, required): Valid API key

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "leave_balances": {
            "Vacation": {
                "total": 15,
                "used": 5,
                "available": 10,
                "percentage": 67
            },
            "Sick": {
                "total": 10,
                "used": 2,
                "available": 8,
                "percentage": 80
            },
            "Emergency": {
                "total": 5,
                "used": 0,
                "available": 5,
                "percentage": 100
            }
        },
        "as_of_date": "2025-01-11T14:20:00.000000Z"
    }
}
```

### 5. Get Leave History

**GET** `/history/{employeeId}?api_key=your_api_key_here&limit=50&status=Approved`

Get leave history for an employee.

#### Parameters
- `employeeId` (string): Employee ID
- `api_key` (query parameter, required): Valid API key
- `limit` (integer, optional): Number of records to return (1-100, default: 50)
- `status` (string, optional): Filter by status ("Pending", "Approved", "Rejected", "Cancelled")

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "leave_history": [
            {
                "leave_id": "LV20250001",
                "application_id": 123,
                "leave_type": "Vacation",
                "days_requested": 5,
                "start_date": "2025-01-15",
                "end_date": "2025-01-19",
                "reason": "Family vacation",
                "status": "Approved",
                "submitted_at": "2025-01-10T10:30:00.000000Z",
                "approved_by": "HR Manager",
                "approved_date": "2025-01-11T14:20:00.000000Z",
                "remarks": "Approved for vacation"
            }
        ],
        "total_records": 1,
        "limit_applied": 50
    }
}
```

### 6. Register Webhook

**POST** `/webhook/status-update`

Register a webhook URL to receive notifications when leave status changes.

### 7. Test HR3 Connection

**POST** `/test-hr3-connection`

Test the connection to the HR3 external system.

#### Request Body
```json
{
    "api_key": "your_api_key_here"
}
```

#### Parameters
- `api_key` (string, required): Valid API key

#### Response
```json
{
    "success": true,
    "message": "Connection to HR3 API successful",
    "hr3_connection_test": {
        "success": true,
        "status_code": 200,
        "response": {
            "message": "Test received successfully",
            "timestamp": "2025-01-11T14:20:00.000000Z"
        }
    },
    "timestamp": "2025-01-11T14:20:00.000000Z"
}
```

## HR3 Integration

### Automatic Data Forwarding

When leave requests are submitted or status updates occur, the system automatically forwards data to:
- **HR3 Endpoint**: `https://hr3.jetlougetravels-ph.com/api/leave-requests/receive`
- **Status Updates**: `https://hr3.jetlougetravels-ph.com/api/leave-requests/status-update`

### HR3 Data Format

#### Leave Request Data Sent to HR3
```json
{
    "employee_id": "EMP001",
    "leave_id": "LV20250001",
    "employee_name": "John Doe",
    "employee_email": "john.doe@company.com",
    "leave_type": "Vacation",
    "leave_days": 5,
    "start_date": "2025-01-15",
    "end_date": "2025-01-19",
    "reason": "Family vacation",
    "contact_info": "john.doe@email.com",
    "status": "Pending",
    "applied_date": "2025-01-10T10:30:00.000000Z",
    "approved_by": null,
    "approved_date": null,
    "remarks": null,
    "source_system": "HR2ESS",
    "timestamp": "2025-01-10T10:30:00.000000Z"
}
```

#### Status Update Data Sent to HR3
```json
{
    "employee_id": "EMP001",
    "leave_id": "LV20250001",
    "old_status": "Pending",
    "new_status": "Approved",
    "approved_by": "HR Manager",
    "approved_date": "2025-01-11T14:20:00.000000Z",
    "remarks": "Approved for vacation",
    "source_system": "HR2ESS",
    "timestamp": "2025-01-11T14:20:00.000000Z"
}
```

### HR3 Integration Response

API responses now include HR3 integration status:

#### Leave Submission Response with HR3
```json
{
    "success": true,
    "message": "Leave application submitted successfully and sent to HR3 system",
    "data": {
        "leave_id": "LV20250001",
        "application_id": 123,
        "status": "Pending",
        "employee_id": "EMP001",
        "leave_type": "Vacation",
        "days_requested": 5,
        "start_date": "2025-01-15",
        "end_date": "2025-01-19",
        "submitted_at": "2025-01-10T10:30:00.000000Z",
        "remaining_balance": 10,
        "hr3_integration": {
            "sent_to_hr3": true,
            "hr3_message": "Leave request successfully sent to HR3 system",
            "hr3_response": {
                "id": "HR3-12345",
                "status": "received",
                "message": "Leave request processed successfully"
            }
        }
    }
}
```

#### Status Update Response with HR3
```json
{
    "success": true,
    "message": "Leave application Approved successfully and status sent to HR3 system",
    "data": {
        "leave_id": "LV20250001",
        "application_id": 123,
        "status": "Approved",
        "approved_by": "HR Manager",
        "approved_date": "2025-01-11T14:20:00.000000Z",
        "remarks": "Approved for vacation",
        "new_balance": 10,
        "hr3_integration": {
            "status_sent_to_hr3": true,
            "hr3_message": "Status update successfully sent to HR3 system"
        }
    }
}
```

### HR3 Integration Configuration

The HR3 integration can be configured with environment variables:

```env
# HR3 API Configuration (if authentication is required)
HR3_API_TOKEN=your_hr3_api_token
HR3_API_KEY=your_hr3_api_key
HR3_BASE_URL=https://hr3.jetlougetravels-ph.com
```

### Error Handling

If HR3 integration fails, the local leave request processing continues normally, but the response will indicate the integration failure:

```json
{
    "success": true,
    "message": "Leave application submitted successfully (HR3 integration failed)",
    "data": {
        "...": "...",
        "hr3_integration": {
            "sent_to_hr3": false,
            "hr3_message": "Failed to send leave request to HR3 system",
            "error": "Connection timeout to HR3 API"
        }
    }
}
```

### 6. Register Webhook (Updated)

**POST** `/webhook/status-update`

Register a webhook URL to receive notifications when leave status changes.

#### Request Body
```json
{
    "webhook_secret": "your_webhook_secret_here",
    "leave_id": "LV20250001",
    "callback_url": "https://your-system.com/webhook/leave-status"
}
```

#### Parameters
- `webhook_secret` (string, required): Valid webhook secret
- `leave_id` (string, required): Leave ID to monitor
- `callback_url` (string, required): Valid URL to receive notifications

#### Response
```json
{
    "success": true,
    "message": "Webhook registered successfully",
    "data": {
        "leave_id": "LV20250001",
        "callback_url": "https://your-system.com/webhook/leave-status",
        "registered_at": "2025-01-11T14:20:00.000000Z"
    }
}
```

## Webhook Notifications

When a leave status changes, registered webhooks will receive a POST request with the following payload:

```json
{
    "leave_id": "LV20250001",
    "employee_id": "EMP001",
    "status": "Approved",
    "leave_type": "Vacation",
    "days_requested": 5,
    "start_date": "2025-01-15",
    "end_date": "2025-01-19",
    "approved_by": "HR Manager",
    "approved_date": "2025-01-11T14:20:00.000000Z",
    "timestamp": "2025-01-11T14:20:00.000000Z"
}
```

## Error Responses

All endpoints return consistent error responses:

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "employee_id": ["The employee id field is required."],
        "leave_type": ["The selected leave type is invalid."]
    }
}
```

### Authentication Error (401)
```json
{
    "success": false,
    "message": "Invalid API key"
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Employee not found"
}
```

### Business Logic Error (400)
```json
{
    "success": false,
    "message": "Insufficient leave balance. Available: 3 days",
    "available_balance": 3,
    "requested_days": 5
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error occurred while processing leave request",
    "error_code": "LEAVE_SUBMISSION_ERROR"
}
```

## Usage Examples

### Example 1: Submit Leave Request
```bash
curl -X POST https://your-domain.com/api/v1/leave/submit \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": "EMP001",
    "leave_type": "Vacation",
    "leave_days": 3,
    "start_date": "2025-01-20",
    "end_date": "2025-01-22",
    "reason": "Personal matters",
    "api_key": "your_api_key_here"
  }'
```

### Example 2: Check Leave Status
```bash
curl -X GET "https://your-domain.com/api/v1/leave/status/LV20250001?api_key=your_api_key_here"
```

### Example 3: Approve Leave (Admin)
```bash
curl -X PUT https://your-domain.com/api/v1/leave/status/LV20250001 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Approved",
    "approved_by": "HR Manager",
    "remarks": "Approved",
    "admin_api_key": "your_admin_api_key_here"
  }'
```

### Example 4: Get Leave Balance
```bash
curl -X GET "https://your-domain.com/api/v1/leave/balance/EMP001?api_key=your_api_key_here"
```

## Integration Workflow

1. **Submit Leave Request**: External system submits leave request via API
2. **Monitor Status**: System polls status endpoint or registers webhook
3. **Admin Review**: HR/Admin reviews and approves/rejects via admin API
4. **Automatic Deduction**: System automatically deducts from balance on approval
5. **Notification**: Webhook notifies external system of status change
6. **Balance Update**: External system can query updated balance

## Rate Limiting

- 100 requests per minute per API key
- 1000 requests per hour per API key
- Rate limit headers included in responses

## Security Considerations

1. **API Keys**: Store securely, rotate regularly
2. **HTTPS**: All API calls must use HTTPS
3. **Input Validation**: All inputs are validated server-side
4. **Logging**: All API calls are logged for audit purposes
5. **Error Handling**: Sensitive information is not exposed in error messages

---

# ATTENDANCE TIME LOGS API

## Base URL: `/api/v1/attendance`

### 1. Get Attendance Logs

**GET** `/logs/{employeeId}?api_key=your_api_key_here&start_date=2025-01-01&end_date=2025-01-31&status=Present&limit=50`

Get attendance logs for an employee with optional filtering.

#### Parameters
- `employeeId` (string): Employee ID
- `api_key` (query parameter, required): Valid API key
- `start_date` (string, optional): Start date filter (YYYY-MM-DD)
- `end_date` (string, optional): End date filter (YYYY-MM-DD)
- `status` (string, optional): Status filter ("Present", "Absent", "Late", "Early Departure", "Overtime")
- `limit` (integer, optional): Number of records (1-100, default: 50)

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "attendance_logs": [
            {
                "id": 123,
                "employee_id": "EMP001",
                "log_date": "2025-01-15",
                "time_in": "08:00:00",
                "time_out": "17:00:00",
                "break_start_time": "12:00:00",
                "break_end_time": "13:00:00",
                "total_hours": 8.0,
                "overtime_hours": 0.0,
                "hours_worked": 8.0,
                "status": "Present",
                "location": "Main Office",
                "ip_address": "192.168.1.100",
                "notes": "Regular workday",
                "created_at": "2025-01-15T08:00:00.000000Z",
                "updated_at": "2025-01-15T17:00:00.000000Z"
            }
        ],
        "total_records": 1,
        "limit_applied": 50,
        "filters_applied": {
            "start_date": "2025-01-01",
            "end_date": "2025-01-31",
            "status": "Present"
        }
    }
}
```

### 2. Create Attendance Log

**POST** `/logs`

Create a new attendance log entry.

#### Request Body
```json
{
    "employee_id": "EMP001",
    "log_date": "2025-01-15",
    "time_in": "08:00:00",
    "time_out": "17:00:00",
    "break_start_time": "12:00:00",
    "break_end_time": "13:00:00",
    "total_hours": 8.0,
    "overtime_hours": 0.0,
    "status": "Present",
    "location": "Main Office",
    "ip_address": "192.168.1.100",
    "notes": "Regular workday",
    "api_key": "your_api_key_here"
}
```

#### Parameters
- `employee_id` (string, required): Employee ID
- `log_date` (date, required): Log date (YYYY-MM-DD)
- `time_in` (time, optional): Clock in time (HH:MM:SS)
- `time_out` (time, optional): Clock out time (HH:MM:SS)
- `break_start_time` (time, optional): Break start time (HH:MM:SS)
- `break_end_time` (time, optional): Break end time (HH:MM:SS)
- `total_hours` (decimal, optional): Total hours worked
- `overtime_hours` (decimal, optional): Overtime hours
- `status` (string, required): Status ("Present", "Absent", "Late", "Early Departure", "Overtime")
- `location` (string, optional): Work location
- `ip_address` (string, optional): IP address
- `notes` (string, optional): Additional notes
- `api_key` (string, required): Valid API key

#### Response
```json
{
    "success": true,
    "message": "Attendance log created successfully",
    "data": {
        "id": 123,
        "employee_id": "EMP001",
        "log_date": "2025-01-15",
        "time_in": "08:00:00",
        "time_out": "17:00:00",
        "total_hours": 8.0,
        "status": "Present",
        "created_at": "2025-01-15T08:00:00.000000Z"
    }
}
```

### 3. Update Attendance Log (Admin Only)

**PUT** `/logs/{logId}`

Update an existing attendance log entry.

#### Request Body
```json
{
    "time_in": "08:30:00",
    "time_out": "17:30:00",
    "status": "Late",
    "notes": "Traffic delay",
    "admin_api_key": "your_admin_api_key_here"
}
```

### 4. Get Attendance Summary

**GET** `/summary/{employeeId}?api_key=your_api_key_here&start_date=2025-01-01&end_date=2025-01-31`

Get attendance summary statistics for an employee.

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "period": {
            "start_date": "2025-01-01",
            "end_date": "2025-01-31"
        },
        "summary": {
            "total_days": 22,
            "present_days": 20,
            "absent_days": 1,
            "late_days": 1,
            "early_departure_days": 0,
            "overtime_days": 3,
            "total_hours_worked": 176.0,
            "total_overtime_hours": 12.0,
            "average_hours_per_day": 8.0
        },
        "generated_at": "2025-01-31T23:59:59.000000Z"
    }
}
```

---

# CLAIM REIMBURSEMENT API

## Base URL: `/api/v1/claims`

### 1. Get Claim Reimbursements

**GET** `/employee/{employeeId}?api_key=your_api_key_here&status=Pending&claim_type=Travel&start_date=2025-01-01&end_date=2025-01-31&limit=50`

Get claim reimbursements for an employee with optional filtering.

#### Parameters
- `employeeId` (string): Employee ID
- `api_key` (query parameter, required): Valid API key
- `status` (string, optional): Status filter ("Pending", "Approved", "Rejected", "Processed")
- `claim_type` (string, optional): Claim type filter
- `start_date` (string, optional): Start date filter (YYYY-MM-DD)
- `end_date` (string, optional): End date filter (YYYY-MM-DD)
- `limit` (integer, optional): Number of records (1-100, default: 50)

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "claims": [
            {
                "id": 123,
                "claim_id": "CR20250001",
                "employee_id": "EMP001",
                "claim_type": "Travel Expense",
                "description": "Business trip to Manila",
                "amount": 5000.00,
                "formatted_amount": "₱5,000.00",
                "claim_date": "2025-01-15",
                "status": "Pending",
                "approved_by": null,
                "approved_date": null,
                "rejected_reason": null,
                "processed_date": null,
                "payment_method": null,
                "reference_number": null,
                "remarks": null,
                "receipt_file": "receipt_1642234567_invoice.pdf",
                "has_receipt": true,
                "can_be_edited": true,
                "can_be_cancelled": true,
                "created_at": "2025-01-15T10:30:00.000000Z",
                "updated_at": "2025-01-15T10:30:00.000000Z"
            }
        ],
        "total_records": 1,
        "limit_applied": 50,
        "filters_applied": {
            "status": "Pending",
            "claim_type": "Travel",
            "start_date": "2025-01-01",
            "end_date": "2025-01-31"
        }
    }
}
```

### 2. Create Claim Reimbursement

**POST** `/submit`

Create a new claim reimbursement with optional file upload.

#### Request Body (multipart/form-data)
```
employee_id: EMP001
claim_type: Travel Expense
description: Business trip to Manila
amount: 5000.00
claim_date: 2025-01-15
receipt_file: [FILE] (optional)
payment_method: Bank Transfer
remarks: Urgent processing needed
api_key: your_api_key_here
```

#### Parameters
- `employee_id` (string, required): Employee ID
- `claim_type` (string, required): Type of claim
- `description` (string, required): Claim description (max 1000 chars)
- `amount` (decimal, required): Claim amount (0.01-999999.99)
- `claim_date` (date, required): Claim date (YYYY-MM-DD, today or earlier)
- `receipt_file` (file, optional): Receipt file (PDF, JPG, PNG, DOC, DOCX, max 5MB)
- `payment_method` (string, optional): Preferred payment method
- `remarks` (string, optional): Additional remarks
- `api_key` (string, required): Valid API key

#### Response
```json
{
    "success": true,
    "message": "Claim reimbursement created successfully",
    "data": {
        "id": 123,
        "claim_id": "CR20250001",
        "employee_id": "EMP001",
        "claim_type": "Travel Expense",
        "description": "Business trip to Manila",
        "amount": 5000.00,
        "formatted_amount": "₱5,000.00",
        "claim_date": "2025-01-15",
        "status": "Pending",
        "has_receipt": true,
        "created_at": "2025-01-15T10:30:00.000000Z"
    }
}
```

### 3. Update Claim Status (Admin Only)

**PUT** `/status/{claimId}`

Update the status of a claim reimbursement (approve, reject, or process).

#### Request Body
```json
{
    "status": "Approved",
    "approved_by": "Finance Manager",
    "rejected_reason": null,
    "payment_method": "Bank Transfer",
    "reference_number": "TXN123456789",
    "remarks": "Approved for processing",
    "admin_api_key": "your_admin_api_key_here"
}
```

#### Parameters
- `status` (string, required): "Approved", "Rejected", or "Processed"
- `approved_by` (string, required): Name of approver
- `rejected_reason` (string, optional): Reason for rejection (required if status is "Rejected")
- `payment_method` (string, optional): Payment method (for "Processed" status)
- `reference_number` (string, optional): Payment reference number (for "Processed" status)
- `remarks` (string, optional): Additional remarks
- `admin_api_key` (string, required): Valid admin API key

### 4. Get Claim Details

**GET** `/details/{claimId}?api_key=your_api_key_here`

Get detailed information about a specific claim.

#### Response
```json
{
    "success": true,
    "data": {
        "id": 123,
        "claim_id": "CR20250001",
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "claim_type": "Travel Expense",
        "description": "Business trip to Manila",
        "amount": 5000.00,
        "formatted_amount": "₱5,000.00",
        "claim_date": "2025-01-15",
        "status": "Approved",
        "approved_by": "Finance Manager",
        "approved_date": "2025-01-16T14:30:00.000000Z",
        "rejected_reason": null,
        "processed_date": null,
        "payment_method": "Bank Transfer",
        "reference_number": null,
        "remarks": "Approved for processing",
        "receipt_file": "receipt_1642234567_invoice.pdf",
        "has_receipt": true,
        "receipt_url": "/storage/receipts/receipt_1642234567_invoice.pdf",
        "can_be_edited": false,
        "can_be_cancelled": true,
        "created_at": "2025-01-15T10:30:00.000000Z",
        "updated_at": "2025-01-16T14:30:00.000000Z"
    }
}
```

### 5. Get Claim Summary

**GET** `/summary/{employeeId}?api_key=your_api_key_here&start_date=2025-01-01&end_date=2025-12-31`

Get claim reimbursement summary statistics for an employee.

#### Response
```json
{
    "success": true,
    "data": {
        "employee_id": "EMP001",
        "employee_name": "John Doe",
        "period": {
            "start_date": "2025-01-01",
            "end_date": "2025-12-31"
        },
        "summary": {
            "total_claims": 15,
            "pending_claims": 2,
            "approved_claims": 10,
            "rejected_claims": 1,
            "processed_claims": 2,
            "total_amount_claimed": 75000.00,
            "total_amount_approved": 65000.00,
            "total_amount_processed": 15000.00,
            "average_claim_amount": 5000.00,
            "formatted_total_claimed": "₱75,000.00",
            "formatted_total_approved": "₱65,000.00",
            "formatted_total_processed": "₱15,000.00",
            "formatted_average_amount": "₱5,000.00"
        },
        "generated_at": "2025-01-31T23:59:59.000000Z"
    }
}
```

---

## Usage Examples

### Attendance API Examples

#### Create Attendance Log
```bash
curl -X POST https://your-domain.com/api/v1/attendance/logs \
  -H "Content-Type: application/json" \
  -d '{
    "employee_id": "EMP001",
    "log_date": "2025-01-15",
    "time_in": "08:00:00",
    "time_out": "17:00:00",
    "status": "Present",
    "location": "Main Office",
    "api_key": "your_api_key_here"
  }'
```

#### Get Attendance Logs
```bash
curl -X GET "https://your-domain.com/api/v1/attendance/logs/EMP001?api_key=your_api_key_here&start_date=2025-01-01&end_date=2025-01-31"
```

### Claims API Examples

#### Submit Claim with Receipt
```bash
curl -X POST https://your-domain.com/api/v1/claims/submit \
  -F "employee_id=EMP001" \
  -F "claim_type=Travel Expense" \
  -F "description=Business trip to Manila" \
  -F "amount=5000.00" \
  -F "claim_date=2025-01-15" \
  -F "receipt_file=@receipt.pdf" \
  -F "api_key=your_api_key_here"
```

#### Approve Claim (Admin)
```bash
curl -X PUT https://your-domain.com/api/v1/claims/status/CR20250001 \
  -H "Content-Type: application/json" \
  -d '{
    "status": "Approved",
    "approved_by": "Finance Manager",
    "remarks": "Approved for processing",
    "admin_api_key": "your_admin_api_key_here"
  }'
```

## Support

For API support, contact: support@hr2ess.com
