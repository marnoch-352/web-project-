<?php
/**
 * Delete Patient API Endpoint
 * 
 * Allows doctors to delete patient records
 * WARNING: This will CASCADE delete all exercise sessions for this patient
 * Requires doctor authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db_config.php';

session_start();

// Check authentication - only doctors can delete patients
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Doctors only.']);
    exit();
}

// Only accept POST or DELETE methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get patient_id from request
$input = json_decode(file_get_contents('php://input'), true);
$patient_id = $input['patient_id'] ?? null;

// Validate patient_id
if (!$patient_id || !is_numeric($patient_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid patient ID']);
    exit();
}

try {
    // First, check if patient exists
    $checkStmt = $pdo->prepare("SELECT patient_id, first_name, last_name FROM patients WHERE patient_id = ?");
    $checkStmt->execute([$patient_id]);
    $patient = $checkStmt->fetch();
    
    if (!$patient) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
        exit();
    }
    
    // Delete patient (CASCADE will automatically delete all exercise_sessions)
    $deleteStmt = $pdo->prepare("DELETE FROM patients WHERE patient_id = ?");
    $deleteStmt->execute([$patient_id]);
    
    // Log the deletion
    error_log("Patient deleted: ID={$patient_id}, Name={$patient['first_name']} {$patient['last_name']}, By Doctor ID={$_SESSION['user_id']}");
    
    // Return success
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Patient and all related records deleted successfully',
        'patient_name' => $patient['first_name'] . ' ' . $patient['last_name']
    ]);
    
} catch (PDOException $e) {
    error_log("Delete Patient Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete patient. Please try again.'
    ]);
}
?>
