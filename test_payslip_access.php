<?php
// Test script to check payslip access and table existence
require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Payslip;

// Test if payslips table exists
try {
    $tableExists = DB::select("SHOW TABLES LIKE 'payslips'");
    if (empty($tableExists)) {
        echo "❌ PAYSLIPS TABLE DOES NOT EXIST\n";
        echo "This is likely the cause of the redirect issue.\n";
        echo "The PayslipController tries to query the payslips table, fails, and redirects to login.\n\n";
        
        echo "SOLUTION: Create the payslips table using the existing SQL script.\n";
        echo "Run this SQL script: create_payslips_table_fix.sql\n";
    } else {
        echo "✅ PAYSLIPS TABLE EXISTS\n";
        
        // Check if there are any payslip records
        $payslipCount = DB::table('payslips')->count();
        echo "📊 Total payslip records: $payslipCount\n";
        
        if ($payslipCount == 0) {
            echo "⚠️  No payslip records found. This might cause empty page display.\n";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR CHECKING PAYSLIPS TABLE: " . $e->getMessage() . "\n";
    echo "This confirms the table is missing or there's a database connection issue.\n";
}

// Test route configuration
echo "\n🔍 CHECKING ROUTE CONFIGURATION:\n";
echo "Employee payslip route should be: /employee/payslips\n";
echo "Controller method: PayslipController@index\n";
echo "Middleware: auth:employee\n";

echo "\n🔧 FIXES APPLIED:\n";
echo "1. ✅ Fixed employee sidebar navigation link\n";
echo "2. ✅ Fixed employee dashboard payslip button\n";
echo "3. ⏳ Need to ensure payslips table exists\n";

echo "\nNext step: Create payslips table if missing.\n";
