<?php

// Direct database test without Laravel
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DIRECT DATABASE TEST ===\n\n";
    
    // Check if upcoming_trainings table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'upcoming_trainings'");
    if ($stmt->rowCount() == 0) {
        echo "Creating upcoming_trainings table...\n";
        $pdo->exec("
            CREATE TABLE upcoming_trainings (
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
        echo "✅ Table created\n";
    } else {
        echo "✅ Table exists\n";
    }
    
    // Check current data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM upcoming_trainings");
    $count = $stmt->fetch()['count'];
    echo "Current records: $count\n";
    
    // Get first employee
    $stmt = $pdo->query("SELECT employee_id, first_name, last_name FROM employees LIMIT 1");
    $employee = $stmt->fetch();
    
    if ($employee) {
        echo "Test employee: {$employee['employee_id']} - {$employee['first_name']} {$employee['last_name']}\n";
        
        // Insert test record
        $stmt = $pdo->prepare("
            INSERT INTO upcoming_trainings 
            (employee_id, training_title, start_date, end_date, status, source, assigned_by, assigned_date, needs_response, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE updated_at = VALUES(updated_at)
        ");
        
        $now = date('Y-m-d H:i:s');
        $stmt->execute([
            $employee['employee_id'],
            'Communication Skills Training',
            date('Y-m-d'),
            date('Y-m-d', strtotime('+3 months')),
            'Assigned',
            'competency_gap',
            'Test Admin',
            $now,
            1,
            $now,
            $now
        ]);
        
        echo "✅ Test record inserted\n";
        
        // Verify insertion
        $stmt = $pdo->prepare("SELECT * FROM upcoming_trainings WHERE employee_id = ?");
        $stmt->execute([$employee['employee_id']]);
        $records = $stmt->fetchAll();
        
        echo "Records for {$employee['employee_id']}: " . count($records) . "\n";
        foreach ($records as $record) {
            echo "  - {$record['training_title']} ({$record['status']}) - Source: {$record['source']}\n";
        }
    }
    
    echo "\n=== TEST COMPLETE ===\n";
    echo "Data should now appear in ESS portal!\n";
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
