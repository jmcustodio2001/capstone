<?php

// Direct database connection to fix upcoming_trainings table
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'hr2system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'upcoming_trainings'");
    if ($stmt->rowCount() == 0) {
        echo "Creating upcoming_trainings table...\n";
        
        $createTable = "
        CREATE TABLE upcoming_trainings (
            upcoming_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(20) NOT NULL,
            training_title VARCHAR(255) NOT NULL,
            start_date DATE,
            end_date DATE,
            status VARCHAR(255) DEFAULT 'Assigned',
            source VARCHAR(255) NULL,
            assigned_by VARCHAR(255) NULL,
            assigned_date TIMESTAMP NULL,
            destination_training_id BIGINT UNSIGNED NULL,
            needs_response BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_employee_id (employee_id),
            INDEX idx_destination_training_id (destination_training_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTable);
        echo "✅ Created upcoming_trainings table with correct schema.\n";
    } else {
        echo "Table exists. Checking and fixing schema...\n";
        
        // Get column info for employee_id
        $stmt = $pdo->query("SHOW COLUMNS FROM upcoming_trainings WHERE Field = 'employee_id'");
        $column = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($column) {
            echo "Current employee_id type: " . $column['Type'] . "\n";
            
            // Always fix the column to ensure it's VARCHAR(20)
            echo "Fixing employee_id column type to VARCHAR(20)...\n";
            $pdo->exec("ALTER TABLE upcoming_trainings MODIFY COLUMN employee_id VARCHAR(20) NOT NULL");
            echo "✅ Fixed employee_id column to VARCHAR(20).\n";
        }
        
        // Also ensure start_date and end_date are DATE type, not TIMESTAMP
        echo "Fixing date column types...\n";
        $pdo->exec("ALTER TABLE upcoming_trainings MODIFY COLUMN start_date DATE");
        $pdo->exec("ALTER TABLE upcoming_trainings MODIFY COLUMN end_date DATE");
        echo "✅ Fixed date column types.\n";
    }
    
    // Show final structure
    echo "\nFinal table structure:\n";
    $stmt = $pdo->query("DESCRIBE upcoming_trainings");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "- {$row['Field']}: {$row['Type']}\n";
    }
    
    // Show existing data
    echo "\nExisting data:\n";
    $stmt = $pdo->query("SELECT * FROM upcoming_trainings LIMIT 5");
    $count = 0;
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $count++;
        echo "Row $count: employee_id={$row['employee_id']}, training_title={$row['training_title']}\n";
    }
    
    if ($count == 0) {
        echo "No existing data in table.\n";
    }
    
    echo "\n🎉 upcoming_trainings table is ready!\n";
    echo "The 'Assign to Upcoming Training' button should now work properly.\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
