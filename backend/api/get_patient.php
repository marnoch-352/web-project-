<?php
header('Content-Type: application/json');
require_once '../db_config.php';
session_start();

// Check authentication - allow patients to view their own data
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

try {
    $stmt = $pdo->prepare("SELECT patient_id, phone, first_name, last_name, national_id FROM patients WHERE patient_id = :patient_id");
    $stmt->execute(['patient_id' => $patientId]);
    $patient = $stmt->fetch();

    if (!$patient) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
        exit();
    }

    $nationalId = $patient['national_id'];
    $patient['masked_id'] = substr($nationalId, 0, 1) . '-' . substr($nationalId, 1, 4) . '-' . substr($nationalId, 5, 5) . '-XX-X';
    unset($patient['national_id']);

    echo json_encode(['success' => true, 'patient' => $patient]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
