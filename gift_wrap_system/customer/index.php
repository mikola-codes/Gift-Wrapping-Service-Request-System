<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gift Wrapping Kiosk</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<style>
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7f6;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    color: #333;
}

.kiosk-container {
    width: 100%;
    max-width: 800px;
    background-color: #ffffff;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 90vh;
}

.kiosk-header {
    background: linear-gradient(135deg, #3E5A8E, #2A3B5C);
    color: #ffffff;
    padding: 30px 20px;
    text-align: center;
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}

.kiosk-logo {
    max-width: 120px;
    height: auto;
    margin-bottom: 15px;
    filter: drop-shadow(0 2px 5px rgba(0,0,0,0.3));
}

.kiosk-header h2 {
    margin: 0;
    font-size: 2.2em;
    font-weight: 700;
    letter-spacing: 1px;
    text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.kiosk-header .tagline {
    font-size: 1.1em;
    opacity: 0.8;
    margin-top: 10px;
    font-style: italic;
}

.kiosk-main {
    flex-grow: 1;
    padding: 30px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    background: url('https://via.placeholder.com/800x600/F0F8FF/DDEEFF?text=Subtle+Background+Pattern') no-repeat center center;
    background-size: cover;
    position: relative;
}

.kiosk-main .card {
    background-color: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 40px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    max-width: 500px;
    width: 100%;
}

.kiosk-main h1 {
    font-size: 2em;
    color: #2A3B5C;
    margin-bottom: 20px;
    font-weight: 600;
}

.instruction-text {
    font-size: 1.1em;
    line-height: 1.6;
    margin-bottom: 30px;
    color: #555;
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
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.btn i {
    margin-right: 10px;
    font-size: 1.3em;
}

.primary-btn {
    background: linear-gradient(45deg, #FF7F50, #FF6347);
    color: #ffffff;
    padding: 20px 40px;
    font-size: 1.6em;
    width: 100%;
    max-width: 380px;
    margin: 20px auto 40px auto;
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
    background: linear-gradient(45deg, #FF6347, #FF7F50);
}

.primary-btn:active {
    transform: translateY(0);
    box-shadow: 0 4px 10px rgba(255, 99, 71, 0.2);
}

.tracking-area {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.tracking-area p {
    font-size: 1em;
    margin-bottom: 15px;
    color: #666;
}

.secondary-btn {
    background-color: #f0f0f0;
    color: #3E5A8E;
    border: 1px solid #ccc;
    font-size: 1.1em;
    padding: 12px 25px;
    max-width: 250px;
    width: 100%;
    box-shadow: none;
}

.secondary-btn:hover {
    background-color: #e0e0e0;
    border-color: #bbb;
    transform: translateY(-2px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.kiosk-footer {
    background-color: #333;
    color: #aaa;
    text-align: center;
    padding: 20px;
    font-size: 0.9em;
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
}

@media (max-width: 600px) {
    .kiosk-container {
        border-radius: 0;
        min-height: 100vh;
    }

    .kiosk-header {
        padding: 20px 15px;
    }

    .kiosk-header h2 {
        font-size: 1.8em;
    }

    .kiosk-main {
        padding: 20px;
    }

    .kiosk-main .card {
        padding: 25px;
    }

    .kiosk-main h1 {
        font-size: 1.7em;
    }

    .primary-btn {
        font-size: 1.4em;
        padding: 18px 30px;
    }

    .btn i {
        font-size: 1.1em;
    }

    .secondary-btn {
        font-size: 1em;
        padding: 10px 20px;
    }
}
</style>
<body>
    <div class="kiosk-container">
        <header class="kiosk-header">
            <img src="image-removebg-preview.png" alt="Gift Wrapping Service Logo" class="kiosk-logo">
            <h2>Mikola's Gift Wrapping Service</h2>
            <p class="tagline">Fast, Easy, Elegant - Self-Service Kiosk</p>
        </header>

        <main class="kiosk-main">
            <div class="card">
                <h1>Welcome to Mikola's Gift Wrapping!</h1>
                <p class="instruction-text">Tap below to begin selecting your perfect wrapping options.</p>

                <a href="request_form.php" class="btn primary-btn giant-btn">
                    <i class="fas fa-gift"></i>
                    <span>Start New Order</span>
                </a>
                
                <div class="tracking-area">
                    <p>Already have an order?</p>
                    <a href="tracking.php" class="btn secondary-btn">
                        <i class="fas fa-search"></i>
                        <span>Track Order Status</span>
                    </a>
                </div>
            </div>
        </main>

        <footer class="kiosk-footer">
            <p>&copy; <?php echo date('Y'); ?> Mikola's Gift Wrapping Service.</p>
        </footer>
    </div>
</body>
</html>