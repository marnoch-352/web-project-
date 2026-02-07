<?php
/**
 * Login API Endpoint
 * 
 * Handles authentication for doctors, physical therapists, and patients
 * Returns user data and appropriate redirect URL based on role
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db_config.php';

// Start session with 1 hour timeout
ini_set('session.gc_maxlifetime', 3600);
session_set_cookie_params(3600);
session_start();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!isset($input['username']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password are required']);
    exit();
}

$username = trim($input['username']);
$password = trim($input['password']);

if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username and password cannot be empty']);
    exit();
}

try {
    // First, try to find user in users table (doctors and therapists)
    $stmt = $pdo->prepare("
        SELECT user_id, username, password, first_name, last_name, role, 'staff' as user_type
        FROM users 
        WHERE username = :username
    ");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    // If not found in users, try patients table (phone number as username)
    if (!$user) {
        $stmt = $pdo->prepare("
            SELECT patient_id as user_id, phone as username, password, first_name, last_name, 'patient' as role, 'patient' as user_type
            FROM patients 
            WHERE phone = :username
        ");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
    }

    // User not found
    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit();
    }

    // Verify password
    if (!verifyPassword($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
        exit();
    }

    // Password verified - create session
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['login_time'] = time();

    // Determine redirect URL based on role
    $redirects = [
        'doctor' => '../../frontend/html/Doctor_dashboard.html',
        'physical_therapist' => '../../frontend/html/patient_search.html',
        'patient' => '../../frontend/html/exercise_history.html?patient_id=' . $user['user_id']
    ];

    $redirect = $redirects[$user['role']] ?? '../../frontend/index.html';

    // Success response
    echo json_encode([
        'success' => true,
        'role' => $user['role'],
        'user_id' => $user['user_id'],
        'name' => $_SESSION['name'],
        'redirect' => $redirect
    ]);

} catch (PDOException $e) {
    error_log("Login Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred during login']);
}
