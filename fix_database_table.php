<?php
// Direct database connection to create missing table
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hr2system";

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected successfully to database: $dbname\n";
    
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'employee_training_dashboards'");
    
    if ($result->num_rows > 0) {
        echo "Table 'employee_training_dashboards' already exists.\n";
    } else {
        echo "Creating table 'employee_training_dashboards'...\n";
        
        $sql = "CREATE TABLE `employee_training_dashboards` (
            `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            `employee_id` varchar(20) NOT NULL,
            `course_id` bigint(20) UNSIGNED NOT NULL,
            `training_date` date DEFAULT NULL,
            `progress` int(11) NOT NULL DEFAULT 0,
            `status` varchar(255) NOT NULL DEFAULT 'Not Started',
            `remarks` text DEFAULT NULL,
            `last_accessed` timestamp NULL DEFAULT NULL,
            `assigned_by` bigint(20) UNSIGNED DEFAULT NULL,
            `expired_date` timestamp NULL DEFAULT NULL,
            `created_at` timestamp NULL DEFAULT NULL,
            `updated_at` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `employee_training_dashboards_employee_id_index` (`employee_id`),
            KEY `employee_training_dashboards_course_id_index` (`course_id`),
            KEY `employee_training_dashboards_status_index` (`status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($sql) === TRUE) {
            echo "Table 'employee_training_dashboards' created successfully!\n";
        } else {
            echo "Error creating table: " . $conn->error . "\n";
        }
    }
    
    // Verify table structure
    $result = $conn->query("DESCRIBE employee_training_dashboards");
    if ($result) {
        echo "\nTable structure verified:\n";
        while($row = $result->fetch_assoc()) {
            echo "- " . $row["Field"] . " (" . $row["Type"] . ")\n";
        }
    }
    
    $conn->close();
    echo "\nDatabase operation completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
