<?php
require_once('includes/session.php');
require_once('../db_connect.php');

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';

$sql_condition = [];
$params = [];

if (!empty($search_term)) {
    $sql_condition[] = "(customer_name LIKE :search OR request_id = :request_id)";
    $params[':search'] = '%' . $search_term . '%';
    $params[':request_id'] = $search_term;
}

if (!empty($filter_status) && $filter_status !== 'All') {
    $sql_condition[] = "status = :status";
    $params[':status'] = $filter_status;
}

$where_clause = count($sql_condition) > 0 ? ' WHERE ' . implode(' AND ', $sql_condition) : '';

$sql = "SELECT request_id, customer_name, occasion, gift_size, wrapping_style, total_price, status, date_created FROM requests" . $where_clause . " ORDER BY date_created DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    $requests = [];
}

$status_options = ['All', 'Pending', 'In Progress', 'Completed', 'Claimed'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Requests | Gift Wrap System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div id="content-wrapper">
            <header>
                <h1>📋 Manage Gift Wrapping Requests</h1>
            </header>

            <main>
                <section class="request-filters">
                    <form method="GET" action="manage_requests.php" class="filter-form">
                        <input type="text" name="search" placeholder="Search by name or Order #" value="<?php echo htmlspecialchars($search_term); ?>">
                        
                        <select name="status">
                            <?php foreach ($status_options as $option): ?>
                                <option value="<?php echo $option; ?>" <?php echo ($filter_status === $option) ? 'selected' : ''; ?>>
                                    <?php echo $option; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <input type="submit" value="Filter" class="btn primary-btn">
                        <a href="manage_requests.php" class="btn secondary-btn">Clear Filters</a>
                    </form>
                </section>

                <section class="request-list">
                    <table class="data-table full-width">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer Name</th>
                                <th>Occasion</th>
                                <th>Size / Style</th>
                                <th>Price</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($requests)): ?>
                                <?php foreach ($requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                        <td><?php echo htmlspecialchars($request['customer_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['occasion']); ?></td>
                                        <td><?php echo htmlspecialchars($request['gift_size']) . ' / ' . htmlspecialchars($request['wrapping_style']); ?></td>
                                        <td><?php echo number_format($request['total_price'], 2); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($request['date_created'])); ?></td>
                                        <td><span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>"><?php echo htmlspecialchars($request['status']); ?></span></td>
                                        <td>
                                            <a href="view_request.php?id=<?php echo $request['request_id']; ?>" class="btn small-btn view-btn">View Details</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No requests found matching the current criteria.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>