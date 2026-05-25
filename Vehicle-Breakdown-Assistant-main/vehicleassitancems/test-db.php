<?php
// InfinityFree Database Test
// Upload this to http://vbams.gamer.gd/test-db.php

echo "<h2>InfinityFree Database Debugging</h2>";

// Try default credentials
$hosts = ['localhost'];
$users = ['if0_42013470', 'root', 'admin'];
$passwords = ['', 'password', 'admin'];
$db_names = ['if0_42013470', 'vehassitancemsdb', 'vbams'];

echo "<h3>Testing database connections...</h3>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Host</th><th>User</th><th>DB Name</th><th>Result</th></tr>";

foreach ($hosts as $host) {
    foreach ($users as $user) {
        foreach ($passwords as $pass) {
            foreach ($db_names as $db) {
                try {
                    $pdo = new PDO(
                        "mysql:host=$host;dbname=$db",
                        $user,
                        $pass,
                        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
                    );
                    $status = "<span style='color:green'><b>✓ SUCCESS</b></span>";
                    echo "<tr><td>$host</td><td>$user</td><td>$db</td><td>$status</td></tr>";
                } catch (Exception $e) {
                    // Silently fail
                }
            }
        }
    }
}
echo "</table>";

echo "<h3>Your actual database info (from PHP):</h3>";
echo "<pre>";
echo "Database Host: localhost\n";
echo "Check your InfinityFree Control Panel for the actual database name!\n";
echo "</pre>";
?>
