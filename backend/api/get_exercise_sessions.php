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
    // Use direct JOINs instead of VIEW (Hosting compatibility)
    $stmt = $pdo->prepare("
        SELECT 
            s.session_id,
            s.session_number,
            s.session_date,
            s.heart_rate,
            s.bp_systolic,
            s.bp_diastolic,
            s.mets,
            s.exercise_method,
            s.recommendations,
            s.ekg_image_path,
            CONCAT(d.first_name, ' ', d.last_name) as doctor_name,
            CONCAT(t.first_name, ' ', t.last_name) as therapist_name,
            s.created_at
        FROM exercise_sessions s
        LEFT JOIN users d ON s.doctor_id = d.user_id
        LEFT JOIN users t ON s.therapist_id = t.user_id
        WHERE s.patient_id = :patient_id
        ORDER BY s.session_number ASC
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
