<?php
/**
 * Check Session API Endpoint
 * 
 * Verifies if user is logged in and returns session data
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    echo json_encode([
        'logged_in' => false,
        'message' => 'Not authenticated'
    ]);
    exit();
}

// Check session timeout (1 hour)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 3600)) {
    session_destroy();
    echo json_encode([
        'logged_in' => false,
        'message' => 'Session expired'
    ]);
    exit();
}

// Update last activity time
$_SESSION['login_time'] = time();

// Return session data
echo json_encode([
    'logged_in' => true,
    'user_id' => $_SESSION['user_id'],
    'username' => $_SESSION['username'],
    'role' => $_SESSION['role'],
    'user_type' => $_SESSION['user_type'] ?? 'staff',
    'name' => $_SESSION['name'] ?? ''
]);
