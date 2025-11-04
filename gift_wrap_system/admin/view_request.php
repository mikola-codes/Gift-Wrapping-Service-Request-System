<?php
require_once('includes/session.php');
require_once('../db_connect.php');

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$request = null;
$message = '';
$status_options = ['Pending', 'In Progress', 'Completed', 'Claimed'];

if ($request_id <= 0) {
    header("Location: manage_requests.php");
    exit;
}

// 1. Handle Status Update POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
    $new_status = trim($_POST['new_status']);
    
    if (in_array($new_status, $status_options)) {
        try {
            $sql = "UPDATE requests SET status = :status WHERE request_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':status', $new_status);
            $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
            $message = "<div class='alert success'>Order status updated to **" . htmlspecialchars($new_status) . "** successfully.</div>";
        } catch (PDOException $e) {
            $message = "<div class='alert error'>Error updating status: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='alert error'>Invalid status selected.</div>";
    }
}

// 2. Fetch Request Details (refetch after update)
try {
    $sql = "SELECT * FROM requests WHERE request_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $request = $stmt->fetch();
} catch (PDOException $e) {
    $message = "<div class='alert error'>Could not fetch request details.</div>";
}

if (!$request) {
    header("Location: manage_requests.php?error=notfound");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Request #<?php echo $request_id; ?> | Gift Wrap System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <div id="wrapper">
        <?php include('includes/sidebar.php'); ?>

        <div id="content-wrapper">
            <header>
                <h1>Request Details: #<?php echo $request['request_id']; ?></h1>
                <a href="manage_requests.php" class="btn secondary-btn back-btn">← Back to Requests</a>
            </header>

            <main>
                <?php echo $message; ?>

                <div class="detail-grid">
                    <div class="card detail-card status-section">
                        <h2>Order Status & Update</h2>
                        <p>Current Status: <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>"><?php echo htmlspecialchars($request['status']); ?></span></p>

                        <form method="POST" action="view_request.php?id=<?php echo $request_id; ?>" class="status-form">
                            <label for="new_status">Update Status:</label>
                            <select name="new_status" id="new_status">
                                <?php foreach ($status_options as $option): ?>
                                    <option value="<?php echo $option; ?>" <?php echo ($request['status'] === $option) ? 'disabled' : ''; ?>>
                                        <?php echo $option; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_status" class="btn primary-btn update-btn">Update</button>
                        </form>
                    </div>

                    <div class="card detail-card customer-info">
                        <h2>Customer Information</h2>
                        <p><strong>Name:</strong> <?php echo htmlspecialchars($request['customer_name']); ?></p>
                        <p><strong>Contact:</strong> <?php echo htmlspecialchars($request['contact_number']); ?></p>
                        <p><strong>Option:</strong> <?php echo htmlspecialchars($request['pickup_option']); ?></p>
                        <p><strong>Time:</strong> <?php echo date('M d, Y h:i A', strtotime($request['preferred_time'])); ?></p>
                    </div>

                    <div class="card detail-card order-specs">
                        <h2>Wrapping Specifications</h2>
                        <p><strong>Occasion:</strong> <?php echo htmlspecialchars($request['occasion']); ?></p>
                        <p><strong>Gift Size:</strong> <?php echo htmlspecialchars($request['gift_size']); ?></p>
                        <p><strong>Style:</strong> <?php echo htmlspecialchars($request['wrapping_style']); ?></p>
                        <p><strong>Add-ons:</strong> <?php echo empty($request['addons']) ? 'None' : htmlspecialchars($request['addons']); ?></p>
                        <p><strong>Card Message:</strong></p>
                        <blockquote class="message-box"><?php echo empty($request['message']) ? 'N/A' : nl2br(htmlspecialchars($request['message'])); ?></blockquote>
                    </div>
                    
                    <div class="card detail-card financial-info">
                        <h2>Financial & Dates</h2>
                        <p><strong>Total Price:</strong> ₱<?php echo number_format($request['total_price'], 2); ?></p>
                        <p><strong>Submitted:</strong> <?php echo date('M d, Y h:i A', strtotime($request['date_created'])); ?></p>
                        <p><strong>Last Update:</strong> <?php echo date('M d, Y h:i A', strtotime($request['date_updated'])); ?></p>
                        <button onclick="window.print()" class="btn print-btn">Print Work Order Slip</button>
                    </div>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>