<?php

// Simple script to clean attendance data
// Connect to database and remove automatic entries

$host = 'localhost';
$database = 'hr2system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Connected to database successfully.\n";
    
    // Check current data
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM attendance_time_logs");
    $total = $stmt->fetch()['total'];
    echo "Current attendance entries: $total\n";
    
    // Show sample data
    $stmt = $pdo->query("SELECT log_id, employee_id, log_date, time_in, time_out, hours_worked FROM attendance_time_logs ORDER BY log_date DESC LIMIT 5");
    echo "\nSample entries:\n";
    while ($row = $stmt->fetch()) {
        echo "ID: {$row['log_id']}, Employee: {$row['employee_id']}, Date: {$row['log_date']}, In: {$row['time_in']}, Out: {$row['time_out']}, Hours: {$row['hours_worked']}\n";
    }
    
    // Delete all entries to start fresh
    echo "\nRemoving all automatic attendance entries...\n";
    $stmt = $pdo->prepare("DELETE FROM attendance_time_logs");
    $stmt->execute();
    
    $deletedCount = $stmt->rowCount();
    echo "✅ Deleted $deletedCount attendance entries.\n";
    
    // Verify cleanup
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM attendance_time_logs");
    $remaining = $stmt->fetch()['total'];
    echo "Remaining entries: $remaining\n";
    
    echo "\n✅ Attendance table cleaned successfully!\n";
    echo "✅ Only manual clock-ins will be recorded going forward.\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}
