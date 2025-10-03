<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Payroll Summary - {{ $employee->first_name }} {{ $employee->last_name }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
    .payroll-summary-card {
      border-radius: 15px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.1);
      border: none;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 2rem;
      margin-bottom: 2rem;
    }
    .summary-stat-card {
      background: rgba(255, 255, 255, 0.95);
      border-radius: 12px;
      padding: 1.5rem;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      border: none;
      height: 140px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .stat-amount {
      font-size: 1.8rem;
      font-weight: bold;
      color: #2c3e50;
      margin-bottom: 0.5rem;
    }
    .stat-label {
      font-size: 0.9rem;
      color: #6c757d;
      font-weight: 500;
    }
    .stat-period {
      font-size: 0.8rem;
      color: #95a5a6;
      margin-top: 0.25rem;
    }
    .year-selector {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      color: white;
      padding: 0.5rem 1rem;
    }
    .year-selector:focus {
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.5);
      color: white;
      box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
    }
    .year-selector option {
      background: #667eea;
      color: white;
    }
    .btn-year {
      background: rgba(255, 255, 255, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: white;
      border-radius: 6px;
      padding: 0.4rem 0.8rem;
      margin: 0 0.2rem;
      transition: all 0.3s ease;
    }
    .btn-year:hover {
      background: rgba(255, 255, 255, 0.3);
      color: white;
    }
    .btn-year.active {
      background: white;
      color: #667eea;
      font-weight: bold;
    }
    .payslip-card {
      border-radius: 10px;
      border: 1px solid #e9ecef;
      transition: all 0.3s ease;
    }
    .payslip-card:hover {
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transform: translateY(-2px);
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
            <h2 class="fw-bold mb-1">Payroll Summary</h2>
            <p class="text-muted mb-0">
              View your earnings, deductions, and payslip history.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payroll Summary</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Payroll Summary Header -->
    <div class="payroll-summary-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h3 class="mb-2">Payroll Summary</h3>
          <p class="mb-0 opacity-90">Your comprehensive payroll overview for {{ $year }}</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <span class="me-2">View Year:</span>
          @foreach($availableYears as $availableYear)
            <button class="btn btn-year {{ $availableYear == $year ? 'active' : '' }}" 
                    onclick="changeYear({{ $availableYear }})">
              {{ $availableYear }}
            </button>
          @endforeach
        </div>
      </div>
      
      <!-- Summary Statistics -->
      <div class="row g-4">
        <div class="col-md-3">
          <div class="summary-stat-card">
            <div class="stat-amount">₱{{ number_format($totalEarningsYTD, 0) }}</div>
            <div class="stat-label">Total Earnings (YTD)</div>
            <div class="stat-period">Year to Date</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="summary-stat-card">
            <div class="stat-amount">₱{{ number_format($averageNetPay, 0) }}</div>
            <div class="stat-label">Average Net Pay</div>
            <div class="stat-period">Per pay period</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="summary-stat-card">
            <div class="stat-amount">₱{{ number_format($totalTaxesYTD, 0) }}</div>
            <div class="stat-label">Taxes Paid (YTD)</div>
            <div class="stat-period">Year to Date</div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="summary-stat-card">
            <div class="stat-amount">₱{{ $lastPayslipDate ? number_format($recentPayslips->first()->net_pay ?? 0, 0) : '0' }}</div>
            <div class="stat-label">Last Payslip</div>
            <div class="stat-period">{{ $lastPayslipDate ? $lastPayslipDate->format('M d, Y') : 'No payslips yet' }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Payslips -->
    <div class="card">
      <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Recent Payslips</h5>
          <a href="{{ route('employee.payroll.payslips', ['year' => $year]) }}" class="btn btn-primary btn-sm">
            <i class="bi bi-eye me-1"></i>View All Payslips
          </a>
        </div>
      </div>
      <div class="card-body">
        @if($recentPayslips->count() > 0)
          <div class="row g-3">
            @foreach($recentPayslips as $payslip)
            <div class="col-md-6 col-lg-4">
              <div class="payslip-card card h-100">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                      <h6 class="card-title mb-1">{{ $payslip->pay_period_start->format('M d') }} - {{ $payslip->pay_period_end->format('M d, Y') }}</h6>
                      <small class="text-muted">Pay Period</small>
                    </div>
                    <span class="badge bg-{{ $payslip->status === 'paid' ? 'success' : ($payslip->status === 'processed' ? 'warning' : 'secondary') }}">
                      {{ ucfirst($payslip->status) }}
                    </span>
                  </div>
                  
                  <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                      <span class="text-muted">Gross Pay:</span>
                      <span class="fw-semibold">₱{{ number_format($payslip->gross_pay, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                      <span class="text-muted">Deductions:</span>
                      <span class="text-danger">-₱{{ number_format($payslip->total_deductions, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between">
                      <span class="fw-bold">Net Pay:</span>
                      <span class="fw-bold text-success">₱{{ number_format($payslip->net_pay, 2) }}</span>
                    </div>
                  </div>
                  
                  <div class="d-flex gap-2">
                    <a href="{{ route('employee.payroll.payslip', $payslip->id) }}" class="btn btn-outline-primary btn-sm flex-fill">
                      <i class="bi bi-eye me-1"></i>View
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" onclick="downloadPayslip({{ $payslip->id }})">
                      <i class="bi bi-download me-1"></i>PDF
                    </button>
                  </div>
                </div>
              </div>
            </div>
            @endforeach
          </div>
        @else
          <div class="text-center py-5">
            <i class="bi bi-receipt text-muted" style="font-size: 3rem;"></i>
            <h5 class="text-muted mt-3">No Payslips Found</h5>
            <p class="text-muted">No payslips available for {{ $year }}. Check back later or contact HR.</p>
          </div>
        @endif
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
      <div class="col-md-6">
        <div class="card">
          <div class="card-body text-center">
            <i class="bi bi-calculator text-primary" style="font-size: 2rem;"></i>
            <h6 class="mt-3">Tax Calculator</h6>
            <p class="text-muted small">Calculate your tax obligations and deductions</p>
            <button class="btn btn-outline-primary btn-sm">Coming Soon</button>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card">
          <div class="card-body text-center">
            <i class="bi bi-graph-up text-success" style="font-size: 2rem;"></i>
            <h6 class="mt-3">Earnings Report</h6>
            <p class="text-muted small">Generate detailed earnings and deductions report</p>
            <button class="btn btn-outline-success btn-sm">Coming Soon</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    function changeYear(year) {
      window.location.href = `{{ route('employee.payroll.summary') }}?year=${year}`;
    }
    
    function downloadPayslip(payslipId) {
      // Show loading state
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Generating...';
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
          // For now, just show success message
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
