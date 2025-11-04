<?php
session_start();
require_once('includes/session.php');
require_once('../db_connect.php');

$stats = [
    'total' => 0,
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'claimed' => 0
];

try {
    $status_counts_sql = "SELECT status, COUNT(request_id) as count FROM requests GROUP BY status";
    $stmt = $pdo->query($status_counts_sql);
    $status_counts = $stmt->fetchAll();

    foreach ($status_counts as $row) {
        $status_key = strtolower(str_replace(' ', '_', $row['status']));
        if (array_key_exists($status_key, $stats)) {
            $stats[$status_key] = $row['count'];
        }
    }
    
    $stats['total'] = array_sum($stats); 
    
    $recent_sql = "SELECT request_id, customer_name, occasion, gift_size, wrapping_style, status, date_created 
                    FROM requests ORDER BY date_created DESC LIMIT 5";
    $recent_requests = $pdo->query($recent_sql)->fetchAll();

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}

$message = '';
if (isset($_SESSION['error_message'])) {
    $message = "<div class='alert error'>" . htmlspecialchars($_SESSION['error_message']) . "</div>";
    unset($_SESSION['error_message']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Gift Wrapping Service System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div id="content-wrapper">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
            </header>

            <main>
                <?php echo $message; ?> 
                
                <section class="dashboard-stats">
                    <h2>System Overview</h2>
                    <div class="stats-cards">
                        <div class="card total">
                            <h3>Total Requests</h3>
                            <p><?php echo $stats['total']; ?></p>
                        </div>
                        <div class="card pending">
                            <h3>Pending Orders</h3>
                            <p><?php echo $stats['pending']; ?></p>
                        </div>
                        <div class="card in-progress">
                            <h3>In Progress</h3>
                            <p><?php echo $stats['in_progress']; ?></p>
                        </div>
                        <div class="card completed">
                            <h3>Completed</h3>
                            <p><?php echo $stats['completed']; ?></p>
                        </div>
                        <div class="card claimed">
                            <h3>Claimed</h3>
                            <p><?php echo $stats['claimed']; ?></p>
                        </div>
                    </div>
                </section>

                <section class="recent-requests">
                    <h2>Recent Requests (Last 5)</h2>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Occasion</th>
                                <th>Style</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_requests)): ?>
                                <?php foreach ($recent_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                        <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['occasion']); ?></td>
                                        <td><?php echo htmlspecialchars($request['wrapping_style']); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($request['date_created'])); ?></td>
                                        <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                        <td><a href="view_request.php?id=<?php echo $request['request_id']; ?>" class="btn small-btn">View</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7">No recent requests found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="table-footer">
                        <a href="manage_requests.php" class="btn secondary-btn">View All Requests</a>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>