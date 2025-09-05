<?php

/**
 * Test Competency Gap Route - Debug Laravel Error
 */

require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    echo "🔍 Testing Laravel application bootstrap...\n";
    
    // Bootstrap Laravel application
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    echo "✅ Laravel application bootstrapped successfully\n";
    
    // Test database connection
    echo "🔗 Testing database connection...\n";
    
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_DATABASE'] ?? 'hr2system';
    $username = $_ENV['DB_USERNAME'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';
    
    $pdo = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    
    echo "✅ Database connection successful\n";
    
    // Test competency_gaps table
    echo "📋 Testing competency_gaps table...\n";
    
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM competency_gaps");
    $stmt->execute();
    $count = $stmt->fetch()['count'];
    
    echo "✅ Found {$count} records in competency_gaps table\n";
    
    // Test CompetencyGap model
    echo "🔧 Testing CompetencyGap model...\n";
    
    // Create a mock request to test the controller
    $request = Illuminate\Http\Request::create('/admin/competency-gap-analysis', 'GET');
    
    // Test controller instantiation
    $controller = new App\Http\Controllers\CompetencyGapAnalysisController();
    echo "✅ CompetencyGapAnalysisController instantiated successfully\n";
    
    // Test model access
    $gaps = App\Models\CompetencyGap::count();
    echo "✅ CompetencyGap model accessible, found {$gaps} records\n";
    
    // Test relationships
    $employees = App\Models\Employee::count();
    $competencies = App\Models\CompetencyLibrary::count();
    
    echo "✅ Found {$employees} employees and {$competencies} competencies\n";
    
    echo "\n🎉 All tests passed! The competency gap functionality should work.\n";
    echo "🌐 Try accessing: http://localhost:8000/admin/competency-gap-analysis\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "🔍 Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
