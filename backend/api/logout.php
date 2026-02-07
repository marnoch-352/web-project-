<?php
/**
 * Logout API Endpoint
 * 
 * Destroys user session and logs out
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

session_start();
session_destroy();

echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully'
]);
