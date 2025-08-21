<?php
require_once 'config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJSONResponse(false, 'Invalid request method');
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJSONResponse(false, 'Invalid JSON input');
}

// Validate required fields
$requiredFields = ['name', 'phone'];

foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        sendJSONResponse(false, "Missing required field: $field");
    }
}

// Sanitize inputs
$name = sanitizeInput($input['name']);
$phone = sanitizeInput($input['phone']);
$email = isset($input['email']) ? sanitizeInput($input['email']) : '';

// Validate email if provided
if ($email && !isValidEmail($email)) {
    sendJSONResponse(false, 'Invalid email format');
}

// Validate phone number (basic validation for Bangladesh)
$phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) < 11) {
    sendJSONResponse(false, 'Invalid phone number format');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if phone number already exists
    $stmt = $pdo->prepare("SELECT id FROM workers WHERE phone = ?");
    $stmt->execute([$phone]);
    if ($stmt->fetch()) {
        sendJSONResponse(false, 'Phone number is already registered');
    }
    
    // Check if email already exists (if provided)
    if ($email) {
        $stmt = $pdo->prepare("SELECT id FROM workers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendJSONResponse(false, 'Email address is already registered');
        }
    }
    
    // Insert new worker
    $stmt = $pdo->prepare("
        INSERT INTO workers (name, phone, email, status) 
        VALUES (?, ?, ?, 'active')
    ");
    
    $stmt->execute([$name, $phone, $email]);
    
    $workerId = $pdo->lastInsertId();
    
    // Get the created worker data
    $stmt = $pdo->prepare("
        SELECT id, name, phone, email, status, completedJobs, createdAt
        FROM workers 
        WHERE id = ?
    ");
    $stmt->execute([$workerId]);
    $worker = $stmt->fetch();
    
    if (!$worker) {
        sendJSONResponse(false, 'Failed to retrieve created worker data');
    }
    
    sendJSONResponse(true, 'Worker added successfully!', $worker);
    
} catch (PDOException $e) {
    error_log("Add worker error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to add worker. Please try again.');
}
?> 