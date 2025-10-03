<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Payslip Details - {{ $payslip->pay_period_start->format('M d') }} - {{ $payslip->pay_period_end->format('M d, Y') }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    .payslip-container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      overflow: hidden;
    }
    .payslip-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
    }
    .payslip-body {
      padding: 2rem;
    }
    .section-title {
      color: #495057;
      font-weight: 600;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 2px solid #e9ecef;
    }
    .amount-positive {
      color: #28a745;
      font-weight: 600;
    }
    .amount-negative {
      color: #dc3545;
      font-weight: 600;
    }
    .total-row {
      background: #f8f9fa;
      font-weight: bold;
      border-top: 2px solid #dee2e6;
    }
  </style>
</head>
<body style="background-color: #f8f9fa !important;">

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

  <main id="main-content">
    <!-- Page Header -->
    <div class="page-header-container mb-4">
      <div class="d-flex justify-content-between align-items-center page-header">
        <div class="d-flex align-items-center">
          <div class="dashboard-logo me-3">
            <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
          </div>
          <div>
            <h2 class="fw-bold mb-1">Payslip Details</h2>
            <p class="text-muted mb-0">
              Detailed breakdown of your payslip for {{ $payslip->pay_period_start->format('M d') }} - {{ $payslip->pay_period_end->format('M d, Y') }}
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employee.payroll.summary') }}" class="text-decoration-none">Payroll</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employee.payroll.payslips') }}" class="text-decoration-none">Payslips</a></li>
            <li class="breadcrumb-item active" aria-current="page">Details</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Payslip Detail -->
    <div class="payslip-container">
      <!-- Header -->
      <div class="payslip-header">
        <div class="row align-items-center">
          <div class="col-md-8">
            <h3 class="mb-2">Jetlouge Travels Corporation</h3>
            <p class="mb-1 opacity-90">Employee Payslip</p>
            <p class="mb-0 opacity-75">Pay Period: {{ $payslip->pay_period_start->format('M d, Y') }} - {{ $payslip->pay_period_end->format('M d, Y') }}</p>
          </div>
          <div class="col-md-4 text-end">
            <div class="mb-2">
              <strong>{{ $employee->first_name }} {{ $employee->last_name }}</strong>
              <br><small class="opacity-75">{{ $employee->employee_id }}</small>
              <br><small class="opacity-75">{{ $employee->position ?? 'Employee' }}</small>
            </div>
            <span class="badge bg-light text-dark px-3 py-2">
              {{ ucfirst($payslip->status) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Body -->
      <div class="payslip-body">
        <!-- Earnings Section -->
        <div class="mb-4">
          <h5 class="section-title">Earnings</h5>
          <div class="table-responsive">
            <table class="table table-borderless">
              <tbody>
                <tr>
                  <td>Basic Salary</td>
                  <td class="text-end amount-positive">₱{{ number_format($payslip->basic_salary, 2) }}</td>
                </tr>
                @if($payslip->overtime_hours > 0)
                <tr>
                  <td>Overtime Pay ({{ $payslip->overtime_hours }} hrs @ ₱{{ number_format($payslip->overtime_rate, 2) }}/hr)</td>
                  <td class="text-end amount-positive">₱{{ number_format($payslip->overtime_pay, 2) }}</td>
                </tr>
                @endif
                @if($payslip->allowances > 0)
                <tr>
                  <td>Allowances</td>
                  <td class="text-end amount-positive">₱{{ number_format($payslip->allowances, 2) }}</td>
                </tr>
                @endif
                @if($payslip->bonuses > 0)
                <tr>
                  <td>Bonuses</td>
                  <td class="text-end amount-positive">₱{{ number_format($payslip->bonuses, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                  <td><strong>Gross Pay</strong></td>
                  <td class="text-end"><strong class="amount-positive">₱{{ number_format($payslip->gross_pay, 2) }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Deductions Section -->
        <div class="mb-4">
          <h5 class="section-title">Deductions</h5>
          <div class="table-responsive">
            <table class="table table-borderless">
              <tbody>
                @if($payslip->sss_contribution > 0)
                <tr>
                  <td>SSS Contribution</td>
                  <td class="text-end amount-negative">₱{{ number_format($payslip->sss_contribution, 2) }}</td>
                </tr>
                @endif
                @if($payslip->philhealth_contribution > 0)
                <tr>
                  <td>PhilHealth Contribution</td>
                  <td class="text-end amount-negative">₱{{ number_format($payslip->philhealth_contribution, 2) }}</td>
                </tr>
                @endif
                @if($payslip->pagibig_contribution > 0)
                <tr>
                  <td>Pag-IBIG Contribution</td>
                  <td class="text-end amount-negative">₱{{ number_format($payslip->pagibig_contribution, 2) }}</td>
                </tr>
                @endif
                @if($payslip->withholding_tax > 0)
                <tr>
                  <td>Withholding Tax</td>
                  <td class="text-end amount-negative">₱{{ number_format($payslip->withholding_tax, 2) }}</td>
                </tr>
                @endif
                @if($payslip->other_deductions > 0)
                <tr>
                  <td>Other Deductions</td>
                  <td class="text-end amount-negative">₱{{ number_format($payslip->other_deductions, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                  <td><strong>Total Deductions</strong></td>
                  <td class="text-end"><strong class="amount-negative">₱{{ number_format($payslip->total_deductions, 2) }}</strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Net Pay Section -->
        <div class="mb-4">
          <div class="card bg-success bg-opacity-10 border-success">
            <div class="card-body text-center">
              <h4 class="text-success mb-2">Net Pay</h4>
              <h2 class="text-success fw-bold">₱{{ number_format($payslip->net_pay, 2) }}</h2>
              <p class="text-muted mb-0">
                @if($payslip->pay_date)
                  Paid on {{ $payslip->pay_date->format('M d, Y') }}
                @else
                  Payment Pending
                @endif
              </p>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="text-center">
          <a href="{{ route('employee.payroll.payslips') }}" class="btn btn-outline-primary me-2">
            <i class="bi bi-arrow-left me-1"></i>Back to Payslips
          </a>
          <button class="btn btn-primary" onclick="downloadPayslip({{ $payslip->id }})">
            <i class="bi bi-download me-1"></i>Download PDF
          </button>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function downloadPayslip(payslipId) {
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating PDF...';
      btn.disabled = true;
      
      fetch(`/employee/payroll/payslip/${payslipId}/download`, {
        method: 'GET',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert('PDF download feature will be implemented soon!');
        } else {
          alert('Error generating PDF');
        }
        btn.innerHTML = originalText;
        btn.disabled = false;
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error downloading payslip');
        btn.innerHTML = originalText;
        btn.disabled = false;
      });
    }
  </script>
</body>
</html>
