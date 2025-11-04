<?php
// CRITICAL: Must be called first to start the session before any HTML output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../db_connect.php');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit;
}

$username = $password = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username)) {
        $error = "Please enter your username.";
    }
    if (empty($password)) {
        $error = "Please enter your password.";
    }

    if (empty($error)) {
        // Prepare a select statement
        // IMPORTANT: The 'password' column in your 'admin' table MUST store hashed passwords
        $sql = "SELECT admin_id, username, password, role FROM admin WHERE username = :username";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind parameters
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $param_username = $username;

            // Attempt to execute the prepared statement
            if ($stmt->execute()) {
                // Check if username exists, if yes then verify password
                if ($stmt->rowCount() == 1) {
                    $row = $stmt->fetch();
                    $hashed_password = $row['password']; // This should be the HASHED password from the DB

                    // *** IMPROVED LOGIN LOGIC: Using password_verify() for security ***
                    if (password_verify($password, $hashed_password)) {
                        // Password is correct, start a new session
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['admin_id'] = $row['admin_id'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['role'] = $row['role']; // Store role in session

                        // Redirect to admin dashboard page
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        // Display an error message if password is not valid
                        $error = "Invalid username or password.";
                    }
                    // *** END IMPROVED LOGIN LOGIC ***

                } else {
                    // Display an error message if username doesn't exist
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Oops! Something went wrong. Please try again later.";
            }
        }
        // Close statement
        unset($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login | Gift Wrap System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="login-page">
    <div class="login-container">
        <h2>Staff Login</h2>
        <p>Please enter your credentials to access the management dashboard.</p>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Login" class="btn primary-btn">
            </div>
        </form>
    </div>
</body>
</html>
<?php
// Close connection
unset($pdo);
?>