<?php
/**
 * Get Exercise Sessions API
 * Returns all exercise sessions for a patient
 */

header('Content-Type: application/json');
require_once '../db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$patientId = isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0;

if (!$patientId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Patient ID required']);
    exit();
}

// For patients, verify they can only access their own data
if ($_SESSION['role'] === 'patient' && $_SESSION['user_id'] != $patientId) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT 
            session_id,
            session_number,
            session_date,
            heart_rate,
            bp_systolic,
            bp_diastolic,
            mets,
            exercise_method,
            recommendations,
            ekg_image_path,
            doctor_name,
            therapist_name,
            created_at
        FROM exercise_sessions_with_staff
        WHERE patient_id = :patient_id
        ORDER BY session_number ASC
    ");
    
    $stmt->execute(['patient_id' => $patientId]);
    $sessions = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'sessions' => $sessions,
        'total' => count($sessions)
    ]);

} catch (PDOException $e) {
    error_log("Get Exercise Sessions Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
