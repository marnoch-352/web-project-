<?php
/**
 * Database Configuration for Cardiac Rehab System
 * 
 * This file contains the database connection settings and 
 * establishes a PDO connection for secure database operations.
 */

// Database credentials - Local Development (XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'cardiacrehabdb');
define('DB_USER', 'root');
define('DB_PASS', ''); // Empty password for XAMPP default

// PDO options for security and error handling
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Fetch as associative array
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Use real prepared statements
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"      // UTF-8 encoding
];

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        $options
    );
} catch (PDOException $e) {
    // Log error and show user-friendly message
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Don't expose database details to users
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Database connection failed. Please try again later.'
    ]));
}

/**
 * Helper function to hash passwords using bcrypt
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Helper function to verify passwords
 * @param string $password Plain text password
 * @param string $hash Stored hash
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Helper function to validate Thai National ID format
 * @param string $nationalId Thai National ID
 * @return bool True if valid format (13 digits)
 */
function validateThaiNationalId($nationalId) {
    // Check if exactly 13 digits
    return preg_match('/^\d{13}$/', $nationalId);
}
