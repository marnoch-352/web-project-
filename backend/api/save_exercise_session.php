<?php
/**
 * Save Exercise Session API
 * Handles exercise session recording with EKG image upload
 */

header('Content-Type: application/json');
require_once '../db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['doctor', 'physical_therapist'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit();
}

// Validate required fields
$required = ['patient_id', 'session_date', 'heart_rate', 'bp_systolic', 'bp_diastolic', 
             'mets', 'exercise_method', 'recommendations', 'doctor_id', 'therapist_id'];

foreach ($required as $field) {
    if (!isset($_POST[$field]) || trim($_POST[$field]) === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Validate EKG image
if (!isset($_FILES['ekg_image']) || $_FILES['ekg_image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'EKG image is required']);
    exit();
}

$file = $_FILES['ekg_image'];
$allowedTypes = ['image/png', 'image/jpeg'];
$maxSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Only PNG and JPEG images are allowed']);
    exit();
}

if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
    exit();
}

try {
    $pdo->beginTransaction();

    $patientId = intval($_POST['patient_id']);

    // Get next session number
    $stmt = $pdo->prepare("
        SELECT COALESCE(MAX(session_number), 0) + 1 AS next_session_number
        FROM exercise_sessions
        WHERE patient_id = :patient_id
    ");
    $stmt->execute(['patient_id' => $patientId]);
    $sessionNumber = $stmt->fetch()['next_session_number'];

    // Create upload directory if not exists
    $uploadDir = dirname(__DIR__, 2) . '/uploads/ekg/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $patientId . '_' . $sessionNumber . '_' . time() . '.' . $extension;
    $uploadPath = $uploadDir . $filename;
    $dbPath = 'uploads/ekg/' . $filename;

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        throw new Exception('Failed to upload file');
    }

    // Insert session record
    $insertStmt = $pdo->prepare("
        INSERT INTO exercise_sessions (
            patient_id, session_number, session_date,
            heart_rate, bp_systolic, bp_diastolic, mets,
            exercise_method, recommendations, ekg_image_path,
            doctor_id, therapist_id
        ) VALUES (
            :patient_id, :session_number, :session_date,
            :heart_rate, :bp_systolic, :bp_diastolic, :mets,
            :exercise_method, :recommendations, :ekg_image_path,
            :doctor_id, :therapist_id
        )
    ");

    $insertStmt->execute([
        'patient_id' => $patientId,
        'session_number' => $sessionNumber,
        'session_date' => $_POST['session_date'],
        'heart_rate' => intval($_POST['heart_rate']),
        'bp_systolic' => intval($_POST['bp_systolic']),
        'bp_diastolic' => intval($_POST['bp_diastolic']),
        'mets' => floatval($_POST['mets']),
        'exercise_method' => trim($_POST['exercise_method']),
        'recommendations' => trim($_POST['recommendations']),
        'ekg_image_path' => $dbPath,
        'doctor_id' => intval($_POST['doctor_id']),
        'therapist_id' => intval($_POST['therapist_id'])
    ]);

    $sessionId = $pdo->lastInsertId();
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'session_id' => $sessionId,
        'session_number' => $sessionNumber,
        'message' => 'Exercise session recorded successfully'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Save Exercise Session Error: " . $e->getMessage());
    
    // Delete uploaded file if exists
    if (isset($uploadPath) && file_exists($uploadPath)) {
        unlink($uploadPath);
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save session: ' . $e->getMessage()]);
}
