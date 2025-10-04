<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>My Payslips - {{ $employee->first_name }} {{ $employee->last_name }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
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
            <h2 class="fw-bold mb-1">My Payslips</h2>
            <p class="text-muted mb-0">
              View and download your payslip history.
            </p>
          </div>
        </div>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ route('employee.payroll.summary') }}" class="text-decoration-none">Payroll</a></li>
            <li class="breadcrumb-item active" aria-current="page">Payslips</li>
          </ol>
        </nav>
      </div>
    </div>

    <!-- Payslips Table -->
    <div class="card">
      <div class="card-header bg-light">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Payslips for {{ $year }}</h5>
          <div class="d-flex gap-2">
            @foreach($availableYears as $availableYear)
              <a href="{{ route('employee.payroll.payslips', ['year' => $availableYear]) }}"
                 class="btn btn-{{ $availableYear == $year ? 'primary' : 'outline-primary' }} btn-sm">
                {{ $availableYear }}
              </a>
            @endforeach
          </div>
        </div>
      </div>
      <div class="card-body">
        @if($payslips->count() > 0)
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Pay Period</th>
                  <th>Pay Date</th>
                  <th class="text-end">Gross Pay</th>
                  <th class="text-end">Deductions</th>
                  <th class="text-end">Net Pay</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach($payslips as $payslip)
                <tr>
                  <td>
                    <div>
                      <strong>{{ $payslip->pay_period_start->format('M d') }} - {{ $payslip->pay_period_end->format('M d, Y') }}</strong>
                      <br><small class="text-muted">{{ $payslip->pay_period_start->diffInDays($payslip->pay_period_end) + 1 }} days</small>
                    </div>
                  </td>
                  <td>{{ $payslip->pay_date ? $payslip->pay_date->format('M d, Y') : 'Pending' }}</td>
                  <td class="text-end">₱{{ number_format($payslip->gross_pay, 2) }}</td>
                  <td class="text-end text-danger">₱{{ number_format($payslip->total_deductions, 2) }}</td>
                  <td class="text-end fw-bold text-success">₱{{ number_format($payslip->net_pay, 2) }}</td>
                  <td>
                    <span class="badge bg-{{ $payslip->status === 'paid' ? 'success' : ($payslip->status === 'processed' ? 'warning' : 'secondary') }}">
                      {{ ucfirst($payslip->status) }}
                    </span>
                  </td>
                  <td class="text-center">
                    <div class="btn-group" role="group">
                      <a href="{{ route('employee.payroll.payslip', $payslip->id) }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i>
                      </a>
                      <button class="btn btn-outline-secondary btn-sm" onclick="downloadPayslip({{ $payslip->id }})">
                        <i class="bi bi-download"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="d-flex justify-content-center mt-4">
            {{ $payslips->links() }}
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
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function downloadPayslip(payslipId) {
      const btn = event.target;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';
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
