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
$requiredFields = ['name', 'email', 'phone', 'message'];

foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        sendJSONResponse(false, "Missing required field: $field");
    }
}

// Sanitize inputs
$name = sanitizeInput($input['name']);
$email = sanitizeInput($input['email']);
$phone = sanitizeInput($input['phone']);
$message = sanitizeInput($input['message']);

// Validate email
if (!isValidEmail($email)) {
    sendJSONResponse(false, 'Invalid email format');
}

// Validate phone number (basic validation for Bangladesh)
$phone = preg_replace('/[^0-9]/', '', $phone);
if (strlen($phone) < 11) {
    sendJSONResponse(false, 'Invalid phone number format');
}

// Validate message length
if (strlen($message) < 10) {
    sendJSONResponse(false, 'Message must be at least 10 characters long');
}

if (strlen($message) > 1000) {
    sendJSONResponse(false, 'Message is too long (maximum 1000 characters)');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Insert contact message
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, message) 
        VALUES (?, ?, ?, ?)
    ");
    
    $stmt->execute([$name, $email, $phone, $message]);
    
    $messageId = $pdo->lastInsertId();
    
    // Send notification email to admin (placeholder)
    // sendContactNotificationEmail($name, $email, $phone, $message);
    
    // Send confirmation email to customer (placeholder)
    // sendContactConfirmationEmail($email, $name);
    
    sendJSONResponse(true, 'Message sent successfully! We will get back to you soon.');
    
} catch (PDOException $e) {
    error_log("Contact form error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to send message. Please try again.');
}

// Function to send notification email to admin (placeholder)
function sendContactNotificationEmail($name, $email, $phone, $message) {
    // This would integrate with an email service like PHPMailer
    // For now, just log the email details
    error_log("Contact notification email would be sent to admin");
    error_log("From: $name ($email)");
    error_log("Phone: $phone");
    error_log("Message: $message");
}

// Function to send confirmation email to customer (placeholder)
function sendContactConfirmationEmail($email, $name) {
    // This would integrate with an email service like PHPMailer
    // For now, just log the email details
    error_log("Contact confirmation email would be sent to: $email");
    error_log("Confirmation for: $name");
}
?> 