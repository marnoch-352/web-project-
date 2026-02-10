<?php
// backend/test_db.php
// ใช้ไฟล์นี้เพื่อทดสอบการเชื่อมต่อ Database และดู Error เต็มๆ

// Show all errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Database Connection Test</h1>";

// 1. Check Config
echo "<h2>1. Configuration</h2>";
$host = 'localhost';
$dbname = 'cardiacrehabdb';
$user = 'root';
$pass = '';

echo "Host: $host<br>";
echo "DB Name: $dbname<br>";
echo "User: $user<br>";
echo "Password: (empty)<br>";

// 2. Try Connecting (Default Port 3306)
echo "<h2>2. Testing Port 3306 (Default)</h2>";
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    echo "<h3 style='color:green'>✅ Connection Success! (Port 3306)</h3>";
} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ Connection Failed:</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
}

// 3. Try Connecting (Port 3307 - Common Alternative)
echo "<h2>3. Testing Port 3307 (Alternative)</h2>";
try {
    $pdo = new PDO("mysql:host=$host;port=3307;dbname=$dbname;charset=utf8mb4", $user, $pass);
    echo "<h3 style='color:green'>✅ Connection Success! (Port 3307)</h3>";
    echo "Please update db_config.php to use port 3307.";
} catch (PDOException $e) {
    echo "<h3 style='color:red'>❌ Connection Failed:</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
