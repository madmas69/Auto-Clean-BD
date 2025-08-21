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
if (empty($input['email']) || empty($input['password'])) {
    sendJSONResponse(false, 'Email and password are required');
}

// Sanitize inputs
$email = sanitizeInput($input['email']);
$password = $input['password'];
$userType = isset($input['userType']) ? sanitizeInput($input['userType']) : 'customer';

// Validate email
if (!isValidEmail($email)) {
    sendJSONResponse(false, 'Invalid email format');
}

// Validate user type
if (!in_array($userType, ['customer', 'admin', 'worker'])) {
    sendJSONResponse(false, 'Invalid user type');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Query using snake_case column names (database schema)
    $stmt = $pdo->prepare("
        SELECT 
            id,
            first_name,
            last_name,
            email,
            phone,
            address,
            password,
            user_type
        FROM users 
        WHERE email = ? AND user_type = ?
    ");
    $stmt->execute([$email, $userType]);

    $user = $stmt->fetch();

if (!$user) {
    sendJSONResponse(false, 'Invalid email or user type');
}
    
    // Verify password
    if (!password_verify($password, $user['password'])) {
        sendJSONResponse(false, 'Invalid password');
    }
    
    // Start session
    session_start();
    
    // Store user data in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    
    // Prepare user data for response (exclude password)
    unset($user['password']);
    
    // Add frontend-friendly duplicates and session info
    $user['firstName'] = $user['first_name'];
    $user['lastName'] = $user['last_name'];
    $user['userType'] = $user['user_type'];
    $user['session_id'] = session_id();
    
    sendJSONResponse(true, 'Login successful!', $user);
    
} catch (PDOException $e) {
    error_log("Login error: " . $e->getMessage());
    sendJSONResponse(false, 'Login failed. Please try again.');
}
?> 