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

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Get all contact messages
    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            email,
            phone,
            message,
            isRead,
            createdAt
        FROM contact_messages
        ORDER BY createdAt DESC
    ");
    $stmt->execute();
    $messages = $stmt->fetchAll();
    
    // Get unread count
    $stmt = $pdo->prepare("SELECT COUNT(*) as unreadCount FROM contact_messages WHERE isRead = 0");
    $stmt->execute();
    $unreadCount = $stmt->fetch()['unreadCount'];
    
    // Format messages
    $formattedMessages = [];
    foreach ($messages as $message) {
        $formattedMessages[] = [
            'id' => $message['id'],
            'name' => $message['name'],
            'email' => $message['email'],
            'phone' => $message['phone'],
            'message' => $message['message'],
            'isRead' => (bool)$message['isRead'],
            'date' => date('M d, Y H:i', strtotime($message['createdAt'])),
            'createdAt' => $message['createdAt']
        ];
    }
    
    // Prepare response data
    $responseData = [
        'messages' => $formattedMessages,
        'unreadCount' => (int)$unreadCount,
        'totalCount' => count($formattedMessages)
    ];
    
    sendJSONResponse(true, 'Contact messages retrieved successfully', $responseData);
    
} catch (PDOException $e) {
    error_log("Get contact messages error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to retrieve contact messages');
}
?> 