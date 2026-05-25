<?php
/**
 * VBAMS Setup Wizard
 * Access at: http://vbams.gamer.gd/setup.php
 * This helps verify everything is set up correctly
 */

session_start();
$step = $_GET['step'] ?? 1;

?>
<!DOCTYPE html>
<html>
<head>
    <title>VBAMS Setup Wizard</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        .step { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .success { background: #d4edda; border-color: #28a745; color: #155724; }
        .error { background: #f8d7da; border-color: #dc3545; color: #721c24; }
        .info { background: #d1ecf1; border-color: #17a2b8; color: #0c5460; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .code { background: #f4f4f4; padding: 10px; border-left: 3px solid #007bff; font-family: monospace; overflow-x: auto; }
    </style>
</head>
<body>

<div class="container">
    <h1>VBAMS Setup Wizard</h1>
    <p>Follow these steps to complete your setup</p>

    <?php if($step == 1): ?>
        <div class="step info">
            <h2>Step 1: Check File Upload</h2>
            <p>Make sure the following files exist:</p>
            <ul>
                <li><?php echo file_exists('index.php') ? '✓' : '✗'; ?> index.php</li>
                <li><?php echo file_exists('admin/login.php') ? '✓' : '✗'; ?> admin/login.php</li>
                <li><?php echo file_exists('driver/login.php') ? '✓' : '✗'; ?> driver/login.php</li>
                <li><?php echo file_exists('includes/dbconnection.php') ? '✓' : '✗'; ?> includes/dbconnection.php</li>
            </ul>
            <p><strong>If all show ✓, files are uploaded correctly!</strong></p>
            <br>
            <button onclick="location.href='setup.php?step=2'">Next Step →</button>
        </div>

    <?php elseif($step == 2): ?>
        <div class="step info">
            <h2>Step 2: Check Database Connection</h2>
            <?php
                try {
                    include('includes/dbconnection.php');
                    echo '<div class="success">✓ Database connection successful!</div>';
                    
                    // Check tables
                    $tables = ['users', 'bookings', 'drivers'];
                    echo '<h3>Database Tables:</h3>';
                    foreach ($tables as $table) {
                        $result = $dbh->query("SHOW TABLES LIKE '$table'");
                        if ($result->rowCount() > 0) {
                            echo "<p>✓ Table <strong>$table</strong> exists</p>";
                        } else {
                            echo "<p>✗ Table <strong>$table</strong> NOT FOUND - Database may not be imported</p>";
                        }
                    }
                } catch (Exception $e) {
                    echo '<div class="error">✗ Database Error: ' . $e->getMessage() . '</div>';
                    echo '<p><strong>Solution:</strong> Make sure you imported the SQL file in PhpMyAdmin</p>';
                }
            ?>
            <br><br>
            <button onclick="location.href='setup.php?step=3'">Next Step →</button>
        </div>

    <?php elseif($step == 3): ?>
        <div class="step success">
            <h2>Step 3: Test Login</h2>
            <p><strong>Congratulations! Your setup is complete!</strong></p>
            <p>You can now access:</p>
            <ul>
                <li><a href="index.php" target="_blank"><strong>Frontend:</strong> http://vbams.gamer.gd</a></li>
                <li><a href="admin/login.php" target="_blank"><strong>Admin Panel:</strong> http://vbams.gamer.gd/admin/</a></li>
                <li><a href="driver/login.php" target="_blank"><strong>Driver Panel:</strong> http://vbams.gamer.gd/driver/</a></li>
            </ul>
            <h3>Default Credentials:</h3>
            <div class="code">
                Admin: admin / Test@123<br>
                Driver: test123 / Test@123
            </div>
            <h3>Next Steps:</h3>
            <p>1. Log in with the credentials above</p>
            <p>2. Delete this setup.php file for security</p>
            <p>3. Start using VBAMS!</p>
        </div>

    <?php endif; ?>

</div>

</body>
</html>
