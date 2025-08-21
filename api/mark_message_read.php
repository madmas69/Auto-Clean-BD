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

if (!$input || !isset($input['messageId'])) {
    sendJSONResponse(false, 'Message ID is required');
}

$messageId = (int)$input['messageId'];

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if message exists
    $stmt = $pdo->prepare("SELECT id FROM contact_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    if (!$stmt->fetch()) {
        sendJSONResponse(false, 'Message not found');
    }
    
    // Mark message as read
    $stmt = $pdo->prepare("UPDATE contact_messages SET isRead = 1 WHERE id = ?");
    $stmt->execute([$messageId]);
    
    if ($stmt->rowCount() === 0) {
        sendJSONResponse(false, 'Message is already marked as read');
    }
    
    sendJSONResponse(true, 'Message marked as read successfully!');
    
} catch (PDOException $e) {
    error_log("Mark message read error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to mark message as read');
}
?> 