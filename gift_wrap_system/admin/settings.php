<?php
require_once('includes/session.php');
require_once('../db_connect.php');

$message = '';

// Placeholder for future logic, e.g., handling password changes or backup requests.
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) { ... }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Settings | Gift Wrap System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div id="content-wrapper">
            <header>
                <h1>⚙️ Admin Settings</h1>
            </header>

            <main>
                <?php echo $message; ?>
                
                <section class="card settings-section">
                    <h2>Account Management</h2>
                    <p>Change your password or update your staff profile.</p>
                    <a href="#" class="btn secondary-btn">Change Password</a>
                </section>

                <section class="card settings-section">
                    <h2>System Maintenance</h2>
                    <p>Perform system backups or clear old data.</p>
                    <a href="#" class="btn secondary-btn disabled-btn">Export Database Backup (Feature Pending)</a>
                </section>
                
                <?php if ($_SESSION['role'] === 'admin'): ?>
                <section class="card settings-section">
                    <h2>Staff Accounts</h2>
                    <p>Manage staff and administrator access levels.</p>
                    <a href="#" class="btn secondary-btn">Manage Users</a>
                </section>
                <?php endif; ?>

            </main>
        </div>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>