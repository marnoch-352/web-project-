<?php
/**
 * Search Patients API Endpoint
 * 
 * Allows doctors and therapists to search for patients
 * Supports search by phone number
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

// Enable error logging
error_reporting(E_ALL);
ini_set('log_errors', 1);

require_once '../db_config.php';

session_start();

// Check authentication - only doctors and therapists
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['doctor', 'physical_therapist'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Doctors and therapists only.']);
    exit();
}

// Get search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';

try {
    if (empty($query)) {
        // Return empty array if no search query
        echo json_encode([
            'success' => true,
            'count' => 0,
            'patients' => []
        ]);
        exit();
    }
    
    // Search by phone number - JOIN tables directly instead of using VIEW
    // (Hosting often has issues with VIEWs)
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT 
            p.patient_id, 
            p.phone, 
            p.first_name, 
            p.last_name,
            p.national_id,
            CONCAT(u.first_name, ' ', u.last_name) as created_by_name,
            u.username as created_by_username,
            p.created_at
        FROM patients p
        LEFT JOIN users u ON p.created_by = u.user_id
        WHERE p.phone LIKE :search 
        ORDER BY p.created_at DESC
        LIMIT 100
    ");
    $stmt->execute(['search' => $searchTerm]);
    
    $patients = $stmt->fetchAll();
    
    // Process patients to add masked_id
    foreach ($patients as &$patient) {
        $nationalId = $patient['national_id'];
        // Create masked version: X-XXXX-XXXXX-XX-X
        if (strlen($nationalId) >= 13) {
            // Show first 8 digits, mask last 5 digits
            // Format example: 1-1234-123XX-XX-X
            $patient['masked_id'] = substr($nationalId, 0, 1) . '-' . 
                                    substr($nationalId, 1, 4) . '-' . 
                                    substr($nationalId, 5, 3) . 'XX-XX-X';
        } else {
            $patient['masked_id'] = 'Invalid ID';
        }
        // Remove the full national_id from response
        unset($patient['national_id']);
    }

    echo json_encode([
        'success' => true,
        'count' => count($patients),
        'patients' => $patients
    ]);

} catch (PDOException $e) {
    error_log("Search Patients Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred during search',
        'error' => $e->getMessage() // Only for debugging, remove in production
    ]);
}
