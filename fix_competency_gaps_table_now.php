<?php

/**
 * Fix Competency Gaps Table - Direct Database Creation
 * 
 * This script creates the missing competency_gaps table that's causing
 * the "competency_gap_analysis/1 could not be found" error.
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'hr2system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    echo "🔗 Connecting to database: {$dbname}@{$host}\n";
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // Check if competency_gaps table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'competency_gaps'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "ℹ️  competency_gaps table already exists. Checking structure...\n";
        
        // Check for missing columns
        $stmt = $pdo->prepare("DESCRIBE competency_gaps");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        $columnNames = array_column($columns, 'Field');
        
        $requiredColumns = ['expired_date', 'is_active'];
        $missingColumns = array_diff($requiredColumns, $columnNames);
        
        if (!empty($missingColumns)) {
            echo "🔧 Adding missing columns: " . implode(', ', $missingColumns) . "\n";
            
            foreach ($missingColumns as $column) {
                if ($column === 'expired_date') {
                    $pdo->exec("ALTER TABLE competency_gaps ADD COLUMN expired_date TIMESTAMP NULL");
                    echo "✅ Added expired_date column\n";
                } elseif ($column === 'is_active') {
                    $pdo->exec("ALTER TABLE competency_gaps ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
                    echo "✅ Added is_active column\n";
                }
            }
        } else {
            echo "✅ All required columns exist\n";
        }
    } else {
        echo "🚀 Creating competency_gaps table...\n";
        
        $createTableSQL = "
        CREATE TABLE competency_gaps (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id VARCHAR(20) NOT NULL,
            competency_id BIGINT UNSIGNED NOT NULL,
            required_level INT NOT NULL,
            current_level INT NOT NULL,
            gap INT NOT NULL,
            gap_description TEXT NULL,
            expired_date TIMESTAMP NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            INDEX idx_employee_competency (employee_id, competency_id),
            INDEX idx_is_active (is_active),
            INDEX idx_expired_date (expired_date)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "✅ competency_gaps table created successfully!\n";
        
        // Add foreign key constraints (with error handling)
        try {
            $pdo->exec("
                ALTER TABLE competency_gaps 
                ADD CONSTRAINT fk_competency_gaps_employee 
                FOREIGN KEY (employee_id) REFERENCES employees(employee_id) ON DELETE CASCADE
            ");
            echo "✅ Added employee_id foreign key constraint\n";
        } catch (Exception $e) {
            echo "⚠️  Could not add employee_id foreign key: " . $e->getMessage() . "\n";
        }
        
        try {
            $pdo->exec("
                ALTER TABLE competency_gaps 
                ADD CONSTRAINT fk_competency_gaps_competency 
                FOREIGN KEY (competency_id) REFERENCES competency_library(id) ON DELETE CASCADE
            ");
            echo "✅ Added competency_id foreign key constraint\n";
        } catch (Exception $e) {
            echo "⚠️  Could not add competency_id foreign key: " . $e->getMessage() . "\n";
        }
        
        // Create sample data if tables are empty
        echo "\n🔍 Checking for existing data...\n";
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM competency_gaps");
        $stmt->execute();
        $gapCount = $stmt->fetch()['count'];
        
        if ($gapCount == 0) {
            echo "📝 Creating sample competency gap data...\n";
            
            // Get sample employees and competencies
            $stmt = $pdo->prepare("SELECT employee_id, first_name, last_name FROM employees LIMIT 3");
            $stmt->execute();
            $employees = $stmt->fetchAll();
            
            $stmt = $pdo->prepare("SELECT id, competency_name FROM competency_library LIMIT 5");
            $stmt->execute();
            $competencies = $stmt->fetchAll();
            
            if (!empty($employees) && !empty($competencies)) {
                $insertStmt = $pdo->prepare("
                    INSERT INTO competency_gaps (employee_id, competency_id, required_level, current_level, gap, gap_description, expired_date, is_active)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $sampleCount = 0;
                foreach ($employees as $employee) {
                    foreach (array_slice($competencies, 0, 2) as $competency) {
                        $requiredLevel = rand(3, 5);
                        $currentLevel = rand(1, 3);
                        $gap = max(0, $requiredLevel - $currentLevel);
                        $expiredDate = date('Y-m-d H:i:s', strtotime('+30 days'));
                        
                        $insertStmt->execute([
                            $employee['employee_id'],
                            $competency['id'],
                            $requiredLevel,
                            $currentLevel,
                            $gap,
                            "Sample gap analysis for {$competency['competency_name']} - Employee: {$employee['first_name']} {$employee['last_name']}",
                            $expiredDate,
                            1
                        ]);
                        $sampleCount++;
                    }
                }
                echo "✅ Created {$sampleCount} sample competency gap records\n";
            } else {
                echo "⚠️  No employees or competencies found for sample data\n";
            }
        } else {
            echo "ℹ️  Found {$gapCount} existing competency gap records\n";
        }
    }
    
    echo "\n🎉 Competency gaps table setup completed successfully!\n";
    echo "🔗 You can now access the competency gap analysis page without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    exit(1);
}
