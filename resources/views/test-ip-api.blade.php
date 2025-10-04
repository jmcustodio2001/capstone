<!DOCTYPE html>
<html>
<head>
    <title>Test IP API</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>IP Address API Test</h2>
        <button id="testBtn" class="btn btn-primary">Test IP API</button>
        <div id="result" class="mt-3"></div>
    </div>

    <script>
        document.getElementById('testBtn').addEventListener('click', async function() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="alert alert-info">Testing API...</div>';

            try {
                const response = await fetch('/api/employees/check-ip-addresses', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        employee_ids: ['EMP001', 'EMP002', 'EMP003'],
                        client_ip: '192.168.1.100'
                    })
                });

                if (response.ok) {
                    const data = await response.json();
                    resultDiv.innerHTML = `
                        <div class="alert alert-success">
                            <h5>API Success!</h5>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    const errorText = await response.text();
                    resultDiv.innerHTML = `
                        <div class="alert alert-danger">
                            <h5>API Error (${response.status})</h5>
                            <p>${errorText}</p>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5>Network Error</h5>
                        <p>${error.message}</p>
                    </div>
                `;
            }
        });
    </script>
</body>
</html>
