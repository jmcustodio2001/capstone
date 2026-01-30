<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Printable Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { padding: 20px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #ddd; padding: 8px; }
    th { background: #f8f9fa; }
  </style>
</head>
<body>
  <h3>Training Completion Trends</h3>
  <table>
    <thead>
      <tr>
        <th>Course Name</th>
        <th>Department</th>
        <th>Participants</th>
        <th>Completed</th>
        <th>Completion %</th>
        <th>Avg. Score</th>
        <th>Status</th>
      </tr>
    </thead>
    <tbody>
      @foreach($courses as $c)
      <tr>
        <td>{{ $c['name'] }}</td>
        <td>{{ $c['department'] }}</td>
        <td>{{ $c['participants'] }}</td>
        <td>{{ $c['completed'] }}</td>
        <td>{{ $c['completion_percent'] }}%</td>
        <td>{{ $c['avg_score'] }}</td>
        <td>{{ $c['status_text'] }}</td>
      </tr>
      @endforeach
    </tbody>
  </table>

  <script>
    window.addEventListener('DOMContentLoaded', function() {
      window.print();
    });
  </script>
</body>
</html>
