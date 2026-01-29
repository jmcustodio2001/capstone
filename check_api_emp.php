<?php
$json = file_get_contents('http://hr4.jetlougetravels-ph.com/api/employees');
$data = json_decode($json, true);
if (isset($data['data'])) {
    foreach ($data['data'] as $emp) {
        $id = $emp['employee_id'] ?? $emp['id'] ?? $emp['external_employee_id'] ?? null;
        if ($id == '2') {
            echo "MATCH FOUND:\n";
            print_r($emp);
        }
    }
} else {
    echo "NO DATA KEY FOUND\n";
    print_r($data);
}
