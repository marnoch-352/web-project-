<?php
/**
 * Get Next Session Number API
 * Returns the next available session number for a patient
 */

header('Content-Type: application/json');
require_once '../db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['doctor', 'physical_therapist'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

$patientId = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

if (!$patientId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Patient ID required']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT COALESCE(MAX(session_number), 0) + 1 AS next_session_number
        FROM exercise_sessions
        WHERE patient_id = :patient_id
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'next_session_number' => $result['next_session_number']
    ]);
} catch (PDOException $e) {
    error_log("Get Next Session Number Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
