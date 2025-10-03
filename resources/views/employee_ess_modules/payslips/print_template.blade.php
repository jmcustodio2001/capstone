<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Payslip - {{ $payslip->payslip_id ?? 'PS' . $payslip->id }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #3498db;
            --accent: #27ae60;
            --danger: #e74c3c;
            --light: #f8f9fa;
            --dark: #343a40;
            --border: #dee2e6;
            --shadow: rgba(0, 0, 0, 0.1);
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7f9;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .payslip-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px var(--shadow);
            overflow: hidden;
        }
        
        .payslip-header {
            background: linear-gradient(135deg, var(--primary) 0%, #1a2530 100%);
            color: white;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .company-info h1 {
            font-size: 28px;
            margin-bottom: 5px;
            font-weight: 700;
        }
        
        .company-info p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .payslip-meta {
            text-align: right;
        }
        
        .payslip-id {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .payment-date {
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .payslip-body {
            padding: 30px;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid var(--secondary);
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 10px;
            color: var(--secondary);
        }
        
        .employee-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .info-card {
            background: var(--light);
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--secondary);
        }
        
        .info-card h3 {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 5px;
        }
        
        .info-card p {
            font-size: 16px;
            font-weight: 500;
            color: var(--dark);
        }
        
        .pay-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 768px) {
            .pay-details {
                grid-template-columns: 1fr;
            }
        }
        
        .earnings, .deductions {
            background: var(--light);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--secondary) 0%, #2980b9 100%);
            color: white;
            padding: 15px 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        
        .deductions .card-header {
            background: linear-gradient(135deg, var(--danger) 0%, #c0392b 100%);
        }
        
        .card-header i {
            margin-right: 10px;
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
            background-color: rgba(52, 152, 219, 0.1);
            font-weight: 700;
            font-size: 16px;
        }
        
        .deductions .pay-item.total {
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .net-pay-section {
            text-align: center;
            padding: 25px;
            background: linear-gradient(135deg, var(--accent) 0%, #219653 100%);
            color: white;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .net-pay-label {
            font-size: 18px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .net-pay-label i {
            margin-right: 10px;
        }
        
        .net-pay-amount {
            font-size: 36px;
            font-weight: 700;
        }
        
        .pay-period {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 8px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid var(--border);
            color: #6c757d;
            font-size: 13px;
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
            padding: 15px 25px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(44, 62, 80, 0.3);
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            z-index: 100;
        }
        
        .print-button:hover {
            background: #1a2530;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(44, 62, 80, 0.4);
        }
        
        .print-button i {
            margin-right: 8px;
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
            
            .pay-details {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 20px;
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
            <div class="company-info">
                <h1>{{ $company['name'] }}</h1>
                <p>{{ $company['address'] }}</p>
            </div>
            <div class="payslip-meta">
                <div class="payslip-id">Payslip #{{ $payslip->payslip_id ?? 'PS' . $payslip->id }}</div>
                <div class="payment-date">
                    <i class="fas fa-calendar-alt"></i>
                    Payment Date: 
                    @if($payslip->release_date)
                        {{ date('M j, Y', strtotime($payslip->release_date)) }}
                    @elseif($payslip->period_end)
                        {{ date('M j, Y', strtotime($payslip->period_end . '+5 days')) }}
                    @else
                        N/A
                    @endif
                </div>
            </div>
        </div>
        
        <div class="payslip-body">
            <div class="section-title">
                <i class="fas fa-user"></i> Employee Information
            </div>
            
            <div class="employee-info">
                <div class="info-card">
                    <h3>EMPLOYEE ID</h3>
                    <p>{{ $payslip->employee_id }}</p>
                </div>
                
                <div class="info-card">
                    <h3>EMPLOYEE NAME</h3>
                    <p>{{ $employee->first_name ?? 'N/A' }} {{ $employee->last_name ?? '' }}</p>
                </div>
                
                <div class="info-card">
                    <h3>DEPARTMENT</h3>
                    <p>{{ $employee->department ?? 'N/A' }}</p>
                </div>
                
                <div class="info-card">
                    <h3>POSITION</h3>
                    <p>{{ $employee->position ?? 'N/A' }}</p>
                </div>
            </div>
            
            <div class="pay-period-card info-card">
                <h3>PAY PERIOD</h3>
                <p>
                    @if($payslip->period_start && $payslip->period_end)
                        {{ date('M j, Y', strtotime($payslip->period_start)) }} - {{ date('M j, Y', strtotime($payslip->period_end)) }}
                    @else
                        {{ $payslip->pay_period }}
                    @endif
                </p>
            </div>
            
            <div class="pay-details">
                <div class="earnings">
                    <div class="card-header">
                        <i class="fas fa-arrow-up"></i> EARNINGS
                    </div>
                    <div class="card-body">
                        <div class="pay-item">
                            <span>Basic Salary</span>
                            <span>₱{{ number_format($payslip->basic_pay ?? 0, 2) }}</span>
                        </div>
                        <div class="pay-item">
                            <span>Overtime Pay</span>
                            <span>₱{{ number_format($payslip->overtime_pay ?? 0, 2) }}</span>
                        </div>
                        <div class="pay-item">
                            <span>Allowances</span>
                            <span>₱{{ number_format($payslip->allowances ?? 0, 2) }}</span>
                        </div>
                        <div class="pay-item total">
                            <span>TOTAL EARNINGS</span>
                            <span>₱{{ number_format($payslip->gross_pay ?? (($payslip->basic_pay ?? 0) + ($payslip->overtime_pay ?? 0) + ($payslip->allowances ?? 0)), 2) }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="deductions">
                    <div class="card-header">
                        <i class="fas fa-arrow-down"></i> DEDUCTIONS
                    </div>
                    <div class="card-body">
                        <div class="pay-item">
                            <span>Tax</span>
                            <span>₱{{ number_format($payslip->tax_deduction ?? 0, 2) }}</span>
                        </div>
                        <div class="pay-item">
                            <span>SSS</span>
                            <span>₱{{ number_format($payslip->sss_deduction ?? 0, 2) }}</span>
                        </div>
                        <div class="pay-item">
                            <span>PhilHealth</span>
                            <span>₱{{ number_format($payslip->philhealth_deduction ?? 0, 2) }}</span>
                        </div>
                        <div class="pay-item">
                            <span>Pag-IBIG</span>
                            <span>₱{{ number_format($payslip->pagibig_deduction ?? 0, 2) }}</span>
                        </div>
                        @if($payslip->other_deductions)
                        <div class="pay-item">
                            <span>Other Deductions</span>
                            <span>₱{{ number_format($payslip->other_deductions, 2) }}</span>
                        </div>
                        @endif
                        <div class="pay-item total">
                            <span>TOTAL DEDUCTIONS</span>
                            <span>₱{{ number_format($payslip->total_deductions ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="net-pay-section">
                <div class="net-pay-label">
                    <i class="fas fa-wallet"></i> NET PAY
                </div>
                <div class="net-pay-amount">
                    ₱{{ number_format($payslip->net_pay, 2) }}
                </div>
                <div class="pay-period">
                    @if($payslip->period_start && $payslip->period_end)
                        For the period {{ date('M j, Y', strtotime($payslip->period_start)) }} - {{ date('M j, Y', strtotime($payslip->period_end)) }}
                    @else
                        {{ $payslip->pay_period }}
                    @endif
                </div>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>This is a computer-generated payslip. No signature required.</strong></p>
            <p>Generated on {{ date('M j, Y g:i A') }} | {{ $company['name'] }}</p>
            <p>For any queries regarding this payslip, please contact HR Department.</p>
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Optional: Add any interactive functionality here
        });
    </script>
</body>
</html>