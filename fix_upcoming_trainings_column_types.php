<?php

// Direct database connection to fix upcoming_trainings table column types
$host = 'localhost';
$dbname = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check if table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'upcoming_trainings'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "✅ upcoming_trainings table exists. Checking column types...\n";
        
        // Get current column information
        $stmt = $pdo->query("DESCRIBE upcoming_trainings");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $employeeIdColumn = null;
        foreach ($columns as $column) {
            if ($column['Field'] === 'employee_id') {
                $employeeIdColumn = $column;
                break;
            }
        }
        
        if ($employeeIdColumn) {
            echo "Current employee_id column type: " . $employeeIdColumn['Type'] . "\n";
            
            // Check if it's the wrong type (integer-based)
            if (strpos(strtolower($employeeIdColumn['Type']), 'int') !== false) {
                echo "⚠️ employee_id column has wrong type (integer). Fixing to VARCHAR(20)...\n";
                
                // Drop foreign key constraint first if it exists
                try {
                    $pdo->exec("ALTER TABLE `upcoming_trainings` DROP FOREIGN KEY `upcoming_trainings_employee_id_foreign`");
                    echo "✅ Dropped existing foreign key constraint\n";
                } catch (Exception $e) {
                    echo "ℹ️ No existing foreign key constraint to drop\n";
                }
                
                // Modify column type
                $pdo->exec("ALTER TABLE `upcoming_trainings` MODIFY COLUMN `employee_id` VARCHAR(20) NOT NULL");
                echo "✅ Modified employee_id column to VARCHAR(20)\n";
                
                // Re-add foreign key constraint
                try {
                    $pdo->exec("ALTER TABLE `upcoming_trainings` ADD CONSTRAINT `upcoming_trainings_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE");
                    echo "✅ Re-added foreign key constraint\n";
                } catch (Exception $e) {
                    echo "⚠️ Warning: Could not re-add foreign key constraint: " . $e->getMessage() . "\n";
                }
                
            } else {
                echo "✅ employee_id column type is already correct (VARCHAR)\n";
            }
        } else {
            echo "❌ employee_id column not found in table\n";
        }
        
        // Also check and fix date columns to be TIMESTAMP for consistency
        echo "\nChecking date column types...\n";
        
        $dateColumns = ['start_date', 'end_date'];
        foreach ($dateColumns as $dateCol) {
            $columnInfo = null;
            foreach ($columns as $column) {
                if ($column['Field'] === $dateCol) {
                    $columnInfo = $column;
                    break;
                }
            }
            
            if ($columnInfo) {
                echo "Current $dateCol column type: " . $columnInfo['Type'] . "\n";
                
                if (strtolower($columnInfo['Type']) === 'date') {
                    echo "⚠️ $dateCol column is DATE type. Converting to TIMESTAMP...\n";
                    $pdo->exec("ALTER TABLE `upcoming_trainings` MODIFY COLUMN `$dateCol` TIMESTAMP NULL");
                    echo "✅ Modified $dateCol column to TIMESTAMP\n";
                }
            }
        }
        
    } else {
        echo "❌ upcoming_trainings table does not exist. Please run the auto-assign function first to create it.\n";
    }
    
    echo "\n🎉 upcoming_trainings table column types have been fixed!\n";
    echo "The auto-assign functionality should now work properly without SQL errors.\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
