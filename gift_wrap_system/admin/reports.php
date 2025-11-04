<?php
require_once('includes/session.php');
require_once('../db_connect.php');

$report_data = [];
$stats_by_style = [];
$stats_by_occasion = [];

$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('-30 days'));
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : 'Completed';

if ($_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "Access denied. You do not have sufficient privileges.";
    header("Location: dashboard.php");
    exit;
}

try {
    $sql_report = "SELECT request_id, customer_name, occasion, wrapping_style, status, total_price, date_created 
                   FROM requests 
                   WHERE DATE(date_created) >= :date_from AND DATE(date_created) <= :date_to
                   AND status = :status_filter
                   ORDER BY date_created DESC";
    
    $stmt_report = $pdo->prepare($sql_report);
    $stmt_report->execute([
        ':date_from' => $date_from,
        ':date_to' => $date_to,
        ':status_filter' => $status_filter
    ]);
    $report_data = $stmt_report->fetchAll();

    $sql_style = "SELECT wrapping_style, COUNT(request_id) as count, SUM(total_price) as total_revenue
                  FROM requests 
                  WHERE DATE(date_created) >= :date_from AND DATE(date_created) <= :date_to
                  GROUP BY wrapping_style
                  ORDER BY count DESC";
    $stmt_style = $pdo->prepare($sql_style);
    $stmt_style->execute([':date_from' => $date_from, ':date_to' => $date_to]);
    $stats_by_style = $stmt_style->fetchAll();

    $sql_occasion = "SELECT occasion, COUNT(request_id) as count
                     FROM requests 
                     WHERE DATE(date_created) >= :date_from AND DATE(date_created) <= :date_to
                     GROUP BY occasion
                     ORDER BY count DESC";
    $stmt_occasion = $pdo->prepare($sql_occasion);
    $stmt_occasion->execute([':date_from' => $date_from, ':date_to' => $date_to]);
    $stats_by_occasion = $stmt_occasion->fetchAll();
    
} catch (PDOException $e) {
    $message = "<div class='alert error'>Database Error: Could not generate reports.</div>";
}

$grand_total_revenue = array_sum(array_column($report_data, 'total_price'));
$total_requests_in_report = count($report_data);
$status_options = ['Pending', 'In Progress', 'Completed', 'Claimed']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports & Analytics | Gift Wrap System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    </head>
<body>

    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div id="content-wrapper">
            <header>
                <h1>📈 Reports & Analytics</h1>
            </header>

            <main>
                <?php echo isset($message) ? $message : ''; ?>

                <section class="report-filters card">
                    <h2>Filter Report Data</h2>
                    <form method="GET" action="reports.php" class="filter-form date-filter">
                        <div class="form-group">
                            <label for="date_from">Date From:</label>
                            <input type="date" name="date_from" id="date_from" value="<?php echo htmlspecialchars($date_from); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="date_to">Date To:</label>
                            <input type="date" name="date_to" id="date_to" value="<?php echo htmlspecialchars($date_to); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="status_filter">Filter Status:</label>
                            <select name="status_filter" id="status_filter">
                                <?php foreach ($status_options as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php echo ($status_filter === $option) ? 'selected' : ''; ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn primary-btn">Generate Report</button>
                    </form>
                </section>
                
                <hr>

                <section class="report-summary-stats">
                    <h2>Summary Statistics (<?php echo date('M d, Y', strtotime($date_from)); ?> to <?php echo date('M d, Y', strtotime($date_to)); ?>)</h2>
                    <div class="stats-cards">
                        <div class="card total">
                            <h3>Total Requests (Filtered)</h3>
                            <p><?php echo $total_requests_in_report; ?></p>
                        </div>
                        <div class="card completed">
                            <h3>Total Revenue (Filtered)</h3>
                            <p>₱<?php echo number_format($grand_total_revenue, 2); ?></p>
                        </div>
                    </div>
                </section>

                <div class="summary-tables-container">
                    
                    <section class="report-by-style card">
                        <h3>Requests by Wrapping Style</h3>
                        <table class="data-table">
                            <thead>
                                <tr><th>Style</th><th>Count</th><th>Revenue</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_by_style as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['wrapping_style']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['count']); ?></td>
                                        <td>₱<?php echo number_format($stat['total_revenue'], 2); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>

                    <section class="report-by-occasion card">
                        <h3>Requests by Occasion</h3>
                        <table class="data-table">
                            <thead>
                                <tr><th>Occasion</th><th>Count</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats_by_occasion as $stat): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($stat['occasion']); ?></td>
                                        <td><?php echo htmlspecialchars($stat['count']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </section>
                </div>
                
                <hr>

                <section class="detailed-report">
                    <h2>Detailed Report (<?php echo htmlspecialchars($status_filter); ?> Orders)</h2>
                    <table class="data-table full-width">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Occasion</th>
                                <th>Style</th>
                                <th>Price</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($report_data)): ?>
                                <?php foreach ($report_data as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                        <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['occasion']); ?></td>
                                        <td><?php echo htmlspecialchars($request['wrapping_style']); ?></td>
                                        <td>₱<?php echo number_format($request['total_price'], 2); ?></td>
                                        <td><?php echo date('Y-m-d H:i', strtotime($request['date_created'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No <?php echo htmlspecialchars($status_filter); ?> requests found in the selected date range.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div class="table-footer">
                        <button onclick="window.print()" class="btn secondary-btn print-btn">Print Report</button>
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