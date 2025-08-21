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
$requiredFields = [
    'firstName', 'lastName', 'email', 'phone', 'address', 'password'
];

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
$password = $input['password'];


// Validate email
if (!isValidEmail($email)) {
    sendJSONResponse(false, 'Invalid email format');
}

// Validate password strength (simplified)
if (strlen($password) < 6) {
    sendJSONResponse(false, 'Password must be at least 6 characters long');
}



// Validate phone number (basic validation for Bangladesh)
$phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) < 10) {
    sendJSONResponse(false, 'Invalid phone number format');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        sendJSONResponse(false, 'Email address is already registered');
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user (using snake_case column names)
    $stmt = $pdo->prepare("
        INSERT INTO users (
            first_name, last_name, email, phone, address, password, 
            user_type
        ) VALUES (?, ?, ?, ?, ?, ?, 'customer')
    ");
    
    $stmt->execute([
        $firstName,
        $lastName,
        $email,
        $phone,
        $address,
        $hashedPassword
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Get the created user data (without password)
    $stmt = $pdo->prepare("
        SELECT id, first_name, last_name, email, phone, address, user_type, created_at
        FROM users 
        WHERE id = ?
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    // Normalize keys to support both snake_case and camelCase on the frontend
    if ($user) {
        $user['firstName'] = $user['first_name'];
        $user['lastName'] = $user['last_name'];
        $user['userType'] = $user['user_type'];
        $user['createdAt'] = $user['created_at'];
    }
    
    // Start session
    session_start();
    
    // Store user data in session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_type'] = $user['user_type'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    
    // Add session info
    $user['session_id'] = session_id();
    
    // Send welcome email (placeholder)
    // sendWelcomeEmail($email, $firstName);
    
    sendJSONResponse(true, 'Account created successfully! Welcome to AutoClean BD.', $user);
    
} catch (PDOException $e) {
    error_log("Registration error: " . $e->getMessage());
    sendJSONResponse(false, 'Registration failed. Please try again.');
}

// Function to send welcome email (placeholder)
function sendWelcomeEmail($email, $firstName) {
    // This would integrate with an email service like PHPMailer
    // For now, just log the email details
    error_log("Welcome email would be sent to: $email");
    error_log("Welcome message for: $firstName");
}
?> 