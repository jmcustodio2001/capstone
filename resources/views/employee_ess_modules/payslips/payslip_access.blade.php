<!DOCTYPE html>
<html lang="en">
<head>
  <!-- SweetAlert2 CDN for feedback -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employee Payslip Access</title>
  <link rel="icon" href="{{ asset('assets/images/jetlouge_logo.png') }}" type="image/png">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/employee_dashboard-style.css') }}">
  <style>
  /* Sidebar/main-content responsive fix */
  #sidebar.collapsed {
    width: 70px !important;
    min-width: 70px !important;
    transition: width 0.3s;
  }
  #main-content.expanded {
    margin-left: 0 !important;
    transition: margin-left 0.3s;
  }
    /* Scope all custom styles under .payslip-access-wrapper to avoid affecting the topbar */
    .payslip-access-wrapper {
      --primary-color: #4361ee;
      --secondary-color: #3f37c9;
      --success-color: #4cc9f0;
      --warning-color: #f72585;
      --light-bg: #f8f9fa;
      background-color: #f8f9fa !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .payslip-access-wrapper .simulation-card {
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.08);
      border: none;
      transition: transform 0.3s ease;
    }
    .payslip-access-wrapper .simulation-card:hover {
      transform: translateY(-5px);
    }
    .payslip-access-wrapper .card-header-custom {
      background-color: #fff;
      border-bottom: 1px solid #eaeaea;
      padding: 1.25rem 1.5rem;
      border-radius: 12px 12px 0 0 !important;
    }
    .payslip-access-wrapper .table th {
      background-color: #f8f9fa;
      font-weight: 600;
      color: #495057;
    }
    .payslip-access-wrapper .summary-card {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
      margin-bottom: 2rem;
    }
    .payslip-access-wrapper .summary-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
    }
    .payslip-access-wrapper .summary-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1.5rem;
    }
    .payslip-access-wrapper .stat-item {
      text-align: center;
      padding: 1rem;
      border-radius: 8px;
      background-color: #f8f9fa;
    }
    .payslip-access-wrapper .stat-value {
      font-size: 1.8rem;
      font-weight: 700;
      margin: 0.5rem 0;
      color: var(--primary-color);
    }
    .payslip-access-wrapper .stat-label {
      color: #6c757d;
      font-weight: 500;
    }
    .payslip-access-wrapper .filter-container {
      background: white;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    .payslip-access-wrapper .btn-download {
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      border: none;
      padding: 0.6rem 1.2rem;
      border-radius: 50px;
      font-weight: 600;
      box-shadow: 0 4px 15px rgba(67, 97, 238, 0.2);
      transition: all 0.3s ease;
    }
    .payslip-access-wrapper .btn-download:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(67, 97, 238, 0.3);
    }
    .payslip-access-wrapper .payslip-row {
      transition: all 0.3s ease;
    }
    .payslip-access-wrapper .payslip-row:hover {
      background-color: rgba(67, 97, 238, 0.05) !important;
    }
    .payslip-access-wrapper .net-pay-cell {
      font-weight: 700;
      color: var(--primary-color);
    }
    .payslip-access-wrapper .action-buttons {
      display: flex;
      gap: 0.5rem;
    }
    .payslip-access-wrapper .action-btn {
      padding: 0.35rem 0.65rem;
      border-radius: 6px;
      font-size: 0.875rem;
    }
    .payslip-access-wrapper .year-selector {
      display: flex;
      gap: 0.5rem;
      align-items: center;
      margin-bottom: 1rem;
    }
    .payslip-access-wrapper .year-btn {
      padding: 0.5rem 1rem;
      border-radius: 6px;
      font-weight: 500;
      background-color: #f8f9fa;
      border: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }
    .payslip-access-wrapper .year-btn.active {
      background-color: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }
    .payslip-access-wrapper .year-btn:hover:not(.active) {
      background-color: #e9ecef;
    }
    .payslip-access-wrapper .modal-content {
      border-radius: 12px;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    .payslip-access-wrapper .modal-header {
      border-bottom: 1px solid #eaeaea;
      padding: 1.5rem;
    }
    .payslip-access-wrapper .modal-footer {
      border-top: 1px solid #eaeaea;
      padding: 1rem 1.5rem;
    }
    .payslip-access-wrapper .payslip-detail {
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 1px solid #f1f1f1;
    }
    .payslip-access-wrapper .payslip-detail:last-child {
      border-bottom: none;
    }
    .payslip-access-wrapper .detail-label {
      font-weight: 600;
      color: #495057;
    }
    .payslip-access-wrapper .detail-value {
      color: #6c757d;
    }
    .payslip-access-wrapper .earnings-deductions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin: 1.5rem 0;
    }
    .payslip-access-wrapper .earnings-box, .payslip-access-wrapper .deductions-box {
      background-color: #f8f9fa;
      border-radius: 8px;
      padding: 1rem;
    }
    .payslip-access-wrapper .earnings-header, .payslip-access-wrapper .deductions-header {
      font-weight: 600;
      margin-bottom: 1rem;
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #dee2e6;
    }
    .payslip-access-wrapper .earning-item, .payslip-access-wrapper .deduction-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 0.5rem;
    }
    .payslip-access-wrapper .total-row {
      font-weight: 700;
      margin-top: 0.5rem;
      padding-top: 0.5rem;
      border-top: 1px solid #dee2e6;
    }
    @media (max-width: 768px) {
      .payslip-access-wrapper .summary-stats {
        grid-template-columns: 1fr;
      }
      .payslip-access-wrapper .earnings-deductions {
        grid-template-columns: 1fr;
      }
      .payslip-access-wrapper .action-buttons {
        flex-direction: column;
      }
      .payslip-access-wrapper .year-selector {
        flex-wrap: wrap;
      }
    }
  </style>
</head>
<body>

@include('employee_ess_modules.partials.employee_topbar')
@include('employee_ess_modules.partials.employee_sidebar')

<div id="overlay" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50" style="z-index:1040; display: none;"></div>

<main id="main-content" class="payslip-access-wrapper" style="margin-left: 280px; padding: 2rem; margin-top: 3.5rem; transition: margin-left 0.3s;">

  <!-- Page Header -->
  <div class="page-header-container mb-4">
    <div class="d-flex justify-content-between align-items-center page-header">
      <div class="d-flex align-items-center">
        <div class="dashboard-logo me-3">
          <img src="{{ asset('assets/images/jetlouge_logo.png') }}" alt="Jetlouge Travels" class="logo-img">
        </div>
        <div>
          <h2 class="fw-bold mb-1">Payslip Access</h2>
          <p class="text-muted mb-0">View and download your payslips for each payroll period.</p>
        </div>
      </div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-0">
          <li class="breadcrumb-item"><a href="{{ route('employee.dashboard') }}" class="text-decoration-none">Home</a></li>
          <li class="breadcrumb-item active" aria-current="page">Payslip Access</li>
        </ol>
      </nav>
    </div>
  </div>

  <!-- Payslip Summary -->
  <div class="card-header summary-card">
    <div class="summary-header">
      <h4 class="fw-bold mb-0">Payroll Summary</h4>
      <div class="year-selector">
        <span class="fw-semibold">View Year:</span>
        <button class="year-btn">2022</button>
        <button class="year-btn active">2023</button>
        <button class="year-btn">2024</button>
      </div>
    </div>
    <div class="summary-stats">
      <div class="stat-item">
        <div class="stat-label">Total Earnings (YTD)</div>
        <div class="stat-value">₱{{ number_format($summaryData['total_earnings_ytd'], 2) }}</div>
        <div class="stat-desc">Year to Date</div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Average Net Pay</div>
        <div class="stat-value">₱{{ number_format($summaryData['average_net_pay'], 2) }}</div>
        <div class="stat-desc">Per pay period</div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Taxes Paid (YTD)</div>
        <div class="stat-value">₱{{ number_format($summaryData['taxes_paid_ytd'], 2) }}</div>
        <div class="stat-desc">Year to Date</div>
      </div>
      <div class="stat-item">
        <div class="stat-label">Last Payslip</div>
        <div class="stat-value">₱{{ number_format($summaryData['last_payslip_amount'], 2) }}</div>
        <div class="stat-desc">{{ $summaryData['last_payslip_date'] ? date('M j, Y', strtotime($summaryData['last_payslip_date'])) : 'No payslips' }}</div>
      </div>
    </div>
  </div>

  <!-- Filters -->
  <div class="filter-container">
    <div class="row">
      <div class="col-md-4 mb-2">
        <label for="period-filter" class="form-label">Pay Period</label>
        <select class="form-select" id="period-filter">
          <option value="">All Periods</option>
          <option value="monthly">Monthly</option>
          <option value="semi-monthly">Semi-Monthly</option>
          <option value="weekly">Weekly</option>
        </select>
      </div>
      <div class="col-md-4 mb-2">
        <label for="year-filter" class="form-label">Year</label>
        <select class="form-select" id="year-filter">
          <option value="">All Years</option>
          <option value="2023" selected>2023</option>
          <option value="2022">2022</option>
          <option value="2021">2021</option>
        </select>
      </div>
      <div class="col-md-4 mb-2">
        <label for="month-filter" class="form-label">Month</label>
        <select class="form-select" id="month-filter">
          <option value="">All Months</option>
          <option value="1">January</option>
          <option value="2">February</option>
          <option value="3">March</option>
          <option value="4">April</option>
          <option value="5">May</option>
          <option value="6">June</option>
          <option value="7">July</option>
          <option value="8">August</option>
          <option value="9">September</option>
          <option value="10">October</option>
          <option value="11">November</option>
          <option value="12">December</option>
        </select>
      </div>
    </div>
    <div class="row mt-2">
      <div class="col-md-8 mb-2">
        <input type="text" id="payslipSearch" class="form-control" placeholder="Search by period, net pay, or employee ID...">
      </div>
      <div class="col-md-4 mb-2 d-flex align-items-end">
        <button id="reset-filters" class="btn btn-outline-secondary w-100">Reset Filters</button>
      </div>
    </div>
  </div>

  <!-- Payslip Table -->
  <div class="simulation-card card mb-4">
    <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
      <h4 class="fw-bold mb-0">Payslip Records</h4>
      <button class="btn btn-download">
        <i class="bi bi-download me-1"></i> Download All
      </button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="payslipTable">
          <thead class="table-light">
            <tr>
              <th class="fw-bold">Pay Period</th>
              <th class="fw-bold">Duration</th>
              <th class="fw-bold">Gross Pay</th>
              <th class="fw-bold">Deductions</th>
              <th class="fw-bold">Net Pay</th>
              <th class="fw-bold">Payment Date</th>
              <th class="fw-bold text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($payslips as $payslip)
              <tr class="payslip-row">
                <td>
                  <div class="fw-semibold">
                    @if($payslip->period_start)
                      {{ date('F Y', strtotime($payslip->period_start)) }}
                    @else
                      {{ $payslip->pay_period }}
                    @endif
                  </div>
                  <small class="text-muted">{{ $payslip->payslip_id ?? 'PS' . $payslip->id }}</small>
                </td>
                <td>
                  @if($payslip->period_start && $payslip->period_end)
                    {{ date('M j', strtotime($payslip->period_start)) }} -
                    {{ date('M j, Y', strtotime($payslip->period_end)) }}
                  @else
                    {{ $payslip->pay_period }}
                  @endif
                </td>
                <td>₱{{ number_format($payslip->gross_pay ?? (($payslip->basic_pay ?? 0) + ($payslip->overtime_pay ?? 0) + ($payslip->allowances ?? 0)), 2) }}</td>
                <td>₱{{ number_format($payslip->total_deductions ?? (($payslip->gross_pay ?? (($payslip->basic_pay ?? 0) + ($payslip->overtime_pay ?? 0) + ($payslip->allowances ?? 0))) - $payslip->net_pay), 2) }}</td>
                <td class="net-pay-cell">₱{{ number_format($payslip->net_pay, 2) }}</td>
                <td>
                  @if($payslip->release_date)
                    {{ date('M j, Y', strtotime($payslip->release_date)) }}
                  @elseif($payslip->period_end)
                    {{ date('M j, Y', strtotime($payslip->period_end . '+5 days')) }}
                  @else
                    N/A
                  @endif
                </td>
                <td class="text-center action-buttons">
                  <button class="btn btn-sm btn-info text-white action-btn view-payslip" data-bs-toggle="modal" data-bs-target="#viewPayslipModal{{ $payslip->id }}">
                    <i class="bi bi-eye"></i>
                  </button>
                  @if($payslip->payslip_file)
                    <a href="{{ asset('storage/payslips/' . $payslip->payslip_file) }}" download class="btn btn-sm btn-success action-btn">
                      <i class="bi bi-download"></i>
                    </a>
                    <a href="{{ asset('storage/payslips/' . $payslip->payslip_file) }}" target="_blank" class="btn btn-sm btn-primary action-btn">
                      <i class="bi bi-printer"></i>
                    </a>
                  @else
                    <button class="btn btn-sm btn-success action-btn" onclick="downloadPayslip({{ $payslip->id }})">
                      <i class="bi bi-download"></i>
                    </button>
                    <button class="btn btn-sm btn-primary action-btn" onclick="printPayslip({{ $payslip->id }})">
                      <i class="bi bi-printer"></i>
                    </button>
                  @endif
                </td>
              </tr>

              <!-- View Payslip Modal -->
              <div class="modal fade" id="viewPayslipModal{{ $payslip->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                  <div class="modal-content">
                    <div class="modal-header">
                      <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Payslip Details</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                      <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                          <h6 class="fw-bold mb-0">Jetlouge Travels</h6>
                          <small class="text-muted">123 Business Avenue, City, Country</small>
                        </div>
                        <div class="text-end">
                          <h6 class="fw-bold mb-0">Payslip #{{ $payslip->payslip_id ?? 'PS' . $payslip->id }}</h6>
                          <small class="text-muted">Payment Date:
                            @if($payslip->release_date)
                              {{ date('M j, Y', strtotime($payslip->release_date)) }}
                            @elseif($payslip->period_end)
                              {{ date('M j, Y', strtotime($payslip->period_end . '+5 days')) }}
                            @else
                              N/A
                            @endif
                          </small>
                        </div>
                      </div>

                      <div class="row mb-4">
                        <div class="col-md-6">
                          <div class="payslip-detail">
                            <div class="detail-label">Employee ID</div>
                            <div class="detail-value">{{ $payslip->employee_id }}</div>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="payslip-detail">
                            <div class="detail-label">Pay Period</div>
                            <div class="detail-value">
                              @if($payslip->period_start && $payslip->period_end)
                                {{ date('M j, Y', strtotime($payslip->period_start)) }} -
                                {{ date('M j, Y', strtotime($payslip->period_end)) }}
                              @else
                                {{ $payslip->pay_period }}
                              @endif
                            </div>
                          </div>
                        </div>
                      </div>

                      <div class="earnings-deductions">
                        <div class="earnings-box">
                          <div class="earnings-header">Earnings</div>
                          <div class="earning-item">
                            <span>Basic Salary</span>
                            <span>₱{{ number_format($payslip->basic_pay ?? ($payslip->net_pay * 0.7), 2) }}</span>
                          </div>
                          <div class="earning-item">
                            <span>Overtime Pay</span>
                            <span>₱{{ number_format($payslip->overtime_pay ?? ($payslip->net_pay * 0.1), 2) }}</span>
                          </div>
                          <div class="earning-item">
                            <span>Allowances</span>
                            <span>₱{{ number_format($payslip->allowances ?? ($payslip->net_pay * 0.2), 2) }}</span>
                          </div>
                          <div class="earning-item total-row">
                            <span>Total Earnings</span>
                            <span>₱{{ number_format($payslip->gross_pay ?? (($payslip->basic_pay ?? 0) + ($payslip->overtime_pay ?? 0) + ($payslip->allowances ?? 0)) ?: ($payslip->net_pay * 1.3), 2) }}</span>
                          </div>
                        </div>

                        <div class="deductions-box">
                          <div class="deductions-header">Deductions</div>
                          <div class="deduction-item">
                            <span>Tax</span>
                            <span>₱{{ number_format($payslip->tax_deduction ?? (($payslip->gross_pay ?? ($payslip->net_pay * 1.3)) * 0.15), 2) }}</span>
                          </div>
                          <div class="deduction-item">
                            <span>SSS</span>
                            <span>₱{{ number_format($payslip->sss_deduction ?? (($payslip->gross_pay ?? ($payslip->net_pay * 1.3)) * 0.05), 2) }}</span>
                          </div>
                          <div class="deduction-item">
                            <span>PhilHealth</span>
                            <span>₱{{ number_format($payslip->philhealth_deduction ?? (($payslip->gross_pay ?? ($payslip->net_pay * 1.3)) * 0.03), 2) }}</span>
                          </div>
                          <div class="deduction-item">
                            <span>Pag-IBIG</span>
                            <span>₱{{ number_format($payslip->pagibig_deduction ?? (($payslip->gross_pay ?? ($payslip->net_pay * 1.3)) * 0.02), 2) }}</span>
                          </div>
                          @if($payslip->other_deductions)
                          <div class="deduction-item">
                            <span>Other Deductions</span>
                            <span>₱{{ number_format($payslip->other_deductions, 2) }}</span>
                          </div>
                          @endif
                          <div class="deduction-item total-row">
                            <span>Total Deductions</span>
                            <span>₱{{ number_format($payslip->total_deductions ?? (($payslip->gross_pay ?? ($payslip->net_pay * 1.3)) - $payslip->net_pay), 2) }}</span>
                          </div>
                        </div>
                      </div>

                      <div class="total-pay bg-light p-3 rounded text-center">
                        <h4 class="fw-bold mb-0">Net Pay: ₱{{ number_format($payslip->net_pay, 2) }}</h4>
                        <small class="text-muted">Deposited to your bank account</small>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                      @if($payslip->payslip_file)
                        <a href="{{ asset('storage/payslips/' . $payslip->payslip_file) }}" download class="btn btn-success">
                          <i class="bi bi-download me-1"></i> Download
                        </a>
                        <a href="{{ asset('storage/payslips/' . $payslip->payslip_file) }}" target="_blank" class="btn btn-primary">
                          <i class="bi bi-printer me-1"></i> Print
                        </a>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <i class="bi bi-info-circle me-2"></i>No payslip records found.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">Showing 1 to 6 of 24 entries</div>
        <nav aria-label="Payslip pagination">
          <ul class="pagination">
            <li class="page-item disabled">
              <a class="page-link" href="#" tabindex="-1">Previous</a>
            </li>
            <li class="page-item active"><a class="page-link" href="#">1</a></li>
            <li class="page-item"><a class="page-link" href="#">2</a></li>
            <li class="page-item"><a class="page-link" href="#">3</a></li>
            <li class="page-item"><a class="page-link" href="#">4</a></li>
            <li class="page-item">
              <a class="page-link" href="#">Next</a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Search functionality
  document.getElementById('payslipSearch').addEventListener('keyup', function() {
    let filter = this.value.toLowerCase();
    let rows = document.querySelectorAll("#payslipTable tbody tr");
    rows.forEach(row => {
      let text = row.textContent.toLowerCase();
      row.style.display = text.includes(filter) ? "" : "none";
    });
  });

  // Filter functionality
  document.getElementById('period-filter').addEventListener('change', filterTable);
  document.getElementById('year-filter').addEventListener('change', filterTable);
  document.getElementById('month-filter').addEventListener('change', filterTable);

  document.getElementById('reset-filters').addEventListener('click', function() {
    document.getElementById('period-filter').value = '';
    document.getElementById('year-filter').value = '2023';
    document.getElementById('month-filter').value = '';
    document.getElementById('payslipSearch').value = '';
    filterTable();
  });

  function filterTable() {
    const periodFilter = document.getElementById('period-filter').value;
    const yearFilter = document.getElementById('year-filter').value;
    const monthFilter = document.getElementById('month-filter').value;
    const searchFilter = document.getElementById('payslipSearch').value.toLowerCase();

    const rows = document.querySelectorAll('#payslipTable tbody tr');

    rows.forEach(row => {
      let showRow = true;
      const periodCell = row.cells[0].textContent;
      const dateCell = row.cells[1].textContent;

      // Apply period filter (simplified)
      if (periodFilter) {
        // In a real application, you would have proper period data to filter by
        showRow = true; // For demo, we're not implementing this fully
      }

      // Apply year filter
      if (yearFilter && dateCell.indexOf(yearFilter) === -1) {
        showRow = false;
      }

      // Apply month filter
      if (monthFilter) {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                           'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const monthName = monthNames[parseInt(monthFilter) - 1];
        if (dateCell.indexOf(monthName) === -1) {
          showRow = false;
        }
      }

      // Apply search filter
      if (searchFilter) {
        const rowText = row.textContent.toLowerCase();
        if (!rowText.includes(searchFilter)) {
          showRow = false;
        }
      }

      row.style.display = showRow ? '' : 'none';
    });
  }

  // Year selector buttons
  document.querySelectorAll('.year-btn').forEach(button => {
    button.addEventListener('click', function() {
      document.querySelectorAll('.year-btn').forEach(btn => {
        btn.classList.remove('active');
      });
      this.classList.add('active');
      // In a real application, you would reload data for the selected year
    });
  });

  // Download individual payslip
  function downloadPayslip(payslipId) {
    // Create a temporary link to trigger download
    const link = document.createElement('a');
    link.href = `/employee/payslips/${payslipId}/download`;
    link.download = `payslip_${payslipId}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }

  // Print individual payslip
  function printPayslip(payslipId) {
    // Open print template in new window
    const printWindow = window.open(`/employee/payslips/${payslipId}/print`, '_blank');
    if (printWindow) {
      printWindow.focus();
    } else {
      alert('Please allow pop-ups to print payslips.');
    }
  }

  // Download all payslips
  document.querySelector('.btn-download').addEventListener('click', function() {
    const button = this;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Preparing...';
    button.disabled = true;

    fetch('/employee/payslips/download-all', {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      }
    })
    .then(response => {
      if (!response.ok) throw new Error('No payslips found or error occurred.');
      return response.blob();
    })
    .then(blob => {
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.style.display = 'none';
      a.href = url;
      a.download = `all_payslips_${new Date().toISOString().split('T')[0]}.zip`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      Swal.fire({
        icon: 'success',
        title: 'Download Started',
        text: 'Your payslips are being downloaded as a ZIP file.'
      });
    })
    .catch(() => {
      // fallback: try the old anchor method
      const link = document.createElement('a');
      link.href = '/employee/payslips/download-all';
      link.download = `all_payslips_${new Date().toISOString().split('T')[0]}.zip`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      Swal.fire({
        icon: 'warning',
        title: 'Fallback Download',
        text: 'Tried fallback download. If nothing downloads, no payslips are available.'
      });
    })
    .finally(() => {
      button.innerHTML = originalText;
      button.disabled = false;
    });
  });

  // Initialize page
  document.addEventListener('DOMContentLoaded', function() {
    // Add CSRF token meta tag if not present
    if (!document.querySelector('meta[name="csrf-token"]')) {
      const meta = document.createElement('meta');
      meta.name = 'csrf-token';
      meta.content = '{{ csrf_token() }}';
      document.getElementsByTagName('head')[0].appendChild(meta);
    }
  });
</script>

<script>
  // Remove all .modal-backdrop elements on page load and after any modal event
  function removeAllModalBackdrops() {
    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
      backdrop.remove();
    });
  }
  window.addEventListener('DOMContentLoaded', removeAllModalBackdrops);
  document.addEventListener('shown.bs.modal', removeAllModalBackdrops);
  document.addEventListener('hidden.bs.modal', removeAllModalBackdrops);
</script>
</body>
</html>
