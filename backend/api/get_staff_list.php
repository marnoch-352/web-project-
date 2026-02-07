<?php
/**
 * Get Staff List API
 * Returns lists of doctors and physical therapists
 */

header('Content-Type: application/json');
require_once '../db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['doctor', 'physical_therapist'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    // Get doctors
    $doctorStmt = $pdo->prepare("
        SELECT user_id, first_name, last_name
        FROM users
        WHERE role = 'doctor'
        ORDER BY first_name, last_name
    ");
    $doctorStmt->execute();
    $doctors = $doctorStmt->fetchAll();

    // Get physical therapists
    $therapistStmt = $pdo->prepare("
        SELECT user_id, first_name, last_name
        FROM users
        WHERE role = 'physical_therapist'
        ORDER BY first_name, last_name
    ");
    $therapistStmt->execute();
    $therapists = $therapistStmt->fetchAll();

    echo json_encode([
        'success' => true,
        'doctors' => $doctors,
        'therapists' => $therapists
    ]);
} catch (PDOException $e) {
    error_log("Get Staff List Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
