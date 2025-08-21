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
    // Delete existing admin user
    $pdo->exec("DELETE FROM users WHERE email = 'admin@autocleanbd.com'");
    
    // Create new admin user with proper password hash
    $hashedPassword = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, phone, password, user_type, address) 
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'Admin',
        'User',
        'admin@autocleanbd.com',
        '+880 1712345678',
        $hashedPassword,
        'admin',
        'Bashundhara R/A, Dhaka'
    ]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Admin user created successfully',
        'admin' => [
            'email' => 'admin@autocleanbd.com',
            'password' => 'admin123',
            'user_type' => 'admin'
        ]
    ]);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?> 