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
$requiredFields = ['firstName', 'lastName', 'email', 'phone', 'address', 'vehicleType'];

foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        sendJSONResponse(false, "Missing required field: $field");
    }
}

// Sanitize inputs
$firstName = sanitizeInput($input['firstName']);
$lastName = sanitizeInput($input['lastName']);
$email = sanitizeInput($input['email']);
$phone = sanitizeInput($input['phone']);
$address = sanitizeInput($input['address']);
$vehicleType = sanitizeInput($input['vehicleType']);

// Validate email
if (!isValidEmail($email)) {
    sendJSONResponse(false, 'Invalid email format');
}

// Validate vehicle type
if (!in_array($vehicleType, ['car', 'bike', 'both'])) {
    sendJSONResponse(false, 'Invalid vehicle type');
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
    // Check if email already exists for another user
    $stmt = $pdo->prepare("
        SELECT id FROM users 
        WHERE email = ? AND id != ?
    ");
    $stmt->execute([$email, $input['userId'] ?? 0]);
    if ($stmt->fetch()) {
        sendJSONResponse(false, 'Email address is already registered by another user');
    }
    
    // Update user profile
    $stmt = $pdo->prepare("
        UPDATE users 
        SET 
            firstName = ?,
            lastName = ?,
            email = ?,
            phone = ?,
            address = ?,
            vehicleType = ?,
            updatedAt = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $userId = $input['userId'] ?? 0;
    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $phone,
        $address,
        $vehicleType,
        $userId
    ]);
    
    if ($stmt->rowCount() === 0) {
        sendJSONResponse(false, 'User not found or no changes made');
    }
    
    // Get updated user data
    $stmt = $pdo->prepare("
        SELECT id, firstName, lastName, email, phone, address, vehicleType, userType, createdAt, updatedAt
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        sendJSONResponse(false, 'Failed to retrieve updated user data');
    }
    
    sendJSONResponse(true, 'Profile updated successfully!', $user);
    
} catch (PDOException $e) {
    error_log("Profile update error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to update profile. Please try again.');
}
?> 