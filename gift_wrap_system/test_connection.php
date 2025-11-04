<?php
// CHANGE THIS TO THE USERNAME AND PASSWORD YOU THINK IS CORRECT
$username = 'root'; 
$password = ''; // Try empty first, then try 'password', then try 'userpassword' or whatever you set

try {
    $pdo = new PDO("mysql:host=localhost;dbname=gift_wrapping_db", $username, $password);
    echo "<h1>✅ SUCCESS: Database connection established!</h1>";
} catch (PDOException $e) {
    die("<h1>❌ ERROR: Could not connect to the database.</h1>" . $e->getMessage());
}
?>