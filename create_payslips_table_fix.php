<?php
/**
 * Direct PHP script to create payslips table
 * This fixes the SQLSTATE[42S02] error where payslips table doesn't exist
 */

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'hr2system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Drop table if exists
    $pdo->exec("DROP TABLE IF EXISTS `payslips`");
    echo "Dropped existing payslips table (if it existed).\n";
    
    // Create payslips table with complete structure
    $createTableSQL = "
    CREATE TABLE `payslips` (
        `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        `payslip_id` varchar(255) NOT NULL,
        `employee_id` varchar(20) NOT NULL,
        `pay_period` varchar(255) NOT NULL,
        `period_start` date DEFAULT NULL,
        `period_end` date DEFAULT NULL,
        `basic_pay` decimal(10,2) NOT NULL,
        `overtime_pay` decimal(10,2) DEFAULT NULL,
        `allowances` decimal(10,2) DEFAULT NULL,
        `gross_pay` decimal(10,2) DEFAULT NULL,
        `tax_deduction` decimal(10,2) DEFAULT NULL,
        `sss_deduction` decimal(10,2) DEFAULT NULL,
        `philhealth_deduction` decimal(10,2) DEFAULT NULL,
        `pagibig_deduction` decimal(10,2) DEFAULT NULL,
        `other_deductions` decimal(10,2) DEFAULT NULL,
        `total_deductions` decimal(10,2) DEFAULT NULL,
        `net_pay` decimal(10,2) NOT NULL,
        `release_date` date NOT NULL,
        `payslip_file` varchar(255) DEFAULT NULL,
        `status` varchar(255) NOT NULL DEFAULT 'Released',
        `created_at` timestamp NULL DEFAULT NULL,
        `updated_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `payslips_payslip_id_unique` (`payslip_id`),
        KEY `payslips_employee_id_foreign` (`employee_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($createTableSQL);
    echo "Created payslips table successfully.\n";
    
    // Add foreign key constraint (separate to handle potential errors)
    try {
        $pdo->exec("ALTER TABLE `payslips` ADD CONSTRAINT `payslips_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE");
        echo "Added foreign key constraint successfully.\n";
    } catch (PDOException $e) {
        echo "Warning: Could not add foreign key constraint: " . $e->getMessage() . "\n";
        echo "This is okay if the employees table doesn't exist yet.\n";
    }
    
    // Insert sample payslip data
    $insertSQL = "
    INSERT INTO `payslips` (`payslip_id`, `employee_id`, `pay_period`, `period_start`, `period_end`, `basic_pay`, `overtime_pay`, `allowances`, `gross_pay`, `tax_deduction`, `sss_deduction`, `philhealth_deduction`, `pagibig_deduction`, `other_deductions`, `total_deductions`, `net_pay`, `release_date`, `status`, `created_at`, `updated_at`) VALUES
    ('PS001', 'EMP001', 'January 2024', '2024-01-01', '2024-01-31', 45000.00, 5000.00, 8000.00, 58000.00, 8700.00, 2900.00, 1740.00, 1160.00, 0.00, 14500.00, 43500.00, '2024-02-05', 'Released', NOW(), NOW()),
    ('PS002', 'EMP001', 'February 2024', '2024-02-01', '2024-02-29', 45000.00, 3000.00, 8000.00, 56000.00, 8400.00, 2800.00, 1680.00, 1120.00, 0.00, 14000.00, 42000.00, '2024-03-05', 'Released', NOW(), NOW()),
    ('PS003', 'EMP001', 'March 2024', '2024-03-01', '2024-03-31', 45000.00, 4000.00, 8000.00, 57000.00, 8550.00, 2850.00, 1710.00, 1140.00, 0.00, 14250.00, 42750.00, '2024-04-05', 'Released', NOW(), NOW()),
    ('PS004', 'EMP001', 'April 2024', '2024-04-01', '2024-04-30', 45000.00, 6000.00, 8000.00, 59000.00, 8850.00, 2950.00, 1770.00, 1180.00, 0.00, 14750.00, 44250.00, '2024-05-05', 'Released', NOW(), NOW()),
    ('PS005', 'EMP001', 'May 2024', '2024-05-01', '2024-05-31', 45000.00, 2000.00, 8000.00, 55000.00, 8250.00, 2750.00, 1650.00, 1100.00, 0.00, 13750.00, 41250.00, '2024-06-05', 'Released', NOW(), NOW()),
    ('PS006', 'EMP001', 'June 2024', '2024-06-01', '2024-06-30', 45000.00, 7000.00, 8000.00, 60000.00, 9000.00, 3000.00, 1800.00, 1200.00, 0.00, 15000.00, 45000.00, '2024-07-05', 'Released', NOW(), NOW())";
    
    $pdo->exec($insertSQL);
    echo "Inserted sample payslip data successfully.\n";
    
    // Verify table creation
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM payslips");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Verification: payslips table now contains " . $result['count'] . " records.\n";
    
    echo "\n✅ SUCCESS: Payslips table created and populated successfully!\n";
    echo "The SQLSTATE[42S02] error should now be resolved.\n";
    
} catch (PDOException $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
?>
