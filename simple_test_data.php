<?php
// Simple test to create data in upcoming_trainings table
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database\n";
    
    // Create table if not exists
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS upcoming_trainings (
            upcoming_id bigint unsigned NOT NULL AUTO_INCREMENT,
            employee_id varchar(20) NOT NULL,
            training_title varchar(255) NOT NULL,
            start_date date NOT NULL,
            end_date date DEFAULT NULL,
            status varchar(255) DEFAULT 'Assigned',
            source varchar(255) DEFAULT NULL,
            assigned_by varchar(255) DEFAULT NULL,
            assigned_date timestamp NULL DEFAULT NULL,
            destination_training_id bigint unsigned DEFAULT NULL,
            needs_response tinyint(1) DEFAULT 1,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (upcoming_id),
            KEY idx_employee_id (employee_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    
    // Get first employee
    $stmt = $pdo->query("SELECT employee_id, first_name, last_name FROM employees LIMIT 1");
    $employee = $stmt->fetch();
    
    if ($employee) {
        echo "Employee: {$employee['employee_id']} - {$employee['first_name']} {$employee['last_name']}\n";
        
        // Clear existing data for this employee
        $stmt = $pdo->prepare("DELETE FROM upcoming_trainings WHERE employee_id = ?");
        $stmt->execute([$employee['employee_id']]);
        
        // Insert test data
        $stmt = $pdo->prepare("
            INSERT INTO upcoming_trainings 
            (employee_id, training_title, start_date, end_date, status, source, assigned_by, assigned_date, needs_response, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $now = date('Y-m-d H:i:s');
        
        // Insert 2 test records
        $trainings = [
            ['Communication Skills', 'competency_gap', 'Test Admin'],
            ['Leadership Development', 'competency_gap', 'HR Manager']
        ];
        
        foreach ($trainings as $training) {
            $stmt->execute([
                $employee['employee_id'],
                $training[0],
                date('Y-m-d'),
                date('Y-m-d', strtotime('+3 months')),
                'Assigned',
                $training[1],
                $training[2],
                $now,
                1,
                $now,
                $now
            ]);
            echo "Created: {$training[0]}\n";
        }
        
        // Verify data
        $stmt = $pdo->prepare("SELECT * FROM upcoming_trainings WHERE employee_id = ?");
        $stmt->execute([$employee['employee_id']]);
        $records = $stmt->fetchAll();
        
        echo "\nVerification - Found " . count($records) . " records:\n";
        foreach ($records as $record) {
            echo "- ID: {$record['upcoming_id']}, Title: {$record['training_title']}, Status: {$record['status']}, Source: {$record['source']}\n";
        }
        
        echo "\n✅ Test data created successfully!\n";
        echo "🔄 Now refresh the ESS portal and login as: {$employee['employee_id']}\n";
        echo "🌐 URL: http://127.0.0.1:8000/employee/login\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
