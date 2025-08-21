<?php
require_once 'config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

try {
    // Check admin user
    $stmt = $pdo->prepare("SELECT id, first_name, last_name, email, password, user_type FROM users WHERE email = 'admin@autocleanbd.com'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo json_encode([
            'success' => true,
            'admin' => [
                'id' => $admin['id'],
                'name' => $admin['first_name'] . ' ' . $admin['last_name'],
                'email' => $admin['email'],
                'user_type' => $admin['user_type'],
                'password_hash' => $admin['password']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Admin user not found']);
    }
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 