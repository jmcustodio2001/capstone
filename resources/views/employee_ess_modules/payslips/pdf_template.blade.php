<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - PS123456</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #64748b;
            --accent: #10b981;
            --danger: #ef4444;
            --light: #f8fafc;
            --dark: #1e293b;
            --border: #e2e8f0;
            --shadow: rgba(0, 0, 0, 0.05);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: #f1f5f9;
            color: #334155;
            line-height: 1.5;
            padding: 20px;
            font-size: 14px;
        }
        
        .payslip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px var(--shadow);
            overflow: hidden;
        }
        
        .payslip-header {
            background: linear-gradient(120deg, var(--primary) 0%, #1d4ed8 100%);
            color: white;
            padding: 25px 30px;
            text-align: center;
        }
        
        .company-name {
            font-size: 26px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .company-address {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 20px;
        }
        
        .document-title {
            font-size: 32px;
            font-weight: 800;
            margin: 15px 0;
            letter-spacing: 1px;
        }
        
        .payslip-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(255, 255, 255, 0.1);
            padding: 12px 20px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .payslip-id {
            font-size: 16px;
            font-weight: 600;
        }
        
        .payment-date {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }
        
        .payslip-content {
            padding: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--dark);
            margin: 25px 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .employee-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: var(--secondary);
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .pay-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin: 30px 0;
        }
        
        @media (max-width: 768px) {
            .pay-details {
                grid-template-columns: 1fr;
            }
        }
        
        .earnings, .deductions {
            border: 1px solid var(--border);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px var(--shadow);
        }
        
        .card-header {
            background: linear-gradient(120deg, var(--accent) 0%, #059669 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .deductions .card-header {
            background: linear-gradient(120deg, var(--danger) 0%, #dc2626 100%);
        }
        
        .card-body {
            padding: 0;
        }
        
        .pay-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 20px;
            border-bottom: 1px solid var(--border);
        }
        
        .pay-item:last-child {
            border-bottom: none;
        }
        
        .pay-item.total {
            background-color: #f1f5f9;
            font-weight: 700;
            font-size: 15px;
        }
        
        .amount {
            font-weight: 600;
            color: var(--dark);
        }
        
        .net-pay-section {
            text-align: center;
            padding: 25px;
            background: linear-gradient(120deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
            border-radius: 10px;
            margin: 30px 0;
        }
        
        .net-pay-label {
            font-size: 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .net-pay-amount {
            font-size: 36px;
            font-weight: 800;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid var(--border);
            color: var(--secondary);
            font-size: 12px;
        }
        
        .footer p {
            margin-bottom: 5px;
        }
        
        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .print-button:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4);
        }
        
        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .payslip-container {
                box-shadow: none;
                border-radius: 0;
            }
            
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <button class="print-button" onclick="window.print()">
        <i class="fas fa-print"></i> Print Payslip
    </button>
    
    <div class="payslip-container">
        <div class="payslip-header">
            <div class="company-name">{{ $company['name'] ?? 'Jetlouge Travels' }}</div>
            <div class="company-address">{{ $company['address'] ?? '123 Business Avenue, City, Country' }}</div>
            <div class="document-title">PAYSLIP</div>
            
            <div class="payslip-info">
                <div class="payslip-id">Payslip #{{ $payslip->payslip_id ?? 'PS' . $payslip->id }}</div>
                <div class="payment-date">
                    <i class="fas fa-calendar-alt"></i>
                    Payment Date: {{ \Carbon\Carbon::parse($payslip->release_date ?? $payslip->created_at)->format('M d, Y') }}
                </div>
            </div>
        </div>
        
        <div class="payslip-content">
            <div class="section-title">
                <i class="fas fa-user-circle"></i> Employee Information
            </div>
            
            <div class="employee-info">
                <div class="info-item">
                    <span class="info-label">EMPLOYEE ID</span>
                    <span class="info-value">{{ $employee->employee_id ?? 'N/A' }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">EMPLOYEE NAME</span>
                    <span class="info-value">{{ $employee->first_name ?? '' }} {{ $employee->last_name ?? '' }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">DEPARTMENT</span>
                    <span class="info-value">{{ $employee->department ?? 'N/A' }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">POSITION</span>
                    <span class="info-value">{{ $employee->position ?? 'N/A' }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">PAY PERIOD</span>
                    <span class="info-value">{{ $payslip->pay_period ?? 'N/A' }}</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">PAYMENT METHOD</span>
                    <span class="info-value">Direct Deposit</span>
                </div>
            </div>
            
            <div class="pay-details">
                <div class="earnings">
                    <div class="card-header">
                        <i class="fas fa-money-bill-wave"></i> EARNINGS
                    </div>
                    <div class="card-body">
                        <div class="pay-item">
                            <span>Basic Salary</span>
                            <span class="amount">₱{{ number_format($payslip->basic_pay ?? 0, 2) }}</span>
                        </div>
                        @if($payslip->overtime_pay ?? 0 > 0)
                        <div class="pay-item">
                            <span>Overtime Pay</span>
                            <span class="amount">₱{{ number_format($payslip->overtime_pay, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->allowances ?? 0 > 0)
                        <div class="pay-item">
                            <span>Allowances</span>
                            <span class="amount">₱{{ number_format($payslip->allowances, 2) }}</span>
                        </div>
                        @endif
                        <div class="pay-item total">
                            <span>TOTAL EARNINGS</span>
                            <span class="amount">₱{{ number_format($payslip->gross_pay ?? (($payslip->basic_pay ?? 0) + ($payslip->overtime_pay ?? 0) + ($payslip->allowances ?? 0)), 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="deductions">
                    <div class="card-header">
                        <i class="fas fa-minus-circle"></i> DEDUCTIONS
                    </div>
                    <div class="card-body">
                        @if($payslip->tax_deduction ?? 0 > 0)
                        <div class="pay-item">
                            <span>Tax</span>
                            <span class="amount">₱{{ number_format($payslip->tax_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->sss_deduction ?? 0 > 0)
                        <div class="pay-item">
                            <span>SSS</span>
                            <span class="amount">₱{{ number_format($payslip->sss_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->philhealth_deduction ?? 0 > 0)
                        <div class="pay-item">
                            <span>PhilHealth</span>
                            <span class="amount">₱{{ number_format($payslip->philhealth_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->pagibig_deduction ?? 0 > 0)
                        <div class="pay-item">
                            <span>Pag-IBIG</span>
                            <span class="amount">₱{{ number_format($payslip->pagibig_deduction, 2) }}</span>
                        </div>
                        @endif
                        @if($payslip->other_deductions ?? 0 > 0)
                        <div class="pay-item">
                            <span>Other Deductions</span>
                            <span class="amount">₱{{ number_format($payslip->other_deductions, 2) }}</span>
                        </div>
                        @endif
                        <div class="pay-item total">
                            <span>TOTAL DEDUCTIONS</span>
                            <span class="amount">₱{{ number_format($payslip->total_deductions ?? (($payslip->tax_deduction ?? 0) + ($payslip->sss_deduction ?? 0) + ($payslip->philhealth_deduction ?? 0) + ($payslip->pagibig_deduction ?? 0) + ($payslip->other_deductions ?? 0)), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="net-pay-section">
                <div class="net-pay-label">
                    <i class="fas fa-wallet"></i> NET PAY
                </div>
                <div class="net-pay-amount">
                    ₱{{ number_format($payslip->net_pay ?? 0, 2) }}
                </div>
            </div>
            
            <div class="section-title">
                <i class="fas fa-info-circle"></i> Additional Information
            </div>
            
            <div class="employee-info">
                <div class="info-item">
                    <span class="info-label">BANK NAME</span>
                    <span class="info-value">Example Bank</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">ACCOUNT NUMBER</span>
                    <span class="info-value">XXXX-XXXX-1234</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">PAYMENT METHOD</span>
                    <span class="info-value">Direct Deposit</span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">WORKING DAYS</span>
                    <span class="info-value">11 days</span>
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>This is a computer-generated payslip. No signature required.</strong></p>
            <p>Generated on Jun 15, 2023 03:45 PM</p>
            <p>For any queries regarding this payslip, please contact HR Department.</p>
        </div>
    </div>

    <script>
        // Optional: Add any interactive functionality here
        document.addEventListener('DOMContentLoaded', function() {
            // You could add functionality to toggle details or calculate values
        });
    </script>
</body>
</html>