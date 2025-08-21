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
$requiredFields = ['name', 'description', 'carPrice', 'bikePrice', 'duration'];

foreach ($requiredFields as $field) {
    if (empty($input[$field])) {
        sendJSONResponse(false, "Missing required field: $field");
    }
}

// Sanitize inputs
$name = sanitizeInput($input['name']);
$description = sanitizeInput($input['description']);
$carPrice = (float)$input['carPrice'];
$bikePrice = (float)$input['bikePrice'];
$duration = (int)$input['duration'];

// Validate prices
if ($carPrice <= 0 || $bikePrice <= 0) {
    sendJSONResponse(false, 'Prices must be greater than zero');
}

// Validate duration
if ($duration <= 0) {
    sendJSONResponse(false, 'Duration must be greater than zero');
}

// Get database connection
$pdo = getDBConnection();
if (!$pdo) {
    sendJSONResponse(false, 'Database connection failed');
}

try {
    // Check if package name already exists
    $stmt = $pdo->prepare("SELECT id FROM service_packages WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->fetch()) {
        sendJSONResponse(false, 'Package name already exists');
    }
    
    // Insert new package
    $stmt = $pdo->prepare("
        INSERT INTO service_packages (name, description, carPrice, bikePrice, duration, isActive) 
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    
    $stmt->execute([$name, $description, $carPrice, $bikePrice, $duration]);
    
    $packageId = $pdo->lastInsertId();
    
    // Get the created package data
    $stmt = $pdo->prepare("
        SELECT id, name, description, carPrice, bikePrice, duration, isActive, createdAt
        FROM service_packages 
        WHERE id = ?
    ");
    $stmt->execute([$packageId]);
    $package = $stmt->fetch();
    
    if (!$package) {
        sendJSONResponse(false, 'Failed to retrieve created package data');
    }
    
    sendJSONResponse(true, 'Service package added successfully!', $package);
    
} catch (PDOException $e) {
    error_log("Add package error: " . $e->getMessage());
    sendJSONResponse(false, 'Failed to add service package. Please try again.');
}
?> 