<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        @page { 
            margin: 15mm; 
            size: A4 landscape;
        }
        @media print {
            body { -webkit-print-color-adjust: exact; }
            .no-print { display: none; }
        }
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 { 
            color: #333; 
            margin: 0 0 10px 0; 
            font-size: 20px;
            font-weight: bold;
        }
        .header .company-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }
        .export-info { 
            text-align: center; 
            margin-bottom: 20px; 
            color: #666; 
            font-size: 10px;
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
            font-size: 9px;
        }
        th, td { 
            border: 1px solid #333; 
            padding: 6px 4px; 
            text-align: left; 
            vertical-align: top;
            word-wrap: break-word;
        }
        th { 
            background-color: #e9ecef; 
            font-weight: bold;
            text-align: center;
            font-size: 9px;
        }
        tr:nth-child(even) { 
            background-color: #f8f9fa; 
        }
        .status-completed { 
            color: #28a745; 
            font-weight: bold; 
        }
        .status-in-progress { 
            color: #007bff; 
            font-weight: bold; 
        }
        .status-not-started { 
            color: #6c757d; 
        }
        .progress { 
            font-weight: bold; 
            text-align: center;
        }
        .progress-high { color: #28a745; }
        .progress-medium { color: #ffc107; }
        .progress-low { color: #dc3545; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .employee-name {
            font-weight: bold;
            color: #495057;
        }
        .destination-name {
            font-weight: 600;
            color: #212529;
        }
        .delivery-mode {
            font-size: 8px;
            padding: 2px 4px;
            border-radius: 3px;
            background-color: #e9ecef;
            color: #495057;
        }
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        .print-button:hover {
            background: #0056b3;
        }
    </style>
    <script>
        function printPage() {
            window.print();
        }
        
        // Auto-trigger print dialog when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        }
    </script>
</head>
<body>
    <button class="print-button no-print" onclick="printPage()">🖨️ Print/Save as PDF</button>
    
    <div class="header">
        <div class="company-info">
            <strong>Jetlouge Travels</strong><br>
            HR2ESS - Human Resource Management System
        </div>
        <h1>{{ $title }}</h1>
    </div>
    
    <div class="export-info">
        <strong>Report Generated:</strong> {{ $generated_at }} | 
        <strong>Total Records:</strong> {{ $total_records }} | 
        <strong>Status:</strong> All Active Training Records
    </div>

    <!-- POSSIBLE TRAINING DESTINATIONS (Master List) -->
    <div style="margin-bottom: 30px;">
        <h2 style="color: #333; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #4a90e2; padding-bottom: 5px;">
            📚 POSSIBLE TRAINING DESTINATIONS (Master List)
        </h2>
        <p style="font-size: 10px; color: #666; margin-bottom: 10px;">
            <em>These are pre-defined training destinations available in the system. Total: {{ $total_master_destinations }}</em>
        </p>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 3%;">#</th>
                    <th style="width: 25%;">Destination / Training Title</th>
                    <th style="width: 8%;">Duration</th>
                    <th style="width: 27%;">Details</th>
                    <th style="width: 27%;">Objectives</th>
                    <th style="width: 10%;">Delivery Mode</th>
                </tr>
            </thead>
            <tbody>
                @foreach($masterDestinations as $index => $master)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td class="destination-name">{{ $master->destination_name }}</td>
                        <td style="text-align: center;">{{ $master->duration }}</td>
                        <td style="font-size: 8px;">{{ $master->details }}</td>
                        <td style="font-size: 8px;">{{ $master->objectives }}</td>
                        <td style="text-align: center;">
                            <span class="delivery-mode">{{ $master->delivery_mode }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- EMPLOYEE TRAINING RECORDS -->
    <div style="margin-top: 40px;">
        <h2 style="color: #333; font-size: 16px; margin-bottom: 15px; border-bottom: 2px solid #28a745; padding-bottom: 5px;">
            👥 EMPLOYEE TRAINING RECORDS
        </h2>
        <p style="font-size: 10px; color: #666; margin-bottom: 10px;">
            <em>Individual employee training assignments and progress tracking</em>
        </p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 4%;">ID</th>
                <th style="width: 16%;">Employee</th>
                <th style="width: 20%;">Destination</th>
                <th style="width: 12%;">Delivery Mode</th>
                <th style="width: 9%;">Created</th>
                <th style="width: 9%;">Expires</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 8%;">Progress</th>
                <th style="width: 12%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @forelse($destinations as $record)
                @php
                    $employeeName = $record->employee ? 
                        ($record->employee->first_name . ' ' . $record->employee->last_name) : 
                        'Unknown Employee';
                    $expiredDate = $record->expired_date ? 
                        $record->expired_date->format('Y-m-d') : 
                        'Not Set';
                    $createdDate = $record->created_at ? 
                        $record->created_at->format('Y-m-d') : 
                        'N/A';
                    $progress = $record->progress ?? 0;
                    
                    // Status styling
                    $statusClass = '';
                    switch($record->status) {
                        case 'completed':
                            $statusClass = 'status-completed';
                            break;
                        case 'in-progress':
                            $statusClass = 'status-in-progress';
                            break;
                        default:
                            $statusClass = 'status-not-started';
                    }
                    
                    // Progress styling
                    $progressClass = '';
                    if ($progress >= 80) {
                        $progressClass = 'progress-high';
                    } elseif ($progress >= 50) {
                        $progressClass = 'progress-medium';
                    } else {
                        $progressClass = 'progress-low';
                    }
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $record->id }}</td>
                    <td class="employee-name">{{ $employeeName }}</td>
                    <td class="destination-name">{{ $record->destination_name }}</td>
                    <td>
                        <span class="delivery-mode">{{ $record->delivery_mode }}</span>
                    </td>
                    <td style="text-align: center;">{{ $createdDate }}</td>
                    <td style="text-align: center;">{{ $expiredDate }}</td>
                    <td class="{{ $statusClass }}" style="text-align: center;">
                        {{ ucfirst($record->status) }}
                    </td>
                    <td class="progress {{ $progressClass }}" style="text-align: center;">
                        {{ $progress }}%
                    </td>
                    <td style="font-size: 8px;">
                        {{ $record->remarks ? Str::limit($record->remarks, 60) : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: #6c757d; font-style: italic;">
                        No destination knowledge training records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <div class="footer">
        <p>
            <strong>Jetlouge Travels HR2ESS System</strong><br>
            Destination Knowledge Training Report - Generated on {{ $generated_at }}<br>
            This report contains {{ $total_records }} training record(s)
        </p>
        <p style="margin-top: 10px; font-size: 7px;">
            <em>To save as PDF: Press Ctrl+P (Windows) or Cmd+P (Mac), then select "Save as PDF" as destination</em>
        </p>
    </div>
</body>
</html>
