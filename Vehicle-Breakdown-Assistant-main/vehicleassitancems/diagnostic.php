<?php
/**
 * VBAMS Diagnostic Script
 * Place this in public_html/vehicleassitancems/diagnostic.php
 * Access at: http://vbams.gamer.gd/diagnostic.php
 */

echo "<h1>VBAMS Diagnostic Report</h1>";
echo "<hr>";

// 1. PHP Version
echo "<h3>1. PHP Version</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";

// 2. Required Extensions
echo "<h3>2. Required Extensions</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'json'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✓ OK" : "✗ MISSING";
    echo "<p>$ext: $status</p>";
}

// 3. File Permissions
echo "<h3>3. Critical Files</h3>";
$critical_files = [
    'includes/dbconnection.php' => 'Database Connection',
    'includes/header.php' => 'Header Include',
    'includes/footer.php' => 'Footer Include',
    'admin/login.php' => 'Admin Login',
    'driver/login.php' => 'Driver Login',
    'index.php' => 'Home Page'
];

foreach ($critical_files as $file => $desc) {
    $exists = file_exists($file) ? "✓ EXISTS" : "✗ MISSING";
    echo "<p>$desc ($file): $exists</p>";
}

// 4. Database Connection Test
echo "<h3>4. Database Connection</h3>";
try {
    include('includes/dbconnection.php');
    echo "<p>✓ Database connection successful</p>";
    
    // Check tables
    $tables = ['users', 'bookings', 'drivers'];
    foreach ($tables as $table) {
        $result = $dbh->query("SHOW TABLES LIKE '$table'");
        $status = $result->rowCount() > 0 ? "✓ EXISTS" : "✗ MISSING";
        echo "<p>  Table '$table': $status</p>";
    }
} catch (Exception $e) {
    echo "<p>✗ Database Error: " . $e->getMessage() . "</p>";
}

// 5. Directory Listing
echo "<h3>5. Directory Structure</h3>";
echo "<pre>";
echo "Root Directory Contents:\n";
$files = scandir('.');
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        if (is_dir($file)) {
            echo "[DIR] $file\n";
        } else {
            echo "[FILE] $file\n";
        }
    }
}
echo "</pre>";

echo "<hr>";
echo "<p><strong>If you see errors above, report them to hosting support.</strong></p>";
?>
