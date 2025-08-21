<?php
require_once 'config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Test database connection
    $pdo = getDBConnection();
    
    if (!$pdo) {
        sendJSONResponse(false, 'Database connection failed');
    }
    
    // Get database info
    $dbInfo = $pdo->query("SELECT DATABASE() as db_name")->fetch();
    
    // Count tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    $totalTables = count($tables);
    
    // Check if required tables exist
    $requiredTables = ['users', 'service_packages', 'workers', 'bookings', 'reviews', 'contact_messages'];
    $existingTables = array_column($tables, 'Tables_in_' . $dbInfo['db_name']);
    $missingTables = array_diff($requiredTables, $existingTables);
    
    $data = [
        'database' => $dbInfo['db_name'],
        'total_tables' => $totalTables,
        'expected_tables' => count($requiredTables),
        'missing_tables' => array_values($missingTables),
        'server_time' => date('Y-m-d H:i:s'),
        'php_version' => PHP_VERSION
    ];
    
    if (empty($missingTables)) {
        sendJSONResponse(true, 'Database connection successful and all tables exist', $data);
    } else {
        sendJSONResponse(false, 'Database connected but some tables are missing', $data);
    }
    
} catch (Exception $e) {
    error_log("Test connection error: " . $e->getMessage());
    sendJSONResponse(false, 'Test failed: ' . $e->getMessage());
}
?> 