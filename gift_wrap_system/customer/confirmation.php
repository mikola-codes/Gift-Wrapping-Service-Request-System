<?php
require_once('../db_connect.php');

$request_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$request = null;

if ($request_id <= 0) {
    header("Location: index.php");
    exit;
}

try {
    $sql = "SELECT request_id, customer_name, total_price, status FROM requests WHERE request_id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $request_id, PDO::PARAM_INT);
    $stmt->execute();
    $request = $stmt->fetch(PDO::FETCH_ASSOC); // Ensure associative array
} catch (PDOException $e) {
    $request = false; 
}

if (!$request) {
    header("Location: index.php?error=conf_fail");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation | Gift Wrapping Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #3E5A8E;
            --secondary-blue: #2A3B5C;
            --accent-orange: #FF7F50;
            --accent-red: #FF6347;
            --success-green: #28a745;
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
            max-width: 700px; /* Slightly narrower for confirmation */
            background-color: var(--bg-white);
            border-radius: 15px;
            box-shadow: 0 10px 30px var(--shadow-light);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 70vh; /* Adjust height for content */
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
            font-size: 2.8em;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px var(--shadow-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .kiosk-header h1 .fa-circle-check {
            color: var(--success-green); /* Green checkmark */
            font-size: 1.2em; /* Larger icon */
            filter: drop-shadow(0 0 5px rgba(0,255,0,0.5));
        }

        .kiosk-main.confirmation-details {
            flex-grow: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            background: url('https://via.placeholder.com/700x400/F0F8FF/DDEEFF?text=Subtle+Pattern') no-repeat center center;
            background-size: cover;
        }

        .kiosk-main.confirmation-details h2 {
            font-size: 2.2em;
            color: var(--primary-blue);
            margin-bottom: 20px;
            font-weight: 600;
        }

        .kiosk-main.confirmation-details p {
            font-size: 1.1em;
            margin-bottom: 15px;
            color: var(--text-medium);
        }

        .confirmation-box {
            background-color: #e8f5e9; /* Light green background */
            border: 2px solid var(--success-green);
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }

        .confirmation-box .label {
            font-size: 1.1em;
            color: var(--success-green);
            font-weight: 500;
            margin-bottom: 10px;
        }

        .request-id-display {
            font-size: 3.5em;
            font-weight: 800;
            color: var(--secondary-blue);
            letter-spacing: 2px;
            margin-bottom: 15px;
            background: linear-gradient(90deg, #d3e9d4, #b2d8b2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .request-id-display::before {
            content: '#';
            color: var(--success-green);
            font-size: 0.8em;
            margin-right: 5px;
            -webkit-text-fill-color: var(--success-green);
        }

        .order-summary {
            background-color: #f0f8ff; /* Light blue background */
            border: 1px solid #cceeff;
            border-radius: 10px;
            padding: 25px;
            margin-top: 25px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: left;
        }

        .order-summary p {
            margin-bottom: 10px;
            font-size: 1.05em;
            color: var(--text-medium);
        }

        .order-summary p strong {
            color: var(--text-dark);
        }

        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3cd; /* Light yellow */
            color: #856404; /* Dark yellow text */
            border: 1px solid #ffeeba;
        }

        .order-summary .note {
            font-size: 0.9em;
            color: #888;
            margin-top: 15px;
            font-style: italic;
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

        .primary-btn {
            background: linear-gradient(45deg, var(--accent-orange), var(--accent-red));
            color: #ffffff;
            box-shadow: 0 6px 15px rgba(255, 99, 71, 0.3);
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.5);
            max-width: 250px;
            flex: 1;
        }

        .primary-btn::before {
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

        .primary-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(255, 99, 71, 0.4);
            background: linear-gradient(45deg, var(--accent-red), var(--accent-orange));
        }

        .primary-btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(255, 99, 71, 0.2);
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
                font-size: 2.2em;
                gap: 10px;
            }

            .kiosk-header h1 .fa-circle-check {
                font-size: 1em;
            }

            .kiosk-main.confirmation-details {
                padding: 30px 20px;
            }

            .kiosk-main.confirmation-details h2 {
                font-size: 1.8em;
            }

            .confirmation-box {
                padding: 25px;
                margin: 20px 0;
            }

            .request-id-display {
                font-size: 3em;
            }

            .order-summary {
                padding: 20px;
            }

            .kiosk-footer {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }

            .btn {
                max-width: 90%;
                width: 100%;
                font-size: 1.1em;
            }

            .primary-btn {
                padding: 18px 30px;
            }

            .secondary-btn {
                padding: 12px 25px;
            }
        }
    </style>
</head>
<body class="kiosk-confirmation-page">
    <div class="kiosk-container">
        <header class="kiosk-header">
            <h1><i class="fas fa-circle-check"></i> Request Confirmed!</h1>
        </header>

        <main class="kiosk-main confirmation-details">
            <h2>Thank You, <?php echo htmlspecialchars($request['customer_name']); ?>!</h2>
            <p>Your gift wrapping request has been successfully submitted and is now in our system.</p>

            <div class="confirmation-box">
                <p class="label">Your Request Number:</p>
                <div class="request-id-display">
                    <?php echo htmlspecialchars($request['request_id']); ?>
                </div>
                <p>Please keep this number. It is required for tracking and pickup.</p>
            </div>

            <div class="order-summary">
                <p><strong>Total Estimated Price:</strong> ₱<?php echo number_format($request['total_price'], 2); ?></p>
                <p><strong>Current Status:</strong> <span class="status-badge status-pending"><?php echo htmlspecialchars($request['status']); ?></span></p>
                <p class="note">Staff will begin processing your request shortly.</p>
            </div>
            
        </main>

        <footer class="kiosk-footer">
            <a href="index.php" class="btn primary-btn">Start New Order</a>
            <a href="tracking.php" class="btn secondary-btn">Check Status</a>
        </footer>
    </div>
</body>
</html>
<?php 
unset($pdo); 
?>