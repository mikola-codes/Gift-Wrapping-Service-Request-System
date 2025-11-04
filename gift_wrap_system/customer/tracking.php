<?php
require_once('../db_connect.php');

$request_id = '';
$request = null;
$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = trim($_POST['request_id']);
    
    if (empty($request_id) || !is_numeric($request_id)) {
        $message = "<div class='alert error'>Please enter a valid Request Number.</div>";
    } else {
        try {
            $sql = "SELECT request_id, customer_name, status, date_created, total_price, preferred_time, gift_size, wrapping_style FROM requests WHERE request_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
            $stmt->execute();
            $request = $stmt->fetch(PDO::FETCH_ASSOC); // Ensure associative array

            if (!$request) {
                $message = "<div class='alert error'>Request ID #{$request_id} not found in our system.</div>";
            }
        } catch (PDOException $e) {
            $message = "<div class='alert error'>An error occurred during lookup. Please try again.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order | Gift Wrapping Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #3E5A8E;
            --secondary-blue: #2A3B5C;
            --accent-orange: #FF7F50;
            --accent-red: #FF6347;
            --status-pending: #ffc107; /* Orange/Yellow for pending */
            --status-processing: #17a2b8; /* Info blue for processing */
            --status-ready: #28a745; /* Success green for ready */
            --status-completed: #6c757d; /* Grey for completed */
            --status-cancelled: #dc3545; /* Danger red for cancelled */
            --text-dark: #333;
            --text-medium: #555;
            --text-light: #666;
            --bg-light: #f4f7f6;
            --bg-white: #ffffff;
            --border-light: #eee;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-medium: rgba(0, 0, 0, 0.2);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-dark);
            line-height: 1.6;
        }

        .kiosk-container {
            width: 100%;
            max-width: 700px;
            background-color: var(--bg-white);
            border-radius: 15px;
            box-shadow: 0 10px 30px var(--shadow-light);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 70vh;
        }

        .kiosk-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .kiosk-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px var(--shadow-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .kiosk-header h1 .fa-search {
            font-size: 0.9em;
            color: #9cdfe8;
        }

        .kiosk-main {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            align-items: center;
            text-align: center;
            background: url('https://via.placeholder.com/700x400/F0F8FF/DDEEFF?text=Subtle+Pattern') no-repeat center center;
            background-size: cover;
        }

        .kiosk-main p {
            font-size: 1.1em;
            margin-bottom: 25px;
            color: var(--text-medium);
        }

        .tracking-form {
            width: 100%;
            max-width: 400px;
            margin-bottom: 30px;
            background-color: var(--bg-white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-medium);
            font-size: 1.05em;
            text-align: left;
        }

        .form-group input[type="text"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1.1em;
            color: var(--text-dark);
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            text-align: center;
        }

        .form-group input[type="text"]:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(255, 127, 80, 0.2);
            outline: none;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px var(--shadow-light);
        }

        .primary-btn.track-btn {
            background: linear-gradient(45deg, var(--accent-orange), var(--accent-red));
            color: #ffffff;
            padding: 15px 35px;
            font-size: 1.4em;
            width: 100%;
            box-shadow: 0 6px 15px rgba(255, 99, 71, 0.3);
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .primary-btn.track-btn::before {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            bottom: 2px;
            border-radius: 6px;
            background: rgba(255,255,255,0.1);
            pointer-events: none;
        }

        .primary-btn.track-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 99, 71, 0.4);
            background: linear-gradient(45deg, var(--accent-red), var(--accent-orange));
        }

        .primary-btn.track-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(255, 99, 71, 0.2);
        }

        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            width: 100%;
            max-width: 450px;
            box-sizing: border-box;
        }

        .alert.error {
            background-color: #ffe0e0;
            color: #cc0000;
            border: 1px solid #ffaa99;
        }

        .tracking-result-box {
            background-color: #f0f8ff; /* Light blue background for results */
            border: 1px solid #cceeff;
            border-radius: 12px;
            padding: 30px;
            margin-top: 30px;
            width: 100%;
            max-width: 500px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            text-align: left;
        }

        .tracking-result-box h3 {
            font-size: 1.8em;
            color: var(--secondary-blue);
            margin-top: 0;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
        }

        .tracking-result-box h3::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: var(--accent-orange);
            margin: 10px auto 0 auto;
            border-radius: 2px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .status-indicator p {
            margin: 0;
            font-size: 1.1em;
            font-weight: 500;
            color: var(--text-dark);
        }

        .status-badge {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 1.2em;
            font-weight: 700;
            text-transform: capitalize;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            color: #fff; /* Default text color for badges */
        }

        .status-badge.status-pending { background-color: var(--status-pending); color: #856404; } /* Darker text for light bg */
        .status-badge.status-processing { background-color: var(--status-processing); }
        .status-badge.status-ready { background-color: var(--status-ready); }
        .status-badge.status-completed { background-color: var(--status-completed); }
        .status-badge.status-cancelled { background-color: var(--status-cancelled); }

        .order-details-mini {
            border-top: 1px solid var(--border-light);
            padding-top: 25px;
            margin-top: 25px;
        }

        .order-details-mini p {
            margin-bottom: 12px;
            font-size: 1em;
            color: var(--text-medium);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .order-details-mini p strong {
            color: var(--text-dark);
            min-width: 120px;
            margin-right: 10px;
        }
        
        .kiosk-footer {
            background-color: var(--secondary-blue);
            padding: 25px;
            text-align: center;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px var(--shadow-light);
        }

        .secondary-btn {
            background-color: #f0f0f0;
            color: var(--primary-blue);
            border: 1px solid #ccc;
            font-size: 1.1em;
            padding: 12px 25px;
            max-width: 200px;
            flex: 1;
        }

        .secondary-btn:hover {
            background-color: #e0e0e0;
            border-color: #bbb;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            .kiosk-container {
                border-radius: 0;
                min-height: 100vh;
            }

            .kiosk-header {
                padding: 25px 15px;
            }

            .kiosk-header h1 {
                font-size: 2em;
                gap: 10px;
            }

            .kiosk-main {
                padding: 30px 20px;
            }

            .tracking-form {
                padding: 20px;
            }

            .primary-btn.track-btn {
                font-size: 1.2em;
                padding: 15px 25px;
            }

            .tracking-result-box {
                padding: 25px;
            }

            .tracking-result-box h3 {
                font-size: 1.6em;
            }

            .status-indicator {
                flex-direction: column;
                gap: 10px;
            }

            .status-badge {
                font-size: 1.1em;
                padding: 8px 18px;
            }

            .order-details-mini p {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-details-mini p strong {
                width: 100%;
                margin-bottom: 5px;
            }

            .kiosk-footer {
                padding: 20px;
            }
        }
    </style>
</head>
<body class="kiosk-tracking-page">
    <div class="kiosk-container">
        <header class="kiosk-header">
            <h1><i class="fas fa-search"></i> Track Your Gift Wrapping Order</h1>
        </header>

        <main class="kiosk-main">
            <p>Enter your Request Number below to see the current status of your order.</p>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="tracking-form">
                <div class="form-group">
                    <label for="request_id">Request Number (Order #)</label>
                    <input type="text" name="request_id" id="request_id" value="<?php echo htmlspecialchars($request_id); ?>" placeholder="e.g., 1001" required>
                </div>
                <button type="submit" class="btn primary-btn track-btn">Check Status</button>
            </form>
            
            <?php echo $message; ?>

            <?php if ($request): ?>
                <div class="tracking-result-box">
                    <h3>Order #<?php echo htmlspecialchars($request['request_id']); ?> Status</h3>
                    <div class="status-indicator">
                        <p>Current Status:</p>
                        <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $request['status'])); ?>">
                            <?php echo htmlspecialchars($request['status']); ?>
                        </span>
                    </div>
                    
                    <div class="order-details-mini">
                        <p><strong>Customer:</strong> <span><?php echo htmlspecialchars($request['customer_name']); ?></span></p>
                        <p><strong>Style:</strong> <span><?php echo htmlspecialchars($request['gift_size']); ?> / <?php echo htmlspecialchars($request['wrapping_style']); ?></span></p>
                        <p><strong>Total:</strong> <span>₱<?php echo number_format($request['total_price'], 2); ?></span></p>
                        <p><strong>Expected Time:</strong> <span><?php echo date('M d, Y h:i A', strtotime($request['preferred_time'])); ?></span></p>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <footer class="kiosk-footer">
            <a href="index.php" class="btn secondary-btn">Start New Order</a>
        </footer>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>