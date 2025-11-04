<?php 
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside id="sidebar">
    <div class="sidebar-header">
        <h3>Mikola's Gift System</h3>
        <p>Admin Panel</p>
    </div>
    <nav class="sidebar-nav">
        <ul>
            <li class="<?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>">
                <a href="dashboard.php">📊 Dashboard</a>
            </li>
            <li class="<?php echo ($current_page == 'manage_requests.php' || $current_page == 'view_request.php') ? 'active' : ''; ?>">
                <a href="manage_requests.php">📋 Manage Requests</a>
            </li>
            <li class="<?php echo ($current_page == 'manage_options.php') ? 'active' : ''; ?>">
                <a href="manage_options.php">🛠 Manage Options</a>
            </li>
            <li class="<?php echo ($current_page == 'reports.php') ? 'active' : ''; ?>">
                <a href="reports.php">📈 Reports & Analytics</a>
            </li>
            <li class="<?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
                <a href="settings.php">⚙️ Admin Settings</a>
            </li>
            <li>
                <a href="includes/logout.php" class="btn logout-btn">Logout</a>
            </li>
        </ul>
    </nav>
</aside>