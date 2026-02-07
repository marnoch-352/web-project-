<?php
/**
 * Add Patient API Endpoint
 * 
 * Allows doctors to add new patient records with medical history
 * Requires doctor authentication
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once '../db_config.php';

session_start();

// Check authentication
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Doctors only.']);
    exit();
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate required fields
$required = ['phone', 'first_name', 'last_name', 'national_id'];
foreach ($required as $field) {
    if (!isset($input[$field]) || empty(trim($input[$field]))) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
        exit();
    }
}

// Get basic fields
$phone = trim($input['phone']);
$firstName = trim($input['first_name']);
$lastName = trim($input['last_name']);
$nationalId = trim($input['national_id']);

// Get optional medical history fields
$symptoms = isset($input['symptoms']) ? trim($input['symptoms']) : null;
$procedureHistory = isset($input['procedure_history']) ? trim($input['procedure_history']) : null;

// Get optional physical measurement fields (convert to int)
$weight = isset($input['weight']) && $input['weight'] !== '' ? intval($input['weight']) : null;
$height = isset($input['height']) && $input['height'] !== '' ? intval($input['height']) : null;
$age = isset($input['age']) && $input['age'] !== '' ? intval($input['age']) : null;

// Get CPET status (boolean)
$cpetCompleted = isset($input['cpet_completed']) ? (bool)$input['cpet_completed'] : false;

// Validate Thai National ID (13 digits)
if (!validateThaiNationalId($nationalId)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'National ID must be exactly 13 digits']);
    exit();
}

try {
    // Check if phone already exists
    $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE phone = :phone");
    $stmt->execute(['phone' => $phone]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Phone number already registered']);
        exit();
    }

    // Check if national_id already exists
    $stmt = $pdo->prepare("SELECT patient_id FROM patients WHERE national_id = :national_id");
    $stmt->execute(['national_id' => $nationalId]);
    if ($stmt->fetch()) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'National ID already registered']);
        exit();
    }

    // Hash the national_id as password
    $hashedPassword = hashPassword($nationalId);

    // Insert new patient with all fields
    $stmt = $pdo->prepare("
        INSERT INTO patients (
            phone, 
            national_id, 
            password, 
            first_name, 
            last_name, 
            symptoms, 
            procedure_history, 
            weight, 
            height, 
            age, 
            cpet_completed, 
            created_by
        )
        VALUES (
            :phone, 
            :national_id, 
            :password, 
            :first_name, 
            :last_name, 
            :symptoms, 
            :procedure_history, 
            :weight, 
            :height, 
            :age, 
            :cpet_completed, 
            :created_by
        )
    ");
    
    $stmt->execute([
        'phone' => $phone,
        'national_id' => $nationalId,
        'password' => $hashedPassword,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'symptoms' => $symptoms,
        'procedure_history' => $procedureHistory,
        'weight' => $weight,
        'height' => $height,
        'age' => $age,
        'cpet_completed' => $cpetCompleted,
        'created_by' => $_SESSION['user_id']
    ]);

    $patientId = $pdo->lastInsertId();

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Patient added successfully',
        'patient_id' => $patientId,
        'patient' => [
            'phone' => $phone,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'weight' => $weight,
            'height' => $height,
            'age' => $age,
            'cpet_completed' => $cpetCompleted
        ]
    ]);

} catch (PDOException $e) {
    error_log("Add Patient Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding patient', 'error' => $e->getMessage()]);
}
