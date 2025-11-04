<?php
require_once('../db_connect.php');

$error = '';
$success_id = null;
$styles = [];
$addons_list = [
    'Ribbons' => 20.00, 
    'Gift Card' => 15.00, 
    'Box' => 50.00, 
    'Flowers' => 80.00
];

try {
    $stmt = $pdo->query("SELECT * FROM wrapping_styles ORDER BY style_name");
    $styles = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "System configuration error. Please try again later.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['customer_name']);
    $contact = trim($_POST['contact_number']);
    $occasion = trim($_POST['occasion']);
    $size = trim($_POST['gift_size']);
    $style_name = trim($_POST['wrapping_style']);
    $message = trim($_POST['message_on_card']);
    $pickup_option = trim($_POST['pickup_option']);
    $preferred_time = trim($_POST['preferred_completion_time']);
    $selected_addons = isset($_POST['addons']) ? $_POST['addons'] : [];
    
    $total_price = 0.00;
    $addons_json = json_encode($selected_addons);

    $base_price_key = 'base_price_' . strtolower(str_replace(' ', '_', $size));
    
    $selected_style = null;
    foreach ($styles as $style) {
        if ($style['style_name'] === $style_name) {
            $selected_style = $style;
            break;
        }
    }

    if ($selected_style && isset($selected_style[$base_price_key])) {
        $total_price = (float)$selected_style[$base_price_key];
    } else {
        $error = "Invalid wrapping style or size selected.";
    }

    $total_addons_price = 0.00;
    $selected_addons_display = [];
    foreach ($selected_addons as $addon_name) {
        if (isset($addons_list[$addon_name])) {
            $total_addons_price += $addons_list[$addon_name];
            $selected_addons_display[$addon_name] = $addons_list[$addon_name];
        }
    }
    $total_price += $total_addons_price;
    $addons_json = json_encode($selected_addons_display);


    if (empty($error)) {
        try {
            $sql = "INSERT INTO requests (customer_name, contact_number, occasion, gift_size, wrapping_style, addons, message, pickup_option, preferred_time, total_price) 
                    VALUES (:name, :contact, :occasion, :size, :style, :addons, :message, :pickup, :time, :price)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':contact', $contact);
            $stmt->bindParam(':occasion', $occasion);
            $stmt->bindParam(':size', $size);
            $stmt->bindParam(':style', $style_name);
            $stmt->bindParam(':addons', $addons_json);
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':pickup', $pickup_option);
            $stmt->bindParam(':time', $preferred_time);
            $stmt->bindParam(':price', $total_price);
            
            if ($stmt->execute()) {
                $success_id = $pdo->lastInsertId();
                header("Location: confirmation.php?id=" . $success_id);
                exit;
            }
        } catch (PDOException $e) {
            $error = "Order submission failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Form | Gift Wrapping Service</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --primary-blue: #3E5A8E;
            --secondary-blue: #2A3B5C;
            --accent-orange: #FF7F50;
            --accent-red: #FF6347;
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
            max-width: 900px;
            background-color: var(--bg-white);
            border-radius: 15px;
            box-shadow: 0 10px 30px var(--shadow-light);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 90vh;
        }

        .kiosk-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .kiosk-header h2 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 700;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px var(--shadow-medium);
        }

        .kiosk-header p {
            font-size: 1.1em;
            opacity: 0.9;
            margin-top: 10px;
        }

        .alert.error {
            background-color: #ffe0e0;
            color: #cc0000;
            padding: 15px;
            margin: 20px;
            border-radius: 8px;
            border: 1px solid #ffaa99;
            text-align: center;
            font-weight: 500;
        }

        .request-form {
            padding: 30px;
            flex-grow: 1;
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        .request-form fieldset {
            border: 1px solid var(--border-light);
            border-radius: 10px;
            padding: 25px;
            margin: 0;
            background-color: #fcfcfc;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .request-form legend {
            font-size: 1.4em;
            font-weight: 600;
            color: var(--secondary-blue);
            padding: 0 10px;
            margin-left: -10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group:last-child {
            margin-bottom: 0;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text-medium);
            font-size: 1.05em;
        }

        .form-group input[type="text"],
        .form-group input[type="datetime-local"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-dark);
            box-sizing: border-box;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="datetime-local"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: var(--accent-orange);
            box-shadow: 0 0 0 3px rgba(255, 127, 80, 0.2);
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .radio-group, .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 10px;
        }

        .radio-group label, .checkbox-group label {
            background-color: #eef4fb;
            border: 1px solid #d0e0f0;
            padding: 12px 20px;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            user-select: none;
            color: var(--primary-blue);
            font-weight: 500;
        }

        .radio-group input[type="radio"],
        .checkbox-group input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.1);
            accent-color: var(--accent-orange);
        }

        .radio-group input[type="radio"]:checked + span,
        .checkbox-group input[type="checkbox"]:checked + span {
             font-weight: 600;
             color: var(--secondary-blue);
        }
        
        .radio-group label:hover,
        .checkbox-group label:hover {
            background-color: #e0ecf7;
            border-color: var(--primary-blue);
        }

        .radio-group input[type="radio"]:checked + span,
        .checkbox-group input[type="checkbox"]:checked + span {
            color: var(--accent-orange);
        }

        .size-options label {
            min-width: 100px;
            justify-content: center;
        }

        .price-summary {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 20px;
            border-radius: 10px;
            margin-top: 30px;
            text-align: center;
            font-size: 1.3em;
            font-weight: 600;
            border: 1px solid #a5d6a7;
        }

        .price-summary span {
            font-size: 1.4em;
            font-weight: 700;
            color: #1b5e20;
        }

        .form-action {
            margin-top: 30px;
            text-align: center;
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
            padding: 20px 40px;
            font-size: 1.6em;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 6px 15px rgba(255, 99, 71, 0.3);
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.5);
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

        @media (max-width: 768px) {
            .kiosk-container {
                border-radius: 0;
                min-height: 100vh;
            }

            .kiosk-header {
                padding: 25px 15px;
            }

            .kiosk-header h2 {
                font-size: 2em;
            }

            .request-form {
                padding: 20px;
            }

            .request-form fieldset {
                padding: 20px 15px;
            }

            .request-form legend {
                font-size: 1.2em;
            }

            .radio-group, .checkbox-group {
                flex-direction: column;
                gap: 10px;
            }
            
            .radio-group label, .checkbox-group label {
                width: 100%;
                justify-content: flex-start;
                border-radius: 8px;
            }

            .primary-btn {
                font-size: 1.4em;
                padding: 18px 30px;
            }
        }
    </style>
    <script>
        const PRICES = {};
        <?php foreach ($styles as $style): ?>
            PRICES['<?php echo $style['style_name']; ?>'] = {
                Small: <?php echo $style['base_price_small']; ?>,
                Medium: <?php echo $style['base_price_medium']; ?>,
                Large: <?php echo $style['base_price_large']; ?>,
                'Extra Large': <?php echo $style['base_price_xl']; ?>
            };
        <?php endforeach; ?>

        const ADDONS = <?php echo json_encode($addons_list); ?>;

        function updatePrice() {
            const size = document.querySelector('input[name="gift_size"]:checked')?.value;
            const style = document.querySelector('select[name="wrapping_style"]').value;
            const addons = document.querySelectorAll('input[name="addons[]"]:checked');
            let total = 0;

            if (size && style && PRICES[style] && PRICES[style][size]) {
                total += PRICES[style][size];
            }

            addons.forEach(addon => {
                total += ADDONS[addon.value];
            });

            document.getElementById('estimated_price').textContent = total.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[name="gift_size"]').forEach(input => input.addEventListener('change', updatePrice));
            document.querySelector('select[name="wrapping_style"]').addEventListener('change', updatePrice);
            document.querySelectorAll('input[name="addons[]"]').forEach(input => input.addEventListener('change', updatePrice));
            
            updatePrice(); 
        });
    </script>
</head>
<body class="kiosk-form-page">
    <div class="kiosk-container large-container">
        <header class="kiosk-header">
            <h2>Start Your Wrapping Request</h2>
            <p>Fill out the details below. Price calculation is real-time.</p>
        </header>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="request-form">
            
            <fieldset>
                <legend><i class="fas fa-user-circle"></i> Your Details</legend>
                <div class="form-group">
                    <label for="customer_name">Your Name</label>
                    <input type="text" name="customer_name" id="customer_name" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" id="contact_number" required>
                </div>
            </fieldset>

            <fieldset>
                <legend><i class="fas fa-box-open"></i> Gift Specifications</legend>
                <div class="form-group">
                    <label>Occasion</label>
                    <select name="occasion" required>
                        <option value=""> </option>
                        <option value="Birthday">Birthday</option>
                        <option value="Anniversary">Anniversary</option>
                        <option value="Wedding">Wedding</option>
                        <option value="Christmas">Christmas</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Gift Size</label>
                    <div class="radio-group size-options">
                        <label><input type="radio" name="gift_size" value="Small" required><span>Small</span></label>
                        <label><input type="radio" name="gift_size" value="Medium"><span>Medium</span></label>
                        <label><input type="radio" name="gift_size" value="Large"><span>Large</span></label>
                        <label><input type="radio" name="gift_size" value="Extra Large"><span>Extra Large</span></label>
                    </div>
                </div>

                <div class="form-group">
                    <label>Wrapping Style</label>
                    <select name="wrapping_style" required>
                        <option value=""> </option>
                        <?php foreach ($styles as $style): ?>
                            <option value="<?php echo htmlspecialchars($style['style_name']); ?>"><?php echo htmlspecialchars($style['style_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>

            <fieldset>
                <legend><i class="fas fa-ribbon"></i> Add-ons & Card</legend>
                <div class="form-group">
                    <label>Optional Add-ons</label>
                    <div class="checkbox-group">
                        <?php foreach ($addons_list as $addon => $price): ?>
                            <label><input type="checkbox" name="addons[]" value="<?php echo htmlspecialchars($addon); ?>"><span> <?php echo htmlspecialchars($addon); ?> (₱<?php echo number_format($price, 2); ?>)</span></label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label for="message_on_card">Message on Card (Optional)</label>
                    <textarea name="message_on_card" id="message_on_card" rows="3" placeholder="Enter a short message to be written on the gift card..."></textarea>
                </div>
            </fieldset>
            
            <fieldset>
                <legend><i class="fas fa-clock"></i> Pickup & Timing</legend>
                <div class="form-group">
                    <label>Pickup or Delivery</label>
                    <div class="radio-group">
                        <label><input type="radio" name="pickup_option" value="Pickup" required checked><span>Store Pickup</span></label>
                        <label><input type="radio" name="pickup_option" value="Delivery"><span>Local Delivery (Extra charges apply, handled in-store)</span></label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="preferred_completion_time">Preferred Completion Date/Time</label>
                    <input type="datetime-local" name="preferred_completion_time" id="preferred_completion_time" required>
                </div>
            </fieldset>

            <div class="price-summary">
                <h3>Estimated Total Price: ₱<span id="estimated_price">0.00</span></h3>
            </div>
            
            <div class="form-action">
                <button type="submit" class="btn primary-btn large-btn submit-btn">Confirm & Submit Request</button>
            </div>
        </form>
    </div>
</body>
</html>